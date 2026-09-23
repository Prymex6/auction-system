/**
 * useFormatters - Centralized utility functions for formatting data
 * Eliminates code duplication across components
 */

export function useFormatters() {
  /**
   * Format price with PLN currency
   */
  const formatPrice = (price) => {
    if (!price && price !== 0) return '0 zł'
    return `${Number(price).toLocaleString('pl-PL', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0,
    })} zł`
  }

  /**
   * Format gender - converts database values to display format
   */
  const formatGender = (gender) => {
    const genderMap = {
      male: 'Samiec',
      samiec: 'Samiec',
      female: 'Samica',
      samica: 'Samica',
      golab_mlody: 'Gołąb młody',
    }
    return genderMap[gender] || gender.charAt(0).toUpperCase() + gender.slice(1)
  }

  /**
   * Format countdown timer for auction
   */
  const formatCountdown = (endsAt) => {
    if (!endsAt) return 'Brak danych'
    const now = new Date()
    const end = new Date(endsAt)

    if (end < now) {
      return 'Zakończona'
    }

    const diff = Math.floor((end - now) / 1000)
    const days = Math.floor(diff / 86400)
    const hours = Math.floor((diff % 86400) / 3600)
    const minutes = Math.floor((diff % 3600) / 60)
    const seconds = diff % 60

    if (days > 0) {
      return `${days}d ${hours}h`
    } else if (hours > 0) {
      return `${hours}h ${minutes}m`
    } else if (minutes > 0) {
      return `${minutes}m ${seconds}s`
    } else {
      return `${seconds}s`
    }
  }

  /**
   * Format date to Polish locale
   */
  const formatDate = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('pl-PL', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    })
  }

  /**
   * Format date and time to Polish locale
   */
  const formatDateTime = (dateString) => {
    if (!dateString) return '-'
    const date = new Date(dateString)
    return date.toLocaleDateString('pl-PL', {
      year: 'numeric',
      month: '2-digit',
      day: '2-digit',
      hour: '2-digit',
      minute: '2-digit',
    })
  }

  /**
   * Format status badge label
   */
  const formatStatus = (status) => {
    const statusMap = {
      active: 'Aktywna',
      pending: 'Oczekująca',
      ended: 'Zakończona',
      rejected: 'Odrzucona',
      approved: 'Zatwierdzona',
      cancelled: 'Anulowana',
    }
    return statusMap[status] || status
  }

  /**
   * Format time remaining for progress bar percentage
   */
  const getTimeProgressPercent = (auction) => {
    if (!auction.created_at || !auction.ends_at) return 0

    const start = new Date(auction.created_at).getTime()
    const end = new Date(auction.ends_at).getTime()
    const now = new Date().getTime()

    const total = end - start
    const elapsed = now - start

    if (elapsed < 0) return 0
    if (elapsed > total) return 100

    return Math.round((elapsed / total) * 100)
  }

  /**
   * Format time left until auction ends
   */
  const getTimeLeft = (endsAt) => {
    if (!endsAt) return 'Brak danych'
    const now = new Date()
    const end = new Date(endsAt)

    if (end < now) {
      return 'Zakończona'
    }

    const diff = Math.floor((end - now) / 1000)
    const days = Math.floor(diff / 86400)
    const hours = Math.floor((diff % 86400) / 3600)
    const minutes = Math.floor((diff % 3600) / 60)
    const seconds = diff % 60

    if (days > 0) {
      return `${days}d ${hours}h`
    } else if (hours > 0) {
      return `${hours}h ${minutes}m`
    } else if (minutes > 0) {
      return `${minutes}m ${seconds}s`
    } else {
      return `${seconds}s`
    }
  }

  return {
    formatPrice,
    formatGender,
    formatCountdown,
    formatDate,
    formatDateTime,
    formatStatus,
    getTimeProgressPercent,
    getTimeLeft,
  }
}
