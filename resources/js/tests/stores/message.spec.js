import { describe, it, expect, vi, beforeEach } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useMessageStore } from '@/stores/message'
import { useToast } from '@/composables/useToast'

const apiPost = vi.fn(() => Promise.resolve({ data: {} }))
const apiGet = vi.fn(() => Promise.resolve({ data: { unread_count: 0 } }))

vi.mock('@/services/api', () => ({
  default: {
    get: (...args) => apiGet(...args),
    post: (...args) => apiPost(...args),
  },
}))

let capturedHandler = null
vi.mock('@/composables/useRealtime', () => ({
  useRealtime: () => ({
    listenPrivate: (channel, event, cb) => {
      capturedHandler = cb
    },
    unsubscribe: vi.fn(),
  }),
}))

vi.mock('@/composables/useNotificationSound', () => ({
  playNotificationSound: vi.fn(),
}))

describe('message store — realtime unread badge (Faza: naprawa przedwczesnego auto-markAsRead)', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    apiPost.mockClear()
    apiGet.mockClear()
    capturedHandler = null
  })

  it('nie oznacza jako przeczytanej wlasnej echniętej (multi-device) wysłanej wiadomości', () => {
    const store = useMessageStore()
    const myId = 10
    const otherId = 20
    store.currentConversation = otherId

    store.listenPrivateMessages(myId)
    expect(capturedHandler).toBeTypeOf('function')

    capturedHandler({
      message_id: 1,
      sender_id: myId,
      recipient_id: otherId,
      content: 'hej',
      created_at: new Date().toISOString(),
    })

    expect(apiPost).not.toHaveBeenCalledWith('/messages/1/read')
  })

  it('oznacza jako przeczytaną nową wiadomość PRZYCHODZĄCĄ gdy rozmowa jest aktualnie otwarta', () => {
    const store = useMessageStore()
    const myId = 10
    const otherId = 20
    store.currentConversation = otherId

    store.listenPrivateMessages(myId)

    capturedHandler({
      message_id: 2,
      sender_id: otherId,
      recipient_id: myId,
      content: 'siema',
      created_at: new Date().toISOString(),
    })

    expect(apiPost).toHaveBeenCalledWith('/messages/2/read')
  })

  it('po zresetowaniu currentConversation (wyjście ze strony Messages) nie auto-oznacza kolejnych wiadomości', () => {
    const store = useMessageStore()
    const myId = 10
    const otherId = 20
    store.currentConversation = otherId
    store.listenPrivateMessages(myId)

    store.currentConversation = null

    capturedHandler({
      message_id: 3,
      sender_id: otherId,
      recipient_id: myId,
      content: 'nieprzeczytana',
      created_at: new Date().toISOString(),
    })

    expect(apiPost).not.toHaveBeenCalledWith('/messages/3/read')
  })

  it('pokazuje toast z imieniem nadawcy dla kazdej nowej PRZYCHODZACEJ wiadomosci (nawet gdy rozmowa jest otwarta - latwo przeoczyc sama tresc)', () => {
    const { clearAll, toasts } = useToast()
    clearAll()

    const store = useMessageStore()
    const myId = 10
    const otherId = 20
    store.currentConversation = otherId
    store.listenPrivateMessages(myId)

    capturedHandler({
      message_id: 4,
      sender_id: otherId,
      sender_name: 'Jan Testowy',
      recipient_id: myId,
      content: 'ktos pisze',
      created_at: new Date().toISOString(),
    })

    expect(toasts.value.some((t) => t.message.includes('Jan Testowy'))).toBe(true)
  })

  it('NIE pokazuje toastu dla wlasnej echniętej (multi-device) wysłanej wiadomości', () => {
    const { clearAll, toasts } = useToast()
    clearAll()

    const store = useMessageStore()
    const myId = 10
    const otherId = 20
    store.currentConversation = otherId
    store.listenPrivateMessages(myId)

    capturedHandler({
      message_id: 5,
      sender_id: myId,
      sender_name: 'Ja Sam',
      recipient_id: otherId,
      content: 'moja wiadomosc',
      created_at: new Date().toISOString(),
    })

    expect(toasts.value.length).toBe(0)
  })
})
