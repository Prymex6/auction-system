<template>
  <div
    v-if="show"
    class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
    @click.self="$emit('cancel')"
  >
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8" @click.stop>
      <div class="flex items-start gap-4 mb-6">
        <div
          class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
          :class="danger ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600'"
        >
          <font-awesome-icon icon="fa-solid fa-triangle-exclamation" class="w-6 h-6" />
        </div>
        <div>
          <h3 class="text-xl font-bold text-gray-900 mb-1">{{ title }}</h3>
          <p class="text-gray-600 text-sm leading-relaxed">{{ message }}</p>
        </div>
      </div>
      <div class="flex gap-3">
        <button
          class="flex-1 px-6 py-3 text-white rounded-xl font-semibold shadow hover:shadow-lg transition-all duration-300"
          :class="danger ? 'bg-red-600 hover:bg-red-700' : 'bg-blue-600 hover:bg-blue-700'"
          @click="$emit('confirm')"
        >
          {{ confirmText }}
        </button>
        <button
          class="flex-1 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50 transition-all duration-300"
          @click="$emit('cancel')"
        >
          Anuluj
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
  defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, required: true },
    message: { type: String, required: true },
    confirmText: { type: String, default: 'Potwierdź' },
    danger: { type: Boolean, default: false },
  })

  defineEmits(['confirm', 'cancel'])
</script>
