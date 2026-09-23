<template>
  <div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <HeroSection
      :badge="filters.watchlist ? 'Twoja lista obserwowanych' : 'Elitarne aukcje gołębi pocztowych'"
      :title="filters.watchlist ? 'Twoja' : 'Odkryj świat'"
      :gradient="filters.watchlist ? 'lista obserwowanych' : 'gołębich aukcji'"
      :description="
        filters.watchlist
          ? 'Twoje zapisane oferty w jednym miejscu - śledź ulubione gołębie'
          : 'Przeglądaj żywą giełdę gołębi pocztowych i zdobywaj wyjątkowe okazy od najlepszych hodowców'
      "
    />

    <!-- Search & Filters -->
    <section class="py-8 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div
          class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 mb-12 relative overflow-hidden"
        >
          <div
            class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -translate-y-16 translate-x-16"
          ></div>
          <div
            class="absolute bottom-0 left-0 w-32 h-32 bg-purple-500/5 rounded-full translate-y-16 -translate-x-16"
          ></div>

          <div class="relative">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Znajdź idealnego gołębia</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
              <!-- Search -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Szukaj</label>
                <div class="relative">
                  <input
                    v-model="filters.search"
                    type="text"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    placeholder="Rasa, hodowca..."
                  />
                  <font-awesome-icon
                    icon="fa-solid fa-magnifying-glass"
                    class="absolute right-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
                  />
                </div>
              </div>

              <!-- Breed Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Rasa</label>
                <select
                  v-model="filters.breed"
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                >
                  <option value="">Wszystkie rasy</option>
                  <option v-for="breed in availableBreeds" :key="breed" :value="breed">
                    {{ breed }}
                  </option>
                </select>
              </div>

              <!-- Gender Filter -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Płeć</label>
                <select
                  v-model="filters.gender"
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                >
                  <option value="">Wszystkie</option>
                  <option value="samiec">Samiec</option>
                  <option value="samica">Samica</option>
                  <option value="golab_mlody">Gołąb młody</option>
                </select>
              </div>

              <!-- Sort -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sortuj</label>
                <select
                  v-model="filters.sort"
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                >
                  <option value="newest">Najnowsze</option>
                  <option value="ending">Kończą się</option>
                  <option value="price_low">Cena: rosnąco</option>
                  <option value="price_high">Cena: malejąco</option>
                </select>
              </div>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 mt-8">
              <button
                class="group relative px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                @click="applyFilters"
              >
                <div
                  class="absolute inset-0 bg-blue-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                ></div>
                <span class="relative flex items-center justify-center gap-2">
                  <font-awesome-icon icon="fa-solid fa-magnifying-glass" class="w-4 h-4" />
                  Szukaj
                </span>
              </button>
              <button
                class="px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300"
                @click="resetFilters"
              >
                Resetuj filtry
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Loading State -->
    <GlobalLoadingOverlay v-if="loading" message="Ładowanie aukcji..." />

    <!-- Empty State -->
    <div v-else-if="auctions.length === 0" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center py-16 bg-gray-50 rounded-3xl border-2 border-dashed border-blue-200">
        <div
          class="w-24 h-24 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center"
        >
          <IconPicker
            :name="filters.watchlist ? 'heart' : 'pigeon'"
            :color-class="filters.watchlist ? 'text-blue-400' : 'text-blue-300'"
            class="w-12 h-12"
          />
        </div>
        <h3 class="text-2xl font-bold text-gray-900 mb-3">
          {{ filters.watchlist ? 'Brak zapisanych aukcji' : 'Nie znaleziono aukcji' }}
        </h3>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">
          <span v-if="filters.watchlist">
            Nie masz jeszcze zapisanych aukcji. Przeglądaj oferty i dodawaj ulubione do swojej listy
            klikając ikonę serca.
          </span>
          <span v-else>
            Brak ofert spełniających wybrane kryteria. Zmień filtry lub sprawdź później.
          </span>
        </p>
        <button
          v-if="filters.watchlist"
          class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl font-medium hover:shadow-md transition-all"
          @click="browseAllAuctions"
        >
          <font-awesome-icon icon="fa-solid fa-magnifying-glass" class="w-4 h-4" />
          Przeglądaj wszystkie aukcje
        </button>
        <button
          v-else
          class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl font-medium hover:shadow-md transition-all"
          @click="resetFilters"
        >
          <font-awesome-icon icon="fa-solid fa-arrows-rotate" class="w-4 h-4" />
          Resetuj filtry
        </button>
      </div>
    </div>

    <!-- Auctions Grid -->
    <div v-else class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
        <router-link
          v-for="auction in auctions"
          :key="auction.id"
          :to="`/auctions/${auction.id}`"
          class="group bg-white rounded-2xl border border-gray-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden"
        >
          <!-- Image -->
          <div class="h-80 relative overflow-hidden bg-white">
            <img
              v-if="
                (auction.pigeon_images?.[0] || auction.images?.[0]) &&
                !brokenImageIds.has(auction.id)
              "
              :src="auction.pigeon_images?.[0] || auction.images?.[0]"
              :alt="auction.title"
              class="absolute inset-0 w-full h-full object-contain p-2 group-hover:scale-110 transition-transform duration-300"
              @error="brokenImageIds.add(auction.id)"
            />
            <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
              <IconPicker
                name="pigeon"
                color-class="text-gray-300"
                class="w-24 h-24 transform group-hover:scale-110 transition-transform duration-300"
              />
            </div>

            <!-- Status Badge -->
            <div class="absolute top-2 left-2 z-10">
              <span
                v-if="auction.status === 'active'"
                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 whitespace-nowrap"
              >
                <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                Aktywna
              </span>
              <span
                v-else-if="auction.status === 'ended'"
                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 whitespace-nowrap"
              >
                <span class="w-1.5 h-1.5 bg-gray-500 rounded-full"></span>
                Zakończona
              </span>
              <span
                v-else
                class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800 whitespace-nowrap"
              >
                <span class="w-1.5 h-1.5 bg-yellow-500 rounded-full"></span>
                Oczekująca
              </span>
            </div>
          </div>

          <!-- Content -->
          <div class="p-6">
            <h3
              class="font-bold text-gray-900 text-lg mb-2 group-hover:text-blue-600 transition-colors duration-300 truncate"
            >
              {{ auction.title || 'Bez tytułu' }}
            </h3>
            <p class="text-xs text-gray-500 mt-0.5 mb-2.5 min-h-4 truncate">
              {{ auction.ring_number || ' ' }}
            </p>

            <div class="flex flex-wrap gap-1.5 mb-4 min-h-6">
              <span
                v-if="auction.color"
                class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 self-start whitespace-nowrap"
              >
                {{ auction.color }}
              </span>
              <span
                v-if="auction.gender"
                class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 self-start whitespace-nowrap"
              >
                {{ formatGender(auction.gender) }}
              </span>
              <span
                v-if="auction.breed"
                class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 self-start whitespace-nowrap"
              >
                {{ auction.breed }}
              </span>
            </div>

            <div class="bg-gray-50 p-4 rounded-xl mb-4">
              <p class="text-xs text-gray-500 mb-1">
                {{ auction.type === 'buy_now' ? 'Cena' : 'Aktualna cena' }}
              </p>
              <p class="text-2xl font-bold text-gray-900">{{ auction.current_price || 0 }} zł</p>
            </div>

            <div
              v-if="auction.type === 'auction' || !auction.type"
              class="flex items-center justify-between text-sm"
            >
              <div class="flex items-center gap-1 text-gray-600">
                <font-awesome-icon icon="fa-solid fa-clipboard" class="w-4 h-4" />
                <span>{{ auction.bids_count || 0 }} licytacji</span>
              </div>
              <span v-if="auction.status === 'active'" class="text-gray-600">{{
                getTimeLeft(auction.ends_at)
              }}</span>
              <span
                v-else-if="auction.status === 'pending' || auction.status === 'rejected'"
                class="text-yellow-600 text-xs"
                >Jeszcze się nie rozpoczęła</span
              >
              <span v-else class="text-gray-600">{{ getTimeLeft(auction.ends_at) }}</span>
            </div>

            <!-- Progress Bar only for auctions -->
            <div v-if="auction.type === 'auction' || !auction.type" class="mt-4">
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-blue-600 h-2 rounded-full"
                  :style="{ width: `${getTimeProgressPercent(auction)}%` }"
                ></div>
              </div>
            </div>
          </div>
        </router-link>
      </div>

      <!-- Pagination -->
      <div v-if="pagination.total > pagination.per_page" class="flex justify-center gap-2 mb-16">
        <button
          :disabled="pagination.current_page === 1"
          class="group flex items-center gap-2 px-5 py-3 border border-gray-200 text-gray-700 rounded-xl font-medium hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white"
          @click="previousPage"
        >
          <font-awesome-icon icon="fa-solid fa-chevron-left" class="w-4 h-4" />
          Poprzednia
        </button>

        <div class="flex items-center gap-1">
          <button
            v-for="page in visiblePages"
            :key="page"
            class="px-4 py-3 rounded-xl font-medium transition-all duration-300"
            :class="
              page === pagination.current_page
                ? 'bg-blue-600 text-white shadow-md'
                : 'border border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50/50'
            "
            @click="goToPage(page)"
          >
            {{ page }}
          </button>
        </div>

        <button
          :disabled="pagination.current_page === pagination.last_page"
          class="group flex items-center gap-2 px-5 py-3 border border-gray-200 text-gray-700 rounded-xl font-medium hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white"
          @click="nextPage"
        >
          Następna
          <font-awesome-icon icon="fa-solid fa-chevron-right" class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Newsletter CTA -->
    <section class="max-w-7xl mx-auto py-16 bg-white">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div
          class="bg-white rounded-2xl p-12 border border-gray-100 shadow-xl overflow-hidden relative"
        >
          <div
            class="absolute top-0 right-0 w-64 h-64 bg-blue-500/5 rounded-full -translate-y-32 translate-x-32"
          ></div>
          <div
            class="absolute bottom-0 left-0 w-64 h-64 bg-purple-500/5 rounded-full translate-y-32 -translate-x-32"
          ></div>

          <div class="relative text-center mb-8">
            <div
              class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-blue-600 flex items-center justify-center text-white shadow-lg"
            >
              <font-awesome-icon icon="fa-solid fa-bell" class="w-8 h-8" />
            </div>
            <h2 class="text-3xl font-bold text-gray-900 mb-3">Nie przegap okazji</h2>
            <p class="text-gray-600">
              Zapisz się do newslettera i otrzymuj powiadomienia o nowych aukcjach
            </p>
          </div>

          <form
            class="relative flex flex-col sm:flex-row gap-3"
            @submit.prevent="subscribeNewsletter"
          >
            <input
              v-model="newsletterEmail"
              type="email"
              placeholder="Wpisz swój email..."
              class="flex-1 px-6 py-4 rounded-xl border border-gray-200 bg-white/90 backdrop-blur-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300"
              required
            />
            <button
              type="submit"
              :disabled="newsletterSending"
              class="px-8 py-4 bg-blue-600 text-white rounded-xl font-semibold hover:shadow-lg hover:-translate-y-1 transition-all duration-300 disabled:opacity-60"
            >
              {{ newsletterSending ? 'Zapisywanie...' : 'Zapisz się' }}
            </button>
          </form>

          <p class="relative text-center text-gray-500 text-sm mt-4">
            Zero spamu. Możesz się wypisać w każdej chwili. Zapisując się akceptujesz
            <router-link to="/privacy" class="text-blue-600 hover:underline"
              >Politykę prywatności</router-link
            >.
          </p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
  import { ref, reactive, computed, onMounted, onUnmounted } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import { useAuctionStore } from '@/stores/auction'
  import { HeroSection } from '@/components'
  import { useRealtimeConnection } from '@/composables/useRealtimeConnection'
  import { useSeo } from '@/composables/useSeo'
  import { useFormatters } from '@/composables/useFormatters'
  import api from '@/services/api'
  import { useToast } from '@/composables/useToast'
  import IconPicker from '@/components/icons/IconPicker.vue'
  import GlobalLoadingOverlay from '@/components/GlobalLoadingOverlay.vue'

  useSeo({
    title: 'Aukcje gołębi',
    description:
      'Przeglądaj tys. aukcji gołębi pocztowych. Filtruj po rasie, płci, kolorze. Bezpieczne licytacje z certyfikacją gołębi.',
    url: '/auctions',
  })

  const route = useRoute()
  const router = useRouter()
  const auctionStore = useAuctionStore()
  const { formatPrice, formatGender } = useFormatters()

  // Setup real-time connection for auctions list
  const { disconnect } = useRealtimeConnection({
    auctions: true,
  })

  const { showSuccess, showError } = useToast()
  const loading = ref(false)

  const newsletterEmail = ref('')
  const newsletterSending = ref(false)
  const subscribeNewsletter = async () => {
    newsletterSending.value = true
    try {
      const response = await api.post('/newsletter', { email: newsletterEmail.value })
      showSuccess(response.data.message || 'Zapisano do newslettera!')
      newsletterEmail.value = ''
    } catch (error) {
      showError(error.response?.data?.message || 'Nie udało się zapisać. Spróbuj ponownie.')
    } finally {
      newsletterSending.value = false
    }
  }

  const availableBreeds = ref([])

  const filters = ref({
    search: route.query.q || '',
    breed: route.query.breed || '',
    gender: route.query.gender || '',
    sort: route.query.sort || 'newest',
    watchlist: route.query.watchlist === '1',
    category: route.query.category || '',
  })

  const auctions = ref([])
  const brokenImageIds = reactive(new Set())
  const pagination = ref({
    current_page: 1,
    per_page: 12,
    total: 0,
    last_page: 1,
  })

  // Wishlist helpers
  const wishlistKey = 'auctionWishlist'
  const getWishlist = () => {
    try {
      const raw = localStorage.getItem(wishlistKey)
      return raw ? JSON.parse(raw) : []
    } catch (e) {
      console.warn('Failed to read wishlist:', e)
      return []
    }
  }

  // Computed
  const visiblePages = computed(() => {
    const pages = []
    const maxPages = 5
    let start = Math.max(1, pagination.value.current_page - 2)
    let end = Math.min(pagination.value.last_page, start + maxPages - 1)

    if (end - start < maxPages - 1) {
      start = Math.max(1, end - maxPages + 1)
    }

    for (let i = start; i <= end; i++) {
      pages.push(i)
    }

    return pages
  })

  // Methods
  const getTimeLeft = (endsAt) => {
    if (!endsAt) return ''
    const end = new Date(endsAt)
    const now = new Date()
    const diff = end - now
    if (diff <= 0) return 'Skończona'

    const days = Math.floor(diff / (1000 * 60 * 60 * 24))
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))

    if (days > 0) return `${days}d ${hours}h`
    return `${hours}h ${minutes}m`
  }

  const getTimeProgressPercent = (auction) => {
    if (!auction.started_at || !auction.ends_at) return 0

    const start = new Date(auction.started_at)
    const end = new Date(auction.ends_at)
    const now = new Date()

    const totalTime = end - start
    const remainingTime = end - now

    if (remainingTime <= 0) return 0
    if (totalTime <= 0) return 100

    return Math.max(0, (remainingTime / totalTime) * 100)
  }

  const isEnding = (endsAt) => {
    const now = new Date()
    const end = new Date(endsAt)
    const diff = end - now
    return diff < 1000 * 60 * 60 // Less than 1 hour
  }

  const applyFilters = async () => {
    pagination.value.current_page = 1
    await fetchAuctions()
  }

  // Leave the watchlist view and show everything instead.
  const browseAllAuctions = () => {
    filters.value.watchlist = false
    router.push('/auctions')
    fetchAuctions()
  }

  const resetFilters = () => {
    filters.value = {
      search: '',
      breed: '',
      gender: '',
      sort: 'newest',
      watchlist: false,
    }
    router.push('/auctions')
    pagination.value.current_page = 1
    fetchAuctions()
  }

  const fetchAuctions = async () => {
    loading.value = true
    try {
      const response = await auctionStore.fetchAuctions(pagination.value.current_page, {
        search: filters.value.search,
        breed: filters.value.breed,
        gender: filters.value.gender,
        sort: filters.value.sort,
        category: filters.value.category,
        ...(filters.value.watchlist ? { per_page: 100 } : {}),
      })

      let auctionsList = response.data

      // Filter by watchlist if enabled
      if (filters.value.watchlist) {
        const wishlist = getWishlist()
        auctionsList = auctionsList.filter((auction) => wishlist.includes(auction.id))
      }

      auctions.value = auctionsList
      pagination.value = response.pagination
    } catch (error) {
      console.error('Error fetching auctions:', error)
    } finally {
      loading.value = false
    }
  }

  const loadBreeds = async () => {
    try {
      const response = await api.get('/breeds')
      availableBreeds.value = response.data.data || []
    } catch (error) {
      console.error('Error loading breeds:', error)
    }
  }

  const goToPage = (page) => {
    pagination.value.current_page = page
    fetchAuctions()
  }

  const nextPage = () => {
    if (pagination.value.current_page < pagination.value.last_page) {
      goToPage(pagination.value.current_page + 1)
    }
  }

  const previousPage = () => {
    if (pagination.value.current_page > 1) {
      goToPage(pagination.value.current_page - 1)
    }
  }

  // Lifecycle
  onMounted(() => {
    loadBreeds()
    fetchAuctions()
  })

  onUnmounted(() => {
    disconnect()
  })
</script>

<style scoped>
  .line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }
</style>
