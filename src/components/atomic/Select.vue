<template>
  <select
    :value="modelValue"
    :class="[
      'input',
      {
        'border-red-500 focus:ring-red-500': error,
      },
    ]"
    :disabled="disabled"
    @change="$emit('update:modelValue', $event.target.value)"
  >
    <option v-if="placeholder" value="">{{ placeholder }}</option>
    <option v-for="option in options" :key="option.value" :value="option.value">
      {{ option.label }}
    </option>
  </select>
</template>

<script setup>
  defineProps({
    /**
     * v-model value
     */
    modelValue: {
      type: [String, Number],
      default: '',
    },
    /**
     * Array of options: { value, label }
     */
    options: {
      type: Array,
      required: true,
    },
    /**
     * Placeholder text
     */
    placeholder: {
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
    /**
     * Error state
     */
    error: {
      type: Boolean,
      default: false,
    },
  })

  defineEmits(['update:modelValue'])
</script>
