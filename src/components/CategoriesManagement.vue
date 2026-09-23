<template>
  <div class="categories-management">
    <!-- Categories List -->
    <div class="bg-white rounded-2xl border border-gray-100 shadow-xl overflow-hidden">
      <div class="p-6 border-b border-gray-200 flex justify-between items-center">
        <h2 class="text-xl font-bold text-gray-900">Zarządzanie kategoriami</h2>
        <button
          class="px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2"
          @click="openCreateModal"
        >
          <font-awesome-icon icon="fa-solid fa-plus" class="w-5 h-5" />
          Dodaj kategorię
        </button>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="border-b border-gray-200 bg-gray-50">
            <tr>
              <th
                class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                Nazwa
              </th>
              <th
                class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                Typ
              </th>
              <th
                class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                Filtr
              </th>
              <th
                class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                Featured
              </th>
              <th
                class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                Aukcje
              </th>
              <th
                class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
              >
                Akcje
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-if="!categories.length" class="hover:bg-gray-50/50">
              <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                <div class="flex flex-col items-center gap-2">
                  <font-awesome-icon
                    icon="fa-solid fa-circle-check"
                    class="w-8 h-8 text-gray-300"
                  />
                  <p class="text-sm font-medium">Brak kategorii</p>
                </div>
              </td>
            </tr>
            <tr
              v-for="category in categories"
              :key="category.id"
              class="hover:bg-gray-50/50 transition-colors duration-200"
            >
              <td class="px-3 py-3">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                    <font-awesome-icon
                      icon="fa-solid fa-pen-to-square"
                      class="w-5 h-5 text-blue-600"
                    />
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ category.name }}</div>
                    <div class="text-sm text-gray-500">{{ category.description || '-' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-3 py-3">
                <span
                  :class="[
                    'inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium',
                    category.category_type === 'smart'
                      ? 'bg-purple-50 text-purple-700'
                      : 'bg-gray-50 text-gray-700',
                  ]"
                >
                  {{ category.category_type === 'smart' ? 'Smart' : 'Standard' }}
                </span>
              </td>
              <td class="px-3 py-3">
                <div v-if="category.smart_filter" class="text-xs text-gray-600 space-y-1">
                  <div v-if="category.smart_filter.type" class="flex items-center gap-1">
                    {{
                      {
                        auction: 'Tylko licytacje',
                        buy_now: 'Tylko Kup teraz',
                        both: 'Licytacja + Kup teraz',
                      }[category.smart_filter.type]
                    }}
                  </div>
                  <div v-if="category.smart_filter.year" class="flex items-center gap-1">
                    Rocznik: {{ category.smart_filter.year }}
                  </div>
                  <div v-if="category.smart_filter.sort_by" class="flex items-center gap-1">
                    {{
                      {
                        latest: 'Najnowsze',
                        newest: 'Najnowsze',
                        bids_count_desc: 'Najwięcej licytacji',
                        price_desc: 'Od najdroższych',
                        price_asc: 'Od najtańszych',
                        ending_soon: 'Kończące się',
                        seller_reputation: 'Najlepsi hodowcy',
                      }[category.smart_filter.sort_by]
                    }}
                  </div>
                  <div v-if="category.smart_filter.user_id" class="flex items-center gap-1">
                    <font-awesome-icon icon="fa-solid fa-user" class="w-3 h-3" />
                    Hodowca: {{ category.smart_filter.user_id }}
                  </div>
                  <div v-if="category.smart_filter.breed" class="flex items-center gap-1">
                    <font-awesome-icon icon="fa-solid fa-chart-bar" class="w-3 h-3" />
                    {{ category.smart_filter.breed }}
                  </div>
                  <div
                    v-if="category.smart_filter.min_price || category.smart_filter.max_price"
                    class="flex items-center gap-1"
                  >
                    <font-awesome-icon icon="fa-solid fa-sack-dollar" class="w-3 h-3" />
                    {{ category.smart_filter.min_price || '0' }}zł -
                    {{ category.smart_filter.max_price || '∞' }}zł
                  </div>
                </div>
                <span v-else class="text-gray-400 text-sm">Brak filtru</span>
              </td>
              <td class="px-3 py-3">
                <span
                  v-if="category.is_featured"
                  class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-yellow-50 text-yellow-700 text-sm font-medium"
                >
                  <font-awesome-icon icon="fa-solid fa-star" class="w-4 h-4" />
                  Featured
                </span>
                <span
                  v-if="category.show_as_home_section"
                  class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-sm font-medium ml-1"
                >
                  Sekcja
                </span>
              </td>
              <td class="px-3 py-3">
                <span
                  class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-sm font-medium"
                >
                  {{ category.auctions_count || 0 }}
                </span>
              </td>
              <td class="px-3 py-3 flex gap-2">
                <button
                  class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300 text-sm font-medium"
                  @click="editCategory(category)"
                >
                  <font-awesome-icon icon="fa-solid fa-pen-to-square" class="w-4 h-4" />
                  Edytuj
                </button>
                <button
                  class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-red-200 text-red-700 hover:border-red-300 hover:bg-red-50/50 transition-all duration-300 text-sm font-medium"
                  @click="deleteCategory(category.id)"
                >
                  <font-awesome-icon icon="fa-solid fa-trash" class="w-4 h-4" />
                  Usuń
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-8 max-h-[90vh] overflow-y-auto relative overflow-hidden"
      >
        <div
          class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -translate-y-16 translate-x-16"
        ></div>
        <div
          class="absolute bottom-0 left-0 w-32 h-32 bg-purple-500/5 rounded-full translate-y-16 -translate-x-16"
        ></div>

        <div class="relative">
          <div class="flex justify-between items-center mb-6 sticky top-0 bg-white pb-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                <font-awesome-icon icon="fa-solid fa-pen-to-square" class="w-5 h-5 text-blue-600" />
              </div>
              <h2 class="text-2xl font-bold text-gray-900">
                {{ editingCategory ? 'Edytuj kategorię' : 'Nowa kategoria' }}
              </h2>
            </div>
            <button
              class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-colors"
              @click="closeModal"
            >
              <font-awesome-icon icon="fa-solid fa-xmark" class="w-4 h-4" />
            </button>
          </div>

          <form class="space-y-6" @submit.prevent="saveCategory">
            <!-- Podstawowe informacje -->
            <div class="border-b border-gray-200 pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-circle-info" class="w-5 h-5 text-blue-500" />
                Informacje podstawowe
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nazwa *</label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    placeholder="np. Gołębie wyścigowe"
                  />
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Opis</label>
                  <textarea
                    v-model="form.description"
                    rows="3"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    placeholder="Dodaj opis kategorii..."
                  ></textarea>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Typ kategorii *</label
                  >
                  <select
                    v-model="form.category_type"
                    required
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  >
                    <option value="standard">Standard</option>
                    <option value="smart">Smart (z filtrami)</option>
                  </select>
                </div>
              </div>
              <div
                class="flex items-center justify-between p-4 bg-amber-50 rounded-xl border border-amber-200 mt-6"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0"
                  >
                    <font-awesome-icon
                      icon="fa-solid fa-wand-magic-sparkles"
                      class="w-5 h-5 text-amber-600"
                    />
                  </div>
                  <div>
                    <p class="font-semibold text-gray-900">Wyświetlaj na stronie głównej</p>
                    <p class="text-sm text-gray-600">Promuj kategorię jako Featured</p>
                  </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                  <input v-model="form.is_featured" type="checkbox" class="sr-only peer" />
                  <div
                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-amber-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-500"
                  ></div>
                </label>
              </div>

              <div
                class="flex items-center justify-between p-4 bg-blue-50 rounded-xl border border-blue-200 mt-4"
              >
                <div class="flex items-center gap-3">
                  <div
                    class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0"
                  >
                    <font-awesome-icon icon="fa-solid fa-bars" class="w-5 h-5 text-blue-600" />
                  </div>
                  <div>
                    <p class="font-semibold text-gray-900">Pokaż jako osobną sekcję</p>
                    <p class="text-sm text-gray-600">
                      Własna sekcja na stronie głównej (jak "Kup teraz"), nie tylko kafelek w
                      Wyróżnionych kategoriach
                    </p>
                  </div>
                </div>
                <label class="relative inline-flex items-center cursor-pointer flex-shrink-0">
                  <input v-model="form.show_as_home_section" type="checkbox" class="sr-only peer" />
                  <div
                    class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"
                  ></div>
                </label>
              </div>
            </div>

            <!-- Smart Filters -->
            <div v-if="form.category_type === 'smart'" class="border-b border-gray-200 pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-sliders" class="w-5 h-5 text-purple-500" />
                Filtry Smart Kategorii
              </h3>
              <p class="text-sm text-gray-500 mb-4">
                Kategoria sama wypełni się aktywnymi aukcjami pasującymi do poniższej reguły.
              </p>
              <div class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Typ oferty</label>
                    <select
                      v-model="form.smart_filter.type"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white"
                    >
                      <option :value="null">Wszystkie oferty</option>
                      <option value="auction">Tylko licytacje</option>
                      <option value="buy_now">Tylko Kup teraz</option>
                      <option value="both">Licytacja + Kup teraz</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Sortowanie</label>
                    <select
                      v-model="form.smart_filter.sort_by"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white"
                    >
                      <option value="latest">Najnowsze najpierw</option>
                      <option value="bids_count_desc">Najwięcej licytacji</option>
                      <option value="price_desc">Od najdroższych</option>
                      <option value="price_asc">Od najtańszych</option>
                      <option value="ending_soon">Kończące się najszybciej</option>
                      <option value="seller_reputation">Najlepsi hodowcy (reputacja)</option>
                    </select>
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Rocznik gołębia (opcjonalnie)</label
                  >
                  <input
                    v-model.number="form.smart_filter.year"
                    type="number"
                    min="2000"
                    max="2100"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white"
                    placeholder="np. 2026 — pokaże tylko gołębie z tego rocznika"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >ID Hodowcy (opcjonalnie)</label
                  >
                  <input
                    v-model.number="form.smart_filter.user_id"
                    type="number"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    placeholder="Pokaż tylko aukcje od konkretnego użytkownika"
                  />
                  <p class="text-xs text-gray-500 mt-1">
                    Pozostaw puste aby pokazać aukcje od wszystkich
                  </p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Rasa (opcjonalnie)</label
                  >
                  <input
                    v-model="form.smart_filter.breed"
                    type="text"
                    list="breeds-list"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    placeholder="np. Janssen — puste = wszystkie rasy"
                  />
                  <datalist id="breeds-list">
                    <option v-for="breed in availableBreeds" :key="breed" :value="breed" />
                  </datalist>
                </div>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"
                      >Cena minimalna (zł)</label
                    >
                    <input
                      v-model.number="form.smart_filter.min_price"
                      type="number"
                      step="0.01"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"
                      >Cena maksymalna (zł)</label
                    >
                    <input
                      v-model.number="form.smart_filter.max_price"
                      type="number"
                      step="0.01"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Buttons -->
            <div class="flex gap-3 pt-6 sticky bottom-0 bg-white">
              <button
                type="submit"
                :disabled="saving"
                class="group relative flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden disabled:opacity-50"
              >
                <div
                  class="absolute inset-0 bg-blue-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                ></div>
                <span class="relative flex items-center justify-center gap-2">
                  <font-awesome-icon icon="fa-solid fa-check" class="w-5 h-5" />
                  {{
                    saving
                      ? 'Zapisywanie...'
                      : editingCategory
                        ? 'Zapisz zmiany'
                        : 'Dodaj kategorię'
                  }}
                </span>
              </button>
              <button
                type="button"
                class="flex-1 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300"
                @click="closeModal"
              >
                Anuluj
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <ConfirmModal
      :show="categoryToDelete !== null"
      title="Usuń kategorię"
      message="Czy na pewno chcesz usunąć tę kategorię? Oferty przypisane do niej nie zostaną usunięte."
      confirm-text="Usuń"
      danger
      @confirm="confirmDeleteCategory"
      @cancel="categoryToDelete = null"
    />
  </div>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import api from '../services/api'
  import { useToast } from '@/composables/useToast'
  import ConfirmModal from '@/components/ConfirmModal.vue'

  const { showSuccess, showError } = useToast()

  const availableBreeds = ref([])
  const loadBreeds = async () => {
    try {
      const response = await api.get('/breeds')
      availableBreeds.value = response.data.data || []
    } catch (e) {
      /* podpowiedzi opcjonalne */
    }
  }
  loadBreeds()

  const categories = ref([])
  const showModal = ref(false)
  const editingCategory = ref(null)
  const saving = ref(false)

  const form = ref({
    name: '',
    description: '',
    category_type: 'standard',
    smart_filter: {
      type: null,
      sort_by: 'latest',
      year: null,
      user_id: null,
      breed: '',
      min_price: null,
      max_price: null,
    },
    is_featured: false,
    show_as_home_section: false,
  })

  const fetchCategories = async () => {
    try {
      const response = await api.get('/admin/categories')
      categories.value = response.data.data
    } catch (error) {
      console.error('Error fetching categories:', error)
      showError('Nie udało się pobrać kategorii')
    }
  }

  const emptyFilter = () => ({
    type: null,
    sort_by: 'latest',
    year: null,
    user_id: null,
    breed: '',
    min_price: null,
    max_price: null,
  })

  const openCreateModal = () => {
    editingCategory.value = null
    form.value = {
      name: '',
      description: '',
      category_type: 'smart',
      smart_filter: emptyFilter(),
      is_featured: false,
      show_as_home_section: false,
    }
    showModal.value = true
  }

  const editCategory = (category) => {
    editingCategory.value = category
    form.value = {
      name: category.name,
      description: category.description || '',
      category_type: category.category_type,
      smart_filter: { ...emptyFilter(), ...(category.smart_filter || {}) },
      is_featured: category.is_featured,
      show_as_home_section: category.show_as_home_section || false,
    }
    showModal.value = true
  }

  const closeModal = () => {
    showModal.value = false
    editingCategory.value = null
  }

  const saveCategory = async () => {
    saving.value = true
    try {
      const payload = { ...form.value }

      // Clean smart_filter if category_type is standard
      if (payload.category_type === 'standard') {
        payload.smart_filter = null
      } else {
        // Remove empty values from smart_filter
        const cleanFilter = {}
        if (payload.smart_filter.type) cleanFilter.type = payload.smart_filter.type
        if (payload.smart_filter.sort_by) cleanFilter.sort_by = payload.smart_filter.sort_by
        if (payload.smart_filter.year) cleanFilter.year = payload.smart_filter.year
        if (payload.smart_filter.user_id) cleanFilter.user_id = payload.smart_filter.user_id
        if (payload.smart_filter.breed) cleanFilter.breed = payload.smart_filter.breed
        if (payload.smart_filter.min_price) cleanFilter.min_price = payload.smart_filter.min_price
        if (payload.smart_filter.max_price) cleanFilter.max_price = payload.smart_filter.max_price
        payload.smart_filter = Object.keys(cleanFilter).length > 0 ? cleanFilter : null
      }

      if (editingCategory.value) {
        await api.patch(`/admin/categories/${editingCategory.value.id}`, payload)
        showSuccess('Kategoria została zaktualizowana')
      } else {
        await api.post('/admin/categories', payload)
        showSuccess('Kategoria została utworzona')
      }

      await fetchCategories()
      closeModal()
    } catch (error) {
      console.error('Error saving category:', error)
      showError(error.response?.data?.message || 'Nie udało się zapisać kategorii')
    } finally {
      saving.value = false
    }
  }

  const categoryToDelete = ref(null)

  const deleteCategory = (id) => {
    categoryToDelete.value = id
  }

  const confirmDeleteCategory = async () => {
    const id = categoryToDelete.value
    categoryToDelete.value = null
    try {
      await api.delete(`/admin/categories/${id}`)
      showSuccess('Kategoria została usunięta')
      await fetchCategories()
    } catch (error) {
      console.error('Error deleting category:', error)
      showError('Nie udało się usunąć kategorii')
    }
  }

  onMounted(() => {
    fetchCategories()
  })
</script>

<style scoped>
  .btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: #2563eb;
    color: white;
    border: none;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
  }

  .btn-primary:hover:not(:disabled) {
    background: #1d4ed8;
  }

  .btn-primary:disabled {
    opacity: 0.5;
    cursor: not-allowed;
  }

  .btn-secondary {
    padding: 0.75rem 1.5rem;
    background: white;
    color: #374151;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
  }

  .btn-secondary:hover {
    background: #f9fafb;
    border-color: #9ca3af;
  }
</style>
