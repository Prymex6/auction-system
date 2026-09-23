import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'
import { useToast } from '@/composables/useToast'

const getMock = vi.fn()
const fetchConversationsMock = vi.fn().mockResolvedValue([])
const fetchConversationMock = vi.fn().mockResolvedValue([])
const fetchUnreadCountMock = vi.fn()

vi.mock('@/services/api', () => ({
  default: {
    get: (...args) => getMock(...args),
  },
}))

const messageStoreState = { lastMessageToastId: null }

vi.mock('@/stores/message', () => ({
  useMessageStore: () => ({
    messages: [],
    fetchConversations: fetchConversationsMock,
    fetchConversation: fetchConversationMock,
    fetchUnreadCount: fetchUnreadCountMock,
    sendMessage: vi.fn(),
    deleteMessage: vi.fn(),
    get lastMessageToastId() {
      return messageStoreState.lastMessageToastId
    },
    set lastMessageToastId(v) {
      messageStoreState.lastMessageToastId = v
    },
  }),
}))

vi.mock('@/stores/auth', () => ({
  useAuthStore: () => ({ user: { id: 1 } }),
}))

vi.mock('@/composables/useRealtimeConnection', () => ({
  useRealtimeConnection: () => ({ disconnect: vi.fn() }),
}))

vi.mock('@/composables/useSeo', () => ({ useSeo: vi.fn() }))

import Messages from '@/pages/Messages.vue'

describe('Messages.vue — otwieranie konwersacji z ?user= w URL', () => {
  let router

  beforeEach(async () => {
    getMock.mockReset()
    fetchConversationsMock.mockClear().mockResolvedValue([])
    fetchConversationMock.mockClear().mockResolvedValue([])
    Element.prototype.scrollTo = vi.fn()

    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/messages', component: Messages },
        { path: '/auctions', component: { template: '<div />' } },
        { path: '/profile/:id', component: { template: '<div />' } },
      ],
    })
  })

  function mountPage() {
    return mount(Messages, { global: { plugins: [router], stubs: { IconPicker: true } } })
  }

  it('gdy user_id z query juz ma istniejaca konwersacje, otwiera ja bez dodatkowego zapytania o profil', async () => {
    fetchConversationsMock.mockResolvedValue([
      { user_id: 42, user_name: 'Istniejący Rozmówca', unread_count: 0 },
    ])
    router.push('/messages?user=42')
    await router.isReady()

    const wrapper = mountPage()
    await flushPromises()

    expect(fetchConversationMock).toHaveBeenCalledWith(42)
    expect(getMock).not.toHaveBeenCalled()
    expect(wrapper.text()).toContain('Istniejący Rozmówca')
  })

  it('regresja: pierwsza wiadomość do kogoś bez wcześniejszej konwersacji otwiera panel czatu zamiast nic nie robić', async () => {
    fetchConversationsMock.mockResolvedValue([])
    getMock.mockResolvedValue({ data: { data: { id: 99, name: 'nowy_rozmowca' } } })

    router.push('/messages?user=99')
    await router.isReady()

    const wrapper = mountPage()
    await flushPromises()

    expect(getMock).toHaveBeenCalledWith('/users/99')
    expect(fetchConversationMock).toHaveBeenCalledWith(99)
    expect(wrapper.text()).toContain('nowy_rozmowca')
    expect(wrapper.find('textarea[placeholder="Napisz wiadomość..."]').exists()).toBe(true)
  })

  it('skupienie sie na polu wpisywania odpowiedzi usuwa toast "Nowa wiadomość od..." (jak w Messengerze)', async () => {
    fetchConversationsMock.mockResolvedValue([
      { user_id: 42, user_name: 'Istniejący Rozmówca', unread_count: 0 },
    ])
    router.push('/messages?user=42')
    await router.isReady()

    const { showInfo, toasts } = useToast()
    const toastId = showInfo('Nowa wiadomość od Istniejący Rozmówca')
    messageStoreState.lastMessageToastId = toastId

    const wrapper = mountPage()
    await flushPromises()

    expect(toasts.value.some((t) => t.id === toastId)).toBe(true)

    await wrapper.find('textarea[placeholder="Napisz wiadomość..."]').trigger('focus')

    expect(toasts.value.some((t) => t.id === toastId)).toBe(false)
    expect(messageStoreState.lastMessageToastId).toBe(null)
  })
})
