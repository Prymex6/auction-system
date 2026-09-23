/**
 * Take the screenshots the README uses, against a running instance.
 *
 *   SEED_ADMIN_PASSWORD=... php artisan db:seed
 *   php artisan serve --port=8000
 *   node scripts/screenshots.mjs
 *
 * Shots land in .github/images at twice the pixel density, so they stay sharp
 * on the displays most people read GitHub on.
 */
import { chromium } from 'playwright'
import { mkdir } from 'node:fs/promises'

const SITE = process.env.SCREENSHOT_URL ?? 'http://localhost:8000'
const EMAIL = process.env.SCREENSHOT_EMAIL ?? 'admin@example.com'
const PASSWORD = process.env.SCREENSHOT_PASSWORD ?? 'demo'
const OUT = '.github/images'

/** The consent banner covers the lower third of every page until answered. */
const dismissCookies = async (page) => {
  const accept = page.getByRole('button', { name: /Akceptuj/i }).first()
  if (await accept.count()) {
    await accept.click().catch(() => {})
    await page.waitForTimeout(500)
  }
}

const settle = async (page) => {
  await page.waitForLoadState('networkidle').catch(() => {})
  await page.waitForTimeout(1200)
  await dismissCookies(page)
}

/**
 * Bring a section into view without the fixed header sitting on top of it.
 *
 * Scrolling an element to the top of the window puts it underneath the header,
 * which is what made earlier shots look broken, so the header's own height is
 * measured and taken off.
 */
const bringIntoView = async (page, selector) => {
  await page.evaluate((target) => {
    const node = document.querySelector(target)
    if (!node) return

    const stuck = [...document.querySelectorAll('header, nav')].find((element) => {
      const style = getComputedStyle(element)
      return style.position === 'fixed' || style.position === 'sticky'
    })
    const margin = (stuck?.getBoundingClientRect().height ?? 0) + 24

    window.scrollTo({ top: node.getBoundingClientRect().top + window.scrollY - margin })
  }, selector)
  await page.waitForTimeout(800)
}

const shoot = async (page, file) => {
  await page.screenshot({ path: `${OUT}/${file}` })
  console.log('zapisano', file)
}

const run = async () => {
  await mkdir(OUT, { recursive: true })

  const browser = await chromium.launch()
  const context = await browser.newContext({
    viewport: { width: 1440, height: 900 },
    deviceScaleFactor: 2,
    locale: 'pl-PL',
  })
  const page = await context.newPage()

  await page.goto(`${SITE}/`, { waitUntil: 'domcontentloaded' })
  await settle(page)
  await shoot(page, 'home.png')

  // The listings themselves, not the hero above them.
  await page.goto(`${SITE}/auctions`, { waitUntil: 'domcontentloaded' })
  await settle(page)
  await bringIntoView(page, 'a[href^="/auctions/"]')
  await shoot(page, 'auctions.png')

  // One listing, from the top, so the photographs are in frame. The address
  // is read off the first card rather than guessed, so the shot keeps working
  // whatever the sample data happens to be numbered.
  const href = await page
    .locator('a[href^="/auctions/"]')
    .first()
    .getAttribute('href')
    .catch(() => null)
  if (href) {
    await page.goto(`${SITE}${href}`, { waitUntil: 'domcontentloaded' })
    await settle(page)
    await page.evaluate(() => window.scrollTo(0, 0))
    await page.waitForTimeout(600)
    await shoot(page, 'auction-detail.png')
  }

  await page.goto(`${SITE}/login`, { waitUntil: 'domcontentloaded' })
  await settle(page)
  await shoot(page, 'login.png')

  // Signed in, for the parts a visitor never sees.
  await page.fill('form input[type="text"]', EMAIL)
  await page.fill('input[type="password"]', PASSWORD)
  await page.click('button[type="submit"]')
  await page.waitForTimeout(3000)

  await page.goto(`${SITE}/admin`, { waitUntil: 'domcontentloaded' })
  await settle(page)
  await bringIntoView(page, 'table')
  await shoot(page, 'admin.png')

  await browser.close()
}

run().catch((error) => {
  console.error(error)
  process.exit(1)
})
