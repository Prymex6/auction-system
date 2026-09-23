import { test, expect } from '@playwright/test'
import { acceptCookies, loginViaApi, getAuctionByTitle, E2E_USER } from './helpers.js'

test.describe('Lista obserwowanych (localStorage)', () => {
  test('dodanie i usuniecie aukcji z obserwowanych ze strony szczegolow', async ({ page }) => {
    const auction = await getAuctionByTitle(page, 'E2E Test Aukcja Licytacyjna')
    expect(auction).toBeTruthy()

    await loginViaApi(page, E2E_USER)
    await page.evaluate(() => localStorage.removeItem('auctionWishlist'))
    await page.goto(`/auctions/${auction.id}`)
    await acceptCookies(page)
    await page.reload()

    // Przycisk ma tekst dostepny (sr-only "Zapisz" + dymek podpowiedzi
    const wishlistButton = page.getByRole('button', { name: /Zapisz/ })
    await wishlistButton.click()
    await expect(page.getByText('Dodano do obserwowanych')).toBeVisible({ timeout: 5000 })

    const stored = await page.evaluate(() =>
      JSON.parse(localStorage.getItem('auctionWishlist') || '[]')
    )
    expect(stored).toContain(auction.id)

    await wishlistButton.click()
    await expect(page.getByText('Usunięto z obserwowanych')).toBeVisible({ timeout: 5000 })

    const storedAfter = await page.evaluate(() =>
      JSON.parse(localStorage.getItem('auctionWishlist') || '[]')
    )
    expect(storedAfter).not.toContain(auction.id)
  })

  test('filtr "Twoja lista obserwowanych" na /auctions pokazuje tylko zapisane aukcje', async ({
    page,
  }) => {
    const auction = await getAuctionByTitle(page, 'E2E Test Aukcja Licytacyjna')
    expect(auction).toBeTruthy()

    await loginViaApi(page, E2E_USER)
    await page.evaluate(
      (id) => localStorage.setItem('auctionWishlist', JSON.stringify([id])),
      auction.id
    )

    await page.goto('/auctions?watchlist=1')
    await acceptCookies(page)
    await page.reload()

    await expect(page.getByRole('heading', { name: 'Twoja lista obserwowanych' })).toBeVisible()
    await expect(page.getByText(auction.title)).toBeVisible()
  })
})
