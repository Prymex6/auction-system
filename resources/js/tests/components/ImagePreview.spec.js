import { mount } from '@vue/test-utils'
import { describe, it, expect } from 'vitest'
import ImagePreview from '../../../../src/components/ImagePreview.vue'

describe('ImagePreview', () => {
  it('renders image and remove button when removable', () => {
    const wrapper = mount(ImagePreview, {
      props: {
        src: '/storage/images/auction/test.jpg',
        alt: 'Test image',
        removable: true,
        downloadable: false,
      },
      global: {
        stubs: {
          Badge: true,
        },
      },
    })

    const img = wrapper.find('img')
    expect(img.exists()).toBe(true)
    expect(img.attributes('src')).toBe('/storage/images/auction/test.jpg')

    // There should be a remove button (in overlay). We can assert presence of a button element.
    const buttons = wrapper.findAll('button')
    expect(buttons.length).toBeGreaterThan(0)
  })
})
