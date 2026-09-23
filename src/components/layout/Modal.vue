<template>
  <Teleport to="body">
    <Transition name="modal">
      <div v-if="open" class="fixed inset-0 z-50 flex items-center justify-center">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black bg-opacity-50" @click="closeModal"></div>

        <!-- Modal Content -->
        <div
          class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-auto"
          @click.stop
        >
          <!-- Header -->
          <div v-if="title" class="flex items-center justify-between p-6 border-b border-gray-200">
            <h2 class="text-xl font-bold">{{ title }}</h2>
            <button class="text-gray-500 hover:text-gray-700 transition-colors" @click="closeModal">
              <font-awesome-icon icon="fa-solid fa-xmark" class="w-4 h-4" />
            </button>
          </div>

          <!-- Body -->
          <div class="p-6">
            <slot />
          </div>

          <!-- Footer -->
          <div
            v-if="$slots.footer"
            class="px-6 py-4 border-t border-gray-200 flex gap-2 justify-end"
          >
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
  import { watch, onMounted, onUnmounted } from 'vue'

  const props = defineProps({
    /**
     * Modal open state
     */
    open: {
      type: Boolean,
      default: false,
    },
    /**
     * Modal title
     */
    title: {
      type: String,
      default: '',
    },
    /**
     * Close modal on backdrop click
     */
    closeOnBackdrop: {
      type: Boolean,
      default: true,
    },
  })

  const emit = defineEmits(['update:open'])

  const closeModal = () => {
    emit('update:open', false)
  }

  // Handle ESC key
  const handleEsc = (e) => {
    if (e.key === 'Escape' && props.open) {
      closeModal()
    }
  }

  onMounted(() => {
    document.addEventListener('keydown', handleEsc)
  })

  onUnmounted(() => {
    document.removeEventListener('keydown', handleEsc)
  })

  // Prevent body scroll when modal is open
  watch(
    () => props.open,
    (isOpen) => {
      if (isOpen) {
        document.body.style.overflow = 'hidden'
      } else {
        document.body.style.overflow = 'auto'
      }
    }
  )
</script>

<style scoped>
  .modal-enter-active,
  .modal-leave-active {
    transition: opacity 0.3s ease;
  }

  .modal-enter-from,
  .modal-leave-to {
    opacity: 0;
  }

  .modal-enter-active > div:nth-child(2),
  .modal-leave-active > div:nth-child(2) {
    transition: transform 0.3s ease;
  }

  .modal-enter-from > div:nth-child(2),
  .modal-leave-to > div:nth-child(2) {
    transform: scale(0.95);
  }
</style>
