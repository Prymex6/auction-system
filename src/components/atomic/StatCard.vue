<template>
  <div
    class="bg-white rounded-2xl border border-gray-100 shadow-xl p-6 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300"
  >
    <div class="flex items-center gap-4">
      <div class="w-12 h-12 rounded-xl flex items-center justify-center" :class="iconBgClass">
        <font-awesome-icon
          icon="fa-solid fa-circle-check"
          class="w-6 h-6"
          :class="iconColorClass"
        />
      </div>
      <div>
        <p class="text-sm text-gray-600">{{ label }}</p>
        <p class="text-3xl font-bold text-gray-900">{{ value }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { computed } from 'vue'

  const props = defineProps({
    label: {
      type: String,
      required: true,
    },
    value: {
      type: [String, Number],
      required: true,
    },
    icon: {
      type: String,
      default: 'users',
      validator: (v) => ['users', 'auctions', 'reports', 'banned'].includes(v),
    },
    color: {
      type: String,
      default: 'blue',
      validator: (v) => ['blue', 'purple', 'pink', 'orange'].includes(v),
    },
  })

  const colorMap = {
    blue: { bg: 'bg-blue-50', text: 'text-blue-600' },
    purple: { bg: 'bg-gray-100', text: 'text-gray-600' },
    pink: { bg: 'bg-gray-100', text: 'text-gray-600' },
    orange: { bg: 'bg-amber-50', text: 'text-amber-600' },
  }

  const iconBgClass = computed(() => colorMap[props.color].bg)
  const iconColorClass = computed(() => colorMap[props.color].text)
</script>
