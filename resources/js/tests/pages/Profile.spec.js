import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'

const patchMock = vi.fn().mockResolvedValue({ data: {} })
const getMock = vi.fn().mockResolvedValue({ data: { data: {} } })
const deleteMock = vi.fn().mockResolvedValue({ data: {} })

vi.mock('@/services/api', () => ({
  default: {
    get: (...args) => getMock(...args),
    post: vi.fn().mockResolvedValue({ data: {} }),
    patch: (...args) => patchMock(...args),
    delete: (...args) => deleteMock(...args),
  },
}))

const mockUser = {
  id: 1,
  name: 'jankowalski',
  email: 'jan@example.com',
  is_public: true,
  is_admin: false,
}
const logoutMock = vi.fn()

vi.mock('@/stores/auth', () => ({
  useAuthStore: () => ({
    user: mockUser,
    logout: logoutMock,
  }),
}))

vi.mock('@/composables/useSeo', () => ({ useSeo: vi.fn() }))
vi.mock('@/composables/useToast', () => ({
  useToast: () => ({ showSuccess: vi.fn(), showError: vi.fn() }),
}))

import Profile from '@/pages/Profile.vue'

describe('Profile.vue — przełącznik prywatności profilu', () => {
  let router

  beforeEach(async () => {
    patchMock.mockClear().mockResolvedValue({ data: {} })
    getMock.mockClear().mockResolvedValue({ data: { data: {} } })
    deleteMock.mockClear().mockResolvedValue({ data: {} })
    logoutMock.mockClear()

    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/profile', component: Profile },
        { path: '/login', component: { template: '<div />' } },
        { path: '/settings', component: { template: '<div />' } },
        { path: '/', component: { template: '<div />' } },
      ],
    })
    router.push('/profile')
    await router.isReady()
  })

  function mountPage() {
    return mount(Profile, {
      global: {
        plugins: [router],
        stubs: { transition: false, ConfirmModal: true },
      },
    })
  }

  it('wysyła is_public: false do /profile po odznaczeniu przełącznika i zapisaniu', async () => {
    const wrapper = mountPage()
    await flushPromises()

    const tabs = wrapper.findAll('button')
    const settingsTab = tabs.find(
      (b) => b.text().includes('Ustawienia') || b.text().includes('Preferencje')
    )
    if (settingsTab) await settingsTab.trigger('click')
    await flushPromises()

    // szosty (indeks 5) to "Publiczny profil" (is_public).
    const checkboxes = wrapper.findAll('input[type="checkbox"]')
    const toggle = checkboxes[5]
    expect(toggle.exists()).toBe(true)
    expect(toggle.element.checked).toBe(true)

    await toggle.setValue(false)

    const saveButton = wrapper
      .findAll('button')
      .find((b) => b.text().includes('Zapisz preferencje'))
    expect(saveButton).toBeTruthy()
    await saveButton.trigger('click')
    await flushPromises()

    expect(patchMock).toHaveBeenCalledWith('/profile', { is_public: false })
    expect(mockUser.is_public).toBe(false)
  })

  async function openSettingsTab(wrapper) {
    const tabs = wrapper.findAll('button')
    const settingsTab = tabs.find(
      (b) => b.text().includes('Ustawienia') || b.text().includes('Preferencje')
    )
    if (settingsTab) await settingsTab.trigger('click')
    await flushPromises()
  }

  it('przycisk "Usuń konto" otwiera modal potwierdzenia hasłem', async () => {
    const wrapper = mountPage()
    await flushPromises()
    await openSettingsTab(wrapper)

    expect(wrapper.text()).not.toContain('Ta operacja jest nieodwracalna')

    const deleteButton = wrapper.findAll('button').find((b) => b.text().includes('Usuń konto'))
    expect(deleteButton).toBeTruthy()
    await deleteButton.trigger('click')

    expect(wrapper.text()).toContain('Ta operacja jest nieodwracalna')
  })

  it('potwierdzenie usunięcia konta wysyła DELETE /profile z hasłem i wylogowuje', async () => {
    const wrapper = mountPage()
    await flushPromises()
    await openSettingsTab(wrapper)

    const deleteButton = wrapper.findAll('button').find((b) => b.text().includes('Usuń konto'))
    await deleteButton.trigger('click')

    const passwordInputs = wrapper.findAll('input[type="password"]')
    const passwordInput = passwordInputs[passwordInputs.length - 1]
    expect(passwordInput.exists()).toBe(true)
    await passwordInput.setValue('mojehaslo123')

    const confirmButton = wrapper.findAll('button').find((b) => b.text() === 'Usuń konto')
    await confirmButton.trigger('click')
    await flushPromises()

    expect(deleteMock).toHaveBeenCalledWith('/profile', { data: { password: 'mojehaslo123' } })
    expect(logoutMock).toHaveBeenCalled()
    expect(wrapper.text()).not.toContain('Ta operacja jest nieodwracalna')
  })
})
