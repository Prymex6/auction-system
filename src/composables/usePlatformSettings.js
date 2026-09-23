import { ref, computed } from 'vue'
import api from '@/services/api'

const platformSettings = ref({
  platform_name: 'Gołębiowy Lot',
  platform_email: 'kontakt@example.com',
  platform_phone: null,
  platform_description: 'Elitarna giełda i platforma aukcyjna dla hodowców gołębi pocztowych.',
  platform_logo_url: null,
})

let settingsLoaded = false

export function usePlatformSettings() {
  const getPlatformName = computed(() => platformSettings.value.platform_name || 'Gołębiowy Lot')
  const getPlatformEmail = computed(
    () => platformSettings.value.platform_email || 'kontakt@example.com'
  )
  const getPlatformPhone = computed(() => platformSettings.value.platform_phone)
  const getPlatformDescription = computed(
    () =>
      platformSettings.value.platform_description ||
      'Elitarna giełda i platforma aukcyjna dla hodowców gołębi pocztowych.'
  )
  const getPlatformLogo = computed(() => platformSettings.value.platform_logo_url)
  const onlyAdminCanList = computed(() => !!platformSettings.value.only_admin_can_list)
  const biddingEnabled = computed(() => platformSettings.value.bidding_enabled !== false)
  const getSeoTitle = computed(() => platformSettings.value.seo_title)
  const getSeoDescription = computed(() => platformSettings.value.seo_description)
  const getSeoKeywords = computed(() => platformSettings.value.seo_keywords)

  const loadSettings = async () => {
    if (settingsLoaded) return

    try {
      const response = await api.get('/settings')
      if (response.data.data) {
        platformSettings.value = response.data.data
        settingsLoaded = true
      }
    } catch (error) {
      console.warn('Could not load platform settings:', error)
      settingsLoaded = true
    }
  }

  const getSettings = () => platformSettings.value

  return {
    onlyAdminCanList,
    biddingEnabled,
    getPlatformName,
    getPlatformEmail,
    getPlatformPhone,
    getPlatformDescription,
    getPlatformLogo,
    getSeoTitle,
    getSeoDescription,
    getSeoKeywords,
    loadSettings,
    getSettings,
    platformSettings,
  }
}
