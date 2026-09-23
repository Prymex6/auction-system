<template>
  <div
    class="min-h-screen bg-gray-50 flex items-center justify-center p-4 py-12 relative overflow-hidden"
  >
    <!-- Background Elements -->
    <div class="absolute top-10 left-10 w-72 h-72 bg-blue-100/20 rounded-full blur-3xl"></div>
    <div class="absolute bottom-10 right-10 w-96 h-96 bg-purple-100/20 rounded-full blur-3xl"></div>

    <div class="w-full max-w-2xl relative z-10">
      <!-- Logo / Header -->
      <div class="text-center mb-8">
        <div class="inline-flex items-center gap-2 mb-4">
          <img :src="'/images/logo-golab.png'" alt="Gołębiowy Lot" class="w-12 h-12" />
          <h1 class="text-4xl font-bold text-gray-900 py-1">Gołębiowy Lot</h1>
        </div>
        <p class="text-gray-600">Dołącz do elitarnej społeczności hodowców</p>
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
          <h2 class="text-3xl font-bold text-gray-900 mb-6">Utwórz konto</h2>

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
          <form class="space-y-6" @submit.prevent="handleRegister">
            <!-- First Name & Last Name -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"> Imię </label>
                <input
                  v-model="form.first_name"
                  type="text"
                  placeholder="Jan"
                  required
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"> Nazwisko </label>
                <input
                  v-model="form.last_name"
                  type="text"
                  placeholder="Kowalski"
                  required
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                />
              </div>
            </div>

            <!-- Username/Display Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Nazwa użytkownika
              </label>
              <input
                v-model="form.name"
                type="text"
                placeholder="jankowalski"
                required
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
              />
              <p class="text-xs text-gray-500 mt-2">Ta nazwa będzie widoczna publicznie</p>
            </div>

            <!-- Email -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"> Adres e-mail </label>
              <input
                v-model="form.email"
                type="email"
                placeholder="you@example.com"
                required
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
              />
            </div>

            <!-- Phone -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"> Numer telefonu </label>
              <input
                v-model="form.phone"
                type="tel"
                placeholder="+48 123 456 789"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
              />
            </div>

            <!-- Address -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"> Adres </label>
              <input
                v-model="form.address"
                type="text"
                placeholder="ul. Przykładowa 12"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
              />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"> Miasto </label>
                <input
                  v-model="form.city"
                  type="text"
                  placeholder="Warszawa"
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"> Kod pocztowy </label>
                <input
                  v-model="form.postcode"
                  type="text"
                  placeholder="00-001"
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"> Kraj </label>
              <select
                v-model="form.country"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
              >
                <option value="" disabled>Wybierz kraj</option>
                <option v-for="option in countryOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
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
              <p class="text-xs text-gray-500 mt-2">Minimum 8 znaków</p>
            </div>

            <!-- Password Confirmation -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"> Potwierdź hasło </label>
              <input
                v-model="form.password_confirmation"
                type="password"
                placeholder="••••••••"
                required
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
              />
            </div>

            <!-- Terms -->
            <div class="flex items-start gap-3 p-4 rounded-xl border border-gray-200 bg-gray-50/50">
              <input
                id="terms"
                v-model="form.terms"
                type="checkbox"
                class="mt-1 w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <label for="terms" class="text-sm text-gray-700">
                Zgadzam się z
                <router-link
                  to="/terms"
                  target="_blank"
                  class="text-blue-600 hover:text-blue-700 font-medium"
                  >regulaminem serwisu</router-link
                >
                i
                <router-link
                  to="/privacy"
                  target="_blank"
                  class="text-blue-600 hover:text-blue-700 font-medium"
                  >polityką prywatności</router-link
                >
              </label>
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
                <span v-else>Utwórz konto</span>
                <font-awesome-icon
                  v-if="!loading"
                  icon="fa-solid fa-arrow-right"
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
              <span class="px-4 bg-white text-gray-500">Masz już konto?</span>
            </div>
          </div>

          <!-- Login Link -->
          <div class="text-center">
            <router-link
              to="/login"
              class="inline-flex items-center gap-2 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300"
            >
              <font-awesome-icon icon="fa-solid fa-arrow-right-to-bracket" class="w-5 h-5" />
              Zaloguj się
            </router-link>
          </div>
        </div>
      </div>

      <!-- Additional Info -->
      <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">Dołączając do nas, otrzymujesz dostęp do:</p>
        <div class="flex flex-wrap gap-3 justify-center mt-3">
          <span
            class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-medium"
          >
            <span class="w-1 h-1 bg-blue-500 rounded-full"></span>
            Elitarnych aukcji
          </span>
          <span
            class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-purple-50 text-purple-700 text-xs font-medium"
          >
            <span class="w-1 h-1 bg-purple-500 rounded-full"></span>
            Systemu auto-bid
          </span>
          <span
            class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-medium"
          >
            <span class="w-1 h-1 bg-green-500 rounded-full"></span>
            24/7 wsparcie
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { useRouter } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'
  import { useSeo } from '@/composables/useSeo'
  import { countryOptions } from '@/constants/countries'
  import IconPicker from '@/components/icons/IconPicker.vue'

  const router = useRouter()
  const authStore = useAuthStore()

  const form = ref({
    first_name: '',
    last_name: '',
    name: '',
    email: '',
    phone: '',
    address: '',
    city: '',
    postcode: '',
    country: '',
    password: '',
    password_confirmation: '',
    terms: false,
  })

  const loading = ref(false)
  const error = ref('')

  const handleRegister = async () => {
    // Validate
    if (form.value.password !== form.value.password_confirmation) {
      error.value = 'Hasła się nie zgadzają'
      return
    }

    if (form.value.password.length < 8) {
      error.value = 'Hasło musi mieć minimum 8 znaków'
      return
    }

    if (!form.value.terms) {
      error.value = 'Musisz zaakceptować warunki korzystania'
      return
    }

    loading.value = true
    error.value = ''

    try {
      await authStore.register({
        first_name: form.value.first_name,
        last_name: form.value.last_name,
        name: form.value.name,
        email: form.value.email,
        phone: form.value.phone,
        address: form.value.address,
        city: form.value.city,
        postcode: form.value.postcode,
        country: form.value.country,
        password: form.value.password,
        password_confirmation: form.value.password_confirmation,
        terms: form.value.terms,
      })

      // Redirect to home page where user will see activation notice
      router.push('/')
    } catch (err) {
      error.value = err.response?.data?.message || 'Rejestracja nie powiodła się'
    } finally {
      loading.value = false
    }
  }

  onMounted(() => {
    useSeo({
      title: 'Rejestracja',
      description:
        'Załóż konto na platformie aukcji gołębi. Dołącz do hodowców i uczestniczyj w licytacjach.',
    })
  })
</script>
