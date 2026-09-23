<template>
  <div>
    <!-- Tab buttons -->
    <div class="flex border-b border-gray-200 gap-1">
      <button
        v-for="(tab, index) in tabs"
        :key="index"
        :class="[
          'px-4 py-2 font-medium transition-colors border-b-2',
          activeTab === index
            ? 'border-primary-600 text-primary-600'
            : 'border-transparent text-gray-600 hover:text-gray-900',
        ]"
        @click="activeTab = index"
      >
        {{ tab.label }}
      </button>
    </div>

    <!-- Tab content -->
    <div class="py-4">
      <slot :name="`tab-${tabs[activeTab]?.id || activeTab}`" />
    </div>
  </div>
</template>

<script setup>
  import { ref } from 'vue'

  defineProps({
    /**
     * Array of tabs: { id, label }
     */
    tabs: {
      type: Array,
      required: true,
    },
    /**
     * Active tab index (use v-model:active for two-way binding)
     */
    active: {
      type: Number,
      default: 0,
    },
  })

  const emit = defineEmits(['update:active'])

  const activeTab = ref(0)
</script>
