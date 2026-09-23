import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'

const postMock = vi.fn().mockResolvedValue({ data: {} })

vi.mock('@/services/api', () => ({
  default: {
    get: vi.fn().mockResolvedValue({ data: { data: [] } }),
    post: (...args) => postMock(...args),
    patch: vi.fn().mockResolvedValue({ data: {} }),
    delete: vi.fn().mockResolvedValue({ data: {} }),
  },
}))

import CategoriesManagement from '@/components/CategoriesManagement.vue'

describe('CategoriesManagement.vue — zapisywanie reguly smart kategorii', () => {
  beforeEach(() => {
    postMock.mockClear()
  })

  it('regresja: type/sort_by/year z filtra smart NIE gina przy zapisie', async () => {
    // saveCategory() mial reczna liste pol do skopiowania z form.smart_filter
    const wrapper = mount(CategoriesManagement)
    await flushPromises()

    await wrapper.find('button').trigger('click')
    await flushPromises()

    await wrapper.find('input[type="text"]').setValue('Rocznik 2026 test')

    const selects = wrapper.findAll('select')
    await selects[0].setValue('smart') // Typ kategorii
    await flushPromises()

    const smartSelects = wrapper.findAll('select')
    await smartSelects[1].setValue('buy_now')
    await smartSelects[2].setValue('bids_count_desc') // Sortowanie

    await wrapper.find('input[type="number"]').setValue(2026) // Rocznik

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(postMock).toHaveBeenCalledTimes(1)
    const [, payload] = postMock.mock.calls[0]
    expect(payload.smart_filter).toEqual({
      type: 'buy_now',
      sort_by: 'bids_count_desc',
      year: 2026,
    })
  })
})
