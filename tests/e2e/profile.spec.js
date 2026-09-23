import { test, expect } from '@playwright/test'
import { acceptCookies, loginViaApi, registerViaApi, E2E_USER, SAMPLE_IMAGE } from './helpers.js'

test.describe('Zarządzanie profilem', () => {
  test('upload avatara faktycznie zapisuje plik (regresja: kolumna avatar + PATCH+multipart)', async ({
    page,
  }) => {
    // Podwojna regresja znaleziona podczas audytu:
    // 2) nawet po naprawie (1), prawdziwy HTTP PATCH z multipart/form-data
    //    POST) - front musial przejsc na POST + spoofing metody `_method=PATCH`.
    // PHPUnit (Illuminate\Foundation\Testing) konstruuje Request bezposrednio
    const { token } = await loginViaApi(page, E2E_USER)
    await page.goto('/profile')
    await acceptCookies(page)
    await page.reload()

    await page.locator('input[type="file"]').setInputFiles(SAMPLE_IMAGE)

    const updateResponsePromise = page.waitForResponse(
      (res) => res.url().includes('/api/profile') && res.request().method() !== 'GET'
    )
    await page.getByRole('button', { name: 'Zapisz zmiany' }).click()
    const updateResponse = await updateResponsePromise
    expect(updateResponse.ok(), await updateResponse.text()).toBeTruthy()

    await expect(page.getByText('Profil został zaktualizowany!')).toBeVisible({ timeout: 10000 })

    const meRes = await page.request.get('/api/auth/me', {
      headers: { Authorization: `Bearer ${token}` },
    })
    const avatarPath = (await meRes.json()).user.avatar
    expect(avatarPath).toBeTruthy()

    const fileRes = await page.request.get(`/storage/${avatarPath}`)
    expect(fileRes.ok()).toBeTruthy()

    await page.reload()
    await expect(page.locator('img[alt="Zdjęcie profilowe"]')).toBeVisible({ timeout: 10000 })
  })

  test('edycja podstawowych informacji (bio) zapisuje sie i jest widoczna po przeladowaniu', async ({
    page,
  }) => {
    await loginViaApi(page, E2E_USER)
    await page.goto('/profile')
    await acceptCookies(page)
    await page.reload()

    const uniqueBio = `E2E testowe bio ${Date.now()}`
    await page.locator('textarea').first().fill(uniqueBio)
    await page.getByRole('button', { name: 'Zapisz zmiany' }).click()

    await expect(page.getByText('Profil został zaktualizowany!')).toBeVisible({ timeout: 10000 })

    await page.reload()
    await expect(page.locator('textarea').first()).toHaveValue(uniqueBio)
  })

  test('zmiana hasla dziala end-to-end: stare haslo przestaje dzialac, nowe pozwala sie zalogowac', async ({
    page,
  }) => {
    const { payload } = await registerViaApi(page)
    await loginViaApi(page, { name: payload.name, password: payload.password })

    await page.goto('/profile')
    await acceptCookies(page)
    await page.reload()

    const newPassword = 'NoweHaslo456!'
    const passwordInputs = page.locator('input[type="password"]')
    await passwordInputs.nth(0).fill(payload.password)
    await passwordInputs.nth(1).fill(newPassword)
    await passwordInputs.nth(2).fill(newPassword)
    await page.getByRole('button', { name: 'Zmień hasło' }).click()

    await expect(page.getByText('Hasło zostało zmienione!')).toBeVisible({ timeout: 10000 })

    const oldPasswordLogin = await page.request.post('/api/auth/login', {
      data: { login: payload.name, password: payload.password },
    })
    expect(oldPasswordLogin.status()).toBe(401)

    const newPasswordLogin = await page.request.post('/api/auth/login', {
      data: { login: payload.name, password: newPassword },
    })
    expect(newPasswordLogin.ok()).toBeTruthy()
  })

  test('eksport danych (RODO) pobiera prawdziwy plik JSON z danymi konta', async ({ page }) => {
    await loginViaApi(page, E2E_USER)
    await page.goto('/profile')
    await acceptCookies(page)
    await page.reload()

    await page.getByRole('button', { name: 'Ustawienia', exact: true }).click()

    const downloadPromise = page.waitForEvent('download')
    await page.getByRole('button', { name: 'Eksportuj moje dane' }).click()
    const download = await downloadPromise

    expect(download.suggestedFilename()).toMatch(/^moje-dane-golebiowylot-\d+\.json$/)

    const stream = await download.createReadStream()
    const chunks = []
    for await (const chunk of stream) chunks.push(chunk)
    const content = JSON.parse(Buffer.concat(chunks).toString('utf-8'))

    expect(content.konto.email).toBe(E2E_USER.email)
    expect(content).toHaveProperty('aukcje_wystawione')
    expect(content).toHaveProperty('wiadomosci_wyslane')
  })
})
