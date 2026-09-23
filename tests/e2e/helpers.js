import path from 'node:path'
import { fileURLToPath } from 'node:url'
import { expect } from '@playwright/test'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

export const E2E_ADMIN = {
  name: 'e2e_admin',
  email: 'e2e_admin@example.com',
  password: 'E2ETestPass123!',
}
export const E2E_USER = {
  name: 'e2e_user',
  email: 'e2e_user@example.com',
  password: 'E2ETestPass123!',
}
export const E2E_SELLER = {
  name: 'e2e_seller',
  email: 'e2e_seller@example.com',
  password: 'E2ETestPass123!',
}

export const SAMPLE_IMAGE = path.resolve(__dirname, '../../public/images/pigeons/pigeon-06.jpg')

export async function acceptCookies(page) {
  await page.evaluate(() => {
    localStorage.setItem(
      'pigeon_cookie_consent_v1',
      JSON.stringify({ necessary: true, analytics: false, savedAt: new Date().toISOString() })
    )
  })
}

/**
 * uzywane gdy test ma zweryfikowac sam przeplyw logowania.
 */
export async function loginViaUi(page, { name, password }) {
  await page.goto('/login')
  await acceptCookies(page)
  await page.getByPlaceholder(/nazwa_uzytkownika|email/i).fill(name)
  await page.locator('input[type="password"]').first().fill(password)
  await page.getByRole('button', { name: /Zaloguj się/i }).click()
  await expect(page).toHaveURL(/\/$|\/profile/, { timeout: 10000 })
}

export async function loginViaApi(page, { name, password }) {
  await page.goto('/')
  const response = await page.request.post('/api/auth/login', {
    data: { login: name, password },
  })
  const body = await response.json()
  if (!body.token) {
    throw new Error(`loginViaApi failed for ${name}: ${JSON.stringify(body)}`)
  }
  await page.evaluate((token) => localStorage.setItem('auth_token', token), body.token)
  await acceptCookies(page)
  return body
}

export async function registerViaApi(page, overrides = {}) {
  const suffix = Date.now() + Math.floor(Math.random() * 1000)
  const payload = {
    name: `e2etest${suffix}`,
    first_name: 'E2E',
    last_name: 'Generated',
    email: `e2etest${suffix}@example.com`,
    password: 'TestHaslo123!',
    password_confirmation: 'TestHaslo123!',
    terms: true,
    ...overrides,
  }
  const response = await page.request.post('/api/auth/register', { data: payload })
  const body = await response.json()
  return { payload, response, body }
}

export async function withPlatformSetting(page, adminToken, key, value) {
  const before = await page.request.get('/api/settings')
  const beforeData = (await before.json()).data
  const previous = beforeData[key]

  await page.request.patch('/api/admin/settings', {
    headers: { Authorization: `Bearer ${adminToken}` },
    data: { [key]: value },
  })

  return async () => {
    await page.request.patch('/api/admin/settings', {
      headers: { Authorization: `Bearer ${adminToken}` },
      data: { [key]: previous },
    })
  }
}

/**
 * Rejestruje jednorazowego, throwaway sellera (nie E2E_SELLER!) do testow
 */
export async function registerThrowawaySeller(page) {
  const suffix = Date.now() + Math.floor(Math.random() * 1000)
  const name = `e2eseller${suffix}`
  const password = 'TestHaslo123!'
  const res = await page.request.post('/api/auth/register', {
    data: {
      name,
      first_name: 'Throwaway',
      last_name: 'Seller',
      email: `${name}@example.com`,
      password,
      password_confirmation: password,
      terms: true,
    },
  })
  const body = await res.json()
  return { id: body.user.id, name, password }
}

export async function getAdminToken(page) {
  const response = await page.request.post('/api/auth/login', {
    data: { login: E2E_ADMIN.name, password: E2E_ADMIN.password },
  })
  return (await response.json()).token
}

/**
 * zagniezdzona data.data jak niektore inne endpointy) - stad ten wspolny
 */
export async function getAuctionByTitle(page, title) {
  const res = await page.request.get('/api/auctions', { params: { search: title, per_page: 5 } })
  const body = await res.json()
  const list = Array.isArray(body.data) ? body.data : body.data?.data || []
  return list.find((a) => a.title === title)
}
