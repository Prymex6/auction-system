<template>
  <div class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8">
    <div class="mb-6">
      <h2 class="text-2xl font-bold text-gray-900 mb-1">Strony statyczne</h2>
      <p class="text-gray-500 text-sm">
        Regulamin, polityki i strony pomocy. Nagłówki sekcji zapisuj jako „## Tytuł", listy jako „1.
        Punkt".
      </p>
    </div>

    <div v-if="loading" class="py-12 flex justify-center">
      <Spinner />
    </div>

    <div v-else class="grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-6">
      <!-- Lista stron -->
      <div class="space-y-1.5">
        <button
          v-for="p in pages"
          :key="p.slug"
          class="w-full text-left px-4 py-2.5 rounded-xl text-sm font-medium transition-colors"
          :class="
            selected?.slug === p.slug
              ? 'bg-blue-600 text-white'
              : 'bg-gray-50 text-gray-700 hover:bg-gray-100'
          "
          @click="selectPage(p.slug)"
        >
          {{ p.title }}
          <span
            class="block text-[11px] mt-0.5"
            :class="selected?.slug === p.slug ? 'text-white/70' : 'text-gray-400'"
          >
            /{{ p.slug }}
          </span>
        </button>
      </div>

      <!-- Edytor -->
      <div v-if="selected">
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Tytuł strony</label>
          <input
            v-model="form.title"
            type="text"
            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400"
          />
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-1.5">Treść</label>
          <textarea
            v-model="form.content"
            rows="22"
            class="w-full px-4 py-3 border border-gray-200 rounded-xl font-mono text-sm leading-relaxed focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400"
          ></textarea>
        </div>

        <div class="flex items-center gap-3">
          <button
            :disabled="saving"
            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-colors disabled:opacity-50"
            @click="save"
          >
            {{ saving ? 'Zapisywanie...' : 'Zapisz zmiany' }}
          </button>
          <a
            :href="`/${selected.slug}`"
            target="_blank"
            class="px-4 py-2.5 border border-gray-200 text-gray-700 rounded-xl text-sm font-medium hover:border-blue-300 transition-colors"
          >
            Podgląd strony
          </a>
          <span v-if="savedAt" class="text-sm text-emerald-600">Zapisano {{ savedAt }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import api from '@/services/api'
  import Spinner from '@/components/feedback/Spinner.vue'
  import { useToast } from '@/composables/useToast'

  const { showSuccess, showError } = useToast()

  const pages = ref([])
  const selected = ref(null)
  const form = ref({ title: '', content: '' })
  const loading = ref(true)
  const saving = ref(false)
  const savedAt = ref('')

  const loadPages = async () => {
    const response = await api.get('/pages')
    pages.value = response.data.data || []
  }

  const selectPage = async (slug) => {
    savedAt.value = ''
    const response = await api.get(`/pages/${slug}`)
    selected.value = response.data.data
    form.value = { title: selected.value.title, content: selected.value.content }
  }

  const save = async () => {
    saving.value = true
    try {
      await api.patch(`/admin/pages/${selected.value.slug}`, form.value)
      savedAt.value = new Date().toLocaleTimeString('pl-PL', { hour: '2-digit', minute: '2-digit' })
      showSuccess('Strona została zapisana')
      await loadPages()
    } catch (error) {
      console.error('Error saving page:', error)
      showError(error.response?.data?.message || 'Nie udało się zapisać strony')
    } finally {
      saving.value = false
    }
  }

  onMounted(async () => {
    try {
      await loadPages()
      if (pages.value.length) {
        await selectPage(pages.value[0].slug)
      }
    } catch (error) {
      console.error('Error loading pages:', error)
    } finally {
      loading.value = false
    }
  })
</script>
