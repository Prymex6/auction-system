import { test, expect } from '@playwright/test'
import {
  acceptCookies,
  loginViaApi,
  getAdminToken,
  withPlatformSetting,
  getAuctionByTitle,
  E2E_USER,
} from './helpers.js'

async function getFixtureAuction(page, title) {
  const found = await getAuctionByTitle(page, title)
  expect(
    found,
    `aukcja fixture "${title}" powinna istniec (uruchom php artisan e2e:seed)`
  ).toBeTruthy()
  return found
}

test.describe('Licytacja', () => {
  let adminToken
  let restoreBidding
  let restoreIncrement
  let setupContext

  test.beforeAll(async ({ browser }) => {
    setupContext = await browser.newContext()
    const page = await setupContext.newPage()
    adminToken = await getAdminToken(page)
    restoreBidding = await withPlatformSetting(page, adminToken, 'bidding_enabled', true)
    restoreIncrement = await withPlatformSetting(page, adminToken, 'bid_increment_percentage', 10)
  })

  test.afterAll(async () => {
    await restoreBidding?.()
    await restoreIncrement?.()
    await setupContext?.close()
  })

  test('oferta ponizej minimalnego kroku jest odrzucana z czytelnym bledem', async ({ page }) => {
    const auction = await getFixtureAuction(page, 'E2E Test Aukcja Licytacyjna')

    await loginViaApi(page, E2E_USER)
    await page.goto(`/auctions/${auction.id}`)
    await acceptCookies(page)
    await page.reload()

    await page.getByRole('button', { name: 'Licytuj' }).click()
    const bidInput = page.locator('#bid_amount')
    await expect(bidInput).toBeVisible()

    await bidInput.fill(String(auction.current_price + 0.01))
    await page.getByRole('button', { name: /Złóż ofertę/i }).click()

    await expect(page.getByText(/musi wynosić co najmniej/i)).toBeVisible({ timeout: 10000 })
  })

  test('poprawna oferta jest przyjeta i podnosi aktualna cene aukcji', async ({ page }) => {
    const auction = await getFixtureAuction(page, 'E2E Test Aukcja Licytacyjna')

    const settingsRes = await page.request.get('/api/settings')
    const incrementPct = (await settingsRes.json()).data.bid_increment_percentage || 0
    const minimumBid = Math.round(auction.current_price * (1 + incrementPct / 100) * 100) / 100
    const bidValue = Math.ceil(minimumBid) + 5

    await loginViaApi(page, E2E_USER)
    await page.goto(`/auctions/${auction.id}`)
    await acceptCookies(page)
    await page.reload()

    await page.getByRole('button', { name: 'Licytuj' }).click()
    await page.locator('#bid_amount').fill(String(bidValue))
    await page.getByRole('button', { name: /Złóż ofertę/i }).click()

    await expect(page.getByText('Oferta została złożona!')).toBeVisible({ timeout: 10000 })

    const afterRes = await page.request.get(`/api/auctions/${auction.id}`)
    expect((await afterRes.json()).data.current_price).toBe(bidValue)
  })

  test('sprzedawca nie widzi przycisku Licytuj na wlasnej aukcji', async ({ page }) => {
    const auction = await getFixtureAuction(page, 'E2E Test Aukcja Licytacyjna')

    await loginViaApi(page, { name: 'e2e_seller', password: 'E2ETestPass123!' })
    await page.goto(`/auctions/${auction.id}`)
    await acceptCookies(page)
    await page.reload()

    const bidButton = page.getByRole('button', { name: 'Licytuj' })
    await expect(bidButton).toBeDisabled()
  })

  test('automatyczna licytacja: przelacznik ustawia auto-bid przez dedykowany endpoint', async ({
    page,
  }) => {
    const auction = await getFixtureAuction(page, 'E2E Test Aukcja Licytacyjna')

    await loginViaApi(page, E2E_USER)
    await page.goto(`/auctions/${auction.id}`)
    await acceptCookies(page)
    await page.reload()

    await page.getByRole('button', { name: 'Licytuj' }).click()
    await expect(page.locator('#bid_amount')).toBeVisible()

    await page.getByRole('switch', { name: 'Automatyczna licytacja' }).click()
    const maxAutoBidInput = page.locator('input[type="number"]').last()
    await expect(maxAutoBidInput).toBeVisible()

    const autoBidResponsePromise = page.waitForResponse((res) => res.url().includes('/auto-bid'))
    await maxAutoBidInput.fill(String(Math.round(auction.current_price) + 500))
    await page.getByRole('button', { name: /Ustaw auto licytację/i }).click()
    const autoBidResponse = await autoBidResponsePromise
    expect(autoBidResponse.ok(), await autoBidResponse.text()).toBeTruthy()

    await expect(page.getByText('Auto licytacja została ustawiona!')).toBeVisible({
      timeout: 10000,
    })
  })

  test('gdy bidding_enabled=false, przycisk Licytuj znika i pokazuje sie informacja', async ({
    page,
  }) => {
    const restore = await withPlatformSetting(page, adminToken, 'bidding_enabled', false)
    try {
      const auction = await getFixtureAuction(page, 'E2E Test Aukcja Licytacyjna')

      await loginViaApi(page, E2E_USER)
      await page.goto(`/auctions/${auction.id}`)
      await acceptCookies(page)
      await page.reload()

      await expect(page.getByRole('button', { name: 'Licytuj' })).toHaveCount(0)
      await expect(page.getByText('Licytacje są obecnie wyłączone')).toBeVisible()
    } finally {
      await restore()
    }
  })

  test('gdy admin wymaga 2FA (require_2fa), user bez wlaczonego 2FA nie moze zalicytowac', async ({
    page,
  }) => {
    const restore = await withPlatformSetting(page, adminToken, 'require_2fa', true)
    try {
      const auction = await getFixtureAuction(page, 'E2E Test Aukcja Licytacyjna')

      await loginViaApi(page, E2E_USER)
      await page.goto(`/auctions/${auction.id}`)
      await acceptCookies(page)
      await page.reload()

      await page.getByRole('button', { name: 'Licytuj' }).click()
      const bidInput = page.locator('#bid_amount')
      await expect(bidInput).toBeVisible()
      await bidInput.fill(String(auction.current_price + 100))
      await page.getByRole('button', { name: /Złóż ofertę/i }).click()

      await expect(page.getByText(/wymaga włączenia weryfikacji dwuetapowej/i)).toBeVisible({
        timeout: 10000,
      })
    } finally {
      await restore()
    }
  })
})
