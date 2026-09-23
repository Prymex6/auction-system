import { test, expect } from '@playwright/test'
import {
  acceptCookies,
  loginViaApi,
  getAdminToken,
  withPlatformSetting,
  registerThrowawaySeller,
  SAMPLE_IMAGE,
  E2E_ADMIN,
} from './helpers.js'

test.describe('Panel admina', () => {
  test('wlaczenie ogloszenia systemowego przez UI panelu admina pokazuje baner na stronie glownej', async ({
    page,
  }) => {
    const adminToken = await getAdminToken(page)
    const restoreActive = await withPlatformSetting(page, adminToken, 'announcement_active', false)
    const restoreText = await withPlatformSetting(page, adminToken, 'system_announcement', '')

    try {
      await loginViaApi(page, E2E_ADMIN)
      await page.goto('/admin')
      await acceptCookies(page)
      await page.reload()

      await page.getByRole('button', { name: 'Ustawienia', exact: true }).click()
      await page.getByRole('button', { name: 'Wiadomości' }).click()

      const uniqueAnnouncement = `E2E ogłoszenie testowe ${Date.now()}`
      await page.getByLabel('Aktywuj ogłoszenie systemowe').check()
      await page.locator('textarea').last().fill(uniqueAnnouncement)
      await page.getByRole('button', { name: /Zapisz ustawienia/i }).click()

      await expect(page.getByText(/zapisane|zapisano/i).first()).toBeVisible({ timeout: 10000 })

      await page.goto('/')
      await acceptCookies(page)
      await page.reload()
      await expect(page.getByText(uniqueAnnouncement)).toBeVisible({ timeout: 10000 })
    } finally {
      await restoreActive()
      await restoreText()
    }
  })

  test('panel admina: pelny CRUD artykulu bloga przez UI', async ({ page }) => {
    await loginViaApi(page, E2E_ADMIN)
    await page.goto('/admin')
    await acceptCookies(page)
    await page.reload()

    await page.getByRole('button', { name: 'Blogi' }).click()
    await page.getByRole('button', { name: 'Dodaj artykuł' }).click()

    const uniqueTitle = `E2E Blog Test ${Date.now()}`
    await page.getByPlaceholder('Wpisz tytuł artykułu').fill(uniqueTitle)
    await page.locator('select').filter({ hasText: 'Wybierz kategorię' }).selectOption('Poradnik')
    await page.getByPlaceholder('Wpisz zawartość artykułu').fill('Tresc testowa E2E.')
    await page.getByPlaceholder('Krótki opis artykułu').fill('Streszczenie testowe E2E.')
    await page.getByLabel('Opublikuj artykuł').check()

    const createResponsePromise = page.waitForResponse(
      (res) => res.url().includes('/api/admin/blogs') && res.request().method() === 'POST'
    )
    await page.getByRole('button', { name: /^Zapisz$/ }).click()
    const createResponse = await createResponsePromise
    expect(createResponse.ok(), await createResponse.text()).toBeTruthy()
    const createdBlog = (await createResponse.json()).data

    await expect(page.getByText(uniqueTitle)).toBeVisible({ timeout: 10000 })

    // Artykul opublikowany powinien byc widoczny na publicznej liscie bloga.
    const publicListRes = await page.request.get('/api/blogs')
    const publicPosts =
      (await publicListRes.json()).data?.data || (await publicListRes.json()).data || []
    expect(publicPosts.some((p) => p.title === uniqueTitle)).toBeTruthy()

    await page.locator('tr', { hasText: uniqueTitle }).getByRole('button', { name: 'Usuń' }).click()
    await expect(page.getByText('Usuń Blog')).toBeVisible({ timeout: 5000 })

    const deleteResponsePromise = page.waitForResponse(
      (res) =>
        res.url().includes(`/api/admin/blogs/${createdBlog.slug}`) &&
        res.request().method() === 'DELETE'
    )
    await page.getByRole('button', { name: 'Usuń', exact: true }).last().click()
    const deleteResponse = await deleteResponsePromise
    expect(deleteResponse.ok(), await deleteResponse.text()).toBeTruthy()

    await expect(page.getByText(uniqueTitle)).toHaveCount(0, { timeout: 10000 })
  })

  test('panel admina: dodanie i usuniecie kategorii przez UI', async ({ page }) => {
    await loginViaApi(page, E2E_ADMIN)
    await page.goto('/admin')
    await acceptCookies(page)
    await page.reload()

    await page.getByRole('button', { name: 'Kategorie' }).click()
    await page.getByRole('button', { name: 'Dodaj kategorię' }).click()

    const uniqueName = `E2E Kategoria ${Date.now()}`
    await page.getByPlaceholder('np. Gołębie wyścigowe').fill(uniqueName)
    await page.getByPlaceholder('Dodaj opis kategorii...').fill('Opis testowy E2E.')
    await page.getByRole('button', { name: 'Dodaj kategorię' }).last().click()

    await expect(page.getByText(uniqueName)).toBeVisible({ timeout: 10000 })

    await page.locator('tr', { hasText: uniqueName }).getByRole('button', { name: 'Usuń' }).click()
    await page.getByRole('button', { name: 'Usuń', exact: true }).last().click()

    await expect(page.getByText(uniqueName)).toHaveCount(0, { timeout: 10000 })
  })

  test('panel admina: upload avatara uzytkownika przez modal edycji faktycznie zapisuje plik', async ({
    page,
  }) => {
    // wysylal base64 string w JSON (walidacja updateUser() go nie znala i
    const seller = await registerThrowawaySeller(page)

    await loginViaApi(page, E2E_ADMIN)
    await page.goto('/admin')
    await acceptCookies(page)
    await page.reload()

    await page.getByRole('button', { name: 'Użytkownicy' }).click()
    const row = page.locator('tr', { hasText: seller.name })
    await expect(row).toBeVisible({ timeout: 10000 })
    await row.getByRole('button', { name: 'Edytuj' }).click()

    await page.locator('input[type="file"]').setInputFiles(SAMPLE_IMAGE)

    const updateResponsePromise = page.waitForResponse(
      (res) =>
        res.url().includes(`/api/admin/users/${seller.id}`) && res.request().method() === 'POST'
    )
    await page.getByRole('button', { name: 'Zapisz zmiany' }).click()
    const updateResponse = await updateResponsePromise
    expect(updateResponse.ok(), await updateResponse.text()).toBeTruthy()
    const avatarPath = (await updateResponse.json()).user.avatar
    expect(avatarPath).toBeTruthy()

    const fileRes = await page.request.get(`/storage/${avatarPath}`)
    expect(fileRes.ok()).toBeTruthy()
  })

  test('panel admina: edycja tresci strony statycznej (Regulamin) jest widoczna publicznie', async ({
    page,
  }) => {
    const { token: adminToken } = await loginViaApi(page, E2E_ADMIN)
    const before = await page.request.get('/api/pages/terms')
    const originalContent = (await before.json()).data.content

    try {
      await page.goto('/admin')
      await acceptCookies(page)
      await page.reload()

      await page.getByRole('button', { name: 'Strony' }).click()
      await page
        .getByRole('button', { name: /Regulamin serwisu/i })
        .first()
        .click()

      const uniqueContent = `E2E tresc regulaminu ${Date.now()}`
      const textarea = page.locator('textarea')
      await textarea.fill(uniqueContent)
      await page.getByRole('button', { name: 'Zapisz zmiany' }).click()

      await expect(page.getByText('Strona została zapisana')).toBeVisible({ timeout: 10000 })

      await page.goto('/terms')
      await expect(page.getByText(uniqueContent)).toBeVisible({ timeout: 10000 })
    } finally {
      await page.request.patch('/api/admin/pages/terms', {
        headers: { Authorization: `Bearer ${adminToken}` },
        data: { content: originalContent },
      })
    }
  })

  test('tryb konserwacji blokuje ruch dla goscia ale nie dla admina', async ({ page }) => {
    const adminToken = await getAdminToken(page)
    const restore = await withPlatformSetting(page, adminToken, 'maintenance_mode', true)

    try {
      const guestRes = await page.request.get('/api/settings')
      expect(guestRes.ok()).toBeTruthy()

      const guestBlocked = await page.request.get('/api/auctions')
      expect(guestBlocked.status()).toBe(503)

      const adminAllowed = await page.request.get('/api/auctions', {
        headers: { Authorization: `Bearer ${adminToken}` },
      })
      expect(adminAllowed.ok()).toBeTruthy()
    } finally {
      await restore()
    }
  })

  test('panel admina: edycja danych golebia (tytul/rasa/opis) w modalu aukcji faktycznie zapisuje sie', async ({
    page,
  }) => {
    const adminToken = await getAdminToken(page)
    const seller = await registerThrowawaySeller(page)
    const uniqueTitle = `E2E Aukcja Przed Edycja ${Date.now()}`
    const auctionRes = await page.request.post('/api/admin/auctions-with-listing', {
      headers: { Authorization: `Bearer ${adminToken}` },
      data: {
        user_id: seller.id,
        status: 'pending',
        type: 'auction',
        start_price: 50,
        title: uniqueTitle,
        breed: 'Janssen',
      },
    })
    const auctionId = (await auctionRes.json()).auction.id

    try {
      await loginViaApi(page, E2E_ADMIN)
      await page.goto('/admin')
      await acceptCookies(page)
      await page.reload()

      await page.getByRole('button', { name: 'Aukcje' }).click()
      const row = page.locator('tr', { hasText: uniqueTitle })
      await expect(row).toBeVisible({ timeout: 10000 })
      await row.getByRole('button', { name: 'Edytuj' }).click()

      const newTitle = `E2E Aukcja Po Edycji ${Date.now()}`
      await page.getByPlaceholder('np. Biały Samiec Dresden').fill(newTitle)
      await page.locator('textarea').first().fill('Zaktualizowany opis E2E golebia.')

      const updateResponsePromise = page.waitForResponse(
        (res) =>
          res.url().includes(`/api/admin/auctions/${auctionId}`) &&
          res.request().method() === 'PATCH'
      )
      await page.getByRole('button', { name: 'Zapisz zmiany' }).click()
      const updateResponse = await updateResponsePromise
      expect(updateResponse.ok(), await updateResponse.text()).toBeTruthy()

      const check = await page.request.get(`/api/auctions/${auctionId}`)
      const auctionData = (await check.json()).data
      expect(auctionData.title).toBe(newTitle)
      expect(auctionData.description).toBe('Zaktualizowany opis E2E golebia.')
    } finally {
      await page.request
        .delete(`/api/admin/auctions/${auctionId}`, {
          headers: { Authorization: `Bearer ${adminToken}` },
        })
        .catch(() => {})
    }
  })

  test('panel admina: dodanie aukcji "Kup teraz" dla wybranego uzytkownika przez formularz, mimo blokady wystawiania w ustawieniach', async ({
    page,
  }) => {
    const adminToken = await getAdminToken(page)
    const restoreOnlyAdmin = await withPlatformSetting(
      page,
      adminToken,
      'only_admin_can_list',
      true
    )
    let createdAuctionId = null

    try {
      const seller = await registerThrowawaySeller(page)

      await loginViaApi(page, E2E_ADMIN)
      await page.goto('/admin')
      await acceptCookies(page)
      await page.reload()

      await page.getByRole('button', { name: 'Aukcje' }).click()
      await page.getByRole('button', { name: 'Dodaj aukcję' }).click()
      await expect(page.getByRole('heading', { name: 'Dodaj aukcję' })).toBeVisible({
        timeout: 5000,
      })

      await page.locator('select').filter({ hasText: 'Licytacja' }).selectOption('buy_now')

      const uniqueTitle = `E2E Kup Teraz ${Date.now()}`
      await page.getByPlaceholder('np. Biały Samiec Dresden').fill(uniqueTitle)
      await page.getByPlaceholder('np. Mondain, Saksoman, Posłaniec').fill('Janssen')

      await page
        .locator('xpath=//label[normalize-space(text())="Sprzedawca"]/following-sibling::select[1]')
        .selectOption(String(seller.id))

      await page
        .locator(
          'xpath=//label[normalize-space(text())="Cena startowa"]/following-sibling::div[1]//input'
        )
        .fill('250')

      const createResponsePromise = page.waitForResponse(
        (res) =>
          res.url().includes('/api/admin/auctions-with-listing') &&
          res.request().method() === 'POST'
      )
      await page.getByRole('button', { name: 'Dodaj aukcję', exact: true }).last().click()
      const createResponse = await createResponsePromise
      expect(createResponse.ok(), await createResponse.text()).toBeTruthy()
      const created = (await createResponse.json()).auction
      createdAuctionId = created.id

      expect(created.type).toBe('buy_now')
      expect(created.user_id).toBe(seller.id)
      expect(created.status).toBe('pending')

      const row = page.locator('tr', { hasText: uniqueTitle })
      await expect(row).toBeVisible({ timeout: 10000 })
      await expect(row.getByText('Oczekuje')).toBeVisible()

      await row.getByRole('button', { name: 'Aktywuj' }).click()
      await expect(page.getByText('Aktywuj aukcję')).toBeVisible({ timeout: 5000 })

      const activateResponsePromise = page.waitForResponse(
        (res) =>
          res.url().includes(`/api/admin/auctions/${created.id}/activate`) &&
          res.request().method() === 'POST'
      )
      await page.getByRole('button', { name: 'Potwierdź', exact: true }).click()
      const activateResponse = await activateResponsePromise
      expect(activateResponse.ok(), await activateResponse.text()).toBeTruthy()

      await expect(row.getByText('Aktywna')).toBeVisible({ timeout: 10000 })
    } finally {
      await restoreOnlyAdmin()
      if (createdAuctionId) {
        await page.request
          .delete(`/api/admin/auctions/${createdAuctionId}`, {
            headers: { Authorization: `Bearer ${adminToken}` },
          })
          .catch(() => {})
      }
    }
  })

  test('panel admina: nadanie userowi wyjatku pozwala mu SAMEMU wystawic "Kup teraz" mimo blokady, admin potem zatwierdza', async ({
    page,
  }) => {
    const adminToken = await getAdminToken(page)
    const restoreOnlyAdmin = await withPlatformSetting(
      page,
      adminToken,
      'only_admin_can_list',
      true
    )
    let createdAuctionId = null

    try {
      const seller = await registerThrowawaySeller(page)

      await loginViaApi(page, E2E_ADMIN)
      await page.goto('/admin')
      await acceptCookies(page)
      await page.reload()

      await page.getByRole('button', { name: 'Użytkownicy' }).click()
      const userRow = page.locator('tr', { hasText: seller.name })
      await expect(userRow).toBeVisible({ timeout: 10000 })
      await userRow.getByRole('button', { name: 'Edytuj' }).click()

      await expect(page.getByText('Wyjątek: może wystawiać mimo blokady')).toBeVisible({
        timeout: 5000,
      })
      const updateUserResponsePromise = page.waitForResponse(
        (res) => res.url().includes('/api/admin/users/') && res.request().method() === 'PATCH'
      )
      const checkboxNearText = (text) =>
        page.locator(
          `xpath=//*[contains(text(), "${text}")]/ancestor::div[contains(@class, "justify-between")][1]//input[@type="checkbox"]`
        )
      await checkboxNearText('Wyjątek: może wystawiać mimo blokady').check({ force: true })
      await checkboxNearText('Status konta').check({ force: true })
      await page.getByRole('button', { name: 'Zapisz zmiany' }).click()
      const updateUserResponse = await updateUserResponsePromise
      expect(updateUserResponse.ok(), await updateUserResponse.text()).toBeTruthy()

      const checkUser = await page.request.get('/api/admin/users', {
        headers: { Authorization: `Bearer ${adminToken}` },
      })
      const sellerFromApi = (await checkUser.json()).data.find((u) => u.id === seller.id)
      expect(sellerFromApi.can_list_when_restricted).toBe(true)

      const { token: sellerToken } = await loginViaApi(page, {
        name: seller.name,
        password: seller.password,
      })

      await page.goto('/create-auction')
      await acceptCookies(page)
      await page.reload()

      await expect(page.getByText('Licytacja', { exact: true })).toHaveCount(0)
      await expect(
        page.getByText('Twoje konto może obecnie wystawiać wyłącznie w trybie "Kup teraz".')
      ).toBeVisible()

      const uploadResponsePromise = page.waitForResponse((res) =>
        res.url().includes('/api/images/upload')
      )
      await page.locator('input[type="file"]').first().setInputFiles(SAMPLE_IMAGE)
      await uploadResponsePromise

      const uniqueTitle = `E2E Wyjatek Kup Teraz ${Date.now()}`
      await page
        .getByPlaceholder('np. Piękny biały gołąb pocztowy - champion wystaw')
        .fill(uniqueTitle)
      await page.getByPlaceholder('np. Warszawski, Sokólnik, Braszławski...').fill('Janssen')
      await page.locator('select').filter({ hasText: 'Wybierz płeć' }).selectOption('samiec')
      await page.locator('select').filter({ hasText: 'Wybierz wielkość' }).selectOption('sredni')
      await page.locator('select').filter({ hasText: 'Wybierz barwę' }).selectOption({ index: 1 })
      await page.getByPlaceholder('100').fill('120')
      await page.getByRole('checkbox').check()

      const createAuctionResponsePromise = page.waitForResponse(
        (res) => res.url().includes('/api/auctions') && res.request().method() === 'POST'
      )
      await page.getByRole('button', { name: /Utwórz aukcję/i }).click()
      const createAuctionResponse = await createAuctionResponsePromise
      expect(createAuctionResponse.ok(), await createAuctionResponse.text()).toBeTruthy()
      const created = (await createAuctionResponse.json()).data
      createdAuctionId = created.id

      expect(created.type).toBe('buy_now')
      expect(created.status).toBe('pending')

      await expect(page).toHaveURL(/\/profile\?tab=my-auctions/, { timeout: 15000 })
      await expect(page.getByText(uniqueTitle)).toBeVisible({ timeout: 10000 })

      // Krok 3: admin pozniej zatwierdza (osobny krok, "ja pozniej musze
      await loginViaApi(page, E2E_ADMIN)
      await page.goto('/admin')
      await acceptCookies(page)
      await page.reload()

      await page.getByRole('button', { name: 'Aukcje' }).click()
      const auctionRow = page.locator('tr', { hasText: uniqueTitle })
      await expect(auctionRow).toBeVisible({ timeout: 10000 })
      await expect(auctionRow.getByText('Oczekuje')).toBeVisible()

      await auctionRow.getByRole('button', { name: 'Aktywuj' }).click()
      await expect(page.getByText('Aktywuj aukcję')).toBeVisible({ timeout: 5000 })
      const activateResponsePromise = page.waitForResponse(
        (res) =>
          res.url().includes(`/api/admin/auctions/${created.id}/activate`) &&
          res.request().method() === 'POST'
      )
      await page.getByRole('button', { name: 'Potwierdź', exact: true }).click()
      const activateResponse = await activateResponsePromise
      expect(activateResponse.ok(), await activateResponse.text()).toBeTruthy()

      await expect(auctionRow.getByText('Aktywna')).toBeVisible({ timeout: 10000 })
    } finally {
      await restoreOnlyAdmin()
      if (createdAuctionId) {
        await page.request
          .delete(`/api/admin/auctions/${createdAuctionId}`, {
            headers: { Authorization: `Bearer ${adminToken}` },
          })
          .catch(() => {})
      }
    }
  })
})
