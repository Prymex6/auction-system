import { onMounted, onUnmounted } from 'vue'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

let echoInstance = null

export const useRealtime = () => {
  /**
   * Inicjalizuj Echo z Reverb
   * Wymagane env variables:
   * - VITE_REVERB_APP_KEY
   * - VITE_REVERB_HOST
   * - VITE_REVERB_PORT
   * - VITE_REVERB_SCHEME
   */
  const initEcho = () => {
    if (echoInstance) return echoInstance

    // Skip initialization if Reverb key is not configured
    const reverbKey = import.meta.env.VITE_REVERB_APP_KEY
    if (!reverbKey) {
      console.warn('Reverb not configured - real-time features disabled')
      return null
    }

    window.Pusher = Pusher

    const scheme = import.meta.env.VITE_REVERB_SCHEME || 'https'
    const port = Number(import.meta.env.VITE_REVERB_PORT) || (scheme === 'https' ? 443 : 80)
    const token = localStorage.getItem('auth_token')

    echoInstance = new Echo({
      broadcaster: 'reverb',
      key: reverbKey,
      wsHost: import.meta.env.VITE_REVERB_HOST,
      wsPort: port,
      wssPort: port,
      forceTLS: scheme === 'https',
      enabledTransports: ['ws', 'wss'],
      authEndpoint: `${(import.meta.env.VITE_API_BASE_URL || '/api').replace(/\/api\/?$/, '')}/broadcasting/auth`,
      auth: {
        headers: token ? { Authorization: `Bearer ${token}` } : {},
      },
    })

    return echoInstance
  }

  /**
   * @param {string} channelName - channel name
   * @param {string} eventName - Nazwa eventu
   * @param {Function} callback - Callback z danymi
   */
  const listenPublic = (channelName, eventName, callback) => {
    const echo = initEcho()
    if (!echo) return null
    return echo.channel(channelName).listen(eventName, callback)
  }

  /**
   * @param {string} channelName - channel name, for example 'private-user.1'
   * @param {string} eventName - Nazwa eventu
   * @param {Function} callback - Callback z danymi
   */
  const listenPrivate = (channelName, eventName, callback) => {
    const echo = initEcho()
    if (!echo) return null
    return echo.private(channelName).listen(eventName, callback)
  }

  /**
   * @param {string} channelName - channel name
   * @param {string} eventName - Nazwa eventu
   * @param {Function} callback - Callback z danymi
   */
  const listenPresence = (channelName, eventName, callback) => {
    const echo = initEcho()
    if (!echo) return null
    return echo.join(channelName).listen(eventName, callback)
  }

  /**
   * @param {string} channelName - channel name
   * @param {Function} onJoined - called when somebody joins
   * @param {Function} onLeft - called when somebody leaves
   */
  const listenPresenceLifecycle = (channelName, onJoined, onLeft) => {
    const echo = initEcho()
    if (!echo) return null
    const channel = echo.join(channelName)

    if (onJoined) {
      channel.joining(onJoined)
    }

    if (onLeft) {
      channel.leaving(onLeft)
    }

    return channel
  }

  /**
   * @param {string} channelName - channel name
   */
  const unsubscribe = (channelName) => {
    const echo = initEcho()
    if (!echo) return
    echo.leave(channelName)
  }

  const connect = () => {
    initEcho()
  }

  const disconnect = () => {
    if (echoInstance) {
      echoInstance.disconnect()
      echoInstance = null
    }
  }

  const useRealtimeConnection = () => {
    onMounted(() => {
      connect()
    })

    onUnmounted(() => {
      disconnect()
    })
  }

  return {
    initEcho,
    listenPublic,
    listenPrivate,
    listenPresence,
    listenPresenceLifecycle,
    unsubscribe,
    connect,
    disconnect,
    useRealtimeConnection,
  }
}
