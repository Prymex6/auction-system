import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'

const getMock = vi.fn()
const postMock = vi.fn().mockResolvedValue({ data: {} })
const deleteMock = vi.fn().mockResolvedValue({ data: {} })

vi.mock('@/services/api', () => ({
  default: {
    get: (...args) => getMock(...args),
    post: (...args) => postMock(...args),
    delete: (...args) => deleteMock(...args),
  },
}))

vi.mock('@/stores/auth', () => ({
  useAuthStore: () => ({
    isAuthenticated: true,
    user: { id: 999, is_active: true },
  }),
}))

vi.mock('@/composables/useSeo', () => ({ useSeo: vi.fn() }))
vi.mock('@/composables/useToast', () => ({
  useToast: () => ({ showSuccess: vi.fn(), showError: vi.fn() }),
}))

import UserProfile from '@/pages/UserProfile.vue'

const viewedUser = { id: 5, name: 'inny_user', is_blocked_by_viewer: false }

describe('UserProfile.vue — blokowanie użytkownika', () => {
  let router

  beforeEach(async () => {
    getMock.mockReset()
    postMock.mockClear()
    deleteMock.mockClear()
    getMock.mockImplementation((url) => {
      if (url === '/users/5') return Promise.resolve({ data: { data: { ...viewedUser } } })
      if (url === '/users/5/auctions') return Promise.resolve({ data: { data: [] } })
      return Promise.resolve({ data: { data: [] } })
    })

    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/users/:id', component: UserProfile },
        { path: '/messages', component: { template: '<div />' } },
        { path: '/auctions', component: { template: '<div />' } },
      ],
    })
    router.push('/users/5')
    await router.isReady()
  })

  function mountPage() {
    return mount(UserProfile, { global: { plugins: [router] } })
  }

  it('klika "Zablokuj użytkownika" -> woła POST /users/5/block i zmienia etykietę na "Odblokuj"', async () => {
    const wrapper = mountPage()
    await flushPromises()

    const button = wrapper.findAll('button').find((b) => b.text().includes('Zablokuj użytkownika'))
    expect(button).toBeTruthy()

    await button.trigger('click')
    await flushPromises()

    expect(postMock).toHaveBeenCalledWith('/users/5/block')
    expect(wrapper.text()).toContain('Odblokuj użytkownika')
  })

  it('klika "Odblokuj użytkownika" gdy juz zablokowany -> woła DELETE /users/5/block', async () => {
    getMock.mockImplementation((url) => {
      if (url === '/users/5')
        return Promise.resolve({ data: { data: { ...viewedUser, is_blocked_by_viewer: true } } })
      if (url === '/users/5/auctions') return Promise.resolve({ data: { data: [] } })
      return Promise.resolve({ data: { data: [] } })
    })

    const wrapper = mountPage()
    await flushPromises()

    const button = wrapper.findAll('button').find((b) => b.text().includes('Odblokuj użytkownika'))
    expect(button).toBeTruthy()

    await button.trigger('click')
    await flushPromises()

    expect(deleteMock).toHaveBeenCalledWith('/users/5/block')
    expect(wrapper.text()).toContain('Zablokuj użytkownika')
  })

  it('nie pokazuje przycisku blokowania na swoim wlasnym profilu', async () => {
    getMock.mockImplementation((url) => {
      if (url === '/users/5') return Promise.resolve({ data: { data: { ...viewedUser, id: 999 } } })
      if (url === '/users/5/auctions') return Promise.resolve({ data: { data: [] } })
      return Promise.resolve({ data: { data: [] } })
    })

    const wrapper = mountPage()
    await flushPromises()

    const button = wrapper
      .findAll('button')
      .find((b) => b.text().includes('Zablokuj') || b.text().includes('Odblokuj'))
    expect(button).toBeFalsy()
  })

  it('nie pokazuje przycisku "Wyslij wiadomosc" na swoim wlasnym profilu (regresja: mozna bylo pisac do siebie)', async () => {
    getMock.mockImplementation((url) => {
      if (url === '/users/5') return Promise.resolve({ data: { data: { ...viewedUser, id: 999 } } })
      if (url === '/users/5/auctions') return Promise.resolve({ data: { data: [] } })
      return Promise.resolve({ data: { data: [] } })
    })

    const wrapper = mountPage()
    await flushPromises()

    const link = wrapper.findAll('a').find((a) => a.text().includes('Wyślij wiadomość'))
    expect(link).toBeFalsy()
  })
})

describe('UserProfile.vue — ocena gwiazdkowa reputacji', () => {
  let router

  beforeEach(async () => {
    getMock.mockReset()
    postMock.mockClear()
    getMock.mockImplementation((url) => {
      if (url === '/users/5') return Promise.resolve({ data: { data: { ...viewedUser } } })
      if (url === '/users/5/auctions') return Promise.resolve({ data: { data: [] } })
      if (url === '/users/5/stats')
        return Promise.resolve({ data: { data: { average_rating: 4.5, total_reviews: 2 } } })
      if (url === '/users/5/reviews') return Promise.resolve({ data: { data: [] } })
      return Promise.resolve({ data: { data: [] } })
    })

    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/users/:id', component: UserProfile },
        { path: '/messages', component: { template: '<div />' } },
        { path: '/auctions', component: { template: '<div />' } },
      ],
    })
    router.push('/users/5')
    await router.isReady()
  })

  it('wyswietla srednia ocene i liczbe recenzji pobrane ze /stats', async () => {
    const wrapper = mount(UserProfile, { global: { plugins: [router] } })
    await flushPromises()

    expect(wrapper.text()).toContain('4.5')
    expect(wrapper.text()).toContain('2 ocen')
  })

  it('klikniecie gwiazdki wysyla POST /users/5/rate z wybrana wartoscia', async () => {
    postMock.mockResolvedValue({ data: { average_rating: 5, total_reviews: 3 } })
    const wrapper = mount(UserProfile, { global: { plugins: [router] } })
    await flushPromises()

    // Druga (prawa) polowka piatej gwiazdki inputu = ocena 5
    const starButtons = wrapper.findAll('button[aria-label^="Oceń na"]')
    expect(starButtons.length).toBe(10) // 5 gwiazdek x 2 polowki
    const fullFifthStar = starButtons.find((b) => b.attributes('aria-label') === 'Oceń na 5')
    await fullFifthStar.trigger('click')
    await flushPromises()

    expect(postMock).toHaveBeenCalledWith('/users/5/rate', { rating: 5 })
  })
})
