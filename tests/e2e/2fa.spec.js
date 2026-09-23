import { execSync } from 'node:child_process'
import { test, expect } from '@playwright/test'
import { acceptCookies, loginViaApi, registerViaApi } from './helpers.js'

test.describe('Weryfikacja dwuetapowa (2FA)', () => {
  test('pelny cykl: wlaczenie przez UI z prawdziwym kodem TOTP, logowanie wymaga 2FA, wylaczenie', async ({
    page,
  }) => {
    const { payload } = await registerViaApi(page)
    const verifyLink = execSync(`php artisan e2e:verify-link ${payload.email}`, {
      encoding: 'utf8',
    }).trim()
    await page.goto(verifyLink)

    await loginViaApi(page, { name: payload.name, password: payload.password })
    await page.goto('/profile')
    await acceptCookies(page)
    await page.reload()

    await page.getByRole('button', { name: 'Włącz 2FA' }).click()
    await expect(page.getByText('Włącz weryfikację dwuetapową')).toBeVisible({ timeout: 10000 })

    const secretText = await page.locator('p.font-mono.text-sm.bg-gray-100').first().innerText()
    expect(secretText).not.toBe('brak')

    const totpCode = execSync(`php artisan e2e:totp-code ${secretText}`, {
      encoding: 'utf8',
    }).trim()
    await page.locator('input[placeholder="123456"]').fill(totpCode)

    const confirmResponsePromise = page.waitForResponse((res) =>
      res.url().includes('/api/auth/2fa/confirm')
    )
    await page.getByRole('button', { name: 'Włącz 2FA' }).last().click()
    const confirmResponse = await confirmResponsePromise
    expect(confirmResponse.ok(), await confirmResponse.text()).toBeTruthy()

    await expect(page.getByRole('button', { name: 'Wyłącz 2FA' })).toBeVisible({ timeout: 10000 })

    const loginRes = await page.request.post('/api/auth/login', {
      data: { login: payload.name, password: payload.password },
    })
    const loginBody = await loginRes.json()
    expect(loginBody.requires_2fa).toBe(true)
    expect(loginBody.token).toBeUndefined()

    await page.goto('/login')
    await page.evaluate(() => localStorage.clear())
    await page.reload()
    await acceptCookies(page)
    await page.getByPlaceholder(/nazwa_uzytkownika|email/i).fill(payload.name)
    await page.locator('input[type="password"]').first().fill(payload.password)
    await page.getByRole('button', { name: /Zaloguj się/i }).click()

    await expect(page.getByText('Weryfikacja 2FA')).toBeVisible({ timeout: 10000 })
    const loginTotpCode = execSync(`php artisan e2e:totp-code ${secretText}`, {
      encoding: 'utf8',
    }).trim()
    await page.locator('input[placeholder="000000"]').fill(loginTotpCode)
    await page.getByRole('button', { name: 'Weryfikuj' }).click()
    await expect(page).toHaveURL(/\/$|\/profile/, { timeout: 10000 })
    // ponownego odbicia do /login?session_expired=1.
    await page.goto('/profile')
    await acceptCookies(page)
    await page.reload()
    await expect(page.getByRole('button', { name: 'Wyłącz 2FA' })).toBeVisible({ timeout: 10000 })

    await page.getByRole('button', { name: 'Wyłącz 2FA' }).click()
    await expect(page.getByText('Wyłącz weryfikację dwuetapową')).toBeVisible({ timeout: 10000 })
    // lapie pole w modalu wylaczania 2FA (dodane pozniej w DOM).
    await page.locator('input[placeholder="••••••••"]').last().fill(payload.password)
    await page.getByRole('button', { name: 'Wyłącz 2FA' }).last().click()

    await expect(page.getByRole('button', { name: 'Włącz 2FA' })).toBeVisible({ timeout: 10000 })

    const loginAfterDisable = await page.request.post('/api/auth/login', {
      data: { login: payload.name, password: payload.password },
    })
    expect((await loginAfterDisable.json()).requires_2fa).toBeUndefined()
  })
})
