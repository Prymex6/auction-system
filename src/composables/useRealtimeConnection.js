import { onMounted, onUnmounted } from 'vue'
import { useRealtime } from './useRealtime'
import { useAuctionStore } from '@/stores/auction'
import { useMessageStore } from '@/stores/message'

/**
 * Setupuje i czyszcza subskrypcje na mount/unmount.
 *
 *
 * @param {Object} options - Opcje
 * @param {boolean} options.auctions - listen for auction changes
 * @param {number} options.auctionId - id of the auction to listen to
 * @param {boolean} options.messages - listen to one open conversation; needs otherUserId
 * @param {number} options.otherUserId - id of the other person in that conversation
 * @returns {Object} - disconnect
 *
 * @example
 * useRealtimeConnection({ auctionId: route.params.id, auctions: true })
 *
 * useRealtimeConnection({ messages: true, otherUserId: currentConversationUserId.value })
 */
export const useRealtimeConnection = (options = {}) => {
  const { connect } = useRealtime()
  const auctionStore = useAuctionStore()
  const messageStore = useMessageStore()

  const { auctions = false, auctionId = null, messages = false, otherUserId = null } = options

  onMounted(async () => {
    try {
      await connect()

      if (auctions) {
        auctionStore.listenAuctionsListUpdates()

        if (auctionId) {
          auctionStore.listenAuctionUpdates(auctionId)
        }
      }

      if (messages && otherUserId) {
        messageStore.listenConversation(otherUserId)
      }
    } catch (err) {
      console.error('Failed to initialize real-time connection:', err)
    }
  })

  onUnmounted(() => {
    try {
      if (auctions) {
        auctionStore.unlistenAll()
      }

      if (messages && otherUserId) {
        messageStore.unlistenChannel(`messages.${otherUserId}`)
      }
    } catch (err) {
      console.error('Failed to cleanup real-time connection:', err)
    }
  })

  const disconnect = () => {
    auctionStore.unlistenAll()
    if (otherUserId) {
      messageStore.unlistenChannel(`messages.${otherUserId}`)
    }
  }

  return {
    disconnect,
  }
}
