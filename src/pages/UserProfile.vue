<template>
  <div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <section class="pt-24 pb-12 md:pt-28 md:pb-16 relative overflow-hidden">
      <div class="absolute inset-0 bg-white"></div>
      <div class="absolute top-20 right-20 w-96 h-96 bg-blue-100/30 rounded-full blur-3xl"></div>
      <div
        class="absolute bottom-20 left-20 w-96 h-96 bg-purple-100/30 rounded-full blur-3xl"
      ></div>
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="text-center max-w-4xl mx-auto">
          <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 mb-6">
            Profil
            <span class="block text-gray-900"> hodowcy </span>
          </h1>
          <p class="text-xl text-gray-600 mb-10 max-w-2xl mx-auto leading-relaxed">
            Poznaj użytkownika i jego aukcje
          </p>
        </div>
      </div>
    </section>

    <!-- Loading -->
    <div v-if="loading" class="min-h-screen flex items-center justify-center">
      <div class="relative">
        <div class="w-12 h-12 border-4 border-gray-200 rounded-full"></div>
        <div
          class="absolute top-0 left-0 w-12 h-12 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"
        ></div>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="py-8 max-w-md mx-auto">
      <div class="bg-red-50 border border-red-200 rounded-2xl p-8">
        <h3 class="font-bold text-gray-900 text-lg mb-2">
          {{
            error.includes('prywatny')
              ? 'Profil prywatny'
              : error.includes('niedostępny')
                ? 'Profil niedostępny'
                : 'Błąd'
          }}
        </h3>
        <p class="text-gray-700">{{ error }}</p>
        <router-link
          to="/"
          class="mt-4 inline-flex items-center gap-2 px-4 py-2 border border-gray-200 text-gray-700 rounded-xl font-medium hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300"
        >
          Wróć do strony głównej
        </router-link>
      </div>
    </div>

    <!-- User Content -->
    <section v-else-if="user" class="py-8 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-xl p-8 mb-10">
          <div class="flex flex-col md:flex-row gap-8 items-center md:items-start">
            <div
              class="w-28 h-28 md:w-32 md:h-32 rounded-2xl overflow-hidden bg-gray-100 flex items-center justify-center text-3xl font-bold text-blue-700"
            >
              <img
                v-if="user?.avatar"
                :src="getAvatarUrl(user.avatar)"
                alt="Avatar"
                class="w-full h-full object-cover"
              />
              <span v-else>{{ (user?.name || 'U').charAt(0).toUpperCase() }}</span>
            </div>

            <div class="flex-1 text-center md:text-left">
              <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                {{
                  user.first_name && user.last_name
                    ? `${user.first_name} ${user.last_name}`
                    : user.name
                }}
              </h2>
              <p class="text-gray-600 mb-4">@{{ user.name }}</p>

              <div
                class="flex flex-wrap justify-center md:justify-start gap-3 text-sm text-gray-600 mb-6"
              >
                <span
                  v-if="user.city"
                  class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-50 border border-gray-200"
                >
                  <font-awesome-icon icon="fa-solid fa-location-dot" class="w-4 h-4" />
                  {{ user.city }}{{ user.country ? `, ${user.country}` : '' }}
                </span>
                <span
                  v-if="user.created_at"
                  class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-gray-50 border border-gray-200"
                >
                  <font-awesome-icon icon="fa-solid fa-calendar" class="w-4 h-4" />
                  Dołączył {{ formatDate(user.created_at) }}
                </span>
              </div>

              <!-- Reputacja -->
              <div class="flex items-center justify-center md:justify-start gap-3 mb-6">
                <StarRating :model-value="averageRating" readonly :size="20" />
                <span class="text-gray-700 font-semibold">{{ averageRating.toFixed(1) }}</span>
                <span class="text-gray-500 text-sm"
                  >({{ totalReviews }} {{ totalReviews === 1 ? 'ocena' : 'ocen' }})</span
                >
              </div>

              <div
                v-if="authStore.isAuthenticated && authStore.user?.id !== user.id"
                class="mb-6 p-4 rounded-2xl bg-gray-50 border border-gray-200 inline-flex flex-col items-center md:items-start gap-2"
              >
                <span class="text-sm font-medium text-gray-700">
                  {{ myRating ? 'Twoja ocena' : 'Oceń tego hodowcę' }}
                </span>
                <StarRating v-model="myRating" :size="26" @update:model-value="submitRating" />
                <span v-if="ratingSaved" class="text-xs text-green-600">Zapisano ocenę</span>
              </div>

              <div class="flex flex-wrap justify-center md:justify-start gap-3">
                <template v-if="!authStore.isAuthenticated || authStore.user?.id !== user.id">
                  <button
                    v-if="authStore.isAuthenticated && !authStore.user?.is_active"
                    disabled
                    title="Będziesz mógł napisać wiadomość po aktywacji konta przez administratora"
                    class="px-5 py-2.5 rounded-xl bg-gray-300 text-gray-600 font-semibold cursor-not-allowed opacity-60"
                  >
                    Wyślij wiadomość
                  </button>
                  <router-link
                    v-else
                    :to="`/messages?user=${user.id}`"
                    class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-semibold shadow-md hover:shadow-lg transition-all"
                  >
                    Wyślij wiadomość
                  </router-link>
                </template>
                <router-link
                  :to="`/auctions?user=${user.id}`"
                  class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-semibold hover:border-blue-300 hover:bg-blue-50 transition-all"
                >
                  Zobacz aukcje
                </router-link>
                <button
                  v-if="authStore.isAuthenticated && authStore.user?.id !== user.id"
                  :disabled="blockActionLoading"
                  class="px-5 py-2.5 rounded-xl border font-semibold transition-all disabled:opacity-60 disabled:cursor-not-allowed"
                  :class="
                    user.is_blocked_by_viewer
                      ? 'border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50'
                      : 'border-red-200 text-red-600 hover:border-red-300 hover:bg-red-50'
                  "
                  @click="toggleBlock"
                >
                  {{ user.is_blocked_by_viewer ? 'Odblokuj użytkownika' : 'Zablokuj użytkownika' }}
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Auctions Grid -->
        <div
          v-if="auctions.length > 0"
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"
        >
          <router-link
            v-for="auction in auctions"
            :key="auction.id"
            :to="`/auctions/${auction.id}`"
            class="group bg-white rounded-2xl border border-gray-100 shadow-xl overflow-hidden hover:shadow-2xl hover:-translate-y-1 transition-all duration-300"
          >
            <div class="h-56 relative overflow-hidden bg-white">
              <img
                v-if="auction.pigeon_images?.[0] || auction.images?.[0]"
                :src="auction.pigeon_images?.[0] || auction.images?.[0]"
                :alt="auction.title || 'Aukcja'"
                class="absolute inset-0 w-full h-full object-contain p-2 group-hover:scale-110 transition-transform duration-300"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-gray-300">
                <font-awesome-icon icon="fa-solid fa-image" class="w-16 h-16" />
              </div>
            </div>
            <div class="p-6">
              <h3 class="font-bold text-gray-900 text-lg mb-2 truncate">
                {{ auction.title || 'Bez tytułu' }}
              </h3>
              <div class="flex flex-wrap gap-1.5 mb-4">
                <span
                  v-if="auction.ring_number"
                  class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 whitespace-nowrap"
                  >{{ auction.ring_number }}</span
                >
                <span
                  v-if="auction.color"
                  class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 whitespace-nowrap"
                  >{{ auction.color }}</span
                >
                <span
                  v-if="auction.gender"
                  class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 whitespace-nowrap"
                  >{{ formatGender(auction.gender) }}</span
                >
                <span
                  v-if="auction.breed"
                  class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 whitespace-nowrap"
                  >{{ auction.breed }}</span
                >
              </div>
              <div class="text-2xl font-bold text-blue-600">
                {{ formatPrice(auction.current_price || auction.start_price || 0) }}
              </div>
            </div>
          </router-link>
        </div>

        <!-- No Auctions -->
        <div v-else class="text-center py-12">
          <p class="text-gray-600">Ten użytkownik nie ma aukcji</p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { useRoute } from 'vue-router'
  import { useSeo } from '@/composables/useSeo'
  import { useFormatters } from '@/composables/useFormatters'
  import { useToast } from '@/composables/useToast'
  import { useAuthStore } from '@/stores/auth'
  import api from '@/services/api'
  import StarRating from '@/components/StarRating.vue'

  const { formatPrice, formatGender } = useFormatters()
  const { showSuccess, showError } = useToast()
  const authStore = useAuthStore()
  const blockActionLoading = ref(false)

  const route = useRoute()
  const userId = route.params.id

  const user = ref(null)
  const auctions = ref([])
  const loading = ref(true)
  const error = ref(null)

  const averageRating = ref(0)
  const totalReviews = ref(0)
  const myRating = ref(0)
  const ratingSaved = ref(false)

  const loadRatingData = async () => {
    try {
      const statsRes = await api.get(`/users/${userId}/stats`)
      const stats = statsRes.data.data || statsRes.data
      averageRating.value = Number(stats.average_rating) || 0
      totalReviews.value = Number(stats.total_reviews) || 0
    } catch {
      // brak statystyk nie powinien blokowac reszty profilu
    }

    if (authStore.isAuthenticated && String(authStore.user?.id) !== String(userId)) {
      try {
        const reviewsRes = await api.get(`/users/${userId}/reviews`)
        const list = reviewsRes.data.data?.data || reviewsRes.data.data || []
        const own = list.find(
          (r) => String(r.from_user_id) === String(authStore.user.id) && !r.auction_id
        )
        if (own) {
          myRating.value = Number(own.rating)
        }
      } catch {
        // Having left no rating of your own is the ordinary case, not a fault.
      }
    }
  }

  const submitRating = async (value) => {
    if (!authStore.isAuthenticated) {
      showError('Zaloguj się, aby ocenić hodowcę')
      return
    }
    try {
      const res = await api.post(`/users/${userId}/rate`, { rating: value })
      myRating.value = value
      averageRating.value = Number(res.data.average_rating) || averageRating.value
      totalReviews.value = Number(res.data.total_reviews) || totalReviews.value
      ratingSaved.value = true
      setTimeout(() => {
        ratingSaved.value = false
      }, 3000)
    } catch (err) {
      showError(err.response?.data?.message || 'Nie udało się zapisać oceny')
    }
  }

  const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('pl-PL', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    })
  }

  const getAvatarUrl = (path) => {
    if (!path) return ''
    if (path.startsWith('http')) return path
    return `${window.location.origin}/storage/${path}`
  }

  onMounted(async () => {
    try {
      const userRes = await api.get(`/users/${userId}`)
      user.value = userRes.data.data || userRes.data

      useSeo({
        title: user.value?.name || 'Profil użytkownika',
        description: `Profil użytkownika ${user.value?.name}. Przeglądaj jego aukcje i oceny.`,
      })

      const auctionsRes = await api.get(`/users/${userId}/auctions`)
      const responseData = auctionsRes.data
      let auctionsList = []
      if (Array.isArray(responseData?.data?.data)) {
        auctionsList = responseData.data.data
      } else if (Array.isArray(responseData?.data)) {
        auctionsList = responseData.data
      } else if (Array.isArray(responseData)) {
        auctionsList = responseData
      }
      auctions.value = auctionsList
      await loadRatingData()
    } catch (err) {
      error.value = err.response?.data?.message || 'Nie można załadować profilu użytkownika'
    } finally {
      loading.value = false
    }
  })

  const toggleBlock = async () => {
    if (!user.value || blockActionLoading.value) return

    blockActionLoading.value = true
    const wasBlocked = user.value.is_blocked_by_viewer
    try {
      if (wasBlocked) {
        await api.delete(`/users/${user.value.id}/block`)
        user.value.is_blocked_by_viewer = false
        showSuccess('Użytkownik odblokowany')
      } else {
        await api.post(`/users/${user.value.id}/block`)
        user.value.is_blocked_by_viewer = true
        showSuccess('Użytkownik zablokowany')
      }
    } catch (err) {
      showError(err.response?.data?.message || 'Nie udało się zaktualizować blokady')
    } finally {
      blockActionLoading.value = false
    }
  }
</script>
