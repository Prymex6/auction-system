<template>
  <div class="flex items-center gap-2">
    <input
      :id="id"
      type="checkbox"
      :checked="modelValue"
      :disabled="disabled"
      class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-2 focus:ring-primary-500 cursor-pointer"
      @change="$emit('update:modelValue', $event.target.checked)"
    />
    <label v-if="label" :for="id" class="text-sm font-medium cursor-pointer">
      {{ label }}
    </label>
  </div>
</template>

<script setup>
  defineProps({
    /**
     * v-model value (boolean)
     */
    modelValue: {
      type: Boolean,
      default: false,
    },
    /**
     * Label text
     */
    label: {
      type: String,
      default: '',
    },
    /**
     * Unique identifier
     */
    id: {
      type: String,
      default: null,
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
