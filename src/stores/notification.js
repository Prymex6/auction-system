import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useRealtime } from '@/composables/useRealtime'
import { playNotificationSound } from '@/composables/useNotificationSound'
import { useAuthStore } from './auth'

export const useNotificationStore = defineStore('notification', () => {
  const notifications = ref([])
  const unreadCount = ref(0)
  const loading = ref(false)
  const realtimeChannels = ref({})

  const fetchNotifications = async (page = 1) => {
    loading.value = true
    try {
      const response = await api.get('/notifications', {
        params: { page, per_page: 20 },
      })
      notifications.value = response.data.data
    } catch (err) {
      console.error('Failed to fetch notifications:', err)
    } finally {
      loading.value = false
    }
  }

  const fetchUnreadCount = async () => {
    try {
      const response = await api.get('/notifications/unread-count')
      unreadCount.value = response.data.unread_count ?? 0
    } catch (err) {
      console.error('Failed to fetch unread count:', err)
    }
  }

  const markAsRead = async (id) => {
    try {
      await api.post(`/notifications/${id}/read`)
      const notification = notifications.value.find((n) => n.id === id)
      if (notification) {
        notification.read_at = new Date().toISOString()
      }
      await fetchUnreadCount()
    } catch (err) {
      console.error('Failed to mark notification as read:', err)
    }
  }

  const markAllAsRead = async () => {
    try {
      await api.post('/notifications/mark-all-read')
      notifications.value.forEach((n) => {
        n.read_at = new Date().toISOString()
      })
      unreadCount.value = 0
    } catch (err) {
      console.error('Failed to mark all notifications as read:', err)
    }
  }

  const deleteNotification = async (id) => {
    try {
      await api.delete(`/notifications/${id}`)
      notifications.value = notifications.value.filter((n) => n.id !== id)
      await fetchUnreadCount()
    } catch (err) {
      console.error('Failed to delete notification:', err)
    }
  }

  const addNotification = (notification) => {
    notifications.value.unshift(notification)
    if (!notification.read_at) {
      unreadCount.value++
    }
  }

  const listenNotifications = () => {
    const authStore = useAuthStore()
    const { listenPrivate, unsubscribe } = useRealtime()

    if (!authStore.user?.id) return

    const channelName = `user.${authStore.user.id}`

    listenPrivate(channelName, '.notification.created', (data) => {
      addNotification({
        id: data.notification_id,
        type: data.type,
        title: data.title,
        message: data.message,
        created_at: data.created_at,
        read_at: null,
      })
      playNotificationSound()
    })

    listenPrivate(channelName, 'NotificationRead', (data) => {
      const notification = notifications.value.find((n) => n.id === data.notification_id)
      if (notification) {
        notification.read_at = new Date().toISOString()
        unreadCount.value = Math.max(0, unreadCount.value - 1)
      }
    })

    realtimeChannels.value[channelName] = () => unsubscribe(channelName)
  }

  /**
   * @param {string} channelName - channel name
   */
  const unlistenChannel = (channelName) => {
    if (realtimeChannels.value[channelName]) {
      realtimeChannels.value[channelName]()
      delete realtimeChannels.value[channelName]
    }
  }

  const unlistenAll = () => {
    Object.keys(realtimeChannels.value).forEach((channelName) => {
      unlistenChannel(channelName)
    })
  }

  return {
    notifications,
    unreadCount,
    loading,
    realtimeChannels,
    fetchNotifications,
    fetchUnreadCount,
    markAsRead,
    markAllAsRead,
    deleteNotification,
    addNotification,
    listenNotifications,
    unlistenChannel,
    unlistenAll,
  }
})
