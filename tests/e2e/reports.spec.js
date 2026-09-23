import { test, expect } from '@playwright/test'
import {
  acceptCookies,
  loginViaApi,
  getAdminToken,
  withPlatformSetting,
  registerThrowawaySeller,
  E2E_ADMIN,
  E2E_USER,
} from './helpers.js'

async function createThrowawayAuction(page, adminToken, sellerId, title) {
  const res = await page.request.post('/api/admin/auctions-with-listing', {
    headers: { Authorization: `Bearer ${adminToken}` },
    data: {
      user_id: sellerId,
      status: 'active',
      type: 'buy_now',
      start_price: 50,
      title,
      breed: 'Janssen',
    },
  })
  expect(res.ok(), await res.text()).toBeTruthy()
  return (await res.json()).auction.id
}

test.describe('Zgłoszenia aukcji', () => {
  test('zgłoszenie aukcji dziala przez UI, a powtorne zgloszenie tej samej aukcji jest odrzucane', async ({
    page,
  }) => {
    const adminToken = await getAdminToken(page)
    const restore = await withPlatformSetting(page, adminToken, 'enable_user_reports', true)

    const seller = await registerThrowawaySeller(page)
    const auctionId = await createThrowawayAuction(
      page,
      adminToken,
      seller.id,
      `E2E Report Target ${Date.now()}`
    )

    try {
      await loginViaApi(page, E2E_USER)
      await page.goto(`/auctions/${auctionId}`)
      await acceptCookies(page)
      await page.reload()

      await page.getByRole('button', { name: 'Zgłoś' }).click()
      await page.locator('select').last().selectOption('spam')
      await page
        .getByPlaceholder('Opisz szczegółowo problem...')
        .fill('To jest testowy opis zgłoszenia E2E, wystarczajaco dlugi.')
      await page
        .getByRole('button', { name: /Wyślij zgłoszenie|Zgłoś/i })
        .last()
        .click()

      await expect(page.getByText(/dziękujemy|wysłane/i)).toBeVisible({ timeout: 10000 })

      const dup = await page.request.post('/api/reports', {
        headers: { Authorization: `Bearer ${(await loginViaApi(page, E2E_USER)).token}` },
        data: {
          auction_id: auctionId,
          reason: 'spam',
          description: 'Kolejna proba zgloszenia tej samej aukcji.',
        },
      })
      expect(dup.status()).toBe(422)
    } finally {
      await page.request
        .delete(`/api/admin/auctions/${auctionId}`, {
          headers: { Authorization: `Bearer ${adminToken}` },
        })
        .catch(() => {})
      await restore()
    }
  })

  test('zgloszenie jest odrzucone gdy enable_user_reports=false', async ({ page }) => {
    const adminToken = await getAdminToken(page)
    const restore = await withPlatformSetting(page, adminToken, 'enable_user_reports', false)

    const seller = await registerThrowawaySeller(page)
    const auctionId = await createThrowawayAuction(
      page,
      adminToken,
      seller.id,
      `E2E Report Disabled ${Date.now()}`
    )

    try {
      const { token: userToken } = await loginViaApi(page, E2E_USER)
      const res = await page.request.post('/api/reports', {
        headers: { Authorization: `Bearer ${userToken}` },
        data: {
          auction_id: auctionId,
          reason: 'spam',
          description: 'To nie powinno przejsc, bo zglaszanie jest wylaczone.',
        },
      })
      expect(res.status()).toBe(403)
    } finally {
      await page.request
        .delete(`/api/admin/auctions/${auctionId}`, {
          headers: { Authorization: `Bearer ${adminToken}` },
        })
        .catch(() => {})
      await restore()
    }
  })

  test('sprzedawca jest automatycznie banowany po przekroczeniu progu zgloszen', async ({
    page,
  }) => {
    const adminToken = await getAdminToken(page)
    const restoreReports = await withPlatformSetting(page, adminToken, 'enable_user_reports', true)
    const restoreThreshold = await withPlatformSetting(
      page,
      adminToken,
      'auto_ban_reports_threshold',
      1
    )

    const seller = await registerThrowawaySeller(page)
    const auctionId = await createThrowawayAuction(
      page,
      adminToken,
      seller.id,
      `E2E Auto-ban Target ${Date.now()}`
    )

    try {
      const { token: userToken } = await loginViaApi(page, E2E_USER)
      const res = await page.request.post('/api/reports', {
        headers: { Authorization: `Bearer ${userToken}` },
        data: {
          auction_id: auctionId,
          reason: 'fraud',
          description: 'Zgloszenie ktore powinno wywolac auto-ban sprzedawcy.',
        },
      })
      expect(res.ok(), await res.text()).toBeTruthy()

      const loginAttempt = await page.request.post('/api/auth/login', {
        data: { login: seller.name, password: seller.password },
      })
      expect(loginAttempt.status()).toBe(403)
      const body = await loginAttempt.json()
      expect(body.message).toContain('zablokowane')
    } finally {
      await page.request
        .delete(`/api/admin/auctions/${auctionId}`, {
          headers: { Authorization: `Bearer ${adminToken}` },
        })
        .catch(() => {})
      await restoreThreshold()
      await restoreReports()
    }
  })

  test('panel admina: rozpatrzenie zgloszenia przez UI zapisuje notatke i banuje zglaszonego sprzedawce', async ({
    page,
  }) => {
    // Regresja podwojna znaleziona przy audycie: (1) front wysylal
    const adminToken = await getAdminToken(page)
    const restore = await withPlatformSetting(page, adminToken, 'enable_user_reports', true)

    const seller = await registerThrowawaySeller(page)
    const auctionRes = await page.request.post('/api/admin/auctions-with-listing', {
      headers: { Authorization: `Bearer ${adminToken}` },
      data: {
        user_id: seller.id,
        status: 'active',
        type: 'buy_now',
        start_price: 50,
        title: `E2E Handle Report ${Date.now()}`,
        breed: 'Janssen',
      },
    })
    const auctionId = (await auctionRes.json()).auction.id

    const uniqueDescription = `E2E opis zgloszenia do rozpatrzenia ${Date.now()}`
    const { token: userToken } = await loginViaApi(page, E2E_USER)
    const reportRes = await page.request.post('/api/reports', {
      headers: { Authorization: `Bearer ${userToken}` },
      data: { auction_id: auctionId, reason: 'fraud', description: uniqueDescription },
    })
    expect(reportRes.ok(), await reportRes.text()).toBeTruthy()
    const reportId = (await reportRes.json()).data.id

    try {
      await loginViaApi(page, E2E_ADMIN)
      await page.goto('/admin')
      await acceptCookies(page)
      await page.reload()

      await page.getByRole('button', { name: 'Zgłoszenia' }).click()
      const card = page.locator('div.bg-white.rounded-2xl', { hasText: uniqueDescription })
      await expect(card).toBeVisible({ timeout: 10000 })
      await card.getByRole('button', { name: 'Rozpatrz' }).click()

      const uniqueNote = `E2E notatka admina ${Date.now()}`
      await page.locator('textarea').last().fill(uniqueNote)
      // Checkbox jest sr-only (wizualnie ukryty pod przelacznikiem) i jego
      await page.locator('input[type="checkbox"]').last().check({ force: true })

      const updateResponsePromise = page.waitForResponse(
        (res) =>
          res.url().includes(`/api/admin/reports/${reportId}`) && res.request().method() === 'PATCH'
      )
      await page.getByRole('button', { name: 'Rozwiąż' }).click()
      const updateResponse = await updateResponsePromise
      expect(updateResponse.ok(), await updateResponse.text()).toBeTruthy()

      const reportCheck = await page.request.get(`/api/admin/reports/${reportId}`, {
        headers: { Authorization: `Bearer ${await getAdminToken(page)}` },
      })
      const reportBody = (await reportCheck.json()).data
      expect(reportBody.notes).toBe(uniqueNote)
      expect(reportBody.status).toBe('resolved')

      const loginAttempt = await page.request.post('/api/auth/login', {
        data: { login: seller.name, password: seller.password },
      })
      expect(loginAttempt.status()).toBe(403)
    } finally {
      await page.request
        .delete(`/api/admin/auctions/${auctionId}`, {
          headers: { Authorization: `Bearer ${adminToken}` },
        })
        .catch(() => {})
      await restore()
    }
  })
})
