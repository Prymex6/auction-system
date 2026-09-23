<template>
  <div class="min-h-screen bg-white">
    <GlobalLoadingOverlay v-if="loading" message="Ładowanie artykułu..." />

    <!-- Article -->
    <article v-else-if="post">
      <!-- Hero Section with Category -->
      <div class="pt-24 pb-16 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
          <!-- Category Badge with Color -->
          <div class="flex items-center gap-3 mb-8">
            <div
              class="w-16 h-16 rounded-2xl flex items-center justify-center text-3xl"
              :style="`background: linear-gradient(135deg, ${getBlogColor(post, 'color1')}, ${getBlogColor(post, 'color2')})`"
            >
              {{ getBlogEmoji(post) }}
            </div>
            <div>
              <span
                class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold"
                :style="`background: ${getBlogColor(post, 'tagBg')}; color: ${getBlogColor(post, 'tagColor')}`"
              >
                {{ post.category || 'Blog' }}
              </span>
            </div>
          </div>

          <!-- Title -->
          <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
            {{ post.title }}
          </h1>

          <!-- Meta Info -->
          <div class="flex flex-wrap items-center gap-6 pb-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
              <div
                class="w-12 h-12 rounded-full bg-blue-600 flex items-center justify-center text-white text-lg font-bold"
              >
                {{ post.author?.name?.charAt(0) || 'A' }}
              </div>
              <div>
                <p class="font-semibold text-gray-900">{{ post.author?.name }}</p>
                <p class="text-sm text-gray-500">Autor</p>
              </div>
            </div>
            <div class="flex items-center gap-4 text-sm text-gray-500">
              <span>{{ formatDate(post.published_at) }}</span>
              <span>•</span>
              <span>{{ post.views }} wyświetleń</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Hero Image -->
      <div v-if="post.image" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mb-4">
        <img
          :src="post.image"
          :alt="post.title"
          class="w-full h-auto rounded-2xl object-cover max-h-[420px]"
        />
      </div>

      <!-- Content -->
      <div class="bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
          <!-- Article Content -->
          <div class="prose prose-lg max-w-none mb-12">
            <div class="blog-content text-gray-700 leading-relaxed" v-html="post.content"></div>
          </div>

          <!-- Back Button -->
          <div class="pt-12 border-t border-gray-100">
            <RouterLink
              to="/blog"
              class="inline-flex items-center gap-2 px-6 py-3 bg-blue-50 text-blue-700 rounded-xl font-semibold hover:shadow-md transition-all duration-300"
            >
              <font-awesome-icon icon="fa-solid fa-chevron-left" class="w-5 h-5" />
              Powrót do bloga
            </RouterLink>
          </div>
        </div>
      </div>
    </article>

    <!-- Not Found -->
    <div v-else class="min-h-screen flex items-center justify-center bg-gray-100">
      <div class="text-center">
        <div class="text-7xl mb-6">📰</div>
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Artykuł nie znaleziony</h2>
        <p class="text-gray-600 mb-8">Artykuł, którego szukasz, nie istnieje.</p>
        <RouterLink
          to="/blog"
          class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:shadow-lg transition-all"
        >
          <font-awesome-icon icon="fa-solid fa-chevron-left" class="w-5 h-5" />
          Powrót do bloga
        </RouterLink>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, onMounted } from 'vue'
  import { useRoute, RouterLink } from 'vue-router'
  import { useSeo } from '@/composables/useSeo'
  import { useBlogFormatting } from '@/composables/useBlogFormatting'
  import api from '@/services/api'
  import GlobalLoadingOverlay from '@/components/GlobalLoadingOverlay.vue'

  const route = useRoute()
  const post = ref(null)
  const loading = ref(true)
  const { getBlogEmoji, getBlogColor } = useBlogFormatting()

  // Update SEO when post loads
  const updateSeo = () => {
    if (post.value) {
      useSeo({
        title: post.value.title,
        description: post.value.excerpt,
        image: post.value.image ? `${window.location.origin}${post.value.image}` : undefined,
        url: `/blog/${route.params.slug}`,
        type: 'article',
      })
    }
  }

  const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    return date.toLocaleDateString('pl-PL', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    })
  }

  onMounted(async () => {
    try {
      const res = await api.get(`/blogs/${route.params.slug}`)
      if (res.data.data) {
        post.value = res.data.data
        updateSeo()
      }
    } catch (err) {
      console.error('Error loading blog post:', err)
    } finally {
      loading.value = false
    }
  })
</script>

<style scoped>
  .blog-content :deep(h2) {
    font-size: 1.5rem;
    font-weight: 700;
    color: #111827;
    margin-top: 2rem;
    margin-bottom: 1rem;
  }
  .blog-content :deep(h3) {
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
  }
  .blog-content :deep(p) {
    margin-bottom: 1rem;
  }
  .blog-content :deep(ul),
  .blog-content :deep(ol) {
    margin: 1rem 0 1.5rem 1.5rem;
  }
  .blog-content :deep(ul) {
    list-style-type: disc;
  }
  .blog-content :deep(ol) {
    list-style-type: decimal;
  }
  .blog-content :deep(li) {
    margin-bottom: 0.5rem;
  }
  .blog-content :deep(strong) {
    color: #111827;
    font-weight: 700;
  }
  .blog-content :deep(blockquote) {
    border-left: 4px solid #3b82f6;
    padding-left: 1rem;
    font-style: italic;
    color: #4b5563;
    margin: 1.5rem 0;
  }
</style>
