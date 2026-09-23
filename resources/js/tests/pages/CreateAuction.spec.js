import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount, flushPromises } from '@vue/test-utils'
import { createRouter, createWebHistory } from 'vue-router'

const postMock = vi.fn()

vi.mock('@/services/api', () => ({
  default: {
    get: vi.fn().mockResolvedValue({ data: { data: [] } }),
    post: (...args) => postMock(...args),
    patch: vi.fn().mockResolvedValue({ data: {} }),
  },
}))

vi.mock('@/stores/auth', () => ({
  useAuthStore: () => ({ user: { id: 1, is_admin: false } }),
}))

vi.mock('@/composables/useSeo', () => ({ useSeo: vi.fn() }))

import CreateAuction from '@/pages/CreateAuction.vue'

function makeFile(name = 'pigeon.jpg') {
  return new File([new Uint8Array(1024)], name, { type: 'image/jpeg' })
}

async function selectFile(input, file) {
  Object.defineProperty(input.element, 'files', { value: [file], writable: true })
  await input.trigger('change')
}

async function selectFiles(input, files) {
  Object.defineProperty(input.element, 'files', { value: files, writable: true })
  await input.trigger('change')
}

async function fillRequiredFields(wrapper) {
  const fileInputs = wrapper.findAll('input[type="file"]')
  await selectFile(fileInputs[0], makeFile())
  await flushPromises()

  await wrapper.find('input[placeholder*="Piękny biały gołąb"]').setValue('Testowy tytul aukcji')
  await wrapper.find('input[placeholder*="Warszawski"]').setValue('Warszawski')

  const selects = wrapper.findAll('select')
  await selects[0].setValue('samiec')
  await selects[2].setValue('sredni')
  await selects[3].setValue('Niebieska')

  await wrapper.find('input[type="number"]').setValue(150)
  await wrapper.find('input[type="checkbox"]').setValue(true)
}

describe('CreateAuction.vue — wysylany payload', () => {
  let router

  beforeEach(async () => {
    postMock.mockReset()
    postMock.mockImplementation((url) => {
      if (url === '/images/upload') {
        return Promise.resolve({ data: { url: '/storage/images/auction/stub-image.jpg' } })
      }
      return Promise.resolve({ data: {} })
    })
    router = createRouter({
      history: createWebHistory(),
      routes: [
        { path: '/auctions/create', component: CreateAuction },
        { path: '/profile', component: { template: '<div />' } },
        { path: '/terms', component: { template: '<div />' } },
      ],
    })
    router.push('/auctions/create')
    await router.isReady()
  })

  it('mapuje auction_type na "type" i wysyla wszystkie pola pod poprawnymi nazwami', async () => {
    const wrapper = mount(CreateAuction, {
      global: { plugins: [router] },
    })
    await flushPromises()

    const fileInputs = wrapper.findAll('input[type="file"]')
    await selectFile(fileInputs[0], makeFile())
    await flushPromises()

    await wrapper.find('input[placeholder*="Piękny biały gołąb"]').setValue('Testowy tytul aukcji')
    await wrapper.find('input[placeholder*="Warszawski"]').setValue('Warszawski')

    const selects = wrapper.findAll('select')
    // Kolejnosc w template: Plec, Rok, Wielkosc, Barwa
    await selects[0].setValue('samiec')
    await selects[2].setValue('sredni')
    await selects[3].setValue('Niebieska')

    await wrapper.find('input[type="number"]').setValue(150)
    await wrapper.find('input[type="checkbox"]').setValue(true)

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    const createCall = postMock.mock.calls.find(([url]) => url === '/auctions')
    expect(createCall, JSON.stringify(postMock.mock.calls)).toBeTruthy()
    const [, payload] = createCall
    expect(payload.type).toBe('auction')
    expect(payload.auction_type).toBeUndefined()
    expect(payload.title).toBe('Testowy tytul aukcji')
    expect(payload.breed).toBe('Warszawski')
    expect(payload.gender).toBe('samiec')
    expect(payload.size).toBe('sredni')
    expect(payload.color).toBe('Niebieska')
    expect(payload.start_price).toBe(150)
    expect(payload.pigeon_images).toEqual(['/storage/images/auction/stub-image.jpg'])
  })

  it('klikniecie napisu "Akceptuję regulamin aukcji" przelacza checkbox (label/for byly rozlaczone)', async () => {
    const wrapper = mount(CreateAuction, {
      global: {
        plugins: [
          createRouter({
            history: createWebHistory(),
            routes: [
              { path: '/auctions/create', component: CreateAuction },
              { path: '/profile', component: { template: '<div />' } },
              { path: '/terms', component: { template: '<div />' } },
            ],
          }),
        ],
      },
    })
    await flushPromises()

    const checkbox = wrapper.find('input[type="checkbox"]')
    expect(checkbox.element.checked).toBe(false)

    const termsLabel = wrapper.findAll('label').find((l) => l.text().includes('Akceptuję'))
    await termsLabel.trigger('click')

    expect(checkbox.element.checked).toBe(true)
  })

  it('nie wysyla formularza i pokazuje bledy, gdy wymagane pola sa puste', async () => {
    const wrapper = mount(CreateAuction, { global: { plugins: [router] } })
    await flushPromises()

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(postMock.mock.calls.find(([url]) => url === '/auctions')).toBeFalsy()
    expect(wrapper.text()).toContain('Tytuł jest wymagany')
    expect(wrapper.text()).toContain('Rasa jest wymagana')
    expect(wrapper.text()).toContain('Dodaj przynajmniej jedno zdjęcie gołębia')
    expect(wrapper.text()).toContain('Musisz zaakceptować regulamin')
  })

  it('blokuje wyslanie i pokazuje blad, gdy cena jest ponizej minimum (10 zl)', async () => {
    const wrapper = mount(CreateAuction, { global: { plugins: [router] } })
    await flushPromises()

    await fillRequiredFields(wrapper)
    await wrapper.find('input[type="number"]').setValue(5)

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(postMock.mock.calls.find(([url]) => url === '/auctions')).toBeFalsy()
    expect(wrapper.text()).toContain('Minimalna cena to 10 zł')
  })

  it('pokazuje blad serwera (422) pod odpowiednim polem i w bannerze, bez przekierowania', async () => {
    // Regresja: err.response.data.errors z backendu powinno trafic do errors.value
    postMock.mockImplementation((url) => {
      if (url === '/images/upload') {
        return Promise.resolve({ data: { url: '/storage/images/auction/stub-image.jpg' } })
      }
      if (url === '/auctions') {
        return Promise.reject({
          response: {
            status: 422,
            data: {
              message: 'Wystąpił błąd walidacji',
              errors: { breed: ['Rasa zawiera niedozwolone znaki'] },
            },
          },
        })
      }
      return Promise.resolve({ data: {} })
    })

    const wrapper = mount(CreateAuction, { global: { plugins: [router] } })
    await flushPromises()
    await fillRequiredFields(wrapper)

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    expect(wrapper.text()).toContain('Wystąpił błąd walidacji')
    expect(wrapper.text()).toContain('Rasa zawiera niedozwolone znaki')
    expect(wrapper.vm.$route.path).toBe('/auctions/create')
  })

  it('pole "Zdjęcia rodowodu" jest opcjonalne i wysyla sie osobno od zdjec golebia', async () => {
    const wrapper = mount(CreateAuction, { global: { plugins: [router] } })
    await flushPromises()
    await fillRequiredFields(wrapper)

    postMock.mockImplementation((url) => {
      if (url === '/images/upload') {
        return Promise.resolve({ data: { url: '/storage/images/auction/pedigree-stub.jpg' } })
      }
      return Promise.resolve({ data: {} })
    })

    const fileInputs = wrapper.findAll('input[type="file"]')
    await selectFile(fileInputs[1], makeFile('rodowod.jpg'))
    await flushPromises()

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    const createCall = postMock.mock.calls.find(([url]) => url === '/auctions')
    expect(createCall).toBeTruthy()
    const [, payload] = createCall
    expect(payload.pedigree_images).toEqual(['/storage/images/auction/pedigree-stub.jpg'])
  })

  it('wysyla aukcje bez zdjec rodowodu, gdy uzytkownik ich nie dodal (pole opcjonalne)', async () => {
    const wrapper = mount(CreateAuction, { global: { plugins: [router] } })
    await flushPromises()
    await fillRequiredFields(wrapper)

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    const createCall = postMock.mock.calls.find(([url]) => url === '/auctions')
    expect(createCall).toBeTruthy()
    const [, payload] = createCall
    expect(payload.pedigree_images).toEqual([])
  })

  it('wysyla wiele zdjec golebia dodanych jednoczesnie w jednym wyborze pliku', async () => {
    const wrapper = mount(CreateAuction, { global: { plugins: [router] } })
    await flushPromises()

    let uploadCount = 0
    postMock.mockImplementation((url) => {
      if (url === '/images/upload') {
        uploadCount += 1
        return Promise.resolve({
          data: { url: `/storage/images/auction/photo-${uploadCount}.jpg` },
        })
      }
      return Promise.resolve({ data: {} })
    })

    const fileInputs = wrapper.findAll('input[type="file"]')
    await selectFiles(fileInputs[0], [makeFile('a.jpg'), makeFile('b.jpg')])
    await flushPromises()

    await wrapper.find('input[placeholder*="Piękny biały gołąb"]').setValue('Testowy tytul aukcji')
    await wrapper.find('input[placeholder*="Warszawski"]').setValue('Warszawski')
    const selects = wrapper.findAll('select')
    await selects[0].setValue('samiec')
    await selects[2].setValue('sredni')
    await selects[3].setValue('Niebieska')
    await wrapper.find('input[type="number"]').setValue(150)
    await wrapper.find('input[type="checkbox"]').setValue(true)

    await wrapper.find('form').trigger('submit.prevent')
    await flushPromises()

    const createCall = postMock.mock.calls.find(([url]) => url === '/auctions')
    expect(createCall).toBeTruthy()
    const [, payload] = createCall
    expect(payload.pigeon_images).toEqual([
      '/storage/images/auction/photo-1.jpg',
      '/storage/images/auction/photo-2.jpg',
    ])
  })
})
