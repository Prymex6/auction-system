<template>
  <Transition name="alert">
    <div
      v-if="visible"
      :class="['p-4 rounded-lg border flex items-start gap-3', variantClasses]"
      role="alert"
    >
      <!-- Icon -->
      <div class="flex-shrink-0 mt-0.5">
        <font-awesome-icon v-if="variant === 'success'" icon="fa-solid fa-check" class="w-5 h-5" />
        <font-awesome-icon
          v-else-if="variant === 'danger'"
          icon="fa-solid fa-xmark"
          class="w-5 h-5"
        />
        <font-awesome-icon
          v-else-if="variant === 'warning'"
          icon="fa-solid fa-circle-exclamation"
          class="w-5 h-5"
        />
        <font-awesome-icon v-else icon="fa-solid fa-circle-info" class="w-5 h-5" />
      </div>

      <!-- Content -->
      <div class="flex-grow">
        <p v-if="title" class="font-semibold">{{ title }}</p>
        <p>{{ message }}</p>
      </div>

      <!-- Close button -->
      <button
        v-if="closable"
        class="flex-shrink-0 text-current opacity-70 hover:opacity-100 transition-opacity"
        @click="visible = false"
      >
        <font-awesome-icon icon="fa-solid fa-xmark" class="w-4 h-4" />
      </button>
    </div>
  </Transition>
</template>

<script setup>
  import { ref, computed, watch } from 'vue'

  const props = defineProps({
    /**
     * Alert variant: 'success', 'danger', 'warning', 'info'
     */
    variant: {
      type: String,
      default: 'info',
      validator: (v) => ['success', 'danger', 'warning', 'info'].includes(v),
    },
    /**
     * Alert title
     */
    title: {
      type: String,
      default: '',
    },
    /**
     * Alert message
     */
    message: {
      type: String,
      required: true,
    },
    /**
     * Show alert
     */
    show: {
      type: Boolean,
      default: true,
    },
    /**
     * Can be closed
     */
    closable: {
      type: Boolean,
      default: true,
    },
    /**
     * Auto-dismiss after milliseconds (0 = never)
     */
    autoDismiss: {
      type: Number,
      default: 0,
    },
  })

  const visible = ref(props.show)

  watch(
    () => props.show,
    (newVal) => {
      visible.value = newVal
    }
  )

  watch(visible, (newVal) => {
    if (newVal && props.autoDismiss > 0) {
      setTimeout(() => {
        visible.value = false
      }, props.autoDismiss)
    }
  })

  const variantClasses = computed(() => {
    const variants = {
      success: 'bg-green-50 border-green-200 text-green-800',
      danger: 'bg-red-50 border-red-200 text-red-800',
      warning: 'bg-yellow-50 border-yellow-200 text-yellow-800',
      info: 'bg-blue-50 border-blue-200 text-blue-800',
    }
    return variants[props.variant]
  })
</script>

<style scoped>
  .alert-enter-active,
  .alert-leave-active {
    transition: all 0.3s ease;
  }

  .alert-enter-from,
  .alert-leave-to {
    opacity: 0;
    transform: translateY(-10px);
  }
</style>
