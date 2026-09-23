<template>
  <div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <HeroSection
      badge="Edytuj aukcję"
      title="Zaktualizuj"
      gradient="szczegóły aukcji"
      description="Zaktualizuj informacje o swoim gołębiu na aukcji"
    />

    <!-- Form Section -->
    <section class="py-8 bg-white">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Account Status Alert -->
        <StatusAlert
          variant="inactive"
          title="Konto oczekuje na zatwierdzenie"
          message="Twoje konto musi być potwierdzone przez administratora aby móc edytować aukcje."
          :show="!authStore.user?.is_active"
        />

        <!--
          novalidate: pole ceny ma HTML min="10" - bez tego przegladarka
          blokowala submit wlasna natywna walidacja (dymek) zanim handleSubmit()
          w ogole sie odpalil, wiec przy niepoprawnej cenie user nie widzial
          zadnego bledu w stylu reszty formularza (patrz to samo w CreateAuction.vue).
        -->
        <form novalidate class="space-y-8" @submit.prevent="handleSubmit">
          <!-- Images Upload Section -->
          <div
            class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 relative overflow-hidden"
          >
            <div
              class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -translate-y-16 translate-x-16"
            ></div>

            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
              <font-awesome-icon icon="fa-solid fa-image" class="w-8 h-8 text-blue-600" />
              Zdjęcia główne
            </h2>

            <ImageUploadField
              v-model="form.pigeon_images"
              label="Zdjęcia gołębia"
              upload-text="Dodaj zdjęcia gołębia (możesz wybrać kilka)"
              format-hint="PNG, JPG, GIF do 10MB każde"
              multiple
              required
            />
          </div>

          <!-- Pedigree Images Section -->
          <div
            class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 relative overflow-hidden"
          >
            <div
              class="absolute top-0 right-0 w-32 h-32 bg-purple-500/5 rounded-full -translate-y-16 translate-x-16"
            ></div>

            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
              <font-awesome-icon icon="fa-solid fa-file-lines" class="w-8 h-8 text-purple-600" />
              Metryka / Genealogia
            </h2>

            <ImageUploadField
              v-model="form.pedigree_images"
              label="Zdjęcia rodowodu"
              upload-text="Dodaj zdjęcia rodowodu (opcjonalne)"
              format-hint="PNG, JPG do 10MB każdy"
              multiple
            />
          </div>

          <!-- Basic Info Section -->
          <div
            class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 relative overflow-hidden"
          >
            <div
              class="absolute top-0 right-0 w-32 h-32 bg-pink-500/5 rounded-full -translate-y-16 translate-x-16"
            ></div>

            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                <font-awesome-icon icon="fa-solid fa-user" class="w-5 h-5 text-pink-600" />
              </div>
              Informacje o gołębiu
            </h2>

            <div class="space-y-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">
                  Tytuł aukcji <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.title"
                  type="text"
                  :disabled="isAuctionActive"
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                  placeholder="np. Gołąb gołębiowaty - samiec"
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
                    :disabled="isAuctionActive"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
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
                    :disabled="isAuctionActive"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                    :class="
                      errors.gender ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : ''
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
                  <p v-if="errors.gender" class="mt-2 text-sm text-red-600">{{ errors.gender }}</p>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-3">
                    Rok urodzenia <span class="text-red-500">*</span>
                  </label>
                  <select
                    v-model.number="form.year"
                    :disabled="isAuctionActive"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                    :class="
                      errors.year ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : ''
                    "
                  >
                    <option v-for="option in yearOptions" :key="option.value" :value="option.value">
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
                    :disabled="isAuctionActive"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                    :class="
                      errors.size ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : ''
                    "
                  >
                    <option value="" disabled selected>Wybierz wielkość</option>
                    <option v-for="option in sizeOptions" :key="option.value" :value="option.value">
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
                  :disabled="isAuctionActive"
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                  :class="
                    errors.color ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : ''
                  "
                >
                  <option value="" disabled selected>Wybierz barwę</option>
                  <option v-for="option in colorOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </select>
                <p v-if="errors.color" class="mt-2 text-sm text-red-600">{{ errors.color }}</p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-3"> Numer obrączki </label>
                <input
                  v-model="form.ring_number"
                  type="text"
                  :disabled="isAuctionActive"
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                  placeholder="np. PL-2024-12345"
                  :class="
                    errors.ring_number
                      ? 'border-red-500 focus:border-red-500 focus:ring-red-200'
                      : ''
                  "
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
                  :disabled="isAuctionActive"
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm disabled:bg-gray-100 disabled:cursor-not-allowed"
                  :class="
                    errors.description
                      ? 'border-red-500 focus:border-red-500 focus:ring-red-200'
                      : ''
                  "
                  placeholder="Opisz szczegółowo swojego gołębia: osiągnięcia, charakter, zdrowie..."
                ></textarea>
                <p v-if="errors.description" class="mt-2 text-sm text-red-600">
                  {{ errors.description }}
                </p>
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
                <font-awesome-icon icon="fa-solid fa-sack-dollar" class="w-5 h-5 text-green-600" />
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
                  <label
                    class="flex items-center gap-3 cursor-pointer"
                    :class="{ 'opacity-50': isAuctionActive }"
                  >
                    <input
                      v-model="form.auction_type"
                      type="radio"
                      value="auction"
                      :disabled="isAuctionActive"
                      class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 disabled:cursor-not-allowed"
                    />
                    <span class="text-sm font-medium text-gray-700">Licytacja</span>
                  </label>
                  <label
                    class="flex items-center gap-3 cursor-pointer"
                    :class="{ 'opacity-50': isAuctionActive }"
                  >
                    <input
                      v-model="form.auction_type"
                      type="radio"
                      value="buy_now"
                      :disabled="isAuctionActive"
                      class="w-4 h-4 text-blue-600 border-gray-300 focus:ring-blue-500 disabled:cursor-not-allowed"
                    />
                    <span class="text-sm font-medium text-gray-700">Kup teraz</span>
                  </label>
                </div>
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
                      :disabled="isAuctionActive"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm pl-12 disabled:bg-gray-100 disabled:cursor-not-allowed"
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
                      :disabled="isAuctionActive"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm pl-12 disabled:bg-gray-100 disabled:cursor-not-allowed"
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
            </div>
          </div>

          <!-- Danger Zone - Delete Auction -->
          <div
            class="bg-red-50 rounded-2xl border-2 border-red-200 shadow-xl p-8 relative overflow-hidden"
          >
            <div
              class="absolute top-0 right-0 w-32 h-32 bg-red-500/5 rounded-full -translate-y-16 translate-x-16"
            ></div>

            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center">
                <font-awesome-icon
                  icon="fa-solid fa-circle-check"
                  class="w-5 h-5 text-orange-600"
                />
              </div>
              Zakończ aukcję
            </h2>

            <p class="text-gray-700 mb-4">
              Zmień status aukcji na zakończoną. Aukcja nie będzie widoczna w wyszukiwarce.
            </p>

            <div class="inline-block" :title="isAuctionActive ? activeAuctionTooltip : ''">
              <button
                type="button"
                :disabled="isAuctionActive"
                class="px-6 py-3 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-semibold transition-all duration-300 shadow-md hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed"
                @click="openCompleteConfirm"
              >
                Zakończ aukcję
              </button>
            </div>
          </div>

          <!-- Complete Confirmation Modal -->
          <div
            v-if="showCompleteConfirm"
            class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
            @click.self="showCompleteConfirm = false"
          >
            <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full" @click.stop>
              <div
                class="flex items-center justify-center w-12 h-12 rounded-full bg-orange-100 mx-auto mb-4"
              >
                <font-awesome-icon
                  icon="fa-solid fa-circle-check"
                  class="w-6 h-6 text-orange-600"
                />
              </div>
              <h3 class="text-xl font-bold text-gray-900 text-center mb-2">
                Potwierdzić zakończenie?
              </h3>
              <p class="text-gray-600 text-center mb-6">
                Czy na pewno chcesz zakończyć tę aukcję? Aukcja będzie już niewidoczna w
                wyszukiwarce.
              </p>
              <div class="flex gap-3">
                <button
                  type="button"
                  class="flex-1 px-4 py-2 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-all duration-300"
                  @click="showCompleteConfirm = false"
                >
                  Anuluj
                </button>
                <button
                  type="button"
                  :disabled="isCompleting || isAuctionActive"
                  class="flex-1 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl font-semibold transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                  @click="completeAuction"
                >
                  <font-awesome-icon
                    v-if="isCompleting"
                    icon="fa-solid fa-spinner"
                    class="animate-spin h-5 w-5 text-white"
                  />
                  {{ isCompleting ? 'Kończę...' : 'Zakończ' }}
                </button>
              </div>
            </div>
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
              <div class="flex-1" :title="isAuctionActive ? activeAuctionTooltip : ''">
                <button
                  type="submit"
                  :disabled="isSubmitting || isAuctionActive"
                  class="group relative w-full px-8 py-4 bg-blue-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed"
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
                    <font-awesome-icon v-else icon="fa-solid fa-circle-check" class="w-5 h-5" />
                    {{ isSubmitting ? 'Aktualizuję...' : 'Zaktualizuj aukcję' }}
                  </span>
                </button>
              </div>
            </div>

            <!-- Feedback Messages -->
            <div v-if="successMessage">
              <div class="bg-green-50 border border-green-200 rounded-2xl p-6">
                <div class="flex items-center gap-4">
                  <div
                    class="w-12 h-12 rounded-xl bg-emerald-500 flex items-center justify-center text-white flex-shrink-0"
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
                    class="w-12 h-12 rounded-xl bg-red-500 flex items-center justify-center text-white flex-shrink-0"
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
  </div>
</template>

<script setup>
  import { ref, computed, onMounted } from 'vue'
  import { useRouter, useRoute } from 'vue-router'
  import api from '@/services/api'
  import { useSeo } from '@/composables/useSeo'
  import { usePlatformSettings } from '@/composables/usePlatformSettings'
  import { useFormatters } from '@/composables/useFormatters'
  import { HeroSection, ImageUploadField, StatusAlert } from '@/components'
  import { useAuthStore } from '@/stores/auth'

  const router = useRouter()
  const route = useRoute()
  const { getPlatformName } = usePlatformSettings()
  const { formatPrice } = useFormatters()
  const authStore = useAuthStore()

  const form = ref({
    title: '',
    breed: '',
    gender: '',
    year: new Date().getFullYear(),
    size: '',
    color: '',
    ring_number: '',
    auction_type: 'auction',
    start_price: 0,
    pigeon_images: [],
    pedigree_images: [],
    description: '',
  })

  const errors = ref({})
  const successMessage = ref('')
  const errorMessage = ref('')
  const isSubmitting = ref(false)
  const isLoading = ref(true)
  const isDeleting = ref(false)
  const isCompleting = ref(false)
  const showDeleteConfirm = ref(false)
  const showCompleteConfirm = ref(false)
  const isAuctionActive = ref(false)
  const activeAuctionTooltip =
    'Nie możesz edytować ani usuwać aktywnych aukcji. Zaczekaj aż aukcja się skończy.'

  const breeds = ref([])
  const genderOptions = [
    { value: 'samiec', label: 'Samiec' },
    { value: 'samica', label: 'Samica' },
    { value: 'golab_mlody', label: 'Gołąb Młody' },
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

  // Generate year options
  const yearOptions = ref([])
  const currentYear = new Date().getFullYear()
  for (let i = 0; i < 40; i++) {
    yearOptions.value.push({ value: currentYear - i, label: (currentYear - i).toString() })
  }

  /**
   * Calculate total cost
   */
  const totalCost = computed(() => {
    let total = form.value.start_price
    return total
  })

  /**
   * Update total cost (trigger for computed)
   */
  const updateTotal = () => {
    // Trigger computed update
  }

  /**
   * Setup SEO on mount
   */
  onMounted(async () => {
    const { getPlatformName } = usePlatformSettings()
    useSeo({
      title: `Edytuj aukcję - ${getPlatformName.value}`,
      description: 'Zaktualizuj szczegóły swoich aukcji gołębi.',
      keywords: 'edycja aukcji, aukcja gołębi',
      ogTitle: `Edytuj aukcję - ${getPlatformName.value}`,
      ogDescription: 'Zaktualizuj szczegóły swoich aukcji gołębi',
      canonical: window.location.origin + '/auctions/' + route.params.id + '/edit',
    })

    // Load auction data
    await loadAuction()

    // Load breeds
    try {
      const res = await api.get('/breeds')
      breeds.value = res.data.data || []
    } catch (err) {
      console.error('Error loading breeds:', err)
    }
  })

  /**
   * Load auction data
   */
  const loadAuction = async () => {
    try {
      isLoading.value = true
      const res = await api.get(`/auctions/${route.params.id}`)
      const auction = res.data.data

      // Check if auction is active - zgodnie z warunkiem faktycznie
      // dopiero po wyslaniu formularza i otrzymaniu bledu 422 z backendu.
      isAuctionActive.value =
        auction.type !== 'buy_now' && ['active', 'ended'].includes(auction.status)

      // Populate form
      form.value = {
        title: auction.title || '',
        breed: auction.breed || '',
        gender: auction.gender || '',
        year: auction.year || new Date().getFullYear(),
        size: auction.size || '',
        color: auction.color || '',
        ring_number: auction.ring_number || '',
        auction_type: auction.type || auction.auction_type || 'auction',
        start_price: auction.start_price || 0,
        pigeon_images: auction.pigeon_images || auction.images || [],
        pedigree_images: auction.pedigree_images || [],
        description: auction.description || '',
      }
    } catch (err) {
      errorMessage.value = err.response?.data?.message || 'Nie udało się załadować aukcji'
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Handle form submit
   */
  const handleSubmit = async () => {
    // Prevent editing active auctions
    if (isAuctionActive.value) {
      errorMessage.value = 'Nie możesz edytować aktywnych aukcji'
      return
    }

    // Reset messages
    errors.value = {}
    successMessage.value = ''
    errorMessage.value = ''

    // Validate
    if (!form.value.title) errors.value.title = 'Tytuł jest wymagany'
    if (!form.value.breed) errors.value.breed = 'Rasa jest wymagana'
    if (!form.value.gender) errors.value.gender = 'Płeć jest wymagana'
    if (!form.value.year) errors.value.year = 'Rok jest wymagany'
    if (!form.value.description) errors.value.description = 'Opis jest wymagany'
    if (form.value.start_price < 0) errors.value.start_price = 'Cena nie może być ujemna'

    // If there are errors, stop
    if (Object.keys(errors.value).length > 0) return

    try {
      isSubmitting.value = true

      const payload = {
        title: form.value.title,
        breed: form.value.breed,
        gender: form.value.gender,
        year: form.value.year,
        size: form.value.size,
        color: form.value.color,
        ring_number: form.value.ring_number,
        type: form.value.auction_type,
        start_price: form.value.start_price,
        description: form.value.description,
        pigeon_images: form.value.pigeon_images,
        pedigree_images: form.value.pedigree_images,
      }

      const response = await api.put(`/auctions/${route.params.id}`, payload)

      successMessage.value = response.data?.message || 'Aukcja została pomyślnie zaktualizowana!'

      setTimeout(() => {
        router.push('/profile?tab=my-auctions')
      }, 2000)
    } catch (err) {
      errorMessage.value = err.response?.data?.message || 'Nie udało się zaktualizować aukcji'
      if (err.response?.data?.errors) {
        errors.value = err.response.data.errors
      }
    } finally {
      isSubmitting.value = false
    }
  }

  /**
   * Open delete confirmation dialog
   */
  const openDeleteConfirm = () => {
    showDeleteConfirm.value = true
  }

  const openCompleteConfirm = () => {
    showCompleteConfirm.value = true
  }

  /**
   * Complete auction (change status to completed)
   */
  const completeAuction = async () => {
    // Prevent completing active auctions
    if (isAuctionActive.value) {
      errorMessage.value = 'Nie możesz zakańczać aktywnych aukcji'
      showCompleteConfirm.value = false
      return
    }

    try {
      isCompleting.value = true

      await api.patch(`/auctions/${route.params.id}/complete`)

      successMessage.value = 'Aukcja została pomyślnie zakończona!'

      setTimeout(() => {
        router.push('/profile?tab=my-auctions')
      }, 2000)
    } catch (err) {
      errorMessage.value = err.response?.data?.message || 'Nie udało się zakończyć aukcji'
      showCompleteConfirm.value = false
    } finally {
      isCompleting.value = false
    }
  }

  /**
   * Delete auction
   */
  const deleteAuction = async () => {
    // Prevent deleting active auctions
    if (isAuctionActive.value) {
      errorMessage.value = 'Nie możesz usuwać aktywnych aukcji'
      showDeleteConfirm.value = false
      return
    }

    try {
      isDeleting.value = true

      await api.delete(`/auctions/${route.params.id}`)

      successMessage.value = 'Aukcja została pomyślnie usunięta!'

      setTimeout(() => {
        router.push('/profile?tab=my-auctions')
      }, 2000)
    } catch (err) {
      errorMessage.value = err.response?.data?.message || 'Nie udało się usunąć aukcji'
      showDeleteConfirm.value = false
    } finally {
      isDeleting.value = false
    }
  }
</script>
