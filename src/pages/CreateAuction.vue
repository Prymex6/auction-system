<template>
  <div class="min-h-screen bg-white">
    <!-- Account Not Active Alert Banner -->
    <div
      v-if="authStore.isAuthenticated && authStore.user && authStore.user.is_active === false"
      class="fixed top-0 left-0 right-0 z-50 bg-blue-600 text-white"
    >
      <div class="max-w-7xl mx-auto px-4 py-4 flex items-center gap-3">
        <font-awesome-icon icon="fa-solid fa-circle-info" class="w-5 h-5 flex-shrink-0" />
        <span class="font-medium"
          >Konto oczekuje na akceptację administratora - funkcja niedostępna</span
        >
      </div>
    </div>

    <!-- Tryb startowy: wystawianie tylko dla administracji -->
    <div v-if="!canList" class="max-w-2xl mx-auto px-4 py-24 text-center">
      <div class="w-16 h-16 mx-auto mb-6 rounded-full bg-amber-50 flex items-center justify-center">
        <font-awesome-icon icon="fa-solid fa-lock" class="w-8 h-8 text-amber-500" />
      </div>
      <h1 class="text-2xl font-bold text-gray-900 mb-3">
        Wystawianie aukcji jest chwilowo niedostępne
      </h1>
      <p class="text-gray-600 mb-8 leading-relaxed">
        W obecnej fazie serwisu aukcje wystawia wyłącznie administracja. Wkrótce udostępnimy tę
        funkcję wszystkim hodowcom — obserwuj nasze ogłoszenia!
      </p>
      <router-link
        to="/auctions"
        class="inline-block px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-colors"
      >
        Przeglądaj dostępne oferty
      </router-link>
    </div>

    <template v-else>
      <!-- Hero Section -->
      <HeroSection
        badge="Nowa aukcja"
        title="Wystaw swojego"
        gradient="gołębia na aukcji"
        description="Wypełnij szczegóły aukcji i rozpocznij sprzedaż swojego gołębia wśród tysięcy hodowców"
        :extra-padding="authStore.user && !authStore.user.is_active"
      />

      <!-- Form Section -->
      <section class="py-8 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <!--
          novalidate: pole ceny ma HTML min="10" - bez tego przegladarka
          blokowala submit WLASNA natywna walidacja (dymek) zanim handleSubmit()
          w ogole sie odpalil, wiec przy cenie ponizej 10 zl user nie widzial
          ZADNEGO bledu w stylu reszty formularza - po prostu nic sie nie dzialo.
        -->
          <form novalidate class="space-y-8" @submit.prevent="handleSubmit">
            <!-- Images Upload Section -->
            <FormSection title="Zdjęcia">
              <template #content>
                <div class="space-y-6">
                  <!-- Pigeon Images -->
                  <ImageUploadField
                    v-model="form.pigeon_images"
                    label="Zdjęcia gołębia"
                    :required="true"
                    upload-text="Dodaj zdjęcia gołębia (możesz wybrać kilka)"
                    format-hint="PNG, JPG, GIF do 10MB każde"
                  />
                  <p v-if="errors.pigeon_images" class="text-sm text-red-600">
                    {{ errors.pigeon_images }}
                  </p>

                  <!-- Divider -->
                  <div class="border-t border-gray-200 pt-6"></div>

                  <!-- Pedigree Images -->
                  <ImageUploadField
                    v-model="form.pedigree_images"
                    label="Zdjęcia rodowodu"
                    upload-text="Dodaj zdjęcia rodowodu (opcjonalne)"
                    format-hint="PNG, JPG do 10MB każdy"
                  />
                </div>
              </template>
            </FormSection>

            <!-- Basic Information -->
            <div
              class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 relative overflow-hidden"
            >
              <div
                class="absolute top-0 right-0 w-32 h-32 bg-purple-500/5 rounded-full -translate-y-16 translate-x-16"
              ></div>

              <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                  <font-awesome-icon icon="fa-solid fa-user" class="w-5 h-5 text-purple-600" />
                </div>
                Podstawowe informacje
              </h2>

              <div class="space-y-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-3">
                    Tytuł aukcji <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model="form.title"
                    type="text"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    placeholder="np. Piękny biały gołąb pocztowy - champion wystaw"
                    :class="
                      errors.title ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : ''
                    "
                  />
                  <p v-if="errors.title" class="mt-2 text-sm text-red-600">{{ errors.title }}</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                      Rasa <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.breed"
                      type="text"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                      placeholder="np. Warszawski, Sokólnik, Braszławski..."
                      :class="
                        errors.breed ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : ''
                      "
                    />
                    <p v-if="errors.breed" class="mt-2 text-sm text-red-600">{{ errors.breed }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                      Płeć <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.gender"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                      :class="
                        errors.gender
                          ? 'border-red-500 focus:border-red-500 focus:ring-red-200'
                          : ''
                      "
                    >
                      <option value="" disabled selected>Wybierz płeć</option>
                      <option
                        v-for="option in genderOptions"
                        :key="option.value"
                        :value="option.value"
                      >
                        {{ option.label }}
                      </option>
                    </select>
                    <p v-if="errors.gender" class="mt-2 text-sm text-red-600">
                      {{ errors.gender }}
                    </p>
                  </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                      Rok urodzenia <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.year"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                      :class="
                        errors.year ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : ''
                      "
                    >
                      <option
                        v-for="option in yearOptions"
                        :key="option.value"
                        :value="option.value"
                      >
                        {{ option.label }}
                      </option>
                    </select>
                    <p v-if="errors.year" class="mt-2 text-sm text-red-600">{{ errors.year }}</p>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                      Wielkość <span class="text-red-500">*</span>
                    </label>
                    <select
                      v-model="form.size"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                      :class="
                        errors.size ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : ''
                      "
                    >
                      <option value="" disabled selected>Wybierz wielkość</option>
                      <option
                        v-for="option in sizeOptions"
                        :key="option.value"
                        :value="option.value"
                      >
                        {{ option.label }}
                      </option>
                    </select>
                    <p v-if="errors.size" class="mt-2 text-sm text-red-600">{{ errors.size }}</p>
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-3">
                    Barwa <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model="form.color"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    :class="
                      errors.color ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : ''
                    "
                  >
                    <option value="" disabled selected>Wybierz barwę</option>
                    <option
                      v-for="option in colorOptions"
                      :key="option.value"
                      :value="option.value"
                    >
                      {{ option.label }}
                    </option>
                  </select>
                  <p v-if="errors.color" class="mt-2 text-sm text-red-600">{{ errors.color }}</p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-3">
                    Numer obrączki
                  </label>
                  <input
                    v-model="form.ring_number"
                    type="text"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    placeholder="np. PL-2024-12345"
                  />
                  <p v-if="errors.ring_number" class="mt-2 text-sm text-red-600">
                    {{ errors.ring_number }}
                  </p>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-3">Opis</label>
                  <textarea
                    v-model="form.description"
                    rows="4"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    placeholder="Opisz szczegółowo swojego gołębia: osiągnięcia, charakter, zdrowie..."
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Auction Settings -->
            <div
              class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 relative overflow-hidden"
            >
              <div
                class="absolute top-0 right-0 w-32 h-32 bg-green-500/5 rounded-full -translate-y-16 translate-x-16"
              ></div>

              <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center">
                  <font-awesome-icon
                    icon="fa-solid fa-sack-dollar"
                    class="w-5 h-5 text-green-600"
                  />
                </div>
                Ustawienia aukcji
              </h2>

              <div class="space-y-6">
                <!-- Auction Type Selection -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-3">
                    Typ aukcji <span class="text-red-500">*</span>
                  </label>
                  <div class="flex gap-4">
                    <label v-if="!mustUseBuyNowOnly" class="flex items-center gap-3 cursor-pointer">
                      <input
                        v-model="form.auction_type"
                        type="radio"
                        value="auction"
                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                      />
                      <span class="text-sm font-medium text-gray-700">Licytacja</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer">
                      <input
                        v-model="form.auction_type"
                        type="radio"
                        value="buy_now"
                        class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500"
                      />
                      <span class="text-sm font-medium text-gray-700">Kup teraz</span>
                    </label>
                  </div>
                  <p v-if="mustUseBuyNowOnly" class="text-sm text-gray-500 mt-2">
                    Twoje konto może obecnie wystawiać wyłącznie w trybie "Kup teraz".
                  </p>
                </div>

                <!-- Auction Type: Licytacja -->
                <template v-if="form.auction_type === 'auction'">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                      Cena wywoławcza (PLN) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                      <input
                        v-model.number="form.start_price"
                        type="number"
                        step="1"
                        min="10"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm pl-12"
                        placeholder="100"
                        :class="
                          errors.start_price
                            ? 'border-red-500 focus:border-red-500 focus:ring-red-200'
                            : ''
                        "
                      />
                      <div class="absolute left-3 top-3 text-gray-400">zł</div>
                    </div>
                    <p v-if="errors.start_price" class="mt-2 text-sm text-red-600">
                      {{ errors.start_price }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">Minimalna cena wywoławcza to 10 zł</p>
                  </div>
                </template>

                <!-- Auction Type: Kup teraz -->
                <template v-if="form.auction_type === 'buy_now'">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                      Cena (PLN) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                      <input
                        v-model.number="form.start_price"
                        type="number"
                        step="1"
                        min="10"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm pl-12"
                        placeholder="100"
                        :class="
                          errors.start_price
                            ? 'border-red-500 focus:border-red-500 focus:ring-red-200'
                            : ''
                        "
                      />
                      <div class="absolute left-3 top-3 text-gray-400">zł</div>
                    </div>
                    <p v-if="errors.start_price" class="mt-2 text-sm text-red-600">
                      {{ errors.start_price }}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">Minimalna cena to 10 zł</p>
                  </div>
                </template>

                <div
                  class="bg-blue-50/50 border border-blue-200 rounded-xl p-4 flex items-start gap-3"
                >
                  <font-awesome-icon
                    icon="fa-solid fa-circle-info"
                    class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5"
                  />
                  <div v-if="form.auction_type === 'buy_now'">
                    <p class="text-sm font-medium text-gray-900 mb-1">Czas trwania ogłoszenia</p>
                    <p class="text-sm text-gray-600">
                      Ogłoszenie "Kup teraz" nie ma limitu czasu - pozostaje aktywne, dopóki ktoś go
                      nie kupi lub sam go nie zakończysz.
                    </p>
                  </div>
                  <div v-else>
                    <p class="text-sm font-medium text-gray-900 mb-1">Czas trwania aukcji</p>
                    <p class="text-sm text-gray-600">
                      Czas zakończenia aukcji ustala system automatycznie - standardowo 7 dni od
                      utworzenia.
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Terms -->
            <div
              class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 relative overflow-hidden"
            >
              <div
                class="absolute top-0 right-0 w-32 h-32 bg-yellow-500/5 rounded-full -translate-y-16 translate-x-16"
              ></div>

              <div class="flex items-start space-x-4">
                <div class="flex items-center h-5">
                  <input
                    id="create-auction-terms"
                    v-model="form.terms"
                    type="checkbox"
                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                    :class="errors.terms ? 'border-red-500' : ''"
                  />
                </div>
                <div class="text-sm">
                  <label for="create-auction-terms" class="font-medium text-gray-900">
                    Akceptuję
                    <router-link
                      to="/terms"
                      target="_blank"
                      class="text-blue-600 hover:text-blue-800 font-medium"
                      >regulamin aukcji</router-link
                    >
                    <span class="text-red-500">*</span>
                  </label>
                  <p class="text-gray-600 mt-1">
                    Potwierdzam, że jestem właścicielem wystawianego gołębia i znam zasady
                    obowiązujące na platformie.
                  </p>
                </div>
              </div>
              <p v-if="errors.terms" class="mt-2 text-sm text-red-600">{{ errors.terms }}</p>
            </div>

            <!-- Actions & Feedback -->
            <div class="space-y-6">
              <!-- Actions -->
              <div class="flex flex-col sm:flex-row gap-4">
                <button
                  type="button"
                  class="px-8 py-4 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300"
                  @click="$router.back()"
                >
                  Anuluj
                </button>
                <button
                  type="submit"
                  :disabled="isSubmitting"
                  class="group relative flex-1 px-8 py-4 bg-blue-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  <div
                    class="absolute inset-0 bg-blue-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                  ></div>
                  <span class="relative flex items-center justify-center gap-2">
                    <font-awesome-icon
                      v-if="isSubmitting"
                      icon="fa-solid fa-spinner"
                      class="animate-spin h-5 w-5 text-white"
                    />
                    <font-awesome-icon v-else icon="fa-solid fa-circle-plus" class="w-5 h-5" />
                    {{ isSubmitting ? 'Tworzenie aukcji...' : 'Utwórz aukcję' }}
                  </span>
                </button>
              </div>

              <!-- Feedback Messages -->
              <div v-if="successMessage">
                <div class="bg-green-50 border border-green-200 rounded-2xl p-6">
                  <div class="flex items-center gap-4">
                    <div
                      class="w-12 h-12 rounded-xl bg-emerald-500 flex items-center justify-center text-white"
                    >
                      <font-awesome-icon icon="fa-solid fa-check" class="w-6 h-6" />
                    </div>
                    <div>
                      <h3 class="font-bold text-gray-900 text-lg">Sukces!</h3>
                      <p class="text-gray-700">{{ successMessage }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="errorMessage">
                <div class="bg-red-50 border border-red-200 rounded-2xl p-6">
                  <div class="flex items-center gap-4">
                    <div
                      class="w-12 h-12 rounded-xl bg-red-500 flex items-center justify-center text-white"
                    >
                      <font-awesome-icon icon="fa-solid fa-circle-info" class="w-6 h-6" />
                    </div>
                    <div>
                      <h3 class="font-bold text-gray-900 text-lg">Błąd</h3>
                      <p class="text-gray-700">{{ errorMessage }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </form>
        </div>
      </section>
    </template>
  </div>
</template>

<script setup>
  import { ref, onMounted, computed, watch } from 'vue'
  import { useRouter } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'
  import HeroSection from '@/components/layout/HeroSection.vue'
  import FormSection from '@/components/form/FormSection.vue'
  import ImageUploadField from '@/components/form/ImageUploadField.vue'
  import { useSeo } from '@/composables/useSeo'
  import { usePlatformSettings } from '@/composables/usePlatformSettings'
  import api from '@/services/api'

  const router = useRouter()
  const authStore = useAuthStore()
  const { onlyAdminCanList, loadSettings: loadPlatformSettings } = usePlatformSettings()
  loadPlatformSettings()
  const canList = computed(
    () =>
      !onlyAdminCanList.value ||
      !!authStore.user?.is_admin ||
      !!authStore.user?.can_list_when_restricted
  )
  const mustUseBuyNowOnly = computed(
    () =>
      onlyAdminCanList.value &&
      !authStore.user?.is_admin &&
      !!authStore.user?.can_list_when_restricted
  )
  const { getPlatformName } = usePlatformSettings()

  const form = ref({
    title: '',
    breed: '',
    gender: '',
    year: new Date().getFullYear(),
    size: '',
    color: '',
    ring_number: '',
    description: '',
    auction_type: 'auction',
    start_price: '',
    pigeon_images: [],
    pedigree_images: [],
    terms: false,
  })

  watch(
    mustUseBuyNowOnly,
    (must) => {
      if (must) {
        form.value.auction_type = 'buy_now'
      }
    },
    { immediate: true }
  )

  const errors = ref({})
  const isSubmitting = ref(false)
  const successMessage = ref('')
  const errorMessage = ref('')

  const genderOptions = [
    { value: 'samiec', label: 'Samiec', icon: 'male' },
    { value: 'samica', label: 'Samica', icon: 'female' },
    { value: 'golab_mlody', label: 'Gołąb młody', icon: 'pigeon' },
  ]

  const sizeOptions = [
    { value: 'maly', label: 'Mały' },
    { value: 'sredni', label: 'Średni' },
    { value: 'duzy', label: 'Duży' },
  ]

  const colorOptions = [
    { value: 'Niebieska', label: 'Niebieska', code: 'NIEB 1' },
    { value: 'Niebieska nakrapiana', label: 'Niebieska nakrapiana', code: 'NNAK 2' },
    { value: 'Ciemna nakrapiana', label: 'Ciemna nakrapiana', code: 'CNAK 9' },
    { value: 'Ciemna', label: 'Ciemna', code: 'CIEM 3' },
    { value: 'Czarna', label: 'Czarna', code: 'CZAR 6' },
    { value: 'Czerwona nakrapiana', label: 'Czerwona nakrapiana', code: 'CZEN 8' },
    { value: 'Czerwona', label: 'Czerwona', code: 'CZER 4' },
    { value: 'Płowa', label: 'Płowa', code: 'PLOW 5' },
    { value: 'Biała', label: 'Biała', code: 'BIAL 10' },
    { value: 'Szpakowata', label: 'Szpakowata', code: 'SZPA 7' },
    { value: 'Niebieska pstra', label: 'Niebieska pstra', code: 'NIEP 11' },
    { value: 'Niebieska nakrapiana pstra', label: 'Niebieska nakrapiana pstra', code: 'NNAP 12' },
    { value: 'Ciemna nakrapiana pstra', label: 'Ciemna nakrapiana pstra', code: 'CNAP 19' },
    { value: 'Ciemna pstra', label: 'Ciemna pstra', code: 'CIEP 13' },
    { value: 'Czarna pstra', label: 'Czarna pstra', code: 'CZAP 16' },
    { value: 'Czerwona nakrapiana pstra', label: 'Czerwona nakrapiana pstra', code: 'CZNP 18' },
    { value: 'Czerwona pstra', label: 'Czerwona pstra', code: 'CZEP 14' },
    { value: 'Płowa pstra', label: 'Płowa pstra', code: 'PLOP 15' },
    { value: 'Szpakowata pstra', label: 'Szpakowata pstra', code: 'SZPP 17' },
    { value: 'Czerwona szpakowata', label: 'Czerwona szpakowata', code: 'CZES 20' },
  ]

  const yearOptions = []
  const currentYear = new Date().getFullYear()
  for (let i = 0; i < 20; i++) {
    yearOptions.push({ value: currentYear - i, label: (currentYear - i).toString() })
  }

  /**
   * Setup SEO on mount
   */
  onMounted(() => {
    const { getPlatformName } = usePlatformSettings()
    useSeo({
      title: `Nowa aukcja - ${getPlatformName.value}`,
      description:
        'Wystaw swojego gołębia na aukcji. Szybka i bezpieczna sprzedaż wśród tysięcy hodowców.',
      keywords: 'aukcja gołębi, sprzedaż gołębi, hodowla gołębi',
      ogTitle: `Nowa aukcja - ${getPlatformName.value}`,
      ogDescription: 'Wystaw swojego gołębia na aukcji',
      canonical: window.location.origin + '/auctions/create',
    })
  })

  /**
   * Handle form submit
   */
  const handleSubmit = async () => {
    // Reset messages
    errors.value = {}
    successMessage.value = ''
    errorMessage.value = ''

    // Validate
    if (!form.value.title) errors.value.title = 'Tytuł jest wymagany'
    if (!form.value.breed) errors.value.breed = 'Rasa jest wymagana'
    if (!form.value.gender) errors.value.gender = 'Płeć jest wymagana'
    if (!form.value.year) errors.value.year = 'Rok jest wymagany'
    if (!form.value.size) errors.value.size = 'Wielkość jest wymagana'
    if (!form.value.color) errors.value.color = 'Barwa jest wymagana'
    if (!form.value.start_price) errors.value.start_price = 'Cena jest wymagana'
    if (form.value.start_price && form.value.start_price < 10)
      errors.value.start_price = 'Minimalna cena to 10 zł'
    if (form.value.pigeon_images.length === 0)
      errors.value.pigeon_images = 'Dodaj przynajmniej jedno zdjęcie gołębia'
    if (!form.value.terms) errors.value.terms = 'Musisz zaakceptować regulamin'

    if (Object.keys(errors.value).length > 0) return

    isSubmitting.value = true

    try {
      const payload = {
        ...form.value,
        type: form.value.auction_type,
      }
      delete payload.auction_type

      await api.post('/auctions', payload)

      successMessage.value =
        'Aukcja została utworzona! Czeka na aktywację administratora. Możesz ją edytować lub usunąć w profilu.'

      setTimeout(() => {
        router.push('/profile?tab=my-auctions')
      }, 2000)
    } catch (err) {
      errorMessage.value = err.response?.data?.message || 'Nie udało się utworzyć aukcji'
      if (err.response?.data?.errors) {
        errors.value = err.response.data.errors
      }
    } finally {
      isSubmitting.value = false
    }
  }
</script>
