<template>
  <div class="min-h-screen bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
      <!-- Loading -->
      <div v-if="loading" class="py-24 flex justify-center">
        <GlobalLoadingOverlay message="Ładowanie strony..." />
      </div>

      <!-- Not found -->
      <div v-else-if="notFound" class="text-center py-24">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Strona nie istnieje</h1>
        <p class="text-gray-500 mb-6">Nie znaleźliśmy treści pod tym adresem.</p>
        <router-link
          to="/"
          class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-colors"
        >
          Wróć na stronę główną
        </router-link>
      </div>

      <!-- Content -->
      <article v-else>
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">{{ page.title }}</h1>
        <p class="text-sm text-gray-400 mb-10">
          Ostatnia aktualizacja: {{ formatDate(page.updated_at) }}
        </p>

        <template v-for="(block, i) in blocks" :key="i">
          <h2
            v-if="block.type === 'h2'"
            class="text-xl font-bold text-gray-900 mt-10 mb-4 first:mt-0"
          >
            {{ block.text }}
          </h2>
          <ol
            v-else-if="block.type === 'list'"
            class="space-y-2 mb-4 text-gray-600 leading-relaxed list-none"
          >
            <li v-for="(item, j) in block.items" :key="j" class="flex gap-3">
              <span class="text-blue-600 font-semibold shrink-0">{{ item.marker }}</span>
              <span>{{ item.text }}</span>
            </li>
          </ol>
          <p v-else class="text-gray-600 leading-relaxed mb-4">{{ block.text }}</p>
        </template>
      </article>
    </div>
  </div>
</template>

<script setup>
  import { ref, computed, watch } from 'vue'
  import { useRoute } from 'vue-router'
  import { useSeo } from '@/composables/useSeo'
  import GlobalLoadingOverlay from '@/components/GlobalLoadingOverlay.vue'

  const route = useRoute()
  const page = ref({ title: '', content: '', updated_at: null })
  const loading = ref(true)
  const notFound = ref(false)

  const slug = computed(() => route.meta.pageSlug)

  const blocks = computed(() => {
    const out = []
    let list = null
    for (const raw of (page.value.content || '').split('\n')) {
      const line = raw.trim()
      if (!line) {
        list = null
        continue
      }
      if (line.startsWith('## ')) {
        list = null
        out.push({ type: 'h2', text: line.slice(3) })
        continue
      }
      const m = line.match(/^(\d+)\.\s+(.*)$/)
      if (m) {
        if (!list) {
          list = { type: 'list', items: [] }
          out.push(list)
        }
        list.items.push({ marker: `${m[1]}.`, text: m[2] })
        continue
      }
      list = null
      out.push({ type: 'p', text: line })
    }
    return out
  })

  const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('pl-PL', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    })
  }

  const fetchPage = async () => {
    loading.value = true
    notFound.value = false
    try {
      const response = await fetch(`/api/pages/${slug.value}`)
      if (response.status === 404) {
        notFound.value = true
        return
      }
      const data = await response.json()
      page.value = data.data
      useSeo({ title: page.value.title, type: 'article' })
    } catch (error) {
      console.error('Error fetching page:', error)
      notFound.value = true
    } finally {
      loading.value = false
    }
  }

  watch(slug, fetchPage, { immediate: true })
</script>
