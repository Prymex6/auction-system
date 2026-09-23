<template>
  <input
    :type="type"
    :value="modelValue"
    :class="[
      'input',
      {
        'border-red-500 focus:ring-red-500': error,
      },
    ]"
    :disabled="disabled"
    :placeholder="placeholder"
    @input="$emit('update:modelValue', $event.target.value)"
    @blur="$emit('blur')"
    @focus="$emit('focus')"
  />
</template>

<script setup>
  defineProps({
    /**
     * Input type: 'text', 'email', 'password', 'number', 'tel', 'date', etc.
     */
    type: {
      type: String,
      default: 'text',
    },
    /**
     * v-model value
     */
    modelValue: {
      type: [String, Number],
      default: '',
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

  defineEmits(['update:modelValue', 'blur', 'focus'])
</script>
