import { ref, computed } from 'vue'

export function useModal(initialState = false) {
  const isOpen = ref(initialState)
  const mode = ref('view') // view, add, edit

  const open = (modalMode = 'view') => {
    mode.value = modalMode
    isOpen.value = true
  }

  const close = () => {
    isOpen.value = false
  }

  const toggle = () => {
    isOpen.value = !isOpen.value
  }

  const openAdd = () => open('add')
  const openEdit = () => open('edit')
  const openView = () => open('view')

  const isAddMode = computed(() => mode.value === 'add')
  const isEditMode = computed(() => mode.value === 'edit')
  const isViewMode = computed(() => mode.value === 'view')

  return {
    isOpen,
    mode,
    open,
    close,
    toggle,
    openAdd,
    openEdit,
    openView,
    isAddMode,
    isEditMode,
    isViewMode,
  }
}
