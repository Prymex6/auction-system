import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

vi.mock('@/stores/auth', () => ({
  useAuthStore: () => ({ isAuthenticated: false, user: null, token: null }),
}))

vi.mock('@/stores/notification', () => ({
  useNotificationStore: () => ({ unreadCount: 0, fetchUnreadCount: vi.fn() }),
}))

vi.mock('@/stores/message', () => ({
  useMessageStore: () => ({ fetchUnreadCount: vi.fn() }),
}))

vi.mock('@/services/api', () => ({
  default: {
    get: vi.fn().mockResolvedValue({ data: { data: {} } }),
    post: vi.fn().mockResolvedValue({}),
  },
}))

vi.mock('@/components/CookieConsent.vue', () => ({ default: { template: '<div />' } }))
vi.mock('@/components/ToastNotifications.vue', () => ({ default: { template: '<div />' } }))

import App from '@/App.vue'
import router, { routeReady } from '@/router'

describe('App.vue — timing stopki względem nawigacji', () => {
  beforeEach(() => {
    routeReady.value = false
  })

  it('nie pokazuje stopki dopóki nawigacja się nie zakończy (routeReady=false)', async () => {
    router.push('/')

    const wrapper = mount(App, {
      global: { plugins: [router], stubs: { IconPicker: true, RouterView: true } },
    })

    expect(routeReady.value).toBe(false)
    expect(wrapper.find('footer').exists()).toBe(false)
  })

  it('pokazuje stopkę dopiero gdy routeReady=true (po zakończeniu nawigacji)', async () => {
    router.push('/')
    await router.isReady()
    await flushPromises()

    const wrapper = mount(App, {
      global: { plugins: [router], stubs: { IconPicker: true, RouterView: true } },
    })
    await flushPromises()

    expect(routeReady.value).toBe(true)
    expect(wrapper.find('footer').exists()).toBe(true)
  })
})

describe('App.vue — wyszukiwarka w gornym menu', () => {
  beforeEach(() => {
    routeReady.value = false
  })

  it('nawiguje do /auctions z parametrem "q" (nie "search") - tylko ten czyta AuctionList.vue', async () => {
    router.push('/')
    await router.isReady()
    await flushPromises()

    const wrapper = mount(App, {
      global: { plugins: [router], stubs: { IconPicker: true, RouterView: true } },
    })
    await flushPromises()

    const pushSpy = vi.spyOn(router, 'push')
    const searchInput = wrapper.find('input[type="search"]')
    await searchInput.setValue('warszawski')
    searchInput.element.dispatchEvent(new KeyboardEvent('keyup', { key: 'Enter', bubbles: true }))

    expect(pushSpy).toHaveBeenCalledWith({ name: 'AuctionList', query: { q: 'warszawski' } })
  })
})
