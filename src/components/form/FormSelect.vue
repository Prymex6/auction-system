<template>
  <FormGroup :id="id" :label="label" :error="error" :hint="hint" :required="required">
    <Select
      :id="id"
      :model-value="modelValue"
      :options="options"
      :placeholder="placeholder"
      :disabled="disabled"
      :error="!!error"
      @update:model-value="$emit('update:modelValue', $event)"
    />
  </FormGroup>
</template>

<script setup>
  import FormGroup from './FormGroup.vue'
  import Select from '@/components/atomic/Select.vue'

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
      type: [String, Number],
      default: '',
    },
    /**
     * Array of options
     */
    options: {
      type: Array,
      required: true,
    },
    /**
     * Placeholder
     */
    placeholder: {
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
     * Input ID
     */
    id: {
      type: String,
      default: '',
    },
    /**
     * Required field
     */
    required: {
      type: Boolean,
      default: false,
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
