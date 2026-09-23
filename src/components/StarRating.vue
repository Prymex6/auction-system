<template>
  <div class="inline-flex items-center gap-0.5" @mouseleave="hoverValue = null">
    <span
      v-for="star in 5"
      :key="star"
      class="relative inline-block"
      :class="readonly ? '' : 'cursor-pointer'"
      :style="{ width: sizePx, height: sizePx }"
    >
      <!-- Tlo: pusta gwiazdka -->
      <font-awesome-icon
        :icon="['far', 'star']"
        class="absolute inset-0 text-gray-300"
        :style="{ width: sizePx, height: sizePx }"
      />

      <!-- Wypelnienie (pelna lub polowka) przycinane do procentu -->
      <span class="absolute inset-0 overflow-hidden" :style="{ width: fillWidth(star) }">
        <font-awesome-icon
          :icon="['fas', 'star']"
          class="text-yellow-400"
          :style="{ width: sizePx, height: sizePx }"
        />
      </span>

      <!-- Kliknieciowe/hover polowki (tylko w trybie edytowalnym) -->
      <template v-if="!readonly">
        <button
          type="button"
          class="absolute inset-y-0 left-0 w-1/2"
          :aria-label="`Oceń na ${star - 0.5}`"
          @mousemove="hoverValue = star - 0.5"
          @click="select(star - 0.5)"
        ></button>
        <button
          type="button"
          class="absolute inset-y-0 right-0 w-1/2"
          :aria-label="`Oceń na ${star}`"
          @mousemove="hoverValue = star"
          @click="select(star)"
        ></button>
      </template>
    </span>
  </div>
</template>

<script setup>
  import { ref, computed } from 'vue'

  const props = defineProps({
    modelValue: {
      type: Number,
      default: 0,
    },
    readonly: {
      type: Boolean,
      default: false,
    },
    size: {
      type: Number,
      default: 24,
    },
  })

  const emit = defineEmits(['update:modelValue'])

  const hoverValue = ref(null)
  const sizePx = computed(() => `${props.size}px`)
  const displayValue = computed(() => hoverValue.value ?? props.modelValue ?? 0)

  const fillWidth = (star) => {
    const remainder = displayValue.value - (star - 1)
    const ratio = Math.max(0, Math.min(1, remainder))
    return `${ratio * 100}%`
  }

  const select = (value) => {
    emit('update:modelValue', value)
  }
</script>
