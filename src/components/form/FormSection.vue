<template>
  <div class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 relative overflow-hidden">
    <div
      class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -translate-y-16 translate-x-16"
    ></div>

    <!-- Header with Icon -->
    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
      <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
        <font-awesome-icon
          v-if="iconType === 'image'"
          icon="fa-solid fa-image"
          class="w-5 h-5 text-blue-600"
        />
        <slot v-else name="icon"></slot>
      </div>
      {{ title }}
    </h2>

    <slot name="content"></slot>
  </div>
</template>

<script setup>
  defineProps({
    title: {
      type: String,
      required: true,
    },
    iconType: {
      type: String,
      default: 'image',
    },
  })
</script>
