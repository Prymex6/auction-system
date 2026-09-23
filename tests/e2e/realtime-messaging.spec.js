import { test, expect } from '@playwright/test'
import { acceptCookies, loginViaApi, E2E_USER, E2E_SELLER } from './helpers.js'

async function getUserId(page, token) {
  const res = await page.request.get('/api/auth/me', {
    headers: { Authorization: `Bearer ${token}` },
  })
  return (await res.json()).user.id
}

test.describe('Wiadomosci na zywo (WebSocket/Reverb)', () => {
  test('nowa wiadomosc pojawia sie na zywo w OTWARTEJ rozmowie, bez recznego odswiezenia', async ({
    browser,
  }) => {
    const senderCtx = await browser.newContext()
    const recipientCtx = await browser.newContext()
    const senderPage = await senderCtx.newPage()
    const recipientPage = await recipientCtx.newPage()

    try {
      const { token: senderToken } = await loginViaApi(senderPage, E2E_USER)
      const { token: recipientToken } = await loginViaApi(recipientPage, E2E_SELLER)
      const senderId = await getUserId(senderPage, senderToken)
      const recipientId = await getUserId(recipientPage, recipientToken)

      await recipientPage.goto(`/messages?user=${senderId}`)
      await acceptCookies(recipientPage)
      await recipientPage.reload()
      await expect(recipientPage.getByPlaceholder('Napisz wiadomość...')).toBeVisible({
        timeout: 10000,
      })

      await senderPage.goto(`/messages?user=${recipientId}`)
      await acceptCookies(senderPage)
      await senderPage.reload()
      const uniqueMessage = `Realtime E2E ${Date.now()}`
      const textarea = senderPage.getByPlaceholder('Napisz wiadomość...')
      await expect(textarea).toBeVisible({ timeout: 10000 })
      await textarea.fill(uniqueMessage)
      await senderPage.getByRole('button', { name: 'Wyślij' }).click()

      // Locator zawezony do dymka czatu (nie generyczny getByText) - od Fazy 24
      await expect(
        recipientPage.locator('p.text-base.leading-relaxed', { hasText: uniqueMessage })
      ).toBeVisible({ timeout: 10000 })

      // latwo przeoczyc (przewiniety czat, zajety czyms innym) - toast daje
      await expect(recipientPage.getByText(/Nowa wiadomość od/)).toBeVisible({ timeout: 10000 })

      await recipientPage.getByPlaceholder('Napisz wiadomość...').click()
      await expect(recipientPage.getByText(/Nowa wiadomość od/)).not.toBeVisible({ timeout: 2000 })
    } finally {
      await senderCtx.close()
      await recipientCtx.close()
    }
  })

  test('licznik nieprzeczytanych wiadomosci i tytul karty aktualizuja sie na zywo, gdy odbiorca NIE jest w danej rozmowie', async ({
    browser,
  }) => {
    const senderCtx = await browser.newContext()
    const recipientCtx = await browser.newContext()
    const senderPage = await senderCtx.newPage()
    const recipientPage = await recipientCtx.newPage()

    try {
      const { token: senderToken } = await loginViaApi(senderPage, E2E_USER)
      const { token: recipientToken } = await loginViaApi(recipientPage, E2E_SELLER)
      const senderId = await getUserId(senderPage, senderToken)

      await recipientPage.goto('/')
      await acceptCookies(recipientPage)
      await recipientPage.reload()
      await expect(recipientPage.locator('nav')).toBeVisible({ timeout: 10000 })

      const recipientId = await getUserId(recipientPage, recipientToken)
      await senderPage.goto(`/messages?user=${recipientId}`)
      await acceptCookies(senderPage)
      await senderPage.reload()
      const uniqueMessage = `Badge E2E ${Date.now()}`
      const textarea = senderPage.getByPlaceholder('Napisz wiadomość...')
      await expect(textarea).toBeVisible({ timeout: 10000 })
      await textarea.fill(uniqueMessage)
      await senderPage.getByRole('button', { name: 'Wyślij' }).click()

      await expect(recipientPage.locator('a[href="/messages"] span.bg-red-500')).toBeVisible({
        timeout: 10000,
      })
      await expect(recipientPage).toHaveTitle(/^\(\d+\)/, { timeout: 10000 })
    } finally {
      await senderCtx.close()
      await recipientCtx.close()
    }
  })
})
