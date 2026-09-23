import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const grantAnalyticsMock = vi.fn()
const denyAnalyticsMock = vi.fn()

vi.mock('@/composables/useAnalytics', () => ({
  useAnalytics: () => ({ grantAnalytics: grantAnalyticsMock, denyAnalytics: denyAnalyticsMock }),
}))

vi.mock('@/composables/usePlatformSettings', () => ({
  usePlatformSettings: () => ({ loadSettings: vi.fn().mockResolvedValue(undefined) }),
}))

import CookieConsent from '@/components/CookieConsent.vue'

const CONSENT_KEY = 'pigeon_cookie_consent_v1'

describe('CookieConsent.vue', () => {
  let resizeObserverObserve

  beforeEach(() => {
    localStorage.clear()
    grantAnalyticsMock.mockClear()
    denyAnalyticsMock.mockClear()
    document.documentElement.style.removeProperty('--cookie-banner-h')

    resizeObserverObserve = vi.fn()
    global.ResizeObserver = vi.fn().mockImplementation(() => ({
      observe: resizeObserverObserve,
      disconnect: vi.fn(),
    }))
  })

  afterEach(() => {
    delete global.ResizeObserver
  })

  it('pokazuje baner gdy brak zapisanej zgody i zapisuje "akceptuj wszystkie" do localStorage', async () => {
    const wrapper = mount(CookieConsent, {
      global: { stubs: { transition: false, 'router-link': true } },
    })
    await flushPromises()

    expect(wrapper.text()).toContain('Szanujemy Twoją prywatność')

    const acceptAllBtn = wrapper
      .findAll('button')
      .find((b) => b.text().includes('Akceptuję wszystkie'))
    await acceptAllBtn.trigger('click')

    const stored = JSON.parse(localStorage.getItem(CONSENT_KEY))
    expect(stored.necessary).toBe(true)
    expect(stored.analytics).toBe(true)
    expect(grantAnalyticsMock).toHaveBeenCalled()
    expect(wrapper.text()).not.toContain('Szanujemy Twoją prywatność')
  })

  it('"Tylko niezbędne" zapisuje analytics: false i wywołuje denyAnalytics', async () => {
    const wrapper = mount(CookieConsent, {
      global: { stubs: { transition: false, 'router-link': true } },
    })
    await flushPromises()

    const necessaryBtn = wrapper.findAll('button').find((b) => b.text().includes('Tylko niezbędne'))
    await necessaryBtn.trigger('click')

    const stored = JSON.parse(localStorage.getItem(CONSENT_KEY))
    expect(stored.analytics).toBe(false)
    expect(denyAnalyticsMock).toHaveBeenCalled()
  })

  it('nie pokazuje banera ponownie, gdy zgoda jest już zapisana w localStorage', async () => {
    localStorage.setItem(
      CONSENT_KEY,
      JSON.stringify({ necessary: true, analytics: true, savedAt: new Date().toISOString() })
    )

    const wrapper = mount(CookieConsent, {
      global: { stubs: { transition: false, 'router-link': true } },
    })
    await flushPromises()

    expect(wrapper.text()).not.toContain('Szanujemy Twoją prywatność')
    expect(grantAnalyticsMock).toHaveBeenCalled()
  })

  it('ustawia zmienną CSS --cookie-banner-h na wysokość bannera, a po odrzuceniu resetuje ją do 0', async () => {
    const wrapper = mount(CookieConsent, {
      global: { stubs: { transition: false, 'router-link': true } },
      attachTo: document.body,
    })
    await flushPromises()

    expect(resizeObserverObserve).toHaveBeenCalled()

    const necessaryBtn = wrapper.findAll('button').find((b) => b.text().includes('Tylko niezbędne'))
    await necessaryBtn.trigger('click')
    await flushPromises()

    wrapper.unmount()
    expect(document.documentElement.style.getPropertyValue('--cookie-banner-h')).toBe('0px')
  })
})
