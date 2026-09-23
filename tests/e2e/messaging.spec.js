import { test, expect } from '@playwright/test'
import { acceptCookies, loginViaApi, getAuctionByTitle, E2E_USER, E2E_SELLER } from './helpers.js'

test.describe('Wiadomości i blokowanie użytkowników', () => {
  test('kliknięcie "Wyślij wiadomość" na profilu publicznym otwiera czat i pozwala napisać pierwszą wiadomość', async ({
    page,
  }) => {
    const auction = await getAuctionByTitle(page, 'E2E Test Oferta Kup Teraz')
    expect(auction).toBeTruthy()
    const sellerId = auction.user_id

    await loginViaApi(page, E2E_USER)
    await page.goto(`/profile/${sellerId}`)
    await acceptCookies(page)
    await page.reload()

    await page.getByRole('link', { name: 'Wyślij wiadomość' }).click()
    await expect(page).toHaveURL(/\/messages\?user=\d+/, { timeout: 10000 })

    const uniqueMessage = `E2E testowa wiadomość ${Date.now()}`
    const textarea = page.getByPlaceholder('Napisz wiadomość...')
    await expect(textarea).toBeVisible({ timeout: 10000 })
    await textarea.fill(uniqueMessage)
    await page.getByRole('button', { name: 'Wyślij' }).click()

    await expect(
      page.locator('p.text-base.leading-relaxed', { hasText: uniqueMessage })
    ).toBeVisible({ timeout: 10000 })
  })

  test('zablokowany user nie moze wyslac wiadomosci do blokujacego', async ({ page }) => {
    const { token: userToken } = await loginViaApi(page, E2E_USER)
    const userId = (
      await (
        await page.request.get('/api/auth/me', {
          headers: { Authorization: `Bearer ${userToken}` },
        })
      ).json()
    ).user.id

    const { token: sellerToken } = await loginViaApi(page, E2E_SELLER)
    const sellerId = (
      await (
        await page.request.get('/api/auth/me', {
          headers: { Authorization: `Bearer ${sellerToken}` },
        })
      ).json()
    ).user.id

    try {
      // Seller blokuje e2e_user.
      await page.request.post(`/api/users/${userId}/block`, {
        headers: { Authorization: `Bearer ${sellerToken}` },
      })

      const sendRes = await page.request.post(`/api/messages/user/${sellerId}`, {
        headers: { Authorization: `Bearer ${userToken}` },
        data: { content: 'Próba wiadomości mimo blokady' },
      })
      expect(sendRes.status()).toBe(403)
    } finally {
      await page.request
        .delete(`/api/users/${userId}/block`, {
          headers: { Authorization: `Bearer ${sellerToken}` },
        })
        .catch(() => {})
    }
  })

  test('panel czatu ma ograniczona wysokosc i nie wylewa sie na stopke, nawet przy dlugiej konwersacji', async ({
    page,
  }) => {
    const { token: userToken } = await loginViaApi(page, E2E_USER)
    const { token: sellerToken } = await loginViaApi(page, E2E_SELLER)
    const sellerId = (
      await (
        await page.request.get('/api/auth/me', {
          headers: { Authorization: `Bearer ${sellerToken}` },
        })
      ).json()
    ).user.id

    for (let i = 0; i < 15; i++) {
      await page.request.post(`/api/messages/user/${sellerId}`, {
        headers: { Authorization: `Bearer ${userToken}` },
        data: { content: `Test wysokosci panelu ${i} - ${Date.now()}` },
      })
    }

    await page.setViewportSize({ width: 1400, height: 1000 })
    await loginViaApi(page, E2E_USER)
    await page.goto(`/messages?user=${sellerId}`)
    await acceptCookies(page)
    await page.reload()
    await expect(page.getByPlaceholder('Napisz wiadomość...')).toBeVisible({ timeout: 10000 })

    const panelHeight = await page
      .locator('.lg\\:col-span-3')
      .first()
      .evaluate((el) => el.getBoundingClientRect().height)
    const viewportHeight = 1000
    expect(panelHeight).toBeLessThan(viewportHeight)
  })
})
