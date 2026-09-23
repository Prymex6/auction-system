<template>
  <div :class="['flex items-center justify-center', sizeClasses]">
    <font-awesome-icon icon="fa-solid fa-spinner" class="animate-spin" :class="colorClasses" />
    <span v-if="label" class="ml-2 text-sm">{{ label }}</span>
  </div>
</template>

<script setup>
  import { computed } from 'vue'

  const props = defineProps({
    /**
     * Spinner size: 'sm', 'md', 'lg'
     */
    size: {
      type: String,
      default: 'md',
      validator: (v) => ['sm', 'md', 'lg'].includes(v),
    },
    /**
     * Spinner color: 'primary', 'secondary', 'success', 'danger'
     */
    color: {
      type: String,
      default: 'primary',
      validator: (v) => ['primary', 'secondary', 'success', 'danger'].includes(v),
    },
    /**
     * Label text
     */
    label: {
      type: String,
      default: '',
    },
  })

  const sizeClasses = computed(() => {
    const sizes = {
      sm: 'h-8 w-8',
      md: 'h-12 w-12',
      lg: 'h-16 w-16',
    }
    return sizes[props.size]
  })

  const colorClasses = computed(() => {
    const colors = {
      primary: 'text-primary-600',
      secondary: 'text-secondary-600',
      success: 'text-green-600',
      danger: 'text-red-600',
    }
    return colors[props.color]
  })
</script>
