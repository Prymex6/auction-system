<template>
  <div class="form-group">
    <label v-if="label" :for="id" class="label">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    <slot />
    <p v-if="error" class="text-red-500 text-sm mt-1">
      {{ error }}
    </p>
    <p v-else-if="hint" class="text-gray-500 text-sm mt-1">
      {{ hint }}
    </p>
  </div>
</template>

<script setup>
  defineProps({
    /**
     * Label text
     */
    label: {
      type: String,
      default: '',
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
     * Input ID (for label association)
     */
    id: {
      type: String,
      default: '',
    },
    /**
     * Required field indicator
     */
    required: {
      type: Boolean,
      default: false,
    },
  })
</script>

<style scoped>
  .form-group {
    margin-bottom: 1.25rem;
  }
</style>
