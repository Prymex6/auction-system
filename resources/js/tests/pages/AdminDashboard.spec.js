import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'

const getMock = vi.fn()
const patchMock = vi.fn().mockResolvedValue({ data: {} })
const deleteMock = vi.fn().mockResolvedValue({ data: {} })

vi.mock('@/services/api', () => ({
  default: {
    get: (...args) => getMock(...args),
    post: vi.fn().mockResolvedValue({ data: {} }),
    patch: (...args) => patchMock(...args),
    delete: (...args) => deleteMock(...args),
  },
}))

vi.mock('@/composables/useSeo', () => ({ useSeo: vi.fn() }))
vi.mock('@/components/CategoriesManagement.vue', () => ({ default: { template: '<div />' } }))
vi.mock('@/components/PagesManagement.vue', () => ({ default: { template: '<div />' } }))
vi.mock('@/components/layout/HeroSection.vue', () => ({ default: { template: '<div />' } }))
vi.mock('@/components/atomic/StatCard.vue', () => ({ default: { template: '<div />' } }))

import AdminDashboard from '@/pages/AdminDashboard.vue'

describe('AdminDashboard.vue — panel ustawień platformy', () => {
  let router

  beforeEach(async () => {
    getMock.mockReset()
    patchMock.mockClear().mockResolvedValue({ data: {} })
    deleteMock.mockClear().mockResolvedValue({ data: {} })
    getMock.mockResolvedValue({ data: { data: [] } })

    router = createRouter({
      history: createWebHistory(),
      routes: [{ path: '/admin', component: AdminDashboard }],
    })
    router.push('/admin')
    await router.isReady()
  })

  it('pobiera ustawienia platformy z /settings, NIE z nieistniejącego /admin/settings', async () => {
    getMock.mockImplementation((url) => {
      if (url === '/settings') {
        return Promise.resolve({
          data: { data: { bidding_enabled: false, platform_name: 'Gołębiowy Lot' } },
        })
      }
      if (url === '/admin/settings') {
        return Promise.reject({ response: { status: 404 } })
      }
      return Promise.resolve({ data: { data: [] } })
    })

    mount(AdminDashboard, {
      global: { plugins: [router], stubs: { transition: false } },
    })
    await flushPromises()

    const calledUrls = getMock.mock.calls.map((c) => c[0])
    expect(calledUrls).toContain('/settings')
    expect(calledUrls).not.toContain('/admin/settings')
  })

  it('zakladka Bledy laduje liste z /admin/error-logs i pozwala oznaczyc jako rozwiazany', async () => {
    const errorLog = {
      id: 1,
      exception_class: 'RuntimeException',
      message: 'Coś się zepsuło',
      file: 'app/Services/Foo.php',
      line: 42,
      occurrences: 3,
      last_seen_at: '2026-07-28T12:00:00Z',
      resolved: false,
      trace: '#0 Foo.php(42): bar()',
    }
    getMock.mockImplementation((url) => {
      if (url === '/admin/error-logs') {
        return Promise.resolve({ data: { data: { data: [errorLog] } } })
      }
      return Promise.resolve({ data: { data: [] } })
    })

    const wrapper = mount(AdminDashboard, {
      global: { plugins: [router], stubs: { transition: false } },
    })
    await flushPromises()

    await wrapper
      .findAll('button')
      .find((b) => b.text().includes('Błędy'))
      .trigger('click')
    await flushPromises()

    expect(wrapper.text()).toContain('RuntimeException')
    expect(wrapper.text()).toContain('Coś się zepsuło')

    const resolveButton = wrapper
      .findAll('button')
      .find((b) => b.text().includes('Oznacz jako rozwiązany'))
    expect(resolveButton).toBeTruthy()
    await resolveButton.trigger('click')
    await flushPromises()

    expect(patchMock).toHaveBeenCalledWith('/admin/error-logs/1/resolve')
  })

  it('przycisk Aktywuj jest wyszarzony/wylaczony gdy user nie potwierdzil e-maila', async () => {
    // niedostepna.
    const unverifiedUser = {
      id: 1,
      name: 'niepotwierdzony',
      is_active: false,
      email_verified_at: null,
      auctions_count: 0,
      reputation: 0,
    }
    const verifiedUser = {
      id: 2,
      name: 'potwierdzony',
      is_active: false,
      email_verified_at: '2026-07-01T00:00:00Z',
      auctions_count: 0,
      reputation: 0,
    }

    getMock.mockImplementation((url) => {
      if (url === '/admin/users') {
        return Promise.resolve({ data: { data: [unverifiedUser, verifiedUser] } })
      }
      return Promise.resolve({ data: { data: [] } })
    })

    const wrapper = mount(AdminDashboard, {
      global: { plugins: [router], stubs: { transition: false } },
    })
    await flushPromises()

    await wrapper
      .findAll('button')
      .find((b) => b.text().includes('Użytkownicy'))
      ?.trigger('click')
    await flushPromises()

    const activateButtons = wrapper.findAll('button').filter((b) => b.text().includes('Aktywuj'))
    expect(activateButtons.length).toBe(2)
    expect(activateButtons[0].attributes('disabled')).toBeDefined()
    expect(activateButtons[1].attributes('disabled')).toBeUndefined()
  })

  it('modal edycji uzytkownika ma przycisk usuwania konta, ktory wola DELETE /admin/users/{id}', async () => {
    const user = {
      id: 5,
      name: 'do_usuniecia',
      is_active: true,
      email_verified_at: '2026-07-01T00:00:00Z',
      auctions_count: 0,
      reputation: 0,
    }

    getMock.mockImplementation((url) => {
      if (url === '/admin/users') {
        return Promise.resolve({ data: { data: [user] } })
      }
      return Promise.resolve({ data: { data: [] } })
    })

    const wrapper = mount(AdminDashboard, {
      global: { plugins: [router], stubs: { transition: false } },
    })
    await flushPromises()

    await wrapper
      .findAll('button')
      .find((b) => b.text().includes('Użytkownicy'))
      ?.trigger('click')
    await flushPromises()

    await wrapper
      .findAll('button')
      .find((b) => b.text().includes('Edytuj'))
      .trigger('click')
    await flushPromises()

    const deleteButton = wrapper
      .findAll('button')
      .find((b) => b.attributes('title') === 'Usuń konto użytkownika')
    expect(deleteButton).toBeTruthy()
    await deleteButton.trigger('click')
    await flushPromises()

    const confirmButton = wrapper.findAll('button').find((b) => b.text().trim() === 'Usuń')
    expect(confirmButton).toBeTruthy()
    await confirmButton.trigger('click')
    await flushPromises()

    expect(deleteMock).toHaveBeenCalledWith('/admin/users/5')
  })

  it('otwiera zakladke Aukcje gdy w URL jest ?tab=auctions (deep-link z powiadomienia push)', async () => {
    router = createRouter({
      history: createWebHistory(),
      routes: [{ path: '/admin', component: AdminDashboard }],
    })
    router.push('/admin?tab=auctions')
    await router.isReady()

    const wrapper = mount(AdminDashboard, {
      global: { plugins: [router], stubs: { transition: false } },
    })
    await flushPromises()

    expect(wrapper.text()).toContain('Zarządzanie aukcjami')
  })
})
