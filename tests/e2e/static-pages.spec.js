import { test, expect } from '@playwright/test'
import { acceptCookies, getAdminToken, withPlatformSetting } from './helpers.js'

const STATIC_PAGES = [
  '/terms',
  '/privacy',
  '/cookies',
  '/help',
  '/how-it-works',
  '/contact',
  '/faq',
  '/security',
]

test.describe('Strony statyczne, blog i baner cookies', () => {
  for (const path of STATIC_PAGES) {
    test(`strona ${path} ładuje się bez błędów konsoli`, async ({ page }) => {
      const consoleErrors = []
      page.on('console', (msg) => {
        if (msg.type() === 'error') consoleErrors.push(msg.text())
      })
      page.on('pageerror', (err) => consoleErrors.push(String(err)))

      const response = await page.goto(path)
      expect(response.ok()).toBeTruthy()
      await page.evaluate(() => document.fonts?.ready)

      expect(consoleErrors, `błędy konsoli na ${path}:\n${consoleErrors.join('\n')}`).toHaveLength(
        0
      )
    })
  }

  test('lista bloga ładuje się i pozwala wejść w pierwszy artykuł', async ({ page }) => {
    await page.goto('/blog')
    await acceptCookies(page)
    await page.reload()

    const res = await page.request.get('/api/blogs')
    const body = await res.json()
    const posts = body.data?.data || body.data || []

    if (posts.length === 0) {
      test.skip(
        true,
        'Brak artykułów na blogu w lokalnej bazie - nic do sprawdzenia (patrz TESTING-REPORT).'
      )
      return
    }

    const article = page.locator('article').filter({ hasText: posts[0].title })
    await expect(article.first()).toBeVisible({ timeout: 10000 })
    await article.first().getByRole('link', { name: 'Czytaj więcej' }).click()

    await expect(page).toHaveURL(new RegExp(`/blog/${posts[0].slug}`))
    await expect(page.getByRole('heading', { name: posts[0].title })).toBeVisible()
  })

  test('nieistniejąca trasa pokazuje stronę 404', async ({ page }) => {
    const response = await page.goto('/to-na-pewno-nie-istnieje-e2e-404')
    expect(response.ok()).toBeTruthy()
    await expect(page.getByText(/nie znaleziono|404/i).first()).toBeVisible({ timeout: 10000 })
  })

  test('baner cookies pokazuje się przy pierwszej wizycie i znika po akceptacji, trwale', async ({
    page,
  }) => {
    await page.goto('/')
    await page.evaluate(() => localStorage.removeItem('pigeon_cookie_consent_v1'))
    await page.reload()

    await expect(page.getByText('Szanujemy Twoją prywatność')).toBeVisible({ timeout: 10000 })
    await page.getByRole('button', { name: 'Akceptuję wszystkie' }).click()
    await expect(page.getByText('Szanujemy Twoją prywatność')).not.toBeVisible()

    await page.reload()
    await expect(page.getByText('Szanujemy Twoją prywatność')).not.toBeVisible()
  })

  test('ustawienia SEO z panelu admina (seo_title/seo_description) faktycznie widac na stronie glownej', async ({
    page,
  }) => {
    const adminToken = await getAdminToken(page)
    const uniqueTitle = `E2E SEO Tytul ${Date.now()}`
    const uniqueDescription = `E2E SEO opis testowy ${Date.now()}`
    const restoreTitle = await withPlatformSetting(page, adminToken, 'seo_title', uniqueTitle)
    const restoreDescription = await withPlatformSetting(
      page,
      adminToken,
      'seo_description',
      uniqueDescription
    )

    try {
      await page.goto('/')
      await acceptCookies(page)
      await page.reload()

      await expect(page).toHaveTitle(uniqueTitle, { timeout: 10000 })
      const metaDescription = await page.locator('meta[name="description"]').getAttribute('content')
      expect(metaDescription).toBe(uniqueDescription)
    } finally {
      await restoreTitle()
      await restoreDescription()
    }
  })

  test('email i telefon platformy z panelu admina pokazuja sie w stopce', async ({ page }) => {
    // nie pokazywala. Dodano blok kontaktowy w stopce (App.vue).
    const adminToken = await getAdminToken(page)
    const uniqueEmail = `e2e-${Date.now()}@example.com`
    const phoneDigits = Date.now().toString().slice(-9)
    const uniquePhone = `+48${phoneDigits}`
    // Stopka formatuje numer do czytelnej postaci "+48 XXX XXX XXX".
    const formattedPhone = `+48 ${phoneDigits.replace(/(\d{3})(?=\d)/g, '$1 ')}`
    const restoreEmail = await withPlatformSetting(page, adminToken, 'platform_email', uniqueEmail)
    const restorePhone = await withPlatformSetting(page, adminToken, 'platform_phone', uniquePhone)

    try {
      await page.goto('/')
      await acceptCookies(page)
      await page.reload()

      const footer = page.locator('footer')
      await expect(footer.getByText(uniqueEmail)).toBeVisible({ timeout: 10000 })
      await expect(footer.getByText(formattedPhone)).toBeVisible()
    } finally {
      await restoreEmail()
      await restorePhone()
    }
  })
})
