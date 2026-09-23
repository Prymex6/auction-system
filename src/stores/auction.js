import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useRealtime } from '@/composables/useRealtime'

export const useAuctionStore = defineStore('auction', () => {
  const auctions = ref([])
  const currentAuction = ref(null)
  const currentBids = ref([])
  const loading = ref(false)
  const error = ref(null)
  const realtimeChannels = ref({})
  const pagination = ref({
    currentPage: 1,
    perPage: 15,
    total: 0,
    totalPages: 0,
  })

  const fetchAuctions = async (page = 1, filters = {}) => {
    loading.value = true
    error.value = null
    try {
      const params = {
        page,
        per_page: pagination.value.perPage,
        ...filters,
      }
      const response = await api.get('/auctions', { params })
      auctions.value = response.data.data || []
      pagination.value = {
        currentPage: response.data.meta?.current_page || 1,
        perPage: response.data.meta?.per_page || 15,
        total: response.data.meta?.total || 0,
        totalPages: response.data.meta?.last_page || 1,
      }
      return {
        data: auctions.value,
        pagination: pagination.value,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch auctions'
      console.error('Fetch auctions error:', err)
      return {
        data: [],
        pagination: pagination.value,
      }
    } finally {
      loading.value = false
    }
  }

  const fetchAuction = async (id) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.get(`/auctions/${id}`)
      currentAuction.value = response.data.data || response.data
      return currentAuction.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch auction'
      throw err
    } finally {
      loading.value = false
    }
  }

  const searchAuctions = async (query, filters = {}) => {
    loading.value = true
    error.value = null
    try {
      const params = {
        query,
        per_page: 15,
        ...filters,
      }
      const response = await api.get('/auctions/search', { params })
      auctions.value = response.data.data
      pagination.value = {
        currentPage: response.data.current_page,
        perPage: response.data.per_page,
        total: response.data.total,
        totalPages: response.data.last_page,
      }
    } catch (err) {
      error.value = err.response?.data?.message || 'Search failed'
    } finally {
      loading.value = false
    }
  }

  const placeBid = async (auctionId, amount) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post(`/bids/auction/${auctionId}`, { amount })
      currentAuction.value = response.data.auction
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Bid placement failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const getBids = async (auctionId) => {
    try {
      const response = await api.get(`/auctions/${auctionId}/bids`)
      const bidsData = response.data?.data || response.data || []

      const mappedBids = bidsData.map((bid) => ({
        ...bid,
        bidder_name: bid.bidder?.name || 'Nieznany',
        bidder_id: bid.bidder?.id,
        user_id: bid.bidder?.id,
        created_at: bid.placed_at || bid.created_at,
        bidder_full_name: bid.bidder_full_name || bid.bidder?.name,
        bidder_phone: bid.bidder_phone || null,
        bidder_email: bid.bidder_email || null,
      }))

      currentBids.value = mappedBids
      return mappedBids
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to fetch bids'
      throw err
    }
  }

  /**
   * @param {number} auctionId - ID aukcji
   */
  const listenAuctionUpdates = (auctionId) => {
    const { listenPublic, unsubscribe } = useRealtime()

    listenPublic(`auction.${auctionId}`, '.bid.placed', (data) => {
      if (currentAuction.value?.id === auctionId) {
        currentAuction.value.current_price = data.amount
        currentAuction.value.bids_count = (currentAuction.value.bids_count || 0) + 1

        if (currentBids.value) {
          currentBids.value.unshift({
            id: data.bid_id,
            amount: data.amount,
            bidder_id: data.bidder_id,
            created_at: new Date().toISOString(),
            is_winning: true,
          })
        }
      }

      const auctionIndex = auctions.value.findIndex((a) => a.id === auctionId)
      if (auctionIndex !== -1) {
        auctions.value[auctionIndex].current_price = data.amount
        auctions.value[auctionIndex].bids_count = (auctions.value[auctionIndex].bids_count || 0) + 1
      }
    })

    listenPublic(`auction.${auctionId}`, '.auction.ended', (data) => {
      if (currentAuction.value?.id === auctionId) {
        currentAuction.value.status = 'ended'
        currentAuction.value.winner_id = data.winner_id
      }

      const auctionIndex = auctions.value.findIndex((a) => a.id === auctionId)
      if (auctionIndex !== -1) {
        auctions.value[auctionIndex].status = 'ended'
      }
    })

    realtimeChannels.value[`auction.${auctionId}`] = () => unsubscribe(`auction.${auctionId}`)
  }

  const listenAuctionsListUpdates = () => {
    const { listenPublic, unsubscribe } = useRealtime()

    listenPublic('auctions', 'AuctionCreated', (data) => {
      auctions.value.unshift(data.auction)
    })

    listenPublic('auctions', 'AuctionStatusChanged', (data) => {
      const index = auctions.value.findIndex((a) => a.id === data.auction_id)
      if (index !== -1) {
        auctions.value[index].status = data.status
      }
    })

    realtimeChannels.value['auctions'] = () => unsubscribe('auctions')
  }

  /**
   * @param {string} channelName - channel name
   */
  const unlistenChannel = (channelName) => {
    if (realtimeChannels.value[channelName]) {
      realtimeChannels.value[channelName]()
      delete realtimeChannels.value[channelName]
    }
  }

  const unlistenAll = () => {
    Object.keys(realtimeChannels.value).forEach((channelName) => {
      unlistenChannel(channelName)
    })
  }

  return {
    auctions,
    currentAuction,
    currentBids,
    loading,
    error,
    pagination,
    realtimeChannels,
    fetchAuctions,
    fetchAuction,
    searchAuctions,
    placeBid,
    getBids,
    listenAuctionUpdates,
    listenAuctionsListUpdates,
    unlistenChannel,
    unlistenAll,
  }
})
