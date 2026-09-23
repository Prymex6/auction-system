<template>
  <div class="form-group">
    <Checkbox
      :id="id"
      :model-value="modelValue"
      :label="label"
      :disabled="disabled"
      @update:model-value="$emit('update:modelValue', $event)"
    />
    <p v-if="error" class="text-red-500 text-sm mt-1">
      {{ error }}
    </p>
    <p v-else-if="hint" class="text-gray-500 text-sm mt-1">
      {{ hint }}
    </p>
  </div>
</template>

<script setup>
  import Checkbox from '@/components/atomic/Checkbox.vue'

  defineProps({
    /**
     * Label text
     */
    label: {
      type: String,
      default: '',
    },
    /**
     * v-model value
     */
    modelValue: {
      type: Boolean,
      default: false,
    },
    /**
     * Error message
     */
    error: {
      type: String,
      default: '',
    },
    /**
     * Hint text
     */
    hint: {
      type: String,
      default: '',
    },
    /**
     * Input ID
     */
    id: {
      type: String,
      default: '',
    },
    /**
     * Disabled state
     */
    disabled: {
      type: Boolean,
      default: false,
    },
  })

  defineEmits(['update:modelValue'])
</script>

<style scoped>
  .form-group {
    margin-bottom: 1.25rem;
  }
</style>
