import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'

const loginMock = vi.fn()

vi.mock('@/stores/auth', () => ({
  useAuthStore: () => ({
    login: loginMock,
    user: null,
  }),
}))

vi.mock('@/composables/useSeo', () => ({ useSeo: vi.fn() }))

import Login from '@/pages/Login.vue'

describe('Login.vue — komunikaty błędów logowania', () => {
  let router

  beforeEach(async () => {
    loginMock.mockReset()
    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/', component: { template: '<div />' } },
        { path: '/login', component: Login },
        { path: '/register', component: { template: '<div />' } },
        { path: '/contact', component: { template: '<div />' } },
      ],
    })
    router.push('/login')
    await router.isReady()
  })

  function mountPage() {
    return mount(Login, { global: { plugins: [router] } })
  }

  it('pokazuje treść z pola "error" w odpowiedzi 401 (a nie tylko ogólny fallback)', async () => {
    loginMock.mockRejectedValue({
      response: { status: 401, data: { error: 'Niepoprawne dane logowania' } },
    })

    const wrapper = mountPage()
    await wrapper.find('input[type="text"]').setValue('ktos')
    await wrapper.find('input[type="password"]').setValue('zlehaslo')
    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(wrapper.text()).toContain('Niepoprawne dane logowania')
    expect(wrapper.text()).not.toContain('Logowanie nie powiodło się')
  })

  it('spada na ogólny fallback gdy backend nie zwraca ani error ani message', async () => {
    loginMock.mockRejectedValue({ response: { status: 500, data: {} } })

    const wrapper = mountPage()
    await wrapper.find('input[type="text"]').setValue('ktos')
    await wrapper.find('input[type="password"]').setValue('cokolwiek')
    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(wrapper.text()).toContain('Logowanie nie powiodło się')
  })

  it('po udanym logowaniu przekierowuje na stronę główną', async () => {
    loginMock.mockResolvedValue({ user: { id: 1 } })
    const pushSpy = vi.spyOn(router, 'push')

    const wrapper = mountPage()
    await wrapper.find('input[type="text"]').setValue('ktos')
    await wrapper.find('input[type="password"]').setValue('haslo123')
    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(pushSpy).toHaveBeenCalledWith('/')
  })

  it('klikniecie napisu "Zapamiętaj mnie" przelacza checkbox (label go opakowuje)', async () => {
    const wrapper = mountPage()
    const checkbox = wrapper.find('input[type="checkbox"]')
    expect(checkbox.element.checked).toBe(false)

    const rememberLabel = wrapper.findAll('label').find((l) => l.text().includes('Zapamiętaj mnie'))
    await rememberLabel.trigger('click')

    expect(checkbox.element.checked).toBe(true)
  })
})
