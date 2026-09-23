<template>
  <fieldset class="space-y-3">
    <legend v-if="legend" class="text-sm font-medium text-gray-900 mb-2">
      {{ legend }}
    </legend>
    <div v-for="option in options" :key="option.value" class="flex items-center gap-2">
      <input
        :id="`${name}-${option.value}`"
        type="radio"
        :name="name"
        :value="option.value"
        :checked="modelValue === option.value"
        :disabled="disabled"
        class="w-4 h-4 border-gray-300 text-primary-600 focus:ring-2 focus:ring-primary-500 cursor-pointer"
        @change="$emit('update:modelValue', option.value)"
      />
      <label :for="`${name}-${option.value}`" class="text-sm font-medium cursor-pointer">
        {{ option.label }}
      </label>
    </div>
  </fieldset>
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
     * Radio group name
     */
    name: {
      type: String,
      required: true,
    },
    /**
     * Legend text
     */
    legend: {
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
