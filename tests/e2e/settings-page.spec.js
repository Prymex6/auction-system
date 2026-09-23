import { test, expect } from '@playwright/test'
import { acceptCookies, loginViaApi, E2E_USER } from './helpers.js'

test.describe('Zakladka "Ustawienia" w /profile (urzadzenia + powiadomienia)', () => {
  test('/settings przekierowuje do /profile?tab=settings i pokazuje Aktywne urzadzenia', async ({
    page,
  }) => {
    await loginViaApi(page, E2E_USER)
    await page.goto('/settings')
    await acceptCookies(page)
    await page.reload()

    await expect(page).toHaveURL(/\/profile\?tab=settings/)
    await expect(page.getByText('Aktywne urządzenia')).toBeVisible({ timeout: 10000 })
  })

  test('klikniecie "Ustawienia" w menu, bedac juz na /profile, przelacza zakladke bez przeladowania strony', async ({
    page,
  }) => {
    await loginViaApi(page, E2E_USER)
    await page.goto('/profile')
    await acceptCookies(page)

    await expect(page.getByRole('heading', { name: 'Informacje osobiste' })).toBeVisible({
      timeout: 10000,
    })

    const openUserMenu = async () => {
      const link = page.getByRole('link', { name: 'Ustawienia', exact: true })
      if (!(await link.isVisible())) {
        await page.getByRole('button', { name: new RegExp(E2E_USER.name, 'i') }).click()
      }
    }

    await openUserMenu()
    await page.getByRole('link', { name: 'Ustawienia', exact: true }).click()

    await expect(page).toHaveURL(/\/profile\?tab=settings/)
    await expect(page.getByText('Preferencje i prywatność')).toBeVisible({ timeout: 10000 })

    // I odwrotnie: klikniecie "Profil" z powrotem na zakladke Informacje.
    await openUserMenu()
    await page.getByRole('link', { name: 'Profil', exact: true }).click()

    await expect(page).toHaveURL('/profile')
    await expect(page.getByRole('heading', { name: 'Informacje osobiste' })).toBeVisible({
      timeout: 10000,
    })
  })

  test('zakladka Ustawienia laduje i zapisuje preferencje powiadomien', async ({ page }) => {
    const { token } = await loginViaApi(page, E2E_USER)
    await page.goto('/profile?tab=settings')
    await acceptCookies(page)
    await page.reload()

    await expect(page.getByText('Preferencje i prywatność')).toBeVisible({ timeout: 10000 })

    const emailToggle = page.locator('input[type="checkbox"]').first()
    await expect(emailToggle).toBeEnabled({ timeout: 10000 })
    await emailToggle.uncheck({ force: true })
    await page.waitForTimeout(500)

    try {
      const prefsRes = await page.request.get('/api/profile/notification-preferences', {
        headers: { Authorization: `Bearer ${token}` },
      })
      expect((await prefsRes.json()).data.email_notifications).toBe(false)
    } finally {
      await page.request.patch('/api/profile/notification-preferences', {
        headers: { Authorization: `Bearer ${token}` },
        data: { email_notifications: true },
      })
    }
  })
})
