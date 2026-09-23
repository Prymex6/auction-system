<template>
  <div
    class="min-h-screen bg-gray-50 flex items-center justify-center p-4 pt-12 pb-6 relative overflow-hidden"
  >
    <!-- Background Elements -->
    <div class="absolute top-10 left-10 w-72 h-72 bg-blue-100/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-10 right-10 w-96 h-96 bg-purple-100/20 rounded-full blur-3xl"></div>

    <div class="w-full max-w-md relative z-10">
      <!-- Logo / Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center gap-2 mb-4">
          <img :src="'/images/logo-golab.png'" alt="Gołębiowy Lot" class="w-12 h-12" />
          <h1 class="text-4xl font-bold text-gray-900 py-1">
            {{ platformName }}
          </h1>
        </div>
        <p class="text-gray-600">{{ platformDescription }}</p>
      </div>

      <!-- Card -->
      <div
        class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 relative overflow-hidden"
      >
        <!-- Card Background Elements -->
        <div
          class="absolute top-0 right-0 w-64 h-64 bg-blue-500/5 rounded-full -translate-y-32 translate-x-32"
        ></div>
        <div
          class="absolute bottom-0 left-0 w-64 h-64 bg-purple-500/5 rounded-full translate-y-32 -translate-x-32"
        ></div>

        <div class="relative">
          <h2 class="text-3xl font-bold text-gray-900 mb-6">Logowanie</h2>

          <!-- Email Verified Alert -->
          <div
            v-if="emailVerifiedStatus === '1'"
            class="mb-6 p-4 rounded-xl bg-green-50 border border-green-200"
          >
            <div class="flex items-center gap-3">
              <div
                class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center text-green-600"
              >
                <IconPicker name="check" color-class="text-green-600" class="w-4 h-4" />
              </div>
              <div class="flex-1">
                <p class="text-green-800 font-medium">Adres e-mail potwierdzony</p>
                <p class="text-sm text-green-700 mt-1">
                  Zaloguj się, aby kontynuować. Administrator wkrótce zaakceptuje Twoje konto.
                </p>
              </div>
              <button
                class="text-green-500 hover:text-green-700"
                @click="emailVerifiedStatus = null"
              >
                <IconPicker name="x" color-class="text-green-500" class="w-4 h-4" />
              </button>
            </div>
          </div>
          <div
            v-else-if="emailVerifiedStatus === 'expired' || emailVerifiedStatus === 'invalid'"
            class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200"
          >
            <div class="flex items-center gap-3">
              <div
                class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center text-red-600"
              >
                <IconPicker name="warning" color-class="text-red-600" class="w-4 h-4" />
              </div>
              <div class="flex-1">
                <p class="text-red-800 font-medium">
                  Link weryfikacyjny wygasł lub jest nieprawidłowy
                </p>
                <p class="text-sm text-red-700 mt-1">
                  Zaloguj się i wyślij link ponownie ze swojego profilu.
                </p>
              </div>
              <button class="text-red-500 hover:text-red-700" @click="emailVerifiedStatus = null">
                <IconPicker name="x" color-class="text-red-500" class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Session Expired Alert -->
          <div
            v-if="sessionExpired"
            class="mb-6 p-4 rounded-xl bg-yellow-50 border border-yellow-200"
          >
            <div class="flex items-center gap-3">
              <div
                class="w-8 h-8 rounded-lg bg-yellow-100 flex items-center justify-center text-yellow-600"
              >
                <IconPicker name="clock" color-class="text-yellow-600" class="w-4 h-4" />
              </div>
              <div class="flex-1">
                <p class="text-yellow-800 font-medium">Sesja wygasła</p>
                <p class="text-sm text-yellow-700 mt-1">Zaloguj się ponownie aby kontynuować.</p>
              </div>
              <button class="text-yellow-500 hover:text-yellow-700" @click="sessionExpired = false">
                <IconPicker name="x" color-class="text-yellow-500" class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Error Alert -->
          <div v-if="error" class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200">
            <div class="flex items-center gap-3">
              <div
                class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center text-red-600"
              >
                <IconPicker name="warning" color-class="text-red-600" class="w-4 h-4" />
              </div>
              <div class="flex-1">
                <p class="text-red-800 font-medium">{{ error }}</p>
              </div>
              <button class="text-red-500 hover:text-red-700" @click="error = null">
                <IconPicker name="x" color-class="text-red-500" class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Form -->
          <form class="space-y-6" @submit.prevent="handleLogin">
            <!-- Login (name or email) -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"> Login lub Email </label>
              <input
                v-model="form.login"
                type="text"
                placeholder="nazwa_uzytkownika lub you@example.com"
                required
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
              />
              <p class="mt-2 text-xs text-gray-500">
                Możesz się zalogować używając swojej ksywki lub adresu e-mail
              </p>
            </div>

            <!-- Password -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"> Hasło </label>
              <input
                v-model="form.password"
                type="password"
                placeholder="••••••••"
                required
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
              />
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="flex items-center justify-between">
              <label class="flex items-center cursor-pointer">
                <input
                  v-model="form.rememberMe"
                  type="checkbox"
                  class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <span class="ml-2 text-sm text-gray-700">Zapamiętaj mnie</span>
              </label>
              <router-link
                to="/contact"
                class="text-sm text-blue-600 hover:text-blue-800 font-medium"
                >Zapomniałeś hasła?</router-link
              >
            </div>

            <!-- Submit -->
            <button
              type="submit"
              :disabled="loading"
              class="group relative w-full py-4 bg-blue-600 text-white rounded-xl font-semibold hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden"
            >
              <div
                class="absolute inset-0 bg-blue-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
              ></div>
              <span class="relative flex items-center justify-center gap-2">
                <span v-if="loading">
                  <font-awesome-icon
                    icon="fa-solid fa-spinner"
                    class="animate-spin h-5 w-5 text-white"
                  />
                </span>
                <span v-else>Zaloguj się</span>
                <font-awesome-icon
                  v-if="!loading"
                  icon="fa-solid fa-arrow-right-to-bracket"
                  class="w-5 h-5 transform group-hover:translate-x-1 transition-transform"
                />
              </span>
            </button>
          </form>

          <!-- Divider -->
          <div class="relative my-8">
            <div class="absolute inset-0 flex items-center">
              <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative flex justify-center text-sm">
              <span class="px-4 bg-white text-gray-500">Nie masz konta?</span>
            </div>
          </div>

          <!-- Register Link -->
          <div class="text-center">
            <router-link
              to="/register"
              class="inline-flex items-center gap-2 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300"
            >
              <font-awesome-icon icon="fa-solid fa-arrow-right" class="w-5 h-5" />
              Zarejestruj się
            </router-link>
          </div>
        </div>
      </div>

      <!-- Test Credentials -->
    </div>

    <!-- 2FA Modal -->
    <div v-if="show2FA" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full relative overflow-hidden">
        <div
          class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full -translate-y-16 translate-x-16"
        ></div>
        <div class="relative">
          <div
            class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-blue-600 flex items-center justify-center text-white"
          >
            <font-awesome-icon icon="fa-solid fa-lock" class="w-7 h-7" />
          </div>
          <h3 class="text-2xl font-bold text-gray-900 text-center mb-2">Weryfikacja 2FA</h3>
          <p class="text-gray-600 text-center mb-6">
            Wpisz 6-cyfrowy kod z aplikacji autentykacyjnej
          </p>

          <!-- Error Alert -->
          <div v-if="error" class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200">
            <div class="flex items-center gap-3">
              <div
                class="w-8 h-8 rounded-lg bg-red-100 flex items-center justify-center text-red-600"
              >
                <IconPicker name="warning" color-class="text-red-600" class="w-4 h-4" />
              </div>
              <div class="flex-1">
                <p class="text-red-800 font-medium">{{ error }}</p>
              </div>
              <button class="text-red-500 hover:text-red-700" @click="error = null">
                <IconPicker name="x" color-class="text-red-500" class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Code Input -->
          <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Kod weryfikacyjny</label>
            <input
              v-model="twoFACode"
              type="text"
              inputmode="numeric"
              maxlength="6"
              class="w-full px-4 py-3 text-center text-3xl tracking-widest rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
              placeholder="000000"
              autofocus
            />
          </div>

          <button
            :disabled="loading"
            class="group relative w-full py-4 bg-blue-600 text-white rounded-xl font-semibold hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden"
            @click="handleVerify2FA"
          >
            <div
              class="absolute inset-0 bg-blue-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
            ></div>
            <span class="relative flex items-center justify-center gap-2">
              <span v-if="loading">
                <font-awesome-icon
                  icon="fa-solid fa-spinner"
                  class="animate-spin h-5 w-5 text-white"
                />
              </span>
              <span v-else>Weryfikuj</span>
              <font-awesome-icon
                v-if="!loading"
                icon="fa-solid fa-check"
                class="w-5 h-5 transform group-hover:translate-x-1 transition-transform"
              />
            </span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup>
  import { ref, computed, onMounted } from 'vue'
  import { useRouter, useRoute } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'
  import { usePlatformSettings } from '@/composables/usePlatformSettings'
  import { useSeo } from '@/composables/useSeo'
  import IconPicker from '@/components/icons/IconPicker.vue'

  const router = useRouter()
  const route = useRoute()
  const { getPlatformName, getPlatformDescription } = usePlatformSettings()
  const platformName = computed(() => getPlatformName.value)
  const platformDescription = computed(() => getPlatformDescription.value)
  const authStore = useAuthStore()

  const form = ref({
    login: '',
    password: '',
    rememberMe: false,
  })

  const loading = ref(false)
  const error = ref('')
  const show2FA = ref(false)
  const twoFACode = ref('')
  const pending2FAUserId = ref(null)
  const sessionExpired = ref(route.query.session_expired === '1')
  const emailVerifiedStatus = ref(route.query.verified || null)

  const handleLogin = async () => {
    loading.value = true
    error.value = ''

    try {
      const result = await authStore.login(form.value.login, form.value.password)

      if (result?.requires_2fa) {
        pending2FAUserId.value = result.user_id
        show2FA.value = true
      } else {
        // Redirect to home
        router.push('/')
      }
    } catch (err) {
      error.value =
        err.response?.data?.error || err.response?.data?.message || 'Logowanie nie powiodło się'
    } finally {
      loading.value = false
    }
  }

  const handleVerify2FA = async () => {
    if (twoFACode.value.length !== 6) {
      error.value = 'Kod musi mieć 6 cyfr'
      return
    }

    loading.value = true
    error.value = ''

    try {
      await authStore.verify2FA(twoFACode.value, pending2FAUserId.value)
      router.push('/')
    } catch (err) {
      error.value = err.response?.data?.message || 'Weryfikacja nie powiodła się'
    } finally {
      loading.value = false
    }
  }

  onMounted(() => {
    useSeo({
      title: 'Logowanie',
      description:
        'Zaloguj się do platformy aukcji gołębi. Uczestniczyj w licytacjach i zarządzaj swoimi aukcjami.',
    })
  })
</script>
