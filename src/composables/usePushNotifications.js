import { ref } from 'vue'
import api from '@/services/api'

// PushManager.subscribe() - standardowy przepis z dokumentacji Web Push.
function urlBase64ToUint8Array(base64String) {
  const padding = '='.repeat((4 - (base64String.length % 4)) % 4)
  const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/')
  const rawData = window.atob(base64)
  return Uint8Array.from([...rawData].map((c) => c.charCodeAt(0)))
}

export function usePushNotifications() {
  const isSupported = 'serviceWorker' in navigator && 'PushManager' in window
  const isSubscribed = ref(false)
  const loading = ref(false)

  const checkSubscription = async () => {
    if (!isSupported) return
    const registration = await navigator.serviceWorker.ready
    const subscription = await registration.pushManager.getSubscription()
    isSubscribed.value = !!subscription
  }

  const subscribe = async () => {
    if (!isSupported) return false
    loading.value = true
    try {
      const permission = await Notification.requestPermission()
      if (permission !== 'granted') {
        return false
      }

      const registration = await navigator.serviceWorker.ready
      const { data } = await api.get('/push/vapid-public-key')

      const subscription = await registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: urlBase64ToUint8Array(data.publicKey),
      })

      await api.post('/push/subscribe', subscription.toJSON())
      isSubscribed.value = true
      return true
    } finally {
      loading.value = false
    }
  }

  const unsubscribe = async () => {
    if (!isSupported) return
    loading.value = true
    try {
      const registration = await navigator.serviceWorker.ready
      const subscription = await registration.pushManager.getSubscription()
      if (subscription) {
        await api.post('/push/unsubscribe', { endpoint: subscription.endpoint })
        await subscription.unsubscribe()
      }
      isSubscribed.value = false
    } finally {
      loading.value = false
    }
  }

  return { isSupported, isSubscribed, loading, checkSubscription, subscribe, unsubscribe }
}
