<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Account Status Alert -->
      <div
        v-if="!authStore.user?.is_active"
        class="mb-6 p-5 bg-blue-50 border border-blue-200 rounded-xl flex items-start gap-4"
      >
        <font-awesome-icon
          icon="fa-solid fa-circle-info"
          class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5"
        />
        <div>
          <h3 class="font-bold text-blue-900">Konto oczekuje na zatwierdzenie</h3>
          <p class="text-sm text-blue-800 mt-1">
            Twoje konto musi być potwierdzone przez administratora przed wysłaniem wiadomości.
          </p>
        </div>
      </div>

      <!-- Header -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-2">Wiadomości</h1>
            <p class="text-gray-600">Komunikuj się z innymi hodowcami</p>
          </div>
          <div class="flex items-center gap-3">
            <div class="relative">
              <div
                class="w-12 h-12 rounded-2xl bg-blue-600 flex items-center justify-center text-white"
              >
                <IconPicker name="chat" color-class="text-white" class="w-6 h-6" />
              </div>
              <div
                v-if="totalUnread > 0"
                class="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-red-500 text-white text-xs flex items-center justify-center font-bold"
              >
                {{ totalUnread }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        class="grid grid-cols-1 lg:grid-cols-4 gap-6 h-[calc(100vh-12rem-var(--cookie-banner-h,0px)-1rem)]"
      >
        <!-- Conversations List - na mobile pokazujemy albo liste, albo wybrany
        czat (nigdy oba naraz, inaczej wiadomosci mialyby prawie zero miejsca) -->
        <div
          class="lg:col-span-1 bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200 shadow-lg overflow-hidden flex-col min-h-0"
          :class="selectedConversation ? 'hidden lg:flex' : 'flex'"
        >
          <!-- Search and Header -->
          <div class="p-5 border-b border-gray-200 bg-white">
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                class="w-full pl-12 pr-4 py-3 bg-white rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-3 focus:ring-blue-200 focus:outline-none transition-all"
                placeholder="Szukaj konwersacji..."
              />
              <font-awesome-icon
                icon="fa-solid fa-magnifying-glass"
                class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
              />
            </div>
            <div class="flex items-center justify-between mt-4">
              <span class="text-sm font-medium text-gray-700">Konwersacje</span>
              <span class="text-xs text-gray-500">{{ conversations.length }} rozmów</span>
            </div>
          </div>

          <!-- Conversations -->
          <div class="flex-1 min-h-0 overflow-y-auto">
            <div v-if="conversations.length === 0" class="p-8 text-center">
              <div
                class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-100 flex items-center justify-center text-gray-400"
              >
                <IconPicker name="chat" color-class="text-gray-400" class="w-8 h-8" />
              </div>
              <p class="text-gray-600 font-medium">Brak konwersacji</p>
              <p class="text-sm text-gray-500 mt-1">Rozpocznij nową rozmowę!</p>
            </div>

            <div v-else class="divide-y divide-gray-100">
              <button
                v-for="conv in filteredConversations"
                :key="conv.id"
                class="w-full p-4 text-left hover:bg-blue-50 transition-all duration-300 group"
                :class="
                  selectedConversation?.id === conv.id
                    ? 'bg-blue-50 border-r-4 border-r-blue-500'
                    : ''
                "
                @click="selectConversation(conv)"
              >
                <div class="flex items-start gap-3">
                  <!-- Avatar -->
                  <div class="relative">
                    <div
                      class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg"
                    >
                      <img
                        v-if="conv.user_avatar"
                        :src="getAvatarUrl(conv.user_avatar)"
                        alt="Avatar"
                        class="w-full h-full object-cover rounded-xl"
                      />
                      <span v-else>{{ conv.user_name.charAt(0) }}</span>
                    </div>
                    <div
                      v-if="conv.unread_count"
                      class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-red-500 text-white text-xs flex items-center justify-center font-bold"
                    >
                      {{ conv.unread_count }}
                    </div>
                    <div
                      v-if="conv.is_online"
                      class="absolute -bottom-1 -right-1 w-3 h-3 rounded-full bg-green-500 border-2 border-white"
                    ></div>
                  </div>

                  <!-- Content -->
                  <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between mb-1">
                      <h3 class="font-bold text-gray-900 truncate">{{ conv.user_name }}</h3>
                      <span class="text-xs text-gray-500 whitespace-nowrap ml-2">{{
                        formatTime(conv.last_message_at)
                      }}</span>
                    </div>
                    <p class="text-sm text-gray-600 truncate mb-2">{{ conv.last_message }}</p>

                    <!-- Tags -->
                    <div class="flex items-center gap-2">
                      <span
                        v-if="conv.is_breeder"
                        class="inline-block px-2 py-1 bg-green-100 text-green-800 text-xs font-medium rounded-full"
                      >
                        Hodowca
                      </span>
                      <span
                        v-if="conv.is_premium"
                        class="inline-block px-2 py-1 bg-yellow-50 text-yellow-800 text-xs font-medium rounded-full"
                      >
                        Premium
                      </span>
                    </div>
                  </div>
                </div>
              </button>
            </div>
          </div>
        </div>

        <!-- Chat Area -->
        <div
          v-if="selectedConversation"
          class="lg:col-span-3 bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200 shadow-lg flex flex-col min-h-0"
        >
          <!-- Header -->
          <div
            class="p-4 sm:p-6 border-b border-gray-200 bg-white flex items-center justify-between gap-3"
          >
            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
              <button
                class="lg:hidden shrink-0 w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 transition-colors"
                aria-label="Wróć do listy konwersacji"
                @click="selectedConversation = null"
              >
                <font-awesome-icon icon="fa-solid fa-arrow-right" class="w-4 h-4 rotate-180" />
              </button>
              <div class="relative shrink-0">
                <div
                  class="w-10 h-10 sm:w-14 sm:h-14 rounded-2xl bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-lg sm:text-xl"
                >
                  <img
                    v-if="selectedConversation.user_avatar"
                    :src="getAvatarUrl(selectedConversation.user_avatar)"
                    alt="Avatar"
                    class="w-full h-full object-cover rounded-2xl"
                  />
                  <span v-else>{{ selectedConversation.user_name.charAt(0) }}</span>
                </div>
                <div
                  v-if="selectedConversation.is_online"
                  class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full bg-green-500 border-2 border-white"
                ></div>
              </div>
              <div class="min-w-0">
                <div class="flex items-center gap-3">
                  <h2 class="text-lg sm:text-2xl font-bold text-gray-900 truncate">
                    {{ selectedConversation.user_name }}
                  </h2>
                  <span
                    v-if="selectedConversation.is_breeder"
                    class="hidden sm:inline-block px-3 py-1 bg-green-500 text-white text-xs font-bold rounded-full"
                  >
                    Zweryfikowany hodowca
                  </span>
                </div>
                <p class="hidden sm:block text-gray-600 text-sm">Użytkownik serwisu</p>
              </div>
            </div>

            <div class="flex items-center gap-3 shrink-0">
              <router-link
                :to="`/profile/${selectedConversation.user_id}`"
                class="px-3 py-2 sm:px-4 rounded-xl border border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50 transition-colors text-sm font-medium"
              >
                Profil
              </router-link>
            </div>
          </div>

          <!-- Messages -->
          <div ref="messagesContainer" class="flex-1 min-h-0 overflow-y-auto p-4 sm:p-6 bg-white">
            <div class="space-y-6">
              <!-- Date Separator -->
              <div class="flex items-center justify-center my-4 sm:my-8">
                <div class="px-4 py-2 bg-gray-100 rounded-full">
                  <span class="text-sm font-medium text-gray-700">Dziś</span>
                </div>
              </div>

              <!-- Messages -->
              <div
                v-for="message in messages"
                :key="message.id"
                class="flex"
                :class="message.is_sent ? 'justify-end' : 'justify-start'"
              >
                <div class="max-w-2xl">
                  <div class="relative group" :class="message.is_sent ? 'ml-auto' : ''">
                    <div
                      class="px-4 py-3 sm:px-6 sm:py-4 rounded-2xl shadow-sm"
                      :class="
                        message.is_sent
                          ? 'bg-blue-500 text-white rounded-br-none'
                          : 'bg-white text-gray-900 border border-gray-200 rounded-bl-none'
                      "
                    >
                      <p class="text-base leading-relaxed">{{ message.content }}</p>
                      <div class="flex items-center justify-between mt-3">
                        <p
                          class="text-xs"
                          :class="message.is_sent ? 'text-blue-200' : 'text-gray-500'"
                        >
                          {{ formatTime(message.created_at) }}
                        </p>
                        <div v-if="message.is_sent" class="flex items-center gap-1">
                          <font-awesome-icon
                            v-if="message.is_read"
                            icon="fa-solid fa-shield-halved"
                            class="w-4 h-4 text-blue-300"
                          />
                          <font-awesome-icon
                            v-else
                            icon="fa-solid fa-circle-check"
                            class="w-4 h-4 text-blue-300"
                          />
                        </div>
                      </div>
                    </div>

                    <!-- Message actions -->
                    <div
                      v-if="message.is_sent"
                      class="absolute right-0 -bottom-6 opacity-0 group-hover:opacity-100 transition-opacity"
                    >
                      <button
                        title="Usuń wiadomość"
                        class="p-2 text-gray-400 hover:text-red-600 transition-colors"
                        @click="removeMessage(message)"
                      >
                        <font-awesome-icon icon="fa-solid fa-trash" class="w-4 h-4" />
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="messages.length === 0" class="text-center py-12">
                <div
                  class="w-24 h-24 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center"
                >
                  <font-awesome-icon icon="fa-solid fa-comment" class="w-12 h-12 text-gray-400" />
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Rozpocznij rozmowę</h3>
                <p class="text-gray-600 max-w-md mx-auto">
                  To początek Twojej konwersacji z {{ selectedConversation.user_name }}. Napisz
                  pierwszą wiadomość!
                </p>
              </div>
            </div>

            <!-- Auto-scroll anchor -->
            <div ref="messagesEnd"></div>
          </div>

          <!-- Input -->
          <div class="p-6 border-t border-gray-200 bg-white/90 backdrop-blur-sm">
            <p v-if="sendError" class="text-sm text-red-600 mb-2">{{ sendError }}</p>
            <div class="flex items-end gap-4">
              <div class="flex-1 relative">
                <textarea
                  v-model="newMessage"
                  maxlength="5000"
                  class="w-full px-6 py-4 bg-white rounded-2xl border border-gray-200 focus:border-blue-500 focus:ring-3 focus:ring-blue-200 focus:outline-none resize-none transition-all"
                  placeholder="Napisz wiadomość..."
                  rows="2"
                  @keydown.enter.exact.prevent="sendMessage"
                  @focus="dismissMessageToast"
                ></textarea>
              </div>
              <button
                :disabled="sending || !newMessage.trim() || !authStore.user?.is_active"
                class="group relative px-8 py-4 bg-blue-600 text-white rounded-2xl font-bold hover:shadow-xl hover:-translate-y-1 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:transform-none"
                @click="sendMessage"
              >
                <div
                  class="absolute inset-0 bg-blue-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                ></div>
                <span v-if="!sending" class="relative flex items-center gap-3">
                  Wyślij
                  <font-awesome-icon
                    icon="fa-solid fa-paper-plane"
                    class="w-5 h-5 transform group-hover:translate-x-1 transition-transform"
                  />
                </span>
                <span v-else class="relative flex items-center gap-2">
                  <font-awesome-icon icon="fa-solid fa-spinner" class="animate-spin h-5 w-5" />
                  Wysyłanie...
                </span>
              </button>
            </div>
            <div class="mt-4 text-xs text-gray-500 flex items-center justify-between">
              <span>Naciśnij Enter aby wysłać</span>
              <span>{{ newMessage.length }}/5000</span>
            </div>
          </div>
        </div>

        <!-- Empty State - tylko desktop, na mobile lista konwersacji juz
        zajmuje cala szerokosc, wiec ten placeholder obok niej jest zbedny -->
        <div
          v-else
          class="hidden lg:flex lg:col-span-3 bg-white/80 backdrop-blur-sm rounded-2xl border border-gray-200 shadow-lg items-center justify-center"
        >
          <div class="text-center p-12 max-w-md">
            <div
              class="w-32 h-32 mx-auto mb-8 rounded-full bg-gray-100 flex items-center justify-center text-gray-400"
            >
              <font-awesome-icon icon="fa-solid fa-comment-dots" class="w-12 h-12" />
            </div>
            <h3 class="text-3xl font-bold text-gray-900 mb-4">Wybierz konwersację</h3>
            <p class="text-gray-600 mb-8">
              Wybierz rozmowę z listy po lewej stronie, aby zobaczyć wiadomości i kontynuować
              dyskusję.
            </p>
            <router-link
              to="/auctions"
              class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all"
            >
              Przeglądaj oferty
              <font-awesome-icon icon="fa-solid fa-arrow-right" class="w-5 h-5" />
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
  import { useMessageStore } from '@/stores/message'
  import { useAuthStore } from '@/stores/auth'
  import { useRoute } from 'vue-router'
  import { useRealtimeConnection } from '@/composables/useRealtimeConnection'
  import { useSeo } from '@/composables/useSeo'
  import { useToast } from '@/composables/useToast'
  import IconPicker from '@/components/icons/IconPicker.vue'
  import api from '@/services/api'

  const route = useRoute()
  const messageStore = useMessageStore()
  const authStore = useAuthStore()
  const { removeToast } = useToast()

  const dismissMessageToast = () => {
    if (messageStore.lastMessageToastId) {
      removeToast(messageStore.lastMessageToastId)
      messageStore.lastMessageToastId = null
    }
  }

  const conversations = ref([])
  const messages = ref([])
  const selectedConversation = ref(null)
  const newMessage = ref('')
  const sending = ref(false)
  const sendError = ref('')

  const totalUnread = computed(() =>
    (conversations.value || []).reduce((sum, c) => sum + (c.unread_count || 0), 0)
  )
  const searchQuery = ref('')
  const messagesEnd = ref(null)
  const messagesContainer = ref(null)

  const { disconnect } = useRealtimeConnection()

  // Most miedzy realtime-owym messageStore.messages (aktualizowanym globalnie
  watch(
    () => messageStore.messages?.length,
    () => {
      if (selectedConversation.value) {
        const otherId = selectedConversation.value.user_id
        const knownIds = new Set(messages.value.map((m) => m.id))
        const incoming = (messageStore.messages || []).filter(
          (m) => !knownIds.has(m.id) && (m.sender_id === otherId || m.recipient_id === otherId)
        )
        if (incoming.length > 0) {
          incoming.forEach((m) => {
            messages.value.push({
              id: m.id,
              content: m.content,
              created_at: m.created_at,
              is_sent: m.sender_id === authStore.user?.id,
              is_read: !!m.read_at,
            })
          })
          scrollToBottom()
        }
      }

      fetchConversations()
    }
  )

  const filteredConversations = computed(() => {
    if (!searchQuery.value) return conversations.value

    return conversations.value.filter((conv) =>
      conv.user_name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  })

  const getAvatarUrl = (path) => {
    if (!path) return ''
    if (path.startsWith('http')) return path
    return `${window.location.origin}/storage/${path}`
  }

  const formatTime = (dateTime) => {
    const date = new Date(dateTime)
    const now = new Date()
    const diff = now - date

    if (diff < 1000 * 60) {
      return 'Teraz'
    } else if (diff < 1000 * 60 * 60) {
      const minutes = Math.floor(diff / (1000 * 60))
      return `${minutes}m temu`
    } else if (diff < 1000 * 60 * 60 * 24) {
      return date.toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' })
    } else {
      return date.toLocaleDateString('pl-PL')
    }
  }

  const scrollToBottom = async () => {
    await nextTick()
    const container = messagesContainer.value
    if (container) {
      container.scrollTo({ top: container.scrollHeight, behavior: 'smooth' })
    }
  }

  const fetchConversations = async () => {
    try {
      const data = await messageStore.fetchConversations(1)
      conversations.value = Array.isArray(data) ? data : data?.data || []
    } catch (error) {
      console.error('Error fetching conversations:', error)
    }
  }

  const selectConversation = async (conversation) => {
    selectedConversation.value = conversation

    try {
      const data = await messageStore.fetchConversation(conversation.user_id)
      messages.value = Array.isArray(data) ? data : []
      await scrollToBottom()

      conversation.unread_count = 0
      messageStore.fetchUnreadCount()
    } catch (error) {
      console.error('Error fetching conversation:', error)
    }
  }

  const removeMessage = async (message) => {
    try {
      await messageStore.deleteMessage(message.id)
      messages.value = messages.value.filter((m) => m.id !== message.id)
    } catch (error) {
      sendError.value = error.response?.data?.message || 'Nie udało się usunąć wiadomości'
      setTimeout(() => {
        sendError.value = ''
      }, 6000)
    }
  }

  const sendMessage = async () => {
    if (!newMessage.value.trim() || !selectedConversation.value) return

    const messageBody = newMessage.value
    newMessage.value = ''
    sending.value = true

    try {
      const sentMessage = await messageStore.sendMessage(
        selectedConversation.value.user_id,
        messageBody
      )

      messages.value.push({
        id: sentMessage.id,
        content: sentMessage.content,
        created_at: sentMessage.created_at,
        is_sent: true,
      })

      await scrollToBottom()
    } catch (error) {
      console.error('Error sending message:', error)
      newMessage.value = messageBody
      sendError.value =
        error.response?.data?.message || 'Nie udało się wysłać wiadomości. Spróbuj ponownie.'
      setTimeout(() => {
        sendError.value = ''
      }, 6000)
    } finally {
      sending.value = false
    }
  }

  let refreshIntervalId = null

  onMounted(async () => {
    useSeo({
      title: 'Wiadomości',
      description:
        'Komunikuj się z innymi hodowcami gołębi. Przeglądaj i zarabiaj z wiadomościami.',
    })

    await fetchConversations()

    // If user_id in query, open that conversation - a jesli to pierwsza
    if (route.query.user) {
      const userId = parseInt(route.query.user)
      const conversation = conversations.value.find((c) => c.user_id === userId)
      if (conversation) {
        selectConversation(conversation)
      } else {
        try {
          const res = await api.get(`/users/${userId}`)
          const otherUser = res.data.data || res.data
          selectConversation({
            user_id: userId,
            user_name: otherUser.name,
            user_avatar: otherUser.avatar || null,
            unread_count: 0,
          })
        } catch (error) {
          console.error('Nie udało się załadować danych nowego odbiorcy wiadomości:', error)
        }
      }
    }

    // Auto-refresh conversations every 5 seconds
    refreshIntervalId = setInterval(fetchConversations, 5000)
  })

  onUnmounted(() => {
    disconnect()
    if (refreshIntervalId) {
      clearInterval(refreshIntervalId)
    }
    messageStore.currentConversation = null
  })
</script>
