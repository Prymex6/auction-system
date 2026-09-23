import { ref } from 'vue'
import { api } from '@/services/api'

export const useFetch = () => {
  const loading = ref(false)
  const error = ref(null)
  const data = ref(null)

  const fetch = async (method, url, options = {}) => {
    loading.value = true
    error.value = null
    data.value = null

    try {
      const config = {
        method,
        url,
        ...options,
      }

      const response = await api(config)
      data.value = response.data
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || err.message || 'Nieznany błąd'
      throw err
    } finally {
      loading.value = false
    }
  }

  const get = async (url) => fetch('GET', url)

  const post = async (url, payload) => fetch('POST', url, { data: payload })

  const put = async (url, payload) => fetch('PUT', url, { data: payload })

  const patch = async (url, payload) => fetch('PATCH', url, { data: payload })

  const del = async (url) => fetch('DELETE', url)

  const reset = () => {
    loading.value = false
    error.value = null
    data.value = null
  }

  return {
    loading,
    error,
    data,
    fetch,
    get,
    post,
    put,
    patch,
    del,
    reset,
  }
}
