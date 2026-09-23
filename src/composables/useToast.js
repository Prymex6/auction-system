import { ref } from 'vue'

const toasts = ref([])
let toastId = 0

export function useToast() {
  const showToast = (message, type = 'success', duration = 5000) => {
    const id = ++toastId
    toasts.value.push({
      id,
      message,
      type, // success, error, warning, info
      duration,
    })

    if (duration > 0) {
      setTimeout(() => {
        removeToast(id)
      }, duration)
    }

    return id
  }

  const removeToast = (id) => {
    const index = toasts.value.findIndex((toast) => toast.id === id)
    if (index > -1) {
      toasts.value.splice(index, 1)
    }
  }

  const showSuccess = (message, duration) => showToast(message, 'success', duration)
  const showError = (message, duration) => showToast(message, 'error', duration)
  const showWarning = (message, duration) => showToast(message, 'warning', duration)
  const showInfo = (message, duration) => showToast(message, 'info', duration)

  const clearAll = () => {
    toasts.value = []
  }

  return {
    toasts,
    showToast,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    removeToast,
    clearAll,
  }
}
