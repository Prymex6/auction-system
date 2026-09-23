import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'

const registerMock = vi.fn()

vi.mock('@/stores/auth', () => ({
  useAuthStore: () => ({
    register: registerMock,
  }),
}))

vi.mock('@/composables/useSeo', () => ({ useSeo: vi.fn() }))

import Register from '@/pages/Register.vue'

describe('Register.vue — walidacja formularza rejestracji', () => {
  let router

  beforeEach(async () => {
    registerMock.mockReset()
    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div />' } },
        { path: '/register', component: Register },
        { path: '/login', component: { template: '<div />' } },
        { path: '/terms', component: { template: '<div />' } },
        { path: '/privacy', component: { template: '<div />' } },
      ],
    })
    router.push('/register')
    await router.isReady()
  })

  function mountPage() {
    return mount(Register, { global: { plugins: [router] } })
  }

  async function fillValidForm(wrapper) {
    await wrapper.find('input[placeholder="Jan"]').setValue('Jan')
    await wrapper.find('input[placeholder="Kowalski"]').setValue('Kowalski')
    await wrapper.find('input[placeholder="jankowalski"]').setValue('jankowalski123')
    await wrapper.find('input[placeholder="you@example.com"]').setValue('jan@example.com')
    await wrapper.findAll('input[type="password"]')[0].setValue('TestHaslo123!')
    await wrapper.findAll('input[type="password"]')[1].setValue('TestHaslo123!')
  }

  it('nie wysyła rejestracji, gdy checkbox regulaminu nie jest zaznaczony', async () => {
    const wrapper = mountPage()
    await fillValidForm(wrapper)
    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(registerMock).not.toHaveBeenCalled()
    expect(wrapper.text()).toContain('Musisz zaakceptować warunki korzystania')
  })

  it('nie wysyła rejestracji, gdy hasła się nie zgadzają', async () => {
    const wrapper = mountPage()
    await fillValidForm(wrapper)
    await wrapper.findAll('input[type="password"]')[1].setValue('InneHaslo123!')
    await wrapper.find('input[type="checkbox"]').setValue(true)
    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(registerMock).not.toHaveBeenCalled()
    expect(wrapper.text()).toContain('Hasła się nie zgadzają')
  })

  it('wysyła rejestrację i przekierowuje po poprawnym wypełnieniu formularza', async () => {
    registerMock.mockResolvedValue({ user: { id: 1 } })
    const pushSpy = vi.spyOn(router, 'push')

    const wrapper = mountPage()
    await fillValidForm(wrapper)
    await wrapper.find('input[type="checkbox"]').setValue(true)
    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(registerMock).toHaveBeenCalledWith(
      expect.objectContaining({ name: 'jankowalski123', email: 'jan@example.com' })
    )
    expect(pushSpy).toHaveBeenCalledWith('/')
  })

  it('pokazuje komunikat błędu z backendu przy rejestracji z zajętym mailem', async () => {
    registerMock.mockRejectedValue({
      response: { data: { message: 'Taka wartość pola adres e-mail już istnieje.' } },
    })

    const wrapper = mountPage()
    await fillValidForm(wrapper)
    await wrapper.find('input[type="checkbox"]').setValue(true)
    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(wrapper.text()).toContain('adres e-mail już istnieje')
  })
})
