import { ref, reactive, computed } from 'vue'

export function useForm(initialData = {}, submitHandler) {
  const form = reactive({ ...initialData })
  const errors = ref({})
  const isSubmitting = ref(false)
  const isDirty = ref(false)

  const resetForm = () => {
    Object.keys(form).forEach((key) => {
      form[key] = initialData[key]
    })
    errors.value = {}
    isDirty.value = false
  }

  const setFormData = (data) => {
    Object.keys(data).forEach((key) => {
      if (key in form) {
        form[key] = data[key]
      }
    })
  }

  const setErrors = (validationErrors) => {
    errors.value = validationErrors
  }

  const clearErrors = () => {
    errors.value = {}
  }

  const clearError = (field) => {
    delete errors.value[field]
  }

  const hasErrors = computed(() => Object.keys(errors.value).length > 0)

  const getError = (field) => {
    const error = errors.value[field]
    return Array.isArray(error) ? error[0] : error
  }

  const submit = async () => {
    if (!submitHandler) return

    isSubmitting.value = true
    clearErrors()

    try {
      await submitHandler(form)
      isDirty.value = false
      return true
    } catch (error) {
      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors)
      }
      throw error
    } finally {
      isSubmitting.value = false
    }
  }

  const markAsDirty = () => {
    isDirty.value = true
  }

  return {
    form,
    errors,
    isSubmitting,
    isDirty,
    hasErrors,
    resetForm,
    setFormData,
    setErrors,
    clearErrors,
    clearError,
    getError,
    submit,
    markAsDirty,
  }
}
