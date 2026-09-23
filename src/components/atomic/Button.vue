<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    :class="[
      'btn',
      variantClasses,
      sizeClasses,
      {
        'opacity-50 cursor-not-allowed': disabled || loading,
      },
    ]"
    @click="$emit('click')"
  >
    <div v-if="loading" class="flex items-center justify-center gap-2">
      <font-awesome-icon icon="fa-solid fa-spinner" class="animate-spin h-5 w-5" />
      <span>{{ loadingText }}</span>
    </div>
    <slot v-else />
  </button>
</template>

<script setup>
  import { computed } from 'vue'

  const props = defineProps({
    /**
     * Button variant: 'primary', 'secondary', 'danger', 'neutral', 'success'
     */
    variant: {
      type: String,
      default: 'primary',
      validator: (v) => ['primary', 'secondary', 'danger', 'neutral', 'success'].includes(v),
    },
    /**
     * Button size: 'sm', 'md', 'lg'
     */
    size: {
      type: String,
      default: 'md',
      validator: (v) => ['sm', 'md', 'lg'].includes(v),
    },
    /**
     * Button type: 'button', 'submit', 'reset'
     */
    type: {
      type: String,
      default: 'button',
      validator: (v) => ['button', 'submit', 'reset'].includes(v),
    },
    /**
     * Disabled state
     */
    disabled: {
      type: Boolean,
      default: false,
    },
    /**
     * Loading state
     */
    loading: {
      type: Boolean,
      default: false,
    },
    /**
     * Text shown while loading
     */
    loadingText: {
      type: String,
      default: 'Ładowanie...',
    },
    /**
     * Full width button
     */
    fullWidth: {
      type: Boolean,
      default: false,
    },
  })

  defineEmits(['click'])

  const variantClasses = computed(() => {
    const variants = {
      primary: 'btn-primary',
      secondary: 'btn-secondary',
      danger: 'btn-error',
      neutral: 'btn-neutral',
      success: 'btn-success',
    }
    return variants[props.variant]
  })

  const sizeClasses = computed(() => {
    const sizes = {
      sm: 'text-sm px-3 py-1.5',
      md: 'text-base px-4 py-2.5',
      lg: 'text-lg px-6 py-3',
    }
    return [sizes[props.size], props.fullWidth ? 'w-full' : '']
  })
</script>

<style scoped>
  button {
    transition: all 0.2s ease;
  }

  button:hover:not(:disabled) {
    transform: translateY(-1px);
  }

  button:active:not(:disabled) {
    transform: translateY(0);
  }
</style>
