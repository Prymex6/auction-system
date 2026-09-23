<template>
  <FormGroup :id="id" :label="label" :error="error" :hint="hint" :required="required">
    <Input
      :id="id"
      :type="type"
      :model-value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :error="!!error"
      @update:model-value="$emit('update:modelValue', $event)"
      @blur="$emit('blur')"
      @focus="$emit('focus')"
    />
  </FormGroup>
</template>

<script setup>
  import FormGroup from './FormGroup.vue'
  import Input from '@/components/atomic/Input.vue'

  defineProps({
    /**
     * Label text
     */
    label: {
      type: String,
      default: '',
    },
    /**
     * Input type
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

  defineEmits(['update:modelValue', 'blur', 'focus'])
</script>
