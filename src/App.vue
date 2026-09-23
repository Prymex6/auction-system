<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white/90 backdrop-blur-md border-b border-gray-100 sticky top-0 z-50 shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <!-- Logo -->
          <div class="flex items-center">
            <RouterLink to="/" class="flex items-center gap-2">
              <img :src="'/images/logo-golab.png'" alt="Gołębiowy Lot" class="w-9 h-9" />
              <span class="text-xl font-bold text-gray-900 hidden sm:block">
                {{ platformName }}
              </span>
            </RouterLink>
          </div>

          <!-- Desktop Menu -->
          <div class="hidden lg:flex items-center space-x-6">
            <div class="relative">
              <input
                type="search"
                placeholder="Szukaj aukcji..."
                class="pl-10 pr-4 py-2 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm w-64"
                @keyup.enter="handleSearch"
              />
              <font-awesome-icon
                icon="fa-solid fa-magnifying-glass"
                class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
              />
            </div>

            <RouterLink
              to="/auctions"
              class="text-gray-700 hover:text-blue-600 font-medium transition-colors"
            >
              Aukcje
            </RouterLink>

            <RouterLink
              to="/how-it-works"
              class="text-gray-700 hover:text-blue-600 font-medium transition-colors"
            >
              Jak to działa?
            </RouterLink>

            <RouterLink
              to="/blog"
              class="text-gray-700 hover:text-blue-600 font-medium transition-colors"
            >
              Blog
            </RouterLink>

            <!-- Search Bar -->

            <!-- Auth Buttons / User Menu -->
            <div class="flex items-center space-x-4">
              <template v-if="!authStore.isAuthenticated">
                <RouterLink
                  to="/login"
                  class="text-gray-700 hover:text-blue-600 font-medium transition-colors"
                >
                  Logowanie
                </RouterLink>
                <RouterLink
                  to="/register"
                  class="px-4 py-2 bg-blue-600 text-white rounded-xl font-medium hover:shadow-md transition-shadow"
                >
                  Rejestracja
                </RouterLink>
              </template>

              <template v-else>
                <!-- Wishlist -->
                <RouterLink
                  to="/auctions?watchlist=1"
                  class="relative p-2 text-gray-700 hover:text-blue-600 transition-colors"
                  aria-label="Obserwowane"
                  title="Obserwowane"
                >
                  <font-awesome-icon icon="fa-solid fa-heart" class="w-5 h-5" />
                </RouterLink>

                <!-- Messages -->
                <RouterLink
                  to="/messages"
                  class="relative p-2 text-gray-700 hover:text-blue-600 transition-colors"
                >
                  <font-awesome-icon icon="fa-solid fa-comment-dots" class="w-5 h-5" />
                  <span
                    v-if="messageStore.unreadCount > 0"
                    class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"
                  >
                    {{ messageStore.unreadCount }}
                  </span>
                </RouterLink>

                <!-- Notifications -->
                <div ref="notificationsWrapperRef" class="relative">
                  <button
                    class="relative p-2 text-gray-700 hover:text-blue-600 transition-colors"
                    @click="toggleNotifications"
                  >
                    <font-awesome-icon icon="fa-solid fa-bell" class="w-5 h-5" />
                    <span
                      v-if="notificationStore.unreadCount > 0"
                      class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"
                    >
                      {{ notificationStore.unreadCount }}
                    </span>
                  </button>

                  <!-- Notifications Dropdown -->
                  <div
                    v-if="notificationsOpen"
                    class="absolute right-0 mt-2 w-80 bg-white rounded-xl border border-gray-100 shadow-xl z-50 overflow-hidden"
                  >
                    <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                      <h3 class="font-bold text-gray-900">Powiadomienia</h3>
                      <button
                        class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                        @click="notificationStore.markAllAsRead()"
                      >
                        Oznacz wszystkie
                      </button>
                    </div>
                    <div class="max-h-96 overflow-y-auto">
                      <p
                        v-if="notificationStore.notifications.length === 0"
                        class="p-4 text-sm text-gray-500 text-center"
                      >
                        Brak powiadomień
                      </p>
                      <div
                        v-for="notif in notificationStore.notifications.slice(0, 5)"
                        :key="notif.id"
                        class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
                        @click="notificationStore.markAsRead(notif.id)"
                      >
                        <p class="font-medium text-sm text-gray-900">{{ notif.title }}</p>
                        <p class="text-sm text-gray-600 mt-1">{{ notif.message }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- User Menu -->
                <div ref="userMenuWrapperRef" class="relative">
                  <button
                    class="flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300"
                    @click="userMenuOpen = !userMenuOpen"
                  >
                    <div
                      class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white text-sm"
                    >
                      {{ authStore.user?.name?.charAt(0) || 'U' }}
                    </div>
                    <span class="text-gray-700 font-medium hidden lg:block">{{
                      authStore.user?.name || 'User'
                    }}</span>
                    <font-awesome-icon
                      icon="fa-solid fa-chevron-down"
                      class="w-4 h-4 text-gray-500"
                    />
                  </button>

                  <div
                    v-if="userMenuOpen"
                    class="absolute right-0 mt-2 w-48 bg-white rounded-xl border border-gray-100 shadow-xl z-50 overflow-hidden"
                  >
                    <RouterLink
                      to="/profile"
                      class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 transition-colors"
                    >
                      <font-awesome-icon icon="fa-solid fa-user" class="w-5 h-5 text-gray-500" />
                      Profil
                    </RouterLink>
                    <RouterLink
                      to="/profile?tab=settings"
                      class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 transition-colors"
                    >
                      <font-awesome-icon icon="fa-solid fa-sliders" class="w-5 h-5 text-gray-500" />
                      Ustawienia
                    </RouterLink>
                    <RouterLink
                      v-if="authStore.user?.is_admin"
                      to="/admin"
                      class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-blue-50 transition-colors"
                    >
                      <font-awesome-icon icon="fa-solid fa-gear" class="w-5 h-5 text-gray-500" />
                      Admin Panel
                    </RouterLink>
                    <button
                      class="flex items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50 transition-colors w-full text-left"
                      @click="handleLogout"
                    >
                      <font-awesome-icon
                        icon="fa-solid fa-arrow-right-from-bracket"
                        class="w-5 h-5"
                      />
                      Wyloguj
                    </button>
                  </div>
                </div>
              </template>
            </div>
          </div>

          <!-- Mobile Menu Button -->
          <div class="flex items-center gap-2 lg:hidden">
            <template v-if="authStore.isAuthenticated">
              <RouterLink
                to="/auctions?watchlist=1"
                class="relative p-2 text-gray-700"
                aria-label="Obserwowane"
              >
                <font-awesome-icon icon="fa-solid fa-heart" class="w-5 h-5" />
              </RouterLink>

              <RouterLink to="/messages" class="relative p-2 text-gray-700" aria-label="Wiadomości">
                <font-awesome-icon icon="fa-solid fa-comment-dots" class="w-5 h-5" />
                <span
                  v-if="messageStore.unreadCount > 0"
                  class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"
                >
                  {{ messageStore.unreadCount }}
                </span>
              </RouterLink>

              <div ref="notificationsMobileWrapperRef" class="relative">
                <button
                  class="relative p-2 text-gray-700"
                  aria-label="Powiadomienia"
                  @click="toggleNotifications"
                >
                  <font-awesome-icon icon="fa-solid fa-bell" class="w-5 h-5" />
                  <span
                    v-if="notificationStore.unreadCount > 0"
                    class="absolute top-1 right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center"
                  >
                    {{ notificationStore.unreadCount }}
                  </span>
                </button>

                <!-- Notifications Dropdown (mobilny odpowiednik panelu z menu desktopowego) -->
                <div
                  v-if="notificationsOpen"
                  class="absolute right-0 mt-2 w-72 max-w-[calc(100vw-2rem)] bg-white rounded-xl border border-gray-100 shadow-xl z-50 overflow-hidden"
                >
                  <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                    <h3 class="font-bold text-gray-900">Powiadomienia</h3>
                    <button
                      class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                      @click="notificationStore.markAllAsRead()"
                    >
                      Oznacz wszystkie
                    </button>
                  </div>
                  <div class="max-h-96 overflow-y-auto">
                    <p
                      v-if="notificationStore.notifications.length === 0"
                      class="p-4 text-sm text-gray-500 text-center"
                    >
                      Brak powiadomień
                    </p>
                    <div
                      v-for="notif in notificationStore.notifications.slice(0, 5)"
                      :key="notif.id"
                      class="p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
                      @click="notificationStore.markAsRead(notif.id)"
                    >
                      <p class="font-medium text-sm text-gray-900">{{ notif.title }}</p>
                      <p class="text-sm text-gray-600 mt-1">{{ notif.message }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </template>

            <!-- Search Button for Mobile -->
            <button class="text-gray-700 p-2" @click="mobileSearchOpen = !mobileSearchOpen">
              <font-awesome-icon icon="fa-solid fa-magnifying-glass" class="w-5 h-5" />
            </button>

            <button class="text-gray-700 p-2" @click="mobileMenuOpen = !mobileMenuOpen">
              <font-awesome-icon icon="fa-solid fa-bars" class="w-6 h-6" />
            </button>
          </div>
        </div>

        <!-- Mobile Search Bar -->
        <div v-if="mobileSearchOpen" class="pb-4 lg:hidden">
          <div class="relative">
            <input
              type="search"
              placeholder="Szukaj aukcji..."
              class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300"
              @keyup.enter="handleSearch"
            />
            <font-awesome-icon
              icon="fa-solid fa-magnifying-glass"
              class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-400"
            />
          </div>
        </div>

        <!-- Mobile Menu -->
        <div v-if="mobileMenuOpen" class="lg:hidden border-t border-gray-100 pt-4 pb-6">
          <div class="space-y-4">
            <RouterLink
              to="/auctions"
              class="block text-gray-700 hover:text-blue-600 font-medium transition-colors py-2"
              @click="mobileMenuOpen = false"
            >
              Aukcje
            </RouterLink>

            <RouterLink
              to="/how-it-works"
              class="block text-gray-700 hover:text-blue-600 font-medium transition-colors py-2"
              @click="mobileMenuOpen = false"
            >
              Jak to działa?
            </RouterLink>

            <RouterLink
              to="/blog"
              class="block text-gray-700 hover:text-blue-600 font-medium transition-colors py-2"
              @click="mobileMenuOpen = false"
            >
              Blog
            </RouterLink>

            <template v-if="!authStore.isAuthenticated">
              <RouterLink
                to="/login"
                class="block text-gray-700 hover:text-blue-600 font-medium transition-colors py-2"
                @click="mobileMenuOpen = false"
              >
                Logowanie
              </RouterLink>
              <RouterLink
                to="/register"
                class="block px-4 py-3 bg-blue-600 text-white rounded-xl font-medium text-center hover:shadow-md transition-shadow"
                @click="mobileMenuOpen = false"
              >
                Rejestracja
              </RouterLink>
            </template>

            <template v-else>
              <RouterLink
                to="/profile"
                class="block text-gray-700 hover:text-blue-600 font-medium transition-colors py-2"
                @click="mobileMenuOpen = false"
              >
                Profil
              </RouterLink>
              <RouterLink
                to="/profile?tab=settings"
                class="block text-gray-700 hover:text-blue-600 font-medium transition-colors py-2"
                @click="mobileMenuOpen = false"
              >
                Ustawienia
              </RouterLink>
              <RouterLink
                v-if="authStore.user?.is_admin"
                to="/admin"
                class="block text-gray-700 hover:text-blue-600 font-medium transition-colors py-2"
                @click="mobileMenuOpen = false"
              >
                Admin Panel
              </RouterLink>
              <button
                class="block w-full text-left text-red-600 hover:text-red-700 font-medium transition-colors py-2"
                @click="handleLogout"
              >
                Wyloguj
              </button>
            </template>
          </div>
        </div>
      </div>
    </nav>

    <!-- Ogłoszenie systemowe -->
    <div
      v-if="showAnnouncement"
      class="px-4 py-3 text-center text-sm font-medium relative"
      :class="announcementClasses"
    >
      <span class="pr-6">{{ platformSettings.system_announcement }}</span>
      <button
        class="absolute right-4 top-1/2 -translate-y-1/2 opacity-70 hover:opacity-100"
        aria-label="Zamknij ogłoszenie"
        @click="announcementDismissed = true"
      >
        <font-awesome-icon icon="fa-solid fa-xmark" class="w-4 h-4" />
      </button>
    </div>

    <!-- Main Content -->
    <main>
      <RouterView />
    </main>

    <!-- Footer -->
    <footer v-if="routeReady" class="bg-gray-900 text-white py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8 text-center md:text-left">
          <div>
            <div class="flex items-center justify-center md:justify-start gap-2 mb-4">
              <img :src="platformLogo" alt="Gołębiowy Lot" class="w-9 h-9" />
              <span class="text-xl font-bold">{{ platformName }}</span>
            </div>
            <p class="text-gray-400 text-sm">
              {{ platformDescription }}
            </p>
            <p v-if="getPlatformEmail" class="text-gray-400 text-sm mt-3">
              <span class="text-gray-500">E-mail:</span>
              <a
                :href="`mailto:${getPlatformEmail}`"
                class="hover:text-white transition-colors ml-1"
                >{{ getPlatformEmail }}</a
              >
            </p>
            <p v-if="getPlatformPhone" class="text-gray-400 text-sm">
              <span class="text-gray-500">Telefon:</span>
              <a
                :href="`tel:${formattedPlatformPhone.replace(/\s/g, '')}`"
                class="hover:text-white transition-colors ml-1"
                >{{ formattedPlatformPhone }}</a
              >
            </p>
          </div>
          <div>
            <h4 class="font-bold mb-4">Platforma</h4>
            <ul class="space-y-2 text-sm text-gray-400">
              <li>
                <RouterLink to="/auctions" class="hover:text-white transition-colors"
                  >Aukcje</RouterLink
                >
              </li>
              <li>
                <RouterLink to="/how-it-works" class="hover:text-white transition-colors"
                  >Jak to działa?</RouterLink
                >
              </li>
              <li>
                <RouterLink to="/blog" class="hover:text-white transition-colors">Blog</RouterLink>
              </li>
            </ul>
          </div>
          <div>
            <h4 class="font-bold mb-4">Wsparcie</h4>
            <ul class="space-y-2 text-sm text-gray-400">
              <li>
                <RouterLink to="/help" class="hover:text-white transition-colors"
                  >Centrum pomocy</RouterLink
                >
              </li>
              <li>
                <RouterLink to="/contact" class="hover:text-white transition-colors"
                  >Kontakt</RouterLink
                >
              </li>
              <li>
                <RouterLink to="/faq" class="hover:text-white transition-colors">FAQ</RouterLink>
              </li>
              <li>
                <RouterLink to="/security" class="hover:text-white transition-colors"
                  >Bezpieczeństwo</RouterLink
                >
              </li>
            </ul>
          </div>
          <div>
            <h4 class="font-bold mb-4">Prawne</h4>
            <ul class="space-y-2 text-sm text-gray-400">
              <li>
                <RouterLink to="/terms" class="hover:text-white transition-colors"
                  >Regulamin</RouterLink
                >
              </li>
              <li>
                <RouterLink to="/privacy" class="hover:text-white transition-colors"
                  >Polityka prywatności</RouterLink
                >
              </li>
              <li>
                <RouterLink to="/cookies" class="hover:text-white transition-colors"
                  >Cookies</RouterLink
                >
              </li>
              <li>
                <button class="hover:text-white transition-colors" @click="openCookieSettings">
                  Ustawienia cookies
                </button>
              </li>
            </ul>
          </div>
        </div>
        <div class="border-t border-gray-800 pt-8 text-center text-sm text-gray-400">
          <p>
            &copy; {{ new Date().getFullYear() }} {{ platformName }}. Wszelkie prawa zastrzeżone.
          </p>
        </div>
      </div>
    </footer>

    <CookieConsent ref="cookieConsentRef" />
    <ToastNotifications />
  </div>
</template>

<script setup>
  import { ref, onMounted, onUnmounted, computed, watch } from 'vue'
  import { useRouter } from 'vue-router'
  import { routeReady } from '@/router'
  import { useAuthStore } from '@/stores/auth'
  import { useNotificationStore } from '@/stores/notification'
  import { useMessageStore } from '@/stores/message'
  import { usePlatformSettings } from '@/composables/usePlatformSettings'
  import { basePageTitle } from '@/composables/usePageTitle'
  import '@/assets/styles.css'
  import CookieConsent from '@/components/CookieConsent.vue'
  import ToastNotifications from '@/components/ToastNotifications.vue'

  const router = useRouter()
  const authStore = useAuthStore()
  const notificationStore = useNotificationStore()
  const messageStore = useMessageStore()
  const {
    getPlatformName,
    getPlatformDescription,
    getPlatformEmail,
    getPlatformPhone,
    getPlatformLogo,
    loadSettings,
    platformSettings,
  } = usePlatformSettings()

  const announcementDismissed = ref(false)
  const showAnnouncement = computed(
    () =>
      !!platformSettings.value.announcement_active &&
      !!platformSettings.value.system_announcement &&
      !announcementDismissed.value
  )
  const announcementClasses = computed(() => {
    const map = {
      info: 'bg-blue-50 text-blue-900 border-b border-blue-200',
      warning: 'bg-amber-50 text-amber-900 border-b border-amber-200',
      success: 'bg-green-50 text-green-900 border-b border-green-200',
      error: 'bg-red-50 text-red-900 border-b border-red-200',
    }
    return map[platformSettings.value.announcement_type] || map.info
  })

  const platformName = computed(() => getPlatformName.value)
  const platformDescription = computed(() => getPlatformDescription.value)
  const platformLogo = computed(() => getPlatformLogo.value || '/images/logo-golab.png')

  // Formatuje numer telefonu do czytelnej postaci: polski numer 9-cyfrowy
  const formattedPlatformPhone = computed(() => {
    const raw = (getPlatformPhone.value || '').trim()
    const digits = raw.replace(/[^\d]/g, '')
    if (raw.startsWith('+')) {
      const countryDigits = digits.length - 9
      if (countryDigits > 0) {
        return `+${digits.slice(0, countryDigits)} ${digits.slice(countryDigits).replace(/(\d{3})(?=\d)/g, '$1 ')}`
      }
    }
    if (digits.length === 9) {
      return `+48 ${digits.replace(/(\d{3})(?=\d)/g, '$1 ')}`
    }
    return raw
  })

  const totalUnreadCount = computed(() => notificationStore.unreadCount + messageStore.unreadCount)
  watch(
    [totalUnreadCount, basePageTitle],
    ([count, title]) => {
      document.title = count > 0 ? `(${count}) ${title}` : title
    },
    { immediate: true }
  )

  const cookieConsentRef = ref(null)
  const openCookieSettings = () => cookieConsentRef.value?.open()

  const notificationsOpen = ref(false)
  const notificationsWrapperRef = ref(null)
  const notificationsMobileWrapperRef = ref(null)
  const toggleNotifications = () => {
    notificationsOpen.value = !notificationsOpen.value
    if (notificationsOpen.value) {
      notificationStore.fetchNotifications()
    }
  }
  const userMenuOpen = ref(false)
  const userMenuWrapperRef = ref(null)
  const mobileMenuOpen = ref(false)
  const mobileSearchOpen = ref(false)

  const handleClickOutside = (event) => {
    if (
      notificationsOpen.value &&
      !notificationsWrapperRef.value?.contains(event.target) &&
      !notificationsMobileWrapperRef.value?.contains(event.target)
    ) {
      notificationsOpen.value = false
    }
    if (userMenuOpen.value && !userMenuWrapperRef.value?.contains(event.target)) {
      userMenuOpen.value = false
    }
  }

  onMounted(async () => {
    document.addEventListener('click', handleClickOutside)
    await loadSettings()
    if (authStore.isAuthenticated) {
      await notificationStore.fetchUnreadCount()
      await messageStore.fetchUnreadCount()

      notificationStore.listenNotifications()
      if (authStore.user?.id) {
        messageStore.listenPrivateMessages(authStore.user.id)
      }
    }
  })

  onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
  })

  const handleLogout = async () => {
    await authStore.logout()
    router.push('/login')
  }

  const handleSearch = (e) => {
    const query = e.target.value
    if (query) {
      // AuctionList.vue czyta parametr 'q' (nie 'search') przy inicjalizacji
      router.push({
        name: 'AuctionList',
        query: { q: query },
      })
    }
  }
</script>

<style scoped></style>
