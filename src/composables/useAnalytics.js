import { usePlatformSettings } from '@/composables/usePlatformSettings'

/**
 * Google Analytics 4 z Consent Mode v2 (RODO).
 */

let gtagLoaded = false

// obiektu `arguments`, a NIE tablicy z rest-parametru (...args). gtag.js
// aktywowana mimo poprawnego UI i poprawnego ID pomiaru.
function gtag() {
  window.dataLayer = window.dataLayer || []
  window.dataLayer.push(arguments)
}

export function useAnalytics() {
  const { platformSettings } = usePlatformSettings()

  const analyticsId = () => platformSettings.value.google_analytics_id || null

  const loadGtag = (id) => {
    if (gtagLoaded || !id) return
    gtagLoaded = true

    gtag('consent', 'default', {
      ad_storage: 'denied',
      ad_user_data: 'denied',
      ad_personalization: 'denied',
      analytics_storage: 'denied',
    })
    gtag('js', new Date())
    gtag('config', id, { anonymize_ip: true })

    const script = document.createElement('script')
    script.async = true
    script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(id)}`
    document.head.appendChild(script)
  }

  const grantAnalytics = () => {
    const id = analyticsId()
    if (!id) return
    loadGtag(id)
    gtag('consent', 'update', { analytics_storage: 'granted' })
  }

  const denyAnalytics = () => {
    if (gtagLoaded) {
      gtag('consent', 'update', { analytics_storage: 'denied' })
    }
    for (const cookie of document.cookie.split(';')) {
      const name = cookie.split('=')[0].trim()
      if (/^(_ga|_gid|_gat)/.test(name)) {
        document.cookie = `${name}=; Max-Age=0; path=/; domain=.${location.hostname}`
        document.cookie = `${name}=; Max-Age=0; path=/`
      }
    }
  }

  return { analyticsId, grantAnalytics, denyAnalytics }
}
