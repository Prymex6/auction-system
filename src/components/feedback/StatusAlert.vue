<template>
  <div
    v-if="show"
    class="mb-8 p-6 rounded-2xl flex items-center gap-4 border-2"
    :class="variantClasses"
  >
    <div class="flex-shrink-0">
      <font-awesome-icon icon="fa-solid fa-circle-info" class="w-8 h-8" />
    </div>
    <div>
      <h3 class="font-bold mb-1" :class="headingClass">{{ title }}</h3>
      <p class="text-sm" :class="textClass">
        <slot>{{ message }}</slot>
      </p>
    </div>
  </div>
</template>

<script setup>
  import { computed } from 'vue'

  const props = defineProps({
    variant: {
      type: String,
      default: 'inactive',
      validator: (v) => ['inactive', 'active', 'warning'].includes(v),
    },
    title: {
      type: String,
      required: true,
    },
    message: {
      type: String,
      default: '',
    },
    show: {
      type: Boolean,
      default: true,
    },
  })

  const variantClasses = computed(() => {
    const variants = {
      inactive: 'bg-blue-50 border-blue-200',
      active: 'bg-red-50 border-red-200',
      warning: 'bg-yellow-50 border-yellow-200',
    }
    return variants[props.variant] || variants.inactive
  })

  const headingClass = computed(() => {
    const variants = {
      inactive: 'text-blue-900',
      active: 'text-red-800',
      warning: 'text-yellow-900',
    }
    return variants[props.variant] || variants.inactive
  })

  const textClass = computed(() => {
    const variants = {
      inactive: 'text-blue-800',
      active: 'text-red-700',
      warning: 'text-yellow-700',
    }
    return variants[props.variant] || variants.inactive
  })
</script>
