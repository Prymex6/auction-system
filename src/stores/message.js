import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '@/services/api'
import { useRealtime } from '@/composables/useRealtime'
import { playNotificationSound } from '@/composables/useNotificationSound'
import { useToast } from '@/composables/useToast'

export const useMessageStore = defineStore('message', () => {
  const conversations = ref([])
  const currentConversation = ref(null)
  const messages = ref([])
  const unreadCount = ref(0)
  const loading = ref(false)
  const error = ref(null)
  const realtimeChannels = ref({})
  const messageSubscriptions = ref([])
  const lastMessageToastId = ref(null)

  const fetchConversations = async (page = 1) => {
    loading.value = true
    try {
      const response = await api.get('/messages/conversations', {
        params: { page, per_page: 20 },
      })
      conversations.value = response.data.data
      return conversations.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Nie udało się pobrać konwersacji'
      return []
    } finally {
      loading.value = false
    }
  }

  const fetchConversation = async (userId) => {
    loading.value = true
    try {
      const response = await api.get(`/messages/user/${userId}`)
      messages.value = response.data.data
      currentConversation.value = userId
      return messages.value
    } catch (err) {
      error.value = err.response?.data?.message || 'Nie udało się pobrać rozmowy'
      return []
    } finally {
      loading.value = false
    }
  }

  const sendMessage = async (userId, content) => {
    try {
      const response = await api.post(`/messages/user/${userId}`, { content })
      return response.data
    } catch (err) {
      error.value = err.response?.data?.message || 'Nie udało się wysłać wiadomości'
      throw err
    }
  }

  const markAsRead = async (messageId) => {
    try {
      await api.post(`/messages/${messageId}/read`)
      const message = messages.value.find((m) => m.id === messageId)
      if (message) {
        message.read_at = new Date().toISOString()
      }
      await fetchUnreadCount()
    } catch (err) {
      console.error('Failed to mark message as read:', err)
    }
  }

  const deleteMessage = async (messageId) => {
    try {
      await api.delete(`/messages/${messageId}`)
      messages.value = messages.value.filter((m) => m.id !== messageId)
    } catch (err) {
      error.value = err.response?.data?.message || 'Failed to delete message'
      throw err
    }
  }

  const fetchUnreadCount = async () => {
    try {
      const response = await api.get('/messages/unread-count')
      unreadCount.value = response.data.unread_count ?? 0
    } catch (err) {
      console.error('Failed to fetch unread message count:', err)
    }
  }

  const addMessage = (message) => {
    messages.value.push(message)
  }

  /**
   * @param {number} userId - id of the signed-in user
   */
  const listenPrivateMessages = (userId) => {
    const { listenPrivate, unsubscribe } = useRealtime()

    const channelName = `user.${userId}`

    listenPrivate(channelName, '.message.sent', (data) => {
      if (
        currentConversation.value === data.sender_id ||
        currentConversation.value === data.recipient_id
      ) {
        messages.value.push({
          id: data.message_id,
          content: data.content,
          sender_id: data.sender_id,
          recipient_id: data.recipient_id,
          created_at: data.created_at,
          read_at: null,
        })

        if (data.recipient_id === userId) {
          markAsRead(data.message_id)
        }
      }

      const convIndex = conversations.value.findIndex(
        (c) => c.user_id === data.sender_id || c.user_id === data.recipient_id
      )
      if (convIndex !== -1) {
        conversations.value[convIndex].last_message = data.content
        conversations.value[convIndex].last_message_at = data.created_at
      }

      // Zaktualizuj unread count
      fetchUnreadCount()

      // przeoczyc (przewiniety czat, otwarta inna rozmowa, patrzy na liste),
      if (data.sender_id !== userId) {
        playNotificationSound()
        const { showInfo } = useToast()
        lastMessageToastId.value = showInfo(
          `Nowa wiadomość od ${data.sender_name || 'użytkownika'}`
        )
      }
    })

    realtimeChannels.value[channelName] = () => unsubscribe(channelName)
  }

  /**
   * @param {number} otherUserId - ID drugiej strony konwersacji
   */
  const listenConversation = (otherUserId) => {
    const { listenPublic, unsubscribe } = useRealtime()

    const channelName = `messages.${otherUserId}`

    listenPublic(channelName, 'MessageSent', (data) => {
      if (currentConversation.value === otherUserId) {
        messages.value.push({
          id: data.message.id,
          content: data.message.content,
          sender_id: data.message.sender_id,
          sender_name: data.message.sender_name,
          recipient_id: data.message.recipient_id,
          created_at: data.message.created_at,
          read_at: null,
        })
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
    conversations,
    currentConversation,
    messages,
    unreadCount,
    loading,
    error,
    realtimeChannels,
    lastMessageToastId,
    fetchConversations,
    fetchConversation,
    sendMessage,
    markAsRead,
    deleteMessage,
    fetchUnreadCount,
    addMessage,
    listenPrivateMessages,
    listenConversation,
    unlistenChannel,
    unlistenAll,
  }
})
