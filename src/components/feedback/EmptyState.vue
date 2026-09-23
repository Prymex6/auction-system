<template>
  <div class="flex flex-col items-center justify-center py-12 gap-4">
    <div class="w-16 h-16 flex items-center justify-center">
      <font-awesome-icon
        v-if="icon === 'rocket'"
        icon="fa-solid fa-bolt"
        class="w-full h-full text-gray-400"
      />
      <font-awesome-icon
        v-else-if="icon === 'search'"
        icon="fa-solid fa-magnifying-glass"
        class="w-full h-full text-gray-400"
      />
      <font-awesome-icon
        v-else-if="icon === 'inbox'"
        icon="fa-solid fa-inbox"
        class="w-full h-full text-gray-400"
      />
      <span v-else class="text-6xl">{{ icon }}</span>
    </div>
    <div class="text-center">
      <p class="text-lg font-semibold text-gray-900">{{ title }}</p>
      <p v-if="message" class="text-gray-600 mt-1">{{ message }}</p>
    </div>
    <slot v-if="$slots.default" />
  </div>
</template>

<script setup>
  defineProps({
    /**
     * Icon name or emoji
     */
    icon: {
      type: String,
      default: 'rocket',
    },
    /**
     * Title text
     */
    title: {
      type: String,
      required: true,
    },
    /**
     * Message text
     */
    message: {
      type: String,
      default: '',
    },
  })
</script>
