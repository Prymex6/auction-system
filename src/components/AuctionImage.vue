<template>
  <div :class="['relative overflow-hidden bg-gray-100', heightClass]">
    <div v-if="src && !imageError" class="absolute inset-0 bg-gray-100"></div>
    <img
      v-if="src && !imageError"
      :src="src"
      :alt="alt"
      class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
      @error="imageError = true"
    />
    <div v-else class="w-full h-full flex items-center justify-center">
      <IconPicker
        name="pigeon"
        color-class="text-gray-300"
        class="w-24 h-24 transform group-hover:scale-110 transition-transform duration-300"
      />
    </div>
    <slot name="overlay" />
  </div>
</template>

<script setup>
  import { ref } from 'vue'
  import IconPicker from '@/components/icons/IconPicker.vue'

  defineProps({
    src: {
      type: String,
      default: '',
    },
    alt: {
      type: String,
      default: '',
    },
    heightClass: {
      type: String,
      default: 'h-80',
    },
  })

  const imageError = ref(false)
</script>
