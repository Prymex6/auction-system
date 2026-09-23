<template>
  <div class="flex flex-col items-center justify-center py-12 gap-4">
    <div class="text-red-500">
      <font-awesome-icon icon="fa-solid fa-circle-exclamation" class="w-16 h-16" />
    </div>
    <div class="text-center">
      <p class="text-lg font-semibold text-red-900">{{ title }}</p>
      <p v-if="message" class="text-red-700 mt-1">{{ message }}</p>
    </div>
    <div v-if="$slots.default" class="mt-4">
      <slot />
    </div>
  </div>
</template>

<script setup>
  defineProps({
    /**
     * Error title
     */
    title: {
      type: String,
      default: 'Coś poszło nie tak',
    },
    /**
     * Error message
     */
    message: {
      type: String,
      default: '',
    },
  })
</script>
