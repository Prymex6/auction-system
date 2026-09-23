import { watchEffect } from 'vue'
import { usePlatformSettings } from './usePlatformSettings'
import { setBasePageTitle } from './usePageTitle'

export function useSeo({ title, description, keywords, image, url, type = 'website' } = {}) {
  const {
    getPlatformName,
    getPlatformDescription,
    getSeoTitle,
    getSeoDescription,
    getSeoKeywords,
    loadSettings,
  } = usePlatformSettings()

  loadSettings()

  watchEffect(() => {
    const siteTitle = getPlatformName.value || 'Gołębiowy Lot'
    const siteDescription =
      description ||
      getSeoDescription.value ||
      getPlatformDescription.value ||
      'Elitarna giełda i platforma aukcyjna dla hodowców gołębi pocztowych. Kupuj, sprzedawaj i odkrywaj najlepsze gołębie na rynku.'
    const siteUrl = 'https://golebiowylot.pl'

    const fullTitle = title ? `${title} - ${siteTitle}` : getSeoTitle.value || siteTitle
    const fullUrl = url ? `${siteUrl}${url}` : siteUrl
    const fullDescription = description || siteDescription
    const fullImage = image || `${siteUrl}/images/logo-golab.png`
    const fullKeywords =
      keywords ||
      getSeoKeywords.value ||
      'aukcje gołębi, giełda gołębi, gołębie pocztowe, hodowla, rynek, licytacja, sprzedaż'

    // Set document title
    if (typeof document !== 'undefined') {
      setBasePageTitle(fullTitle)

      // Update or create meta tags
      const updateMetaTag = (name, content, isProperty = false) => {
        let tag = document.querySelector(
          isProperty ? `meta[property="${name}"]` : `meta[name="${name}"]`
        )
        if (!tag) {
          tag = document.createElement('meta')
          isProperty ? tag.setAttribute('property', name) : tag.setAttribute('name', name)
          document.head.appendChild(tag)
        }
        tag.setAttribute('content', content)
      }

      // Meta tags
      updateMetaTag('description', fullDescription)
      updateMetaTag('keywords', fullKeywords)
      updateMetaTag(
        'robots',
        'index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1'
      )
      updateMetaTag('language', 'pl')
      updateMetaTag('viewport', 'width=device-width, initial-scale=1.0')

      // OG Tags
      updateMetaTag('og:title', fullTitle, true)
      updateMetaTag('og:description', fullDescription, true)
      updateMetaTag('og:image', fullImage, true)
      updateMetaTag('og:url', fullUrl, true)
      updateMetaTag('og:type', type, true)
      updateMetaTag('og:site_name', siteTitle, true)

      // Twitter Tags
      updateMetaTag('twitter:card', 'summary_large_image')
      updateMetaTag('twitter:title', fullTitle)
      updateMetaTag('twitter:description', fullDescription)
      updateMetaTag('twitter:image', fullImage)

      // Update or create canonical link
      let canonical = document.querySelector('link[rel="canonical"]')
      if (!canonical) {
        canonical = document.createElement('link')
        canonical.setAttribute('rel', 'canonical')
        document.head.appendChild(canonical)
      }
      canonical.setAttribute('href', fullUrl)
    }
  })
}
