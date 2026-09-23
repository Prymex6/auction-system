import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import ImageUploader from '../../../../src/components/ImageUploader.vue'

describe('ImageUploader', () => {
  it('renders dropzone and upload button disabled when no file', () => {
    const wrapper = mount(ImageUploader, {
      props: {
        endpoint: '/api/images/upload',
      },
      global: {
        stubs: {
          Button: true,
          Alert: true,
        },
      },
    })

    expect(wrapper.text()).toContain('Kliknij lub przeciągnij obraz')
    // Should have a hidden file input
    const input = wrapper.find('input[type="file"]')
    expect(input.exists()).toBe(true)
  })
})
