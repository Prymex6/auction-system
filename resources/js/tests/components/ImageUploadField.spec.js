import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'

const postMock = vi.fn()

vi.mock('@/services/api', () => ({
  default: {
    post: (...args) => postMock(...args),
  },
}))

import ImageUploadField from '@/components/form/ImageUploadField.vue'

function makeFile(name = 'pigeon.jpg', sizeMB = 1, type = 'image/jpeg') {
  const file = new File([new Uint8Array(sizeMB * 1024 * 1024)], name, { type })
  return file
}

async function selectFile(wrapper, file) {
  const input = wrapper.find('input[type="file"]')
  Object.defineProperty(input.element, 'files', {
    value: [file],
    writable: true,
  })
  await input.trigger('change')
}

describe('ImageUploadField.vue', () => {
  beforeEach(() => {
    postMock.mockReset()
  })

  it('uploads the selected file as multipart FormData, not JSON', async () => {
    postMock.mockResolvedValue({ data: { url: '/storage/images/auction/new-photo.jpg' } })

    const wrapper = mount(ImageUploadField, {
      props: { label: 'Zdjęcia gołębia', modelValue: [], uploadType: 'auction' },
    })

    await selectFile(wrapper, makeFile())

    expect(postMock).toHaveBeenCalledTimes(1)
    const [url, body, config] = postMock.mock.calls[0]

    expect(url).toBe('/images/upload')
    // Regression guard: the payload must remain a real FormData instance so axios
    // sends multipart data. If this ever gets JSON.stringified again (the bug from
    // the shared axios instance's default Content-Type: application/json), the
    // backend receives no file and every upload fails with a validation error.
    expect(body).toBeInstanceOf(FormData)
    expect(body.get('type')).toBe('auction')
    expect(body.get('image')).toBeInstanceOf(File)
    expect(config.headers['Content-Type']).toBe('multipart/form-data')
  })

  it('emits update:modelValue with the URL returned by the server', async () => {
    postMock.mockResolvedValue({ data: { url: '/storage/images/auction/new-photo.jpg' } })

    const wrapper = mount(ImageUploadField, {
      props: { label: 'Zdjęcia gołębia', modelValue: ['/existing.jpg'] },
    })

    await selectFile(wrapper, makeFile())

    const emitted = wrapper.emitted('update:modelValue')
    expect(emitted).toBeTruthy()
    expect(emitted[0][0]).toEqual(['/existing.jpg', '/storage/images/auction/new-photo.jpg'])
  })

  it('rejects files larger than maxSizePerFile without calling the API', async () => {
    const wrapper = mount(ImageUploadField, {
      props: { label: 'Zdjęcia gołębia', modelValue: [], maxSizePerFile: 5 },
    })

    await selectFile(wrapper, makeFile('too-big.jpg', 10))

    expect(postMock).not.toHaveBeenCalled()
    expect(wrapper.text()).toContain('za duży')
  })

  it('surfaces a field-specific validation error from the backend response', async () => {
    postMock.mockRejectedValue({
      response: { data: { errors: { image: ['Wybrana wartość pola płeć jest nieprawidłowa'] } } },
    })

    const wrapper = mount(ImageUploadField, {
      props: { label: 'Zdjęcia gołębia', modelValue: [] },
    })

    await selectFile(wrapper, makeFile())

    expect(wrapper.text()).toContain('Wybrana wartość pola płeć jest nieprawidłowa')
    expect(wrapper.emitted('update:modelValue')).toBeFalsy()
  })

  it('removes an image from modelValue when remove is clicked', async () => {
    const wrapper = mount(ImageUploadField, {
      props: { label: 'Zdjęcia gołębia', modelValue: ['/a.jpg', '/b.jpg'] },
    })

    await wrapper.findAll('button')[0].trigger('click')

    expect(wrapper.emitted('update:modelValue')[0][0]).toEqual(['/b.jpg'])
  })
})
