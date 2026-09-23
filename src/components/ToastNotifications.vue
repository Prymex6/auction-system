<template>
  <Teleport to="body">
    <TransitionGroup name="toast" tag="div" class="fixed top-4 right-4 z-[9999] space-y-2">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        :class="[
          'max-w-md w-full bg-white rounded-xl shadow-2xl border-l-4 p-4 flex items-start gap-3 transform transition-all duration-300',
          toastStyles[toast.type].border,
        ]"
      >
        <!-- Icon -->
        <div
          :class="[
            'flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center',
            toastStyles[toast.type].bg,
          ]"
        >
          <font-awesome-icon
            icon="fa-solid fa-xmark"
            class="w-5 h-5"
            :class="toastStyles[toast.type].text"
          />
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-gray-900">
            {{ toastTitles[toast.type] }}
          </p>
          <p class="text-sm text-gray-600 mt-1">
            {{ toast.message }}
          </p>
        </div>

        <!-- Close button -->
        <button
          class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors"
          @click="removeToast(toast.id)"
        >
          <font-awesome-icon icon="fa-solid fa-xmark" class="w-5 h-5" />
        </button>
      </div>
    </TransitionGroup>
  </Teleport>
</template>

<script setup>
  import { useToast } from '@/composables/useToast'

  const { toasts, removeToast } = useToast()

  const toastStyles = {
    success: {
      border: 'border-green-500',
      bg: 'bg-green-50',
      text: 'text-green-600',
    },
    error: {
      border: 'border-red-500',
      bg: 'bg-red-50',
      text: 'text-red-600',
    },
    warning: {
      border: 'border-yellow-500',
      bg: 'bg-yellow-50',
      text: 'text-yellow-600',
    },
    info: {
      border: 'border-blue-500',
      bg: 'bg-blue-50',
      text: 'text-blue-600',
    },
  }

  const toastTitles = {
    success: 'Sukces!',
    error: 'Błąd',
    warning: 'Uwaga',
    info: 'Informacja',
  }
</script>

<style scoped>
  .toast-enter-active,
  .toast-leave-active {
    transition: all 0.3s ease;
  }

  .toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
  }

  .toast-leave-to {
    opacity: 0;
    transform: translateY(-20px);
  }
</style>
