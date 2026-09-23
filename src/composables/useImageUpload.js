import { ref, computed } from 'vue'
import api from '@/services/api'

/**
 *
 * @returns {Object} Upload state i metody
 *
 * @example
 * const {
 *   file,
 *   preview,
 *   progress,
 *   uploading,
 *   selectFile,
 *   removeFile,
 *   uploadFile
 * } = useImageUpload({ maxSize: 5, allowedTypes: ['image/jpeg', 'image/png'] })
 */
export const useImageUpload = (options = {}) => {
  const {
    maxSize = 5, // MB
    allowedTypes = ['image/jpeg', 'image/png', 'image/webp'],
    maxWidth = 2000,
    maxHeight = 2000,
    quality = 0.8,
  } = options

  const file = ref(null)
  const preview = ref(null)
  const progress = ref(0)
  const uploading = ref(false)
  const error = ref('')

  const validateFile = (selectedFile) => {
    error.value = ''

    if (!allowedTypes.includes(selectedFile.type)) {
      error.value = `Dozwolone typy: ${allowedTypes.join(', ')}`
      return false
    }

    const sizeInMB = selectedFile.size / (1024 * 1024)
    if (sizeInMB > maxSize) {
      error.value = `Plik nie może być większy niż ${maxSize}MB (aktualnie: ${sizeInMB.toFixed(2)}MB)`
      return false
    }

    return true
  }

  /**
   * Kompresuj obraz
   */
  const compressImage = (selectedFile) => {
    return new Promise((resolve, reject) => {
      const reader = new FileReader()

      reader.onload = (e) => {
        const img = new Image()

        img.onload = () => {
          const canvas = document.createElement('canvas')
          let width = img.width
          let height = img.height

          if (width > maxWidth || height > maxHeight) {
            const ratio = Math.min(maxWidth / width, maxHeight / height)
            width *= ratio
            height *= ratio
          }

          canvas.width = width
          canvas.height = height

          const ctx = canvas.getContext('2d')
          ctx.drawImage(img, 0, 0, width, height)

          // Konwertuj do blob
          canvas.toBlob(
            (blob) => {
              resolve(blob)
            },
            selectedFile.type,
            quality
          )
        }

        img.onerror = () => {
          reject(new Error('Failed to load image'))
        }

        img.src = e.target.result
      }

      reader.onerror = () => {
        reject(new Error('Failed to read file'))
      }

      reader.readAsDataURL(selectedFile)
    })
  }

  /**
   * Wygeneruj preview
   */
  const generatePreview = (selectedFile) => {
    return new Promise((resolve) => {
      const reader = new FileReader()

      reader.onload = (e) => {
        preview.value = e.target.result
        resolve(e.target.result)
      }

      reader.readAsDataURL(selectedFile)
    })
  }

  const selectFile = async (selectedFile) => {
    if (!validateFile(selectedFile)) {
      return false
    }

    try {
      // Kompresuj
      const compressedBlob = await compressImage(selectedFile)

      // Konwertuj blob na File
      file.value = new File([compressedBlob], selectedFile.name, {
        type: selectedFile.type,
        lastModified: Date.now(),
      })

      // Wygeneruj preview
      await generatePreview(file.value)

      return true
    } catch (err) {
      error.value = `Błąd przy przetwarzaniu obrazu: ${err.message}`
      return false
    }
  }

  const removeFile = () => {
    file.value = null
    preview.value = null
    progress.value = 0
    error.value = ''
  }

  /**
   * @param {string} endpoint - API endpoint do uploadu
   * @param {Object} formData - extra fields to send along
   */
  const uploadFile = async (endpoint, additionalData = {}) => {
    if (!file.value) {
      error.value = 'Brak wybranego pliku'
      return null
    }

    uploading.value = true
    progress.value = 0

    try {
      const data = new FormData()
      data.append('image', file.value)

      Object.keys(additionalData).forEach((key) => {
        data.append(key, additionalData[key])
      })

      const response = await api.post(endpoint, data, {
        headers: {
          'Content-Type': 'multipart/form-data',
        },
        onUploadProgress: (progressEvent) => {
          progress.value = Math.round((progressEvent.loaded / progressEvent.total) * 100)
        },
      })

      progress.value = 100

      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Błąd przy uplozie pliku'
      console.error('Upload error:', err)
      return null
    } finally {
      uploading.value = false
    }
  }

  /**
   * File size w MB
   */
  const fileSizeInMB = computed(() => {
    if (!file.value) return 0
    return (file.value.size / (1024 * 1024)).toFixed(2)
  })

  const hasFile = computed(() => !!file.value)

  const hasError = computed(() => !!error.value)

  return {
    // State
    file,
    preview,
    progress,
    uploading,
    error,

    // Computed
    fileSizeInMB,
    hasFile,
    hasError,

    // Methods
    selectFile,
    removeFile,
    uploadFile,
    validateFile,
    compressImage,
    generatePreview,
  }
}
