import { execSync } from 'node:child_process'
import { test, expect } from '@playwright/test'
import { acceptCookies, loginViaUi, registerViaApi, E2E_USER } from './helpers.js'

test.describe('Rejestracja i logowanie (E2E, prawdziwa przegladarka)', () => {
  test('nowy user moze sie zarejestrowac przez formularz UI', async ({ page }) => {
    const suffix = Date.now()
    await page.goto('/register')
    await acceptCookies(page)
    await page.reload()

    await page.getByPlaceholder('Jan', { exact: true }).fill('E2E')
    await page.getByPlaceholder('Kowalski', { exact: true }).fill('Formularz')
    await page.getByPlaceholder('jankowalski').fill(`e2eform${suffix}`)
    await page.getByPlaceholder('you@example.com').fill(`e2eform${suffix}@example.com`)
    await page.locator('input[type="password"]').first().fill('TestHaslo123!')
    await page.locator('input[type="password"]').nth(1).fill('TestHaslo123!')
    await page.getByRole('checkbox', { name: /regulaminem/i }).check()
    await page.getByRole('button', { name: /Utwórz konto|Zarejestruj/i }).click()

    await expect(page).toHaveURL(/\/(profile)?$/, { timeout: 10000 })
  })

  test('rejestracja z email juz istniejacym w bazie pokazuje blad', async ({ page }) => {
    await page.goto('/register')
    await acceptCookies(page)
    await page.reload()
    await page.getByPlaceholder('Jan', { exact: true }).fill('Duplikat')
    await page.getByPlaceholder('Kowalski', { exact: true }).fill('Test')
    await page.getByPlaceholder('jankowalski').fill(`inny${Date.now()}`)
    await page.getByPlaceholder('you@example.com').fill(E2E_USER.email)
    await page.locator('input[type="password"]').first().fill('TestHaslo123!')
    await page.locator('input[type="password"]').nth(1).fill('TestHaslo123!')
    await page.getByRole('checkbox', { name: /regulaminem/i }).check()
    await page.getByRole('button', { name: /Utwórz konto|Zarejestruj/i }).click()

    await expect(
      page.getByText(/e-?mail.*(zaj|istnieje|uzyt)|juz.*(zarejestrowan|istnieje)/i)
    ).toBeVisible({ timeout: 10000 })
    await expect(page).toHaveURL(/\/register/)
  })

  test('logowanie z prawidlowymi danymi dziala przez UI', async ({ page }) => {
    await loginViaUi(page, E2E_USER)
    await expect(page.getByText(E2E_USER.name, { exact: false }).first()).toBeVisible()
  })

  test('logowanie z bledym haslem pokazuje komunikat bledu', async ({ page }) => {
    await page.goto('/login')
    await acceptCookies(page)
    await page.reload()
    await page.getByPlaceholder(/nazwa_uzytkownika|email/i).fill(E2E_USER.name)
    await page.locator('input[type="password"]').first().fill('zdecydowanieZleHaslo')
    await page.getByRole('button', { name: /Zaloguj się/i }).click()

    await expect(page.getByText(/niepoprawne dane logowania/i)).toBeVisible({ timeout: 10000 })
    await expect(page).toHaveURL(/\/login/)
  })

  test('link weryfikacyjny email faktycznie potwierdza konto', async ({ page }) => {
    const { payload } = await registerViaApi(page)

    const loginResponse = await page.request.post('/api/auth/login', {
      data: { login: payload.name, password: payload.password },
    })
    const { token } = await loginResponse.json()

    const meBeforeResponse = await page.request.get('/api/auth/me', {
      headers: { Authorization: `Bearer ${token}` },
    })
    expect((await meBeforeResponse.json()).user.email_verified_at).toBeNull()

    // Generujemy PRAWDZIWY podpisany link weryfikacyjny (ten sam mechanizm
    // co VerifyEmailMail) i faktycznie w niego klikamy w przegladarce -
    const link = execSync(`php artisan e2e:verify-link ${payload.email}`, {
      encoding: 'utf8',
    }).trim()
    await page.goto(link)
    await expect(page).toHaveURL(/verified=1/)

    const meAfterResponse = await page.request.get('/api/auth/me', {
      headers: { Authorization: `Bearer ${token}` },
    })
    expect((await meAfterResponse.json()).user.email_verified_at).not.toBeNull()
  })
})
