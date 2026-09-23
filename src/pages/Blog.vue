<template>
  <div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <section class="pt-24 pb-16 md:pt-32 md:pb-24 relative overflow-hidden">
      <div class="absolute inset-0 bg-white"></div>
      <div class="absolute top-20 right-20 w-96 h-96 bg-blue-100/30 rounded-full blur-3xl"></div>
      <div
        class="absolute bottom-20 left-20 w-96 h-96 bg-purple-100/30 rounded-full blur-3xl"
      ></div>

      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
        <div class="text-center max-w-4xl mx-auto">
          <div
            class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-sm border border-gray-100 mb-8"
          >
            <span class="text-sm font-medium text-gray-700">Wiedza i porady</span>
          </div>

          <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 mb-6">
            Blog
            <span class="block text-gray-900">
              {{ platformName }}
            </span>
          </h1>

          <p class="text-xl text-gray-600 mb-10 max-w-2xl mx-auto leading-relaxed">
            {{ platformDescription }}
          </p>
        </div>
      </div>
    </section>

    <!-- Blog Posts Grid -->
    <section class="py-16 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <GlobalLoadingOverlay v-if="loading" message="Ładowanie artykułów..." />

        <!-- Blog Posts -->
        <div
          v-else-if="posts.length > 0"
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
        >
          <article
            v-for="blog in posts"
            :key="blog.id"
            class="group bg-white rounded-2xl border border-gray-100 hover:shadow-xl hover:-translate-y-2 transition-all duration-500 overflow-hidden"
          >
            <!-- Hero Image with Category Colors -->
            <div class="h-48 relative overflow-hidden">
              <img
                v-if="blog.image"
                :src="blog.image"
                :alt="blog.title"
                class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-500"
              />
              <div
                v-else
                class="w-full h-full"
                :style="`background: linear-gradient(135deg, ${getBlogColor(blog, 'color1')}, ${getBlogColor(blog, 'color2')})`"
              >
                <div class="absolute inset-0 flex items-center justify-center">
                  <div
                    class="text-6xl transform group-hover:scale-110 transition-transform duration-500"
                  >
                    {{ getBlogEmoji(blog) }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Content -->
            <div class="p-6">
              <div class="flex items-center gap-3 mb-4">
                <span
                  class="px-3 py-1 rounded-full text-xs font-semibold"
                  :style="`background: ${getBlogColor(blog, 'tagBg')}; color: ${getBlogColor(blog, 'tagColor')}`"
                >
                  {{ blog.category || 'Blog' }}
                </span>
                <span class="text-sm text-gray-500">{{ formatDate(blog.published_at) }}</span>
              </div>

              <h3
                class="text-xl font-bold text-gray-900 mb-4 group-hover:text-blue-600 transition-colors duration-300"
              >
                {{ blog.title }}
              </h3>
              <p class="text-gray-600 mb-6 leading-relaxed">
                {{ blog.excerpt }}
              </p>

              <RouterLink
                :to="{ name: 'BlogDetail', params: { slug: blog.slug } }"
                class="inline-flex items-center gap-2 text-blue-600 font-semibold group/link"
              >
                Czytaj więcej
                <font-awesome-icon
                  icon="fa-solid fa-arrow-right"
                  class="w-4 h-4 transform group-hover/link:translate-x-1 transition-transform"
                />
              </RouterLink>
            </div>
          </article>
        </div>

        <!-- No Posts -->
        <div
          v-else
          class="text-center py-16 bg-gray-50 rounded-3xl border-2 border-dashed border-blue-200"
        >
          <div
            class="w-24 h-24 mx-auto mb-6 rounded-full bg-gray-100 flex items-center justify-center"
          >
            <IconPicker name="book" color-class="text-blue-600" class="w-12 h-12" />
          </div>
          <h3 class="text-2xl font-bold text-gray-900 mb-3">Brak artykułów</h3>
          <p class="text-gray-600 mb-6 max-w-md mx-auto">
            Wróć wkrótce! Pracujemy nad ciekawymi artykułami dla Ciebie.
          </p>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
  import { ref, onMounted, computed } from 'vue'
  import { RouterLink } from 'vue-router'
  import { useSeo } from '@/composables/useSeo'
  import { usePlatformSettings } from '@/composables/usePlatformSettings'
  import { useBlogFormatting } from '@/composables/useBlogFormatting'
  import api from '@/services/api'
  import IconPicker from '@/components/icons/IconPicker.vue'
  import GlobalLoadingOverlay from '@/components/GlobalLoadingOverlay.vue'

  const { getPlatformName, getPlatformDescription } = usePlatformSettings()
  const platformDescription = computed(() => getPlatformDescription.value)

  const platformName = computed(() => getPlatformName.value)
  const { getBlogEmoji, getBlogColor } = useBlogFormatting()

  useSeo({
    title: 'Blog',
    description:
      'Poradniki, artykuły i porady dla hodowców gołębi. Dowiedz się o zdrowiu, strategiach hodowli i trendach na rynku.',
    url: '/blog',
  })

  const posts = ref([])
  const loading = ref(true)

  const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('pl-PL', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    })
  }

  onMounted(async () => {
    try {
      const res = await api.get('/blogs')
      if (res.data.data) {
        posts.value = res.data.data
      }
    } catch (err) {
      console.error('Error loading blogs:', err)
    } finally {
      loading.value = false
    }
  })
</script>
