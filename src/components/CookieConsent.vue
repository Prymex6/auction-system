<template>
  <!-- Baner zgody (dolny pasek) -->
  <transition name="cookie-slide">
    <div
      v-if="showBanner && !showModal"
      ref="bannerEl"
      class="fixed bottom-0 inset-x-0 z-[45] p-3 sm:p-4"
      role="dialog"
      aria-label="Zgoda na pliki cookie"
    >
      <div
        class="max-w-4xl mx-auto bg-white rounded-2xl border border-gray-200 shadow-2xl p-5 sm:p-6"
      >
        <div class="flex items-start gap-3 mb-4">
          <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center shrink-0">
            <font-awesome-icon icon="fa-solid fa-cookie-bite" class="w-5 h-5 text-blue-600" />
          </div>
          <div class="min-w-0">
            <h3 class="font-bold text-gray-900 mb-1">Szanujemy Twoją prywatność</h3>
            <p class="text-sm text-gray-600 leading-relaxed">
              Używamy plików cookie niezbędnych do działania serwisu oraz — za Twoją zgodą —
              analitycznych (Google Analytics), które pomagają nam ulepszać platformę. Szczegóły
              znajdziesz w
              <router-link to="/cookies" class="text-blue-600 hover:underline"
                >Polityce cookies</router-link
              >
              i
              <router-link to="/privacy" class="text-blue-600 hover:underline"
                >Polityce prywatności</router-link
              >.
            </p>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-2.5 sm:justify-end">
          <button
            class="px-5 py-2.5 text-sm font-medium text-gray-600 hover:text-gray-900 rounded-xl border border-gray-200 hover:border-gray-300 transition-colors order-3 sm:order-1"
            @click="showModal = true"
          >
            Dostosuj
          </button>
          <button
            class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors order-2"
            @click="acceptNecessary"
          >
            Tylko niezbędne
          </button>
          <button
            class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-colors order-1 sm:order-3"
            @click="acceptAll"
          >
            Akceptuję wszystkie
          </button>
        </div>
      </div>
    </div>
  </transition>

  <!-- Modal ustawień zgód -->
  <div
    v-if="showModal"
    class="fixed inset-0 z-[95] bg-black/50 flex items-end sm:items-center justify-center p-0 sm:p-4"
    role="dialog"
    aria-modal="true"
    aria-label="Ustawienia plików cookie"
    @click.self="showModal = false"
  >
    <div
      class="bg-white w-full sm:max-w-lg sm:rounded-2xl rounded-t-2xl shadow-2xl max-h-[90vh] flex flex-col"
    >
      <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <h3 class="font-bold text-gray-900">Ustawienia plików cookie</h3>
        <button
          class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 hover:text-gray-600 transition-colors"
          aria-label="Zamknij"
          @click="showModal = false"
        >
          <font-awesome-icon icon="fa-solid fa-xmark" class="w-5 h-5" />
        </button>
      </div>

      <div class="px-6 py-4 overflow-y-auto space-y-4">
        <div
          v-for="category in categories"
          :key="category.id"
          class="border border-gray-200 rounded-xl p-4"
        >
          <div class="flex items-center justify-between gap-3 mb-1.5">
            <span class="font-semibold text-gray-900 text-sm">{{ category.label }}</span>
            <span
              v-if="category.required"
              class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 whitespace-nowrap"
            >
              Zawsze aktywne
            </span>
            <button
              v-else
              class="relative w-11 h-6 rounded-full transition-colors shrink-0"
              :class="consents[category.id] ? 'bg-blue-600' : 'bg-gray-300'"
              role="switch"
              :aria-checked="consents[category.id]"
              @click="consents[category.id] = !consents[category.id]"
            >
              <span
                class="absolute left-0 top-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform"
                :class="consents[category.id] ? 'translate-x-[1.375rem]' : 'translate-x-0.5'"
              ></span>
            </button>
          </div>
          <p class="text-xs text-gray-500 leading-relaxed">{{ category.description }}</p>
          <p v-if="category.cookies" class="text-[11px] text-gray-400 mt-1.5 font-mono">
            {{ category.cookies }}
          </p>
        </div>
      </div>

      <div
        class="px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row gap-2.5 sm:justify-end"
      >
        <button
          class="px-5 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
          @click="acceptNecessary"
        >
          Tylko niezbędne
        </button>
        <button
          class="px-5 py-2.5 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl transition-colors"
          @click="saveChoices"
        >
          Zapisz wybór
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, reactive, onMounted, onUnmounted, watch, nextTick } from 'vue'
  import { useAnalytics } from '@/composables/useAnalytics'
  import { usePlatformSettings } from '@/composables/usePlatformSettings'

  const CONSENT_KEY = 'pigeon_cookie_consent_v1'
  const bannerEl = ref(null)
  let bannerResizeObserver = null

  const { grantAnalytics, denyAnalytics } = useAnalytics()
  const { loadSettings } = usePlatformSettings()

  const showBanner = ref(false)
  const showModal = ref(false)
  const consents = reactive({ analytics: false })

  const categories = [
    {
      id: 'necessary',
      label: 'Niezbędne',
      required: true,
      description:
        'Kluczowe dla działania serwisu: utrzymanie sesji zalogowanego użytkownika, bezpieczeństwo (CSRF) i zapamiętanie Twoich preferencji zgody. Nie przechowują danych marketingowych.',
      cookies: 'XSRF-TOKEN, laravel_session, pigeon_cookie_consent_v1',
    },
    {
      id: 'analytics',
      label: 'Analityczne (Google Analytics)',
      required: false,
      description:
        'Pomagają nam zrozumieć, jak korzystasz z serwisu (odwiedzane strony, czas wizyty), dzięki czemu możemy go ulepszać. Dane są anonimizowane (anonimizacja IP). Włączane wyłącznie za Twoją zgodą.',
      cookies: '_ga, _gid, _gat',
    },
  ]

  const persist = (value) => {
    localStorage.setItem(
      CONSENT_KEY,
      JSON.stringify({ ...value, savedAt: new Date().toISOString() })
    )
  }

  const applyConsents = () => {
    if (consents.analytics) {
      grantAnalytics()
    } else {
      denyAnalytics()
    }
  }

  const acceptAll = () => {
    consents.analytics = true
    persist({ necessary: true, analytics: true })
    applyConsents()
    showBanner.value = false
    showModal.value = false
  }

  const acceptNecessary = () => {
    consents.analytics = false
    persist({ necessary: true, analytics: false })
    applyConsents()
    showBanner.value = false
    showModal.value = false
  }

  const saveChoices = () => {
    persist({ necessary: true, analytics: consents.analytics })
    applyConsents()
    showBanner.value = false
    showModal.value = false
  }

  onMounted(async () => {
    await loadSettings()
    try {
      const stored = JSON.parse(localStorage.getItem(CONSENT_KEY) || 'null')
      if (stored) {
        consents.analytics = !!stored.analytics
        applyConsents()
        return
      }
    } catch {
      // Unreadable or blocked storage means no answer has been recorded, so
      // the banner is shown and the question asked again.
    }
    showBanner.value = true
  })

  const setBannerHeightVar = (px) => {
    document.documentElement.style.setProperty('--cookie-banner-h', `${px}px`)
  }

  watch(bannerEl, async (el) => {
    if (bannerResizeObserver) {
      bannerResizeObserver.disconnect()
      bannerResizeObserver = null
    }
    if (!el) {
      setBannerHeightVar(0)
      return
    }
    await nextTick()
    setBannerHeightVar(el.offsetHeight)
    bannerResizeObserver = new ResizeObserver((entries) => {
      setBannerHeightVar(entries[0].contentRect.height)
    })
    bannerResizeObserver.observe(el)
  })

  onUnmounted(() => {
    bannerResizeObserver?.disconnect()
    setBannerHeightVar(0)
  })

  defineExpose({
    open: () => {
      showModal.value = true
    },
  })
</script>

<style scoped>
  .cookie-slide-enter-active,
  .cookie-slide-leave-active {
    transition:
      transform 0.35s ease,
      opacity 0.35s ease;
  }
  .cookie-slide-enter-from,
  .cookie-slide-leave-to {
    transform: translateY(100%);
    opacity: 0;
  }
</style>
