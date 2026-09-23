import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'

const getMock = vi.fn()

vi.mock('@/services/api', () => ({
  default: {
    get: (...args) => getMock(...args),
    put: vi.fn().mockResolvedValue({ data: {} }),
    patch: vi.fn().mockResolvedValue({ data: {} }),
    delete: vi.fn().mockResolvedValue({ data: {} }),
  },
}))

vi.mock('@/stores/auth', () => ({
  useAuthStore: () => ({ user: { id: 1, is_admin: false } }),
}))

vi.mock('@/composables/useSeo', () => ({ useSeo: vi.fn() }))

import EditAuction from '@/pages/EditAuction.vue'

const baseAuction = {
  id: 42,
  title: 'Testowa aukcja',
  breed: 'Janssen',
  gender: 'samiec',
  year: 2024,
  size: 'sredni',
  color: 'Niebieska',
  ring_number: '',
  description: 'Opis',
  type: 'auction',
  start_price: 100,
  pigeon_images: ['img.jpg'],
  pedigree_images: [],
  user_id: 1,
}

describe('EditAuction.vue — blokada edycji aktywnej/zakonczonej aukcji', () => {
  let router

  beforeEach(async () => {
    getMock.mockReset()
    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/auctions/:id/edit', component: EditAuction },
        { path: '/profile', component: { template: '<div />' } },
      ],
    })
    router.push('/auctions/42/edit')
    await router.isReady()
  })

  function mountPage() {
    return mount(EditAuction, {
      global: { plugins: [router], stubs: { IconPicker: true, ConfirmModal: true } },
    })
  }

  it('regresja: pola SA zablokowane gdy aukcja jest juz aktywna (status=active)', async () => {
    getMock.mockImplementation((url) => {
      if (url.includes('/breeds')) return Promise.resolve({ data: { data: [] } })
      return Promise.resolve({ data: { data: { ...baseAuction, status: 'active' } } })
    })

    const wrapper = mountPage()
    await flushPromises()

    const titleInput = wrapper.find('input[type="text"]')
    expect(titleInput.attributes('disabled')).toBeDefined()
  })

  it('pola NIE sa zablokowane gdy aukcja jest w statusie pending', async () => {
    getMock.mockImplementation((url) => {
      if (url.includes('/breeds')) return Promise.resolve({ data: { data: [] } })
      return Promise.resolve({ data: { data: { ...baseAuction, status: 'pending' } } })
    })

    const wrapper = mountPage()
    await flushPromises()

    const titleInput = wrapper.find('input[type="text"]')
    expect(titleInput.attributes('disabled')).toBeUndefined()
  })

  it('pola NIE sa zablokowane dla typu buy_now nawet gdy status=active (backend tez to pozwala)', async () => {
    getMock.mockImplementation((url) => {
      if (url.includes('/breeds')) return Promise.resolve({ data: { data: [] } })
      return Promise.resolve({
        data: { data: { ...baseAuction, status: 'active', type: 'buy_now' } },
      })
    })

    const wrapper = mountPage()
    await flushPromises()

    const titleInput = wrapper.find('input[type="text"]')
    expect(titleInput.attributes('disabled')).toBeUndefined()
  })
})
