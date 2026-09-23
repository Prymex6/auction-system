import { test, expect } from '@playwright/test'
import {
  acceptCookies,
  loginViaApi,
  getAdminToken,
  withPlatformSetting,
  E2E_SELLER,
  SAMPLE_IMAGE,
} from './helpers.js'

test.describe('Cykl życia aukcji: utworzenie -> moderacja admina -> edycja -> usunięcie', () => {
  test('seller tworzy aukcję przez formularz UI, admin ją akceptuje, seller edytuje i usuwa', async ({
    page,
  }) => {
    const adminToken = await getAdminToken(page)

    const restoreOnlyAdmin = await withPlatformSetting(
      page,
      adminToken,
      'only_admin_can_list',
      false
    )

    let createdAuctionId = null
    let sellerToken = null

    try {
      ;({ token: sellerToken } = await loginViaApi(page, E2E_SELLER))
      const uniqueTitle = `E2E Nowa Aukcja ${Date.now()}`

      await page.goto('/create-auction')
      await acceptCookies(page)
      await page.reload()

      const uploadResponsePromise = page.waitForResponse((res) =>
        res.url().includes('/api/images/upload')
      )
      await page.locator('input[type="file"]').first().setInputFiles(SAMPLE_IMAGE)
      await uploadResponsePromise

      await page
        .getByPlaceholder('np. Piękny biały gołąb pocztowy - champion wystaw')
        .fill(uniqueTitle)
      await page.getByPlaceholder('np. Warszawski, Sokólnik, Braszławski...').fill('Janssen')
      await page
        .getByPlaceholder('Opisz szczegółowo swojego gołębia: osiągnięcia, charakter, zdrowie...')
        .fill('Opis testowy E2E.')
      await page.locator('select').filter({ hasText: 'Wybierz płeć' }).selectOption('samiec')
      await page.locator('select').filter({ hasText: 'Wybierz wielkość' }).selectOption('sredni')
      await page.locator('select').filter({ hasText: 'Wybierz barwę' }).selectOption({ index: 1 })
      await page.getByPlaceholder('100').fill('150')
      await page.getByRole('checkbox').check()

      await page.getByRole('button', { name: /Utwórz aukcję/i }).click()

      // stanie "pending" (w odroznieniu od publicznej listy /auctions).
      await expect(page).toHaveURL(/\/profile\?tab=my-auctions/, { timeout: 15000 })
      await expect(page.getByText(uniqueTitle)).toBeVisible({ timeout: 10000 })

      const myAuctionsRes = await page.request.get('/api/auctions/my', {
        headers: { Authorization: `Bearer ${sellerToken}` },
      })
      const myAuctionsBody = await myAuctionsRes.json()
      const myAuctionsList = myAuctionsBody.data?.data || myAuctionsBody.data || []
      const auctionData = myAuctionsList.find((a) => a.title === uniqueTitle)
      expect(
        auctionData,
        'nowo utworzona aukcja powinna byc widoczna w /api/auctions/my'
      ).toBeTruthy()
      createdAuctionId = auctionData.id
      expect(auctionData.status).toBe('pending')

      // jedyna poprawna.
      await page.goto(`/auctions/${createdAuctionId}/edit`)
      await acceptCookies(page)
      const loadResponsePromise = page.waitForResponse(
        (res) =>
          res.url().includes(`/api/auctions/${createdAuctionId}`) &&
          res.request().method() === 'GET'
      )
      await page.reload()
      // chwili po cichu nadpisane oryginalnymi danymi z serwera - dokladnie
      // to powodowalo pozorne "znikniecie" edycji i utkniecie na /edit.
      await loadResponsePromise

      const editedTitle = `${uniqueTitle} (edytowana)`
      await page.getByPlaceholder('np. Gołąb gołębiowaty - samiec').fill(editedTitle)
      await page.getByPlaceholder('np. Warszawski, Sokólnik, Braszławski...').fill('Janssen')
      await page.locator('select').filter({ hasText: 'Samiec' }).selectOption('samiec')
      await page.locator('textarea').first().fill('Opis testowy E2E (edytowany).')

      await page.getByRole('button', { name: /Zaktualizuj aukcję/i }).click()
      // Podobnie jak przy tworzeniu, edycja tez przekierowuje na profil, nie
      await expect(page).toHaveURL(/\/profile\?tab=my-auctions/, { timeout: 10000 })

      const afterEditRes = await page.request.get(`/api/auctions/${createdAuctionId}`)
      expect((await afterEditRes.json()).data.title).toBe(editedTitle)

      await page.request.post(`/api/admin/auctions/${createdAuctionId}/approve`, {
        headers: { Authorization: `Bearer ${adminToken}` },
      })

      const afterApprovalRes = await page.request.get(`/api/auctions/${createdAuctionId}`)
      expect((await afterApprovalRes.json()).data.status).toBe('active')
    } finally {
      if (createdAuctionId) {
        await page.request
          .delete(`/api/auctions/${createdAuctionId}`, {
            headers: { Authorization: `Bearer ${sellerToken}` },
          })
          .catch(() => {})
      }
      await restoreOnlyAdmin()
    }
  })

  test('formularz wystawiania: blad walidacji z serwera jest widoczny i naprawialny, wiele zdjec golebia + zdjecie rodowodu zapisuja sie poprawnie', async ({
    page,
  }) => {
    const adminToken = await getAdminToken(page)
    const restoreOnlyAdmin = await withPlatformSetting(
      page,
      adminToken,
      'only_admin_can_list',
      false
    )
    let createdAuctionId = null
    let sellerToken = null

    try {
      ;({ token: sellerToken } = await loginViaApi(page, E2E_SELLER))
      const uniqueTitle = `E2E Zdjecia i Bledy ${Date.now()}`

      await page.goto('/create-auction')
      await acceptCookies(page)
      await page.reload()

      const pigeonUploadPromise = page.waitForResponse((res) =>
        res.url().includes('/api/images/upload')
      )
      await page.locator('input[type="file"]').first().setInputFiles([SAMPLE_IMAGE, SAMPLE_IMAGE])
      await pigeonUploadPromise
      await expect(page.locator('img[alt="Zdjęcia gołębia"]')).toHaveCount(2, { timeout: 10000 })

      // Zdjecie rodowodu (drugie pole uploadu, opcjonalne).
      const pedigreeUploadPromise = page.waitForResponse((res) =>
        res.url().includes('/api/images/upload')
      )
      await page.locator('input[type="file"]').nth(1).setInputFiles(SAMPLE_IMAGE)
      await pedigreeUploadPromise
      await expect(page.locator('img[alt="Zdjęcia rodowodu"]')).toHaveCount(1, { timeout: 10000 })

      await page
        .getByPlaceholder('np. Piękny biały gołąb pocztowy - champion wystaw')
        .fill(uniqueTitle)
      await page.getByPlaceholder('np. Warszawski, Sokólnik, Braszławski...').fill('Janssen')
      await page.locator('select').filter({ hasText: 'Wybierz płeć' }).selectOption('samiec')
      await page.locator('select').filter({ hasText: 'Wybierz wielkość' }).selectOption('sredni')
      await page.locator('select').filter({ hasText: 'Wybierz barwę' }).selectOption({ index: 1 })
      await page.getByRole('checkbox').check()

      await page.getByPlaceholder('100').fill('5')
      const auctionPostSpy = []
      page.on('request', (req) => {
        if (req.url().includes('/api/auctions') && req.method() === 'POST')
          auctionPostSpy.push(req.url())
      })
      await page.getByRole('button', { name: /Utwórz aukcję/i }).click()
      await expect(page.getByText('Minimalna cena to 10 zł')).toBeVisible({ timeout: 5000 })
      await expect(page).toHaveURL(/\/create-auction/)
      expect(
        auctionPostSpy,
        'formularz nie powinien wyslac /api/auctions z bledna cena'
      ).toHaveLength(0)

      await page.getByPlaceholder('100').fill('150')
      await page.getByRole('button', { name: /Utwórz aukcję/i }).click()

      await expect(page).toHaveURL(/\/profile\?tab=my-auctions/, { timeout: 15000 })

      const myAuctionsRes = await page.request.get('/api/auctions/my', {
        headers: { Authorization: `Bearer ${sellerToken}` },
      })
      const myAuctionsBody = await myAuctionsRes.json()
      const myAuctionsList = myAuctionsBody.data?.data || myAuctionsBody.data || []
      const auctionData = myAuctionsList.find((a) => a.title === uniqueTitle)
      expect(auctionData).toBeTruthy()
      createdAuctionId = auctionData.id

      const detailRes = await page.request.get(`/api/auctions/${createdAuctionId}`)
      const detail = (await detailRes.json()).data
      expect(detail.pigeon_images).toHaveLength(2)
      expect(detail.pedigree_images).toHaveLength(1)
    } finally {
      if (createdAuctionId) {
        await page.request
          .delete(`/api/auctions/${createdAuctionId}`, {
            headers: { Authorization: `Bearer ${sellerToken}` },
          })
          .catch(() => {})
      }
      await restoreOnlyAdmin()
    }
  })

  test('edycja juz aktywnego ogloszenia "Kup teraz" wraca do moderacji i znika z publicznej listy, dopoki admin znow nie zatwierdzi', async ({
    page,
  }) => {
    const adminToken = await getAdminToken(page)
    const restoreOnlyAdmin = await withPlatformSetting(
      page,
      adminToken,
      'only_admin_can_list',
      false
    )
    const restoreApproval = await withPlatformSetting(
      page,
      adminToken,
      'require_auction_approval',
      true
    )
    let createdAuctionId = null
    let sellerToken = null

    try {
      ;({ token: sellerToken } = await loginViaApi(page, E2E_SELLER))
      const uniqueTitle = `E2E Wraca Do Moderacji ${Date.now()}`

      const createRes = await page.request.post('/api/auctions', {
        headers: { Authorization: `Bearer ${sellerToken}` },
        data: {
          title: uniqueTitle,
          breed: 'Janssen',
          gender: 'samiec',
          year: new Date().getFullYear(),
          size: 'sredni',
          color: 'Niebieska',
          type: 'buy_now',
          start_price: 200,
          // EditAuction.vue (w odroznieniu od CreateAuction.vue) wymaga po
          description: 'Opis testowy E2E.',
          pigeon_images: ['images/auction/e2e-placeholder.jpg'],
        },
      })
      createdAuctionId = (await createRes.json()).data.id

      await page.request.post(`/api/admin/auctions/${createdAuctionId}/approve`, {
        headers: { Authorization: `Bearer ${adminToken}` },
      })

      const beforeEditRes = await page.request.get(`/api/auctions/${createdAuctionId}`)
      expect((await beforeEditRes.json()).data.status).toBe('active')

      const publicListBefore = (await (await page.request.get('/api/auctions')).json()).data
      expect(publicListBefore.some((a) => a.id === createdAuctionId)).toBe(true)

      await loginViaApi(page, E2E_SELLER)
      await page.goto(`/auctions/${createdAuctionId}/edit`)
      await acceptCookies(page)
      const loadResponsePromise = page.waitForResponse(
        (res) =>
          res.url().includes(`/api/auctions/${createdAuctionId}`) &&
          res.request().method() === 'GET'
      )
      await page.reload()
      await loadResponsePromise
      await expect(page.getByPlaceholder('np. Warszawski, Sokólnik, Braszławski...')).toHaveValue(
        'Janssen',
        {
          timeout: 10000,
        }
      )

      const editedTitle = `${uniqueTitle} (zmieniona cena)`
      await page.getByPlaceholder('np. Gołąb gołębiowaty - samiec').fill(editedTitle)

      const updateResponsePromise = page.waitForResponse(
        (res) =>
          res.url().includes(`/api/auctions/${createdAuctionId}`) &&
          res.request().method() === 'PUT'
      )
      await page.getByRole('button', { name: /Zaktualizuj aukcję/i }).click()
      const updateResponse = await updateResponsePromise
      const updateBody = await updateResponse.json()
      expect(updateBody.requeued_for_approval).toBe(true)
      expect(updateBody.data.status).toBe('pending')

      await expect(page.getByText(/wymagają ponownej akceptacji administratora/i)).toBeVisible({
        timeout: 5000,
      })

      await expect(page).toHaveURL(/\/profile\?tab=my-auctions/, { timeout: 10000 })
      await expect(page.getByText(editedTitle)).toBeVisible({ timeout: 10000 })
      await expect(
        page.locator('*', { hasText: editedTitle }).locator('..').getByText('Oczekująca').first()
      ).toBeVisible()

      const publicListAfterEdit = (await (await page.request.get('/api/auctions')).json()).data
      expect(publicListAfterEdit.some((a) => a.id === createdAuctionId)).toBe(false)

      // Admin zatwierdza ponownie - wraca.
      await page.request.post(`/api/admin/auctions/${createdAuctionId}/approve`, {
        headers: { Authorization: `Bearer ${adminToken}` },
      })
      const afterReapprovalRes = await page.request.get(`/api/auctions/${createdAuctionId}`)
      const afterReapproval = (await afterReapprovalRes.json()).data
      expect(afterReapproval.status).toBe('active')
      expect(afterReapproval.title).toBe(editedTitle)

      const publicListAfterApproval = (await (await page.request.get('/api/auctions')).json()).data
      expect(publicListAfterApproval.some((a) => a.id === createdAuctionId)).toBe(true)
    } finally {
      if (createdAuctionId) {
        await page.request
          .delete(`/api/admin/auctions/${createdAuctionId}`, {
            headers: { Authorization: `Bearer ${adminToken}` },
          })
          .catch(() => {})
      }
      await restoreOnlyAdmin()
      await restoreApproval()
    }
  })

  test('wlasciciel moze recznie zakonczyc wlasne aktywne ogloszenie "Kup teraz" przyciskiem "Zakoncz aukcje"', async ({
    page,
  }) => {
    const adminToken = await getAdminToken(page)
    let createdAuctionId = null
    let sellerToken = null

    try {
      ;({ token: sellerToken } = await loginViaApi(page, E2E_SELLER))
      const uniqueTitle = `E2E Konczenie Kup Teraz ${Date.now()}`

      const createRes = await page.request.post('/api/auctions', {
        headers: { Authorization: `Bearer ${sellerToken}` },
        data: {
          title: uniqueTitle,
          breed: 'Janssen',
          gender: 'samiec',
          year: new Date().getFullYear(),
          size: 'sredni',
          color: 'Niebieska',
          type: 'buy_now',
          start_price: 200,
          description: 'Opis testowy E2E.',
          pigeon_images: ['images/auction/e2e-placeholder.jpg'],
        },
      })
      createdAuctionId = (await createRes.json()).data.id

      await page.request.post(`/api/admin/auctions/${createdAuctionId}/approve`, {
        headers: { Authorization: `Bearer ${adminToken}` },
      })

      await loginViaApi(page, E2E_SELLER)
      await page.goto(`/auctions/${createdAuctionId}/edit`)
      await acceptCookies(page)
      const loadResponsePromise = page.waitForResponse(
        (res) =>
          res.url().includes(`/api/auctions/${createdAuctionId}`) &&
          res.request().method() === 'GET'
      )
      await page.reload()
      await loadResponsePromise
      await expect(page.getByPlaceholder('np. Warszawski, Sokólnik, Braszławski...')).toHaveValue(
        'Janssen',
        {
          timeout: 10000,
        }
      )

      const completeButton = page.getByRole('button', { name: 'Zakończ aukcję' })
      await expect(completeButton).toBeEnabled()
      await completeButton.click()

      await expect(page.getByText('Potwierdzić zakończenie?')).toBeVisible({ timeout: 5000 })
      const completeResponsePromise = page.waitForResponse(
        (res) =>
          res.url().includes(`/api/auctions/${createdAuctionId}/complete`) &&
          res.request().method() === 'PATCH'
      )
      await page.getByRole('button', { name: 'Zakończ', exact: true }).click()
      const completeResponse = await completeResponsePromise
      expect(completeResponse.ok(), await completeResponse.text()).toBeTruthy()
      const completeBody = await completeResponse.json()
      expect(completeBody.data.status).toBe('ended')

      await expect(page.getByText('Aukcja została pomyślnie zakończona!')).toBeVisible({
        timeout: 5000,
      })
      await expect(page).toHaveURL(/\/profile\?tab=my-auctions/, { timeout: 10000 })

      const finalRes = await page.request.get(`/api/auctions/${createdAuctionId}`)
      expect((await finalRes.json()).data.status).toBe('ended')
    } finally {
      if (createdAuctionId) {
        await page.request
          .delete(`/api/admin/auctions/${createdAuctionId}`, {
            headers: { Authorization: `Bearer ${adminToken}` },
          })
          .catch(() => {})
      }
    }
  })

  test('user bez uprawnien nie widzi formularza wystawiania gdy only_admin_can_list=true', async ({
    page,
  }) => {
    const adminToken = await getAdminToken(page)
    const restore = await withPlatformSetting(page, adminToken, 'only_admin_can_list', true)

    try {
      await loginViaApi(page, E2E_SELLER)
      await page.goto('/create-auction')
      await acceptCookies(page)
      await page.reload()

      await expect(page.getByText('Wystawianie aukcji jest chwilowo niedostępne')).toBeVisible()
      await expect(page.locator('input[type="file"]')).toHaveCount(0)
    } finally {
      await restore()
    }
  })
})
