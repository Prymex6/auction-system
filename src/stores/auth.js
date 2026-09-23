import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/services/api'

export const useAuthStore = defineStore('auth', () => {
  const user = ref(null)
  const token = ref(localStorage.getItem('auth_token') || null)
  const loading = ref(false)
  const error = ref(null)

  const isAuthenticated = computed(() => !!token.value)

  const register = async (data) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/auth/register', data)
      token.value = response.data.token
      user.value = response.data.user
      localStorage.setItem('auth_token', token.value)
      await fetchMe()
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Registration failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const login = async (login, password) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/auth/login', { login, password })
      if (response.data.token) {
        token.value = response.data.token
        user.value = response.data.user
        localStorage.setItem('auth_token', token.value)
      }
      return response.data
    } catch (err) {
      error.value = err.response?.data?.error || err.response?.data?.message || 'Login failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const verify2FA = async (code, userId) => {
    loading.value = true
    error.value = null
    try {
      const response = await api.post('/auth/verify-2fa', { code, user_id: userId })
      token.value = response.data.token
      user.value = response.data.user
      localStorage.setItem('auth_token', token.value)
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || '2FA verification failed'
      throw err
    } finally {
      loading.value = false
    }
  }

  const fetchMe = async () => {
    if (!token.value) return
    loading.value = true
    try {
      const response = await api.get('/auth/me')
      user.value = response.data.user || response.data
    } catch (err) {
      if (err.response?.status === 401 || err.response?.status === 404) {
        await logout()
      }
      error.value = err.response?.data?.message || 'Failed to fetch user'
      throw err
    } finally {
      loading.value = false
    }
  }

  const logout = async () => {
    try {
      if (token.value) {
        await api.post('/auth/logout')
      }
    } catch (err) {
      console.error('Logout error:', err)
    } finally {
      token.value = null
      user.value = null
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user')

      document.cookie.split(';').forEach((c) => {
        document.cookie = c
          .replace(/^ +/, '')
          .replace(/=.*/, '=;expires=' + new Date().toUTCString() + ';path=/')
      })
    }
  }

  const setup2FA = async () => {
    try {
      const response = await api.post('/auth/2fa/setup')
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to setup 2FA'
      throw err
    }
  }

  const confirm2FA = async (code) => {
    try {
      const response = await api.post('/auth/2fa/confirm', { code })
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to confirm 2FA'
      throw err
    }
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    register,
    login,
    verify2FA,
    fetchMe,
    logout,
    setup2FA,
    confirm2FA,
  }
})
