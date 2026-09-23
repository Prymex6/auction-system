<template>
  <span :class="['badge', variantClasses, sizeClasses]">
    <slot />
  </span>
</template>

<script setup>
  import { computed } from 'vue'

  const props = defineProps({
    /**
     * Badge variant: 'primary', 'secondary', 'success', 'danger', 'warning'
     */
    variant: {
      type: String,
      default: 'primary',
      validator: (v) => ['primary', 'secondary', 'success', 'danger', 'warning'].includes(v),
    },
    /**
     * Badge size: 'sm', 'md', 'lg'
     */
    size: {
      type: String,
      default: 'md',
      validator: (v) => ['sm', 'md', 'lg'].includes(v),
    },
  })

  const variantClasses = computed(() => {
    const variants = {
      primary: 'badge-primary',
      secondary: 'badge-secondary',
      success: 'badge-success',
      danger: 'badge-error',
      warning: 'badge-warning',
    }
    return variants[props.variant]
  })

  const sizeClasses = computed(() => {
    const sizes = {
      sm: 'text-xs px-2 py-1',
      md: 'text-sm px-3 py-1.5',
      lg: 'text-base px-4 py-2',
    }
    return sizes[props.size]
  })
</script>
