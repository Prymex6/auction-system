<template>
  <div class="flex flex-col items-center justify-center py-12 gap-4">
    <Spinner :size="size" :color="color" />
    <div class="text-center">
      <p v-if="message" class="text-gray-600">{{ message }}</p>
      <p v-if="submessage" class="text-sm text-gray-500 mt-1">{{ submessage }}</p>
    </div>
  </div>
</template>

<script setup>
  import Spinner from './Spinner.vue'

  defineProps({
    /**
     * Loading message
     */
    message: {
      type: String,
      default: 'Ładowanie...',
    },
    /**
     * Sub-message
     */
    submessage: {
      type: String,
      default: '',
    },
    /**
     * Spinner size
     */
    size: {
      type: String,
      default: 'md',
    },
    /**
     * Spinner color
     */
    color: {
      type: String,
      default: 'primary',
    },
  })
</script>
