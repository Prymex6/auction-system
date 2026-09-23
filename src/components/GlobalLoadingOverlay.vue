<template>
  <div class="fixed inset-0 flex items-center justify-center bg-white/80 backdrop-blur-sm z-50">
    <div class="text-center">
      <img
        :src="'/images/logo-golab.png'"
        alt="Gołębiowy Lot"
        class="w-16 h-16 mx-auto mb-6 rounded-2xl shadow-lg"
      />

      <div class="relative w-20 h-20 mx-auto">
        <div class="absolute inset-0 rounded-full border-4 border-blue-100"></div>
        <div
          class="absolute inset-0 rounded-full border-4 border-blue-500 border-t-transparent animate-spin"
        ></div>
      </div>

      <p class="mt-6 text-lg font-medium text-gray-700">{{ message }}</p>
      <p v-if="subMessage" class="mt-1 text-sm text-gray-500">{{ subMessage }}</p>
    </div>
  </div>
</template>

<script setup>
  defineProps({
    message: {
      type: String,
      default: 'Ładowanie...',
    },
    subMessage: {
      type: String,
      default: '',
    },
  })
</script>
