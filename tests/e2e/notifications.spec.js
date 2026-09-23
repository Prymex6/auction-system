import { test, expect } from '@playwright/test'
import {
  acceptCookies,
  loginViaApi,
  getAuctionByTitle,
  withPlatformSetting,
  getAdminToken,
  E2E_USER,
  E2E_SELLER,
} from './helpers.js'

test.describe('Powiadomienia (dzwoneczek)', () => {
  test('nowa oferta generuje powiadomienie dla sprzedawcy, ktore mozna oznaczyc jako przeczytane', async ({
    page,
  }) => {
    const adminToken = await getAdminToken(page)
    const restoreBidding = await withPlatformSetting(page, adminToken, 'bidding_enabled', true)

    try {
      const auction = await getAuctionByTitle(page, 'E2E Test Aukcja Licytacyjna')
      expect(auction).toBeTruthy()

      const { token: userToken } = await loginViaApi(page, E2E_USER)
      const bidAmount = Math.ceil(auction.current_price * 1.5) + 10
      await page.request.post(`/api/bids/auction/${auction.id}`, {
        headers: { Authorization: `Bearer ${userToken}` },
        data: { amount: bidAmount },
      })

      await loginViaApi(page, E2E_SELLER)
      await page.goto('/')
      await acceptCookies(page)
      await page.reload()

      const bellButton = page
        .locator('nav button')
        .filter({ has: page.locator('svg[data-icon="bell"]') })
      await bellButton.click()

      await expect(page.getByText('Powiadomienia')).toBeVisible()
      await expect(page.getByText(/nowa oferta|złożono ofertę|oferta/i).first()).toBeVisible({
        timeout: 10000,
      })

      await page.getByRole('button', { name: 'Oznacz wszystkie' }).click()
      await expect(bellButton.locator('span.bg-red-500')).toHaveCount(0, { timeout: 10000 })
    } finally {
      await restoreBidding()
    }
  })
})
