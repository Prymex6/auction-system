import { formatDistanceToNow, format, parseISO } from 'date-fns'
import { pl } from 'date-fns/locale'

export const useFormat = () => {
  const formatPrice = (price, currency = 'PLN') => {
    return new Intl.NumberFormat('pl-PL', {
      style: 'currency',
      currency,
    }).format(price)
  }

  const formatDate = (date, formatStr = 'dd.MM.yyyy') => {
    try {
      const parsed = typeof date === 'string' ? parseISO(date) : new Date(date)
      return format(parsed, formatStr, { locale: pl })
    } catch {
      return '-'
    }
  }

  const formatDateTime = (dateTime, formatStr = 'dd.MM.yyyy HH:mm') => {
    try {
      const parsed = typeof dateTime === 'string' ? parseISO(dateTime) : new Date(dateTime)
      return format(parsed, formatStr, { locale: pl })
    } catch {
      return '-'
    }
  }

  const formatTimeAgo = (date) => {
    try {
      const parsed = typeof date === 'string' ? parseISO(date) : new Date(date)
      return formatDistanceToNow(parsed, { addSuffix: true, locale: pl })
    } catch {
      return '-'
    }
  }

  const formatPhoneNumber = (phone) => {
    if (!phone) return ''
    const cleaned = phone.replace(/\D/g, '')
    if (cleaned.length === 9) {
      return `${cleaned.slice(0, 2)} ${cleaned.slice(2, 5)} ${cleaned.slice(5, 7)} ${cleaned.slice(7, 9)}`
    }
    return phone
  }

  const formatTimeLeft = (endsAt) => {
    const now = new Date()
    const end = typeof endsAt === 'string' ? parseISO(endsAt) : new Date(endsAt)
    const diff = end - now

    if (diff <= 0) return 'Zakończona'

    const days = Math.floor(diff / (1000 * 60 * 60 * 24))
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
    const seconds = Math.floor((diff % (1000 * 60)) / 1000)

    if (days > 0) return `${days}d ${hours}h`
    if (hours > 0) return `${hours}h ${minutes}m`
    if (minutes > 0) return `${minutes}m ${seconds}s`
    return `${seconds}s`
  }

  const isEnding = (endsAt, minutesThreshold = 60) => {
    const now = new Date()
    const end = typeof endsAt === 'string' ? parseISO(endsAt) : new Date(endsAt)
    const diff = end - now
    return diff < 1000 * 60 * minutesThreshold
  }

  const truncate = (text, length = 100) => {
    if (text.length <= length) return text
    return text.substring(0, length) + '...'
  }

  const capitalize = (text) => {
    if (!text) return ''
    return text.charAt(0).toUpperCase() + text.slice(1)
  }

  return {
    formatPrice,
    formatDate,
    formatDateTime,
    formatTimeAgo,
    formatPhoneNumber,
    formatTimeLeft,
    isEnding,
    truncate,
    capitalize,
  }
}
