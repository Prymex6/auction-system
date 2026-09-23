/**
 * Utility functions for safe localStorage operations
 */

/**
 * Safely get item from localStorage
 * @param {string} key
 * @param {*} defaultValue
 * @returns {*}
 */
export function getItem(key, defaultValue = null) {
  try {
    const item = localStorage.getItem(key)
    return item ? JSON.parse(item) : defaultValue
  } catch (e) {
    console.warn(`Failed to get localStorage item "${key}":`, e)
    return defaultValue
  }
}

/**
 * Safely set item in localStorage with quota handling
 * @param {string} key
 * @param {*} value
 * @returns {boolean} Success status
 */
export function setItem(key, value) {
  try {
    const serialized = JSON.stringify(value)
    localStorage.setItem(key, serialized)
    return true
  } catch (e) {
    console.error(`Failed to set localStorage item "${key}":`, e)

    if (e.name === 'QuotaExceededError') {
      console.warn('localStorage quota exceeded, attempting cleanup...')
      cleanupOldData()

      // Try one more time after cleanup
      try {
        const serialized = JSON.stringify(value)
        localStorage.setItem(key, serialized)
        return true
      } catch (retryError) {
        console.error('Still failed after cleanup:', retryError)
        return false
      }
    }
    return false
  }
}

/**
 * Remove item from localStorage
 * @param {string} key
 */
export function removeItem(key) {
  try {
    localStorage.removeItem(key)
  } catch (e) {
    console.warn(`Failed to remove localStorage item "${key}":`, e)
  }
}

/**
 * Get localStorage usage in bytes
 * @returns {number}
 */
export function getUsage() {
  let total = 0
  for (let key in localStorage) {
    if (Object.prototype.hasOwnProperty.call(localStorage, key)) {
      total += localStorage[key].length + key.length
    }
  }
  return total
}

/**
 * Get localStorage usage as formatted string
 * @returns {string}
 */
export function getUsageFormatted() {
  const bytes = getUsage()
  if (bytes < 1024) return `${bytes} B`
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(2)} KB`
  return `${(bytes / (1024 * 1024)).toFixed(2)} MB`
}

/**
 * Clean up old or unnecessary data from localStorage
 */
export function cleanupOldData() {
  const keysToCheck = []

  // Find all keys
  for (let i = 0; i < localStorage.length; i++) {
    const key = localStorage.key(i)
    if (key) {
      keysToCheck.push(key)
    }
  }

  // Sort by size (descending) and remove largest non-essential items
  const itemSizes = keysToCheck.map((key) => ({
    key,
    size: (localStorage.getItem(key) || '').length,
    essential: ['auth_token', 'user'].includes(key), // Essential keys to keep
  }))

  itemSizes.sort((a, b) => b.size - a.size)

  // Remove non-essential items starting with largest
  let removed = 0
  for (const item of itemSizes) {
    if (!item.essential && removed < 3) {
      // Remove up to 3 largest non-essential items
      if (import.meta.env?.DEV) {
        console.log(`Removing localStorage item "${item.key}" (${item.size} bytes)`)
      }
      localStorage.removeItem(item.key)
      removed++
    }
  }

  if (import.meta.env?.DEV) {
    console.log(`Cleaned up ${removed} items from localStorage`)
  }
}

/**
 * Clear all localStorage except essential data
 */
export function clearNonEssential() {
  const essential = {
    auth_token: localStorage.getItem('auth_token'),
    user: localStorage.getItem('user'),
  }

  localStorage.clear()

  // Restore essential data
  if (essential.auth_token) localStorage.setItem('auth_token', essential.auth_token)
  if (essential.user) localStorage.setItem('user', essential.user)

  if (import.meta.env?.DEV) {
    console.log('Cleared non-essential localStorage data')
  }
}

export default {
  getItem,
  setItem,
  removeItem,
  getUsage,
  getUsageFormatted,
  cleanupOldData,
  clearNonEssential,
}
