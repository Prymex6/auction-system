import { describe, it, expect, vi, beforeEach } from 'vitest'

const getMock = vi.fn()

vi.mock('@/services/api', () => ({
  default: {
    get: (...args) => getMock(...args),
  },
}))

import { useSeo } from '@/composables/useSeo'
import { usePlatformSettings } from '@/composables/usePlatformSettings'
import { basePageTitle } from '@/composables/usePageTitle'

describe('useSeo — ustawienia SEO z panelu admina', () => {
  beforeEach(() => {
    document.head.innerHTML = ''
    getMock.mockReset()
  })

  it('uzywa seo_title/seo_description/seo_keywords z panelu admina gdy strona nie poda wlasnych, a pole keywords per-strona nadpisuje globalne (regresja: parametr keywords byl calkowicie ignorowany)', async () => {
    getMock.mockResolvedValue({
      data: {
        data: {
          platform_name: 'Gołębiowy Lot',
          seo_title: 'Niestandardowy tytul SEO',
          seo_description: 'Niestandardowy opis SEO',
          seo_keywords: 'globalne, frazy, kluczowe',
        },
      },
    })

    await usePlatformSettings().loadSettings()

    useSeo({})

    expect(basePageTitle.value).toBe('Niestandardowy tytul SEO')
    expect(document.querySelector('meta[name="description"]').getAttribute('content')).toBe(
      'Niestandardowy opis SEO'
    )
    expect(document.querySelector('meta[name="keywords"]').getAttribute('content')).toBe(
      'globalne, frazy, kluczowe'
    )

    useSeo({ keywords: 'aukcja golebi, sprzedaz golebi' })
    expect(document.querySelector('meta[name="keywords"]').getAttribute('content')).toBe(
      'aukcja golebi, sprzedaz golebi'
    )
  })
})
