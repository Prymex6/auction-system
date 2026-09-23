/**
 * @param {string|Date} dateString - Data do sformatowania
 * @returns {string} - Format: YYYY-MM-DDTHH:mm
 */
export function formatDateForInput(dateString) {
  if (!dateString) return ''

  try {
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return ''

    const year = date.getFullYear()
    const month = String(date.getMonth() + 1).padStart(2, '0')
    const day = String(date.getDate()).padStart(2, '0')
    const hours = String(date.getHours()).padStart(2, '0')
    const minutes = String(date.getMinutes()).padStart(2, '0')

    return `${year}-${month}-${day}T${hours}:${minutes}`
  } catch (e) {
    return ''
  }
}

/**
 * @param {string|Date} dateString
 * @returns {string} - Format: DD.MM.YYYY HH:mm
 */
export function formatDatePL(dateString) {
  if (!dateString) return '—'

  try {
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return '—'

    return new Intl.DateTimeFormat('pl-PL', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    }).format(date)
  } catch (e) {
    return '—'
  }
}

/**
 * @param {string|Date} dateString
 * @returns {string} - Format: DD.MM.YYYY
 */
export function formatDateShort(dateString) {
  if (!dateString) return '—'

  try {
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return '—'

    return new Intl.DateTimeFormat('pl-PL', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
    }).format(date)
  } catch (e) {
    return '—'
  }
}

/**
 * @param {string|Date} dateString
 */
export function timeAgo(dateString) {
  if (!dateString) return '—'

  try {
    const date = new Date(dateString)
    const now = new Date()
    const diffMs = now - date
    const diffSec = Math.floor(diffMs / 1000)
    const diffMin = Math.floor(diffSec / 60)
    const diffHour = Math.floor(diffMin / 60)
    const diffDay = Math.floor(diffHour / 24)

    if (diffDay > 0) return `${diffDay} ${diffDay === 1 ? 'dzień' : 'dni'} temu`
    if (diffHour > 0) return `${diffHour} ${diffHour === 1 ? 'godzinę' : 'godzin'} temu`
    if (diffMin > 0) return `${diffMin} ${diffMin === 1 ? 'minutę' : 'minut'} temu`
    return 'przed chwilą'
  } catch (e) {
    return '—'
  }
}

/**
 * @param {string|Date} dateString
 * @returns {boolean}
 */
export function isPast(dateString) {
  if (!dateString) return false
  return new Date(dateString) < new Date()
}

/**
 * @param {string|Date} dateString
 * @returns {boolean}
 */
export function isFuture(dateString) {
  if (!dateString) return false
  return new Date(dateString) > new Date()
}
