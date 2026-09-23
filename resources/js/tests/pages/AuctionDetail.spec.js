import { describe, it, expect, vi, beforeEach } from 'vitest'
import { flushPromises, mount } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'

const mockAuction = {
  id: 1,
  title: 'PL-0216-26-4844',
  description: 'Piękny gołąb pocztowy',
  status: 'active',
  type: 'buy_now',
  gender: 'male',
  breed: 'Gołąb pocztowy',
  current_price: 500,
  start_price: 500,
  bids_count: 0,
  views: 0,
  created_at: '2026-01-01T00:00:00Z',
  ends_at: '2027-01-01T00:00:00Z',
  pigeon_images: ['/storage/images/auction/pigeon-1.jpg'],
  pedigree_images: [
    '/storage/images/auction/pedigree-1.jpg',
    '/storage/images/auction/pedigree-2.jpg',
  ],
  seller: { id: 2, name: 'Jan Kowalski' },
}

const fetchAuctionMock = vi.fn().mockResolvedValue(mockAuction)
const getBidsMock = vi.fn().mockResolvedValue([])

vi.mock('@/stores/auction', () => ({
  useAuctionStore: () => ({
    fetchAuction: fetchAuctionMock,
    getBids: getBidsMock,
    placeBid: vi.fn(),
    listenAuctionsListUpdates: vi.fn(),
    listenAuctionUpdates: vi.fn(),
    unlistenAll: vi.fn(),
  }),
}))

const mockAuthState = { user: null, isAuthenticated: false }

vi.mock('@/stores/auth', () => ({
  useAuthStore: () => mockAuthState,
}))

vi.mock('@/stores/message', () => ({
  useMessageStore: () => ({
    listenPrivateMessages: vi.fn(),
    listenConversation: vi.fn(),
    unlistenAll: vi.fn(),
  }),
}))

vi.mock('@/stores/notification', () => ({
  useNotificationStore: () => ({ listenNotifications: vi.fn(), unlistenAll: vi.fn() }),
}))

vi.mock('@/composables/useRealtime', () => ({
  useRealtime: () => ({
    connect: vi.fn().mockResolvedValue(undefined),
    useRealtimeConnection: vi.fn(),
  }),
}))

vi.mock('@/services/api', () => ({
  default: {
    get: vi.fn().mockResolvedValue({ data: { data: {} } }),
    post: vi.fn().mockResolvedValue({ data: {} }),
  },
}))

import AuctionDetail from '@/pages/AuctionDetail.vue'

describe('AuctionDetail.vue', () => {
  let router

  beforeEach(async () => {
    fetchAuctionMock.mockClear().mockResolvedValue(mockAuction)
    getBidsMock.mockClear().mockResolvedValue([])
    mockAuthState.user = null
    mockAuthState.isAuthenticated = false

    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div />' } },
        { path: '/auctions', component: { template: '<div />' } },
        { path: '/auctions/:id', component: AuctionDetail },
        { path: '/login', component: { template: '<div />' } },
        { path: '/profile/:id', component: { template: '<div />' } },
      ],
    })
    router.push('/auctions/1')
    await router.isReady()
  })

  function mountPage() {
    return mount(AuctionDetail, {
      global: {
        plugins: [router],
        stubs: {
          IconPicker: true,
          GlobalLoadingOverlay: true,
          ConfirmModal: true,
        },
      },
    })
  }

  it('renders every pedigree image returned by the API', async () => {
    const wrapper = mountPage()
    await flushPromises()

    const pedigreeImgs = wrapper.findAll('img[alt="Rodowód"]')
    expect(pedigreeImgs).toHaveLength(2)
    expect(pedigreeImgs[0].attributes('src')).toBe('/storage/images/auction/pedigree-1.jpg')
    expect(pedigreeImgs[1].attributes('src')).toBe('/storage/images/auction/pedigree-2.jpg')
  })

  it('does not render a pedigree section when pedigree_images is empty', async () => {
    fetchAuctionMock.mockResolvedValue({ ...mockAuction, pedigree_images: [] })
    const wrapper = mountPage()
    await flushPromises()

    expect(wrapper.findAll('img[alt="Rodowód"]')).toHaveLength(0)
  })

  it('renders the main pigeon photo from pigeon_images', async () => {
    const wrapper = mountPage()
    await flushPromises()

    const mainImg = wrapper.find('img[alt="Gołąb pocztowy"]')
    expect(mainImg.exists()).toBe(true)
    expect(mainImg.attributes('src')).toBe('/storage/images/auction/pigeon-1.jpg')
  })

  it('regresja: nie pokazuje dwoch identycznych przyciskow "Wyślij wiadomość" na ofercie kup-teraz', async () => {
    mockAuthState.isAuthenticated = true
    mockAuthState.user = { id: 999, is_active: true }

    const wrapper = mountPage()
    await flushPromises()

    const sendMessageButtons = wrapper
      .findAll('button')
      .filter((b) => b.text().includes('Wyślij wiadomość'))
    expect(sendMessageButtons).toHaveLength(1)
  })
})
