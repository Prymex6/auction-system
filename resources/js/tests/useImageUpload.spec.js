import { describe, it, expect } from 'vitest'
import { useImageUpload } from '../../../src/composables/useImageUpload'

describe('useImageUpload composable', () => {
  it('has expected initial state', () => {
    const { file, preview, progress, uploading, error } = useImageUpload()

    expect(file.value).toBeNull()
    expect(preview.value).toBeNull()
    expect(progress.value).toBe(0)
    expect(uploading.value).toBe(false)
    expect(error.value).toBe('')
  })
})
