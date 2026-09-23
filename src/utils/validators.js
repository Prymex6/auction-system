export function isValidEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return re.test(email)
}

export function isValidPhoneNumber(phone) {
  const re = /^(\+48)?[\s-]?\d{3}[\s-]?\d{3}[\s-]?\d{3}$/
  return re.test(phone)
}

export function isValidUsername(username) {
  const re = /^[a-zA-Z0-9_-]+$/
  return re.test(username)
}

export function checkPasswordStrength(password) {
  let score = 0

  if (!password) return { strength: 'weak', score: 0 }

  if (password.length >= 8) score += 25
  if (password.length >= 12) score += 15

  if (/[a-z]/.test(password)) score += 15

  // Wielkie litery
  if (/[A-Z]/.test(password)) score += 15

  // Cyfry
  if (/\d/.test(password)) score += 15

  // Znaki specjalne
  if (/[^a-zA-Z0-9]/.test(password)) score += 15

  let strength = 'weak'
  if (score >= 70) strength = 'strong'
  else if (score >= 40) strength = 'medium'

  return { strength, score }
}

export function isEmpty(value) {
  if (value === null || value === undefined) return true
  if (typeof value === 'string') return value.trim() === ''
  if (Array.isArray(value)) return value.length === 0
  if (typeof value === 'object') return Object.keys(value).length === 0
  return false
}

export function isNumeric(value) {
  return !isNaN(parseFloat(value)) && isFinite(value)
}

export function isValidPostalCode(code) {
  const re = /^\d{2}-\d{3}$/
  return re.test(code)
}

export function isValidUrl(url) {
  try {
    new URL(url)
    return true
  } catch {
    return false
  }
}
