<template>
  <div class="min-h-screen flex items-center justify-center bg-white px-4 relative overflow-hidden">
    <!-- Background Elements -->
    <div class="absolute top-20 right-20 w-96 h-96 bg-blue-100/30 rounded-full blur-3xl"></div>
    <div class="absolute bottom-20 left-20 w-96 h-96 bg-purple-100/30 rounded-full blur-3xl"></div>

    <div class="text-center relative z-10">
      <!-- 404 Number with Gradient -->
      <div class="mb-8">
        <h1 class="text-9xl font-bold text-gray-900 mb-2">404</h1>
        <div class="w-32 h-2 mx-auto bg-blue-600 rounded-full"></div>
      </div>

      <!-- Content -->
      <h2 class="text-3xl font-bold text-gray-900 mb-4">Strona nie znaleziona</h2>
      <p class="text-xl text-gray-600 mb-10 max-w-md mx-auto">
        Przepraszamy, strona którą szukasz nie istnieje lub została przeniesiona.
      </p>

      <!-- Button -->
      <router-link
        to="/"
        class="inline-flex items-center gap-2 px-8 py-4 bg-blue-600 text-white rounded-xl font-bold hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
      >
        <font-awesome-icon icon="fa-solid fa-house" class="w-5 h-5" />
        Powrót na stronę główną
      </router-link>
    </div>
  </div>
</template>

<script setup>
  import { onMounted } from 'vue'
  import { useSeo } from '@/composables/useSeo'

  onMounted(() => {
    useSeo({
      title: 'Strona nie znaleziona',
      description: 'Przepraszamy, strona którą szukasz nie istnieje lub została przeniesiona.',
    })
  })
</script>
