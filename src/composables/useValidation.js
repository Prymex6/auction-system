import { ref, computed } from 'vue'

export const useValidation = () => {
  const errors = ref({})

  const isEmail = (email) => {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
    return re.test(email)
  }

  const isPhone = (phone) => {
    const re = /^(?=(?:.*\d){7,})\+?[\d\s()-]+$/
    return re.test(phone)
  }

  const isPostalCode = (code) => {
    const polish = /^\d{2}-\d{3}$/
    const generic = /^[A-Za-z0-9][A-Za-z0-9\s-]{2,9}$/
    return polish.test(code) || generic.test(code)
  }

  const isStrongPassword = (password) => {
    // Min 8 chars, 1 uppercase, 1 lowercase, 1 number
    const re = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/
    return re.test(password)
  }

  const validateEmail = (email) => {
    if (!email) return 'Email jest wymagany'
    if (!isEmail(email)) return 'Podaj poprawny email'
    return ''
  }

  const validatePassword = (password) => {
    if (!password) return 'Hasło jest wymagane'
    if (password.length < 8) return 'Hasło musi mieć minimum 8 znaków'
    return ''
  }

  const validatePhone = (phone) => {
    if (!phone) return 'Telefon jest wymagany'
    if (!isPhone(phone)) return 'Podaj poprawny numer telefonu'
    return ''
  }

  const validatePostalCode = (code) => {
    if (!code) return ''
    if (!isPostalCode(code)) return 'Kod pocztowy ma niepoprawny format'
    return ''
  }

  const validateField = (fieldName, value, rules) => {
    const errors = []

    if (rules.required && !value) {
      errors.push(`${fieldName} jest wymagane`)
    }

    if (rules.minLength && value?.length < rules.minLength) {
      errors.push(`${fieldName} musi mieć minimum ${rules.minLength} znaków`)
    }

    if (rules.maxLength && value?.length > rules.maxLength) {
      errors.push(`${fieldName} może mieć maksimum ${rules.maxLength} znaków`)
    }

    if (rules.pattern && !rules.pattern.test(value)) {
      errors.push(`${fieldName} ma nieproperny format`)
    }

    if (rules.custom && rules.custom(value)) {
      errors.push(rules.custom(value))
    }

    return errors[0] || ''
  }

  const setFieldError = (fieldName, error) => {
    errors.value[fieldName] = error
  }

  const clearFieldError = (fieldName) => {
    delete errors.value[fieldName]
  }

  const clearAllErrors = () => {
    errors.value = {}
  }

  const hasErrors = computed(() => Object.keys(errors.value).length > 0)

  const getFieldError = (fieldName) => errors.value[fieldName]

  return {
    errors,
    isEmail,
    isPhone,
    isPostalCode,
    isStrongPassword,
    validateEmail,
    validatePassword,
    validatePhone,
    validatePostalCode,
    validateField,
    setFieldError,
    clearFieldError,
    clearAllErrors,
    hasErrors,
    getFieldError,
  }
}
