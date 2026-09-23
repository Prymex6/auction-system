import axios from 'axios'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

export function useApi() {
  const router = useRouter()
  const authStore = useAuthStore()

  const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL || '/api',
    withCredentials: true,
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
  })

  api.interceptors.request.use(
    (config) => {
      const token = authStore.token || localStorage.getItem('token')
      if (token) {
        config.headers.Authorization = `Bearer ${token}`
      }
      return config
    },
    (error) => Promise.reject(error)
  )

  api.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response) {
        // Unauthorized - wyloguj
        if (error.response.status === 401) {
          authStore.logout()
          router.push('/login')
        }

        // chroni guard routera (requiresAdmin).

        if (error.response.status === 404 && error.config?.method === 'get') {
          router.push('/404')
        }
      }

      return Promise.reject(error)
    }
  )

  return { api }
}
