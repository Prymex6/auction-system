<template>
  <div class="min-h-screen bg-white">
    <!-- Loading State -->
    <GlobalLoadingOverlay v-if="loading" message="Ładowanie aukcji..." />

    <!-- Content -->
    <div v-else-if="auction" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lg:py-12">
      <!-- Breadcrumb -->
      <div class="mb-8">
        <nav class="flex items-center gap-2 text-sm text-gray-600">
          <router-link to="/auctions" class="hover:text-blue-600 transition-colors"
            >Aukcje</router-link
          >
          <font-awesome-icon icon="fa-solid fa-chevron-right" class="w-4 h-4" />
          <span class="font-medium text-gray-900 truncate">{{ auction.title }}</span>
        </nav>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
          <!-- Image Gallery -->
          <div class="group relative bg-white rounded-3xl overflow-hidden shadow-xl">
            <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
            <div class="aspect-[4/3] relative">
              <img
                v-if="auction.pigeon_images?.[0] || auction.image"
                :src="auction.pigeon_images?.[0] || auction.image"
                :alt="auction.breed"
                class="w-full h-full object-contain p-2 group-hover:scale-105 transition-transform duration-500 cursor-zoom-in"
                @click="openLightbox(auction.pigeon_images?.[0] || auction.image)"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <IconPicker
                  name="pigeon"
                  color-class="text-gray-300"
                  class="w-32 h-32 transform group-hover:scale-110 transition-transform duration-500"
                />
              </div>

              <!-- Badge Overlay -->
              <div class="absolute top-6 left-6">
                <span
                  v-if="auction.status === 'active'"
                  class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800 whitespace-nowrap"
                >
                  <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                  Aktywna
                </span>
                <span
                  v-else-if="auction.status === 'ended'"
                  class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 whitespace-nowrap"
                >
                  <span class="w-1.5 h-1.5 bg-gray-500 rounded-full"></span>
                  Zakończona
                </span>
              </div>
            </div>

            <div class="p-6 bg-white/80 backdrop-blur-sm border-t border-gray-100">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                  <div class="flex items-center gap-1.5 flex-wrap">
                    <span
                      class="px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600 whitespace-nowrap"
                    >
                      {{
                        auction.gender === 'golab_mlody'
                          ? 'Gołąb młody'
                          : ['male', 'samiec'].includes(auction.gender)
                            ? 'Samiec'
                            : 'Samica'
                      }}
                    </span>
                    <span
                      v-if="auction.breed"
                      class="px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600 whitespace-nowrap"
                    >
                      {{ auction.breed }}
                    </span>
                    <span
                      v-if="auction.ring_number"
                      title="Numer obrączki"
                      class="px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600 whitespace-nowrap"
                    >
                      {{ auction.ring_number }}
                    </span>
                    <span
                      v-if="auction.color"
                      title="Kolor"
                      class="px-2.5 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-600 whitespace-nowrap"
                    >
                      {{ auction.color }}
                    </span>
                  </div>
                </div>
                <div v-if="isAdmin" class="text-sm text-gray-500">ID: {{ auction.id }}</div>
              </div>
            </div>
          </div>

          <!-- Title & Info -->
          <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-lg">
            <div class="mb-6">
              <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 mb-4">
                {{ auction.title }}
              </h1>
              <div class="flex flex-wrap items-center gap-4 text-gray-600">
                <div class="flex items-center gap-2">
                  <font-awesome-icon icon="fa-solid fa-clock" class="w-5 h-5" />
                  <span class="text-sm">{{ formatDate(auction.created_at) }}</span>
                </div>
                <div
                  v-if="isAdmin"
                  class="flex items-center gap-2 px-3 py-1 rounded-lg"
                  :class="
                    auction.status === 'active'
                      ? 'bg-green-50'
                      : auction.status === 'ended'
                        ? 'bg-gray-50'
                        : auction.status === 'pending'
                          ? 'bg-yellow-50'
                          : 'bg-red-50'
                  "
                >
                  <font-awesome-icon
                    icon="fa-solid fa-circle-check"
                    class="w-4 h-4"
                    :class="
                      auction.status === 'active'
                        ? 'text-green-600'
                        : auction.status === 'ended'
                          ? 'text-gray-600'
                          : auction.status === 'pending'
                            ? 'text-yellow-600'
                            : 'text-red-600'
                    "
                  />
                  <span
                    class="text-sm font-medium"
                    :class="
                      auction.status === 'active'
                        ? 'text-green-700'
                        : auction.status === 'ended'
                          ? 'text-gray-700'
                          : auction.status === 'pending'
                            ? 'text-yellow-700'
                            : 'text-red-700'
                    "
                    >{{ translateStatus(auction.status) }}</span
                  >
                </div>
              </div>
            </div>
            <p class="text-gray-700 leading-relaxed whitespace-pre-wrap">
              {{ auction.description }}
            </p>

            <!-- Rodowód -->
            <div v-if="auction.pedigree_images?.length" class="mt-8 pt-8 border-t border-gray-100">
              <div
                v-for="(img, idx) in auction.pedigree_images"
                :key="idx"
                class="rounded-2xl overflow-hidden"
                :class="{ 'mt-4': idx > 0 }"
              >
                <img
                  :src="img"
                  alt="Rodowód"
                  class="w-full object-contain max-h-[54rem] cursor-zoom-in"
                  @click="openLightbox(img)"
                />
              </div>
            </div>
          </div>

          <!-- Seller Info -->
          <div class="bg-gray-50 rounded-3xl p-8 border border-blue-100 shadow-lg">
            <div class="flex items-center gap-3 mb-8">
              <div
                class="w-12 h-12 rounded-xl bg-indigo-500 flex items-center justify-center text-white"
              >
                <IconPicker name="user" color-class="text-white" class="w-6 h-6" />
              </div>
              <h2 class="text-3xl font-bold text-gray-900">Sprzedawca</h2>
            </div>

            <div class="flex flex-col gap-6">
              <div class="flex flex-col sm:flex-row gap-6">
                <div class="relative flex-shrink-0">
                  <div
                    class="w-24 h-24 rounded-2xl bg-gray-100 flex items-center justify-center text-4xl border-4 border-white shadow-lg"
                  >
                    <img
                      v-if="auction.seller?.avatar"
                      :src="getAvatarUrl(auction.seller.avatar)"
                      alt="Avatar"
                      class="w-full h-full object-cover rounded-2xl"
                    />
                    <span v-else>{{ auction.seller?.name?.charAt(0)?.toUpperCase() || '?' }}</span>
                  </div>
                </div>

                <div class="flex-1">
                  <h3 class="text-2xl font-bold text-gray-900 mb-2">
                    {{ auction.seller?.name || 'Nieznany' }}
                  </h3>
                  <p v-if="isAdmin" class="text-gray-600 text-sm mb-4">
                    ID: {{ auction.seller?.id }}
                  </p>

                  <div class="flex gap-3">
                    <router-link
                      v-if="auction.seller?.id"
                      :to="`/profile/${auction.seller.id}`"
                      class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg font-medium hover:bg-blue-50 hover:border-blue-300 transition-all duration-300"
                    >
                      <font-awesome-icon icon="fa-solid fa-circle-info" class="w-4 h-4" />
                      Profil
                    </router-link>
                    <button
                      class="inline-flex items-center gap-2 px-4 py-2 bg-green-500 text-white rounded-lg font-medium hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300"
                      @click="togglePhoneVisibility"
                    >
                      <font-awesome-icon icon="fa-solid fa-phone" class="w-4 h-4" />
                      {{ showPhone ? 'Ukryj numer' : 'Pokaż numer' }}
                    </button>
                  </div>
                </div>
              </div>

              <!-- Phone Display -->
              <div
                v-if="showPhone"
                class="bg-white rounded-lg p-4 border border-green-200 flex items-center justify-between"
              >
                <div>
                  <p class="text-sm text-gray-600 mb-1">Numer telefonu:</p>
                  <p v-if="auction.seller_phone" class="text-2xl font-bold text-gray-900">
                    {{ auction.seller_phone }}
                  </p>
                  <p v-else-if="!authStore.isAuthenticated" class="text-gray-600 italic">
                    Zaloguj się, aby zobaczyć numer telefonu
                  </p>
                  <p v-else-if="!authStore.user?.is_active" class="text-gray-600 italic">
                    Numer będzie widoczny po weryfikacji Twojego konta przez administratora
                  </p>
                  <p v-else class="text-gray-600 italic">Brak numeru telefonu</p>
                </div>
                <button
                  v-if="auction.seller_phone"
                  class="px-3 py-2 bg-blue-100 text-blue-700 rounded-lg font-medium text-sm hover:bg-blue-200 transition-all"
                  @click="copyPhoneToClipboard"
                >
                  Skopiuj
                </button>
              </div>

              <!-- Bio Section -->
              <div v-if="auction.seller_bio" class="bg-white rounded-lg p-4 border border-blue-200">
                <p class="text-sm text-gray-600 mb-2">O sprzedawcy:</p>
                <p class="text-gray-800 leading-relaxed">{{ auction.seller_bio }}</p>
              </div>

              <!-- Message Button - pomijamy dla buy_now/both, bo tam ten sam
              przycisk "Wyślij wiadomość" jest juz w glownej sekcji akcji
              (Bid Action) nizej - inaczej pokazywalyby sie dwa identyczne
              przyciski jednoczesnie. -->
              <button
                v-if="
                  authStore.isAuthenticated &&
                  authStore.user?.is_active &&
                  authStore.user?.id !== auction.seller?.id &&
                  !['buy_now', 'both'].includes(auction.type)
                "
                class="w-full py-3 bg-blue-600 text-white rounded-lg font-semibold hover:shadow-lg hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-2"
                @click="sendMessage"
              >
                <font-awesome-icon icon="fa-solid fa-envelope" class="w-5 h-5" />
                Wyślij wiadomość
              </button>
              <button
                v-else-if="
                  authStore.isAuthenticated &&
                  !authStore.user?.is_active &&
                  authStore.user?.id !== auction.seller?.id &&
                  !['buy_now', 'both'].includes(auction.type)
                "
                disabled
                class="w-full py-3 bg-gray-300 text-gray-700 rounded-lg font-semibold cursor-not-allowed opacity-60"
                title="Konto oczekuje na weryfikację przez administratora"
              >
                Wiadomości dostępne po weryfikacji konta
              </button>
              <button
                v-else-if="
                  !authStore.isAuthenticated && !['buy_now', 'both'].includes(auction.type)
                "
                class="w-full py-3 bg-gray-300 text-gray-700 rounded-lg font-semibold cursor-not-allowed opacity-60"
                @click="$router.push('/login')"
              >
                Zaloguj się aby napisać wiadomość
              </button>
            </div>
          </div>

          <!-- Info Cards - tylko dla admina -->
          <div v-if="isAdmin" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div
              class="bg-white rounded-xl p-6 border border-gray-100 shadow-md hover:shadow-lg transition-all"
            >
              <div class="flex items-center gap-3 mb-4">
                <div
                  class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600"
                >
                  <IconPicker name="money" color-class="text-blue-600" class="w-5 h-5" />
                </div>
                <h3 class="font-bold text-gray-900">Cena początkowa</h3>
              </div>
              <p class="text-3xl font-bold text-blue-600">
                {{ formatPrice(auction.start_price || 0) }}
              </p>
            </div>

            <div
              v-if="auction.type !== 'buy_now'"
              class="bg-white rounded-xl p-6 border border-gray-100 shadow-md hover:shadow-lg transition-all"
            >
              <div class="flex items-center gap-3 mb-4">
                <div
                  class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center text-green-600"
                >
                  <IconPicker name="chart" color-class="text-green-600" class="w-5 h-5" />
                </div>
                <h3 class="font-bold text-gray-900">Liczba ofert</h3>
              </div>
              <p class="text-3xl font-bold text-green-600">{{ auction.bids_count || 0 }}</p>
            </div>

            <div
              class="bg-white rounded-xl p-6 border border-gray-100 shadow-md hover:shadow-lg transition-all"
            >
              <div class="flex items-center gap-3 mb-4">
                <div
                  class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600"
                >
                  <font-awesome-icon icon="fa-solid fa-eye" class="w-5 h-5" />
                </div>
                <h3 class="font-bold text-gray-900">Wyświetlenia</h3>
              </div>
              <p class="text-3xl font-bold text-orange-600">{{ auction.views || 0 }}</p>
            </div>
          </div>
        </div>

        <!-- Sidebar - sticky na desktopie, zeby karta z cena/akcjami towarzyszyla
        przy przewijaniu dluzszej lewej kolumny (opis, rodowod), zamiast konczyc
        sie po wlasnej (znacznie krotszej) wysokosci tresci i zostawiac pusta
        przestrzen az do stopki. self-start wylacza domyslne rozciaganie
        elementu grida na cala wysokosc wiersza, ktore inaczej kolidowaloby ze
        sticky. -->
        <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-24 lg:self-start">
          <!-- Auction Status Card -->
          <div>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-2xl overflow-hidden">
              <!-- Price Section -->
              <div class="bg-blue-600 p-8 text-white">
                <div class="text-sm font-semibold mb-2 opacity-90">Aktualna cena</div>
                <div class="text-5xl font-bold mb-4">
                  {{ formatPrice(auction.current_price || 0) }}
                </div>
                <div v-if="bids && bids.length > 0" class="text-sm opacity-75">
                  {{ auction.bids_count || 0 }} {{ auction.bids_count === 1 ? 'oferta' : 'ofert' }}
                </div>
                <div v-else class="text-sm opacity-75">
                  {{ auction.type === 'buy_now' ? 'Cena zakupu' : 'Brak ofert - cena początkowa' }}
                </div>
              </div>

              <div class="p-8">
                <!-- Dostępność dla ofert Kup teraz (bez odliczania czasu) -->
                <div v-if="auction.status === 'active' && auction.type === 'buy_now'" class="mb-8">
                  <div class="flex items-center gap-3 mb-1">
                    <div
                      class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center text-white"
                    >
                      <IconPicker name="check" color-class="text-white" class="w-5 h-5" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Dostępny od ręki</h3>
                  </div>
                  <p class="text-sm text-gray-600 pl-13">
                    Oferta bez licytacji - kup w stałej cenie.
                  </p>
                </div>

                <!-- Timer for Active Auctions -->
                <div v-else-if="auction.status === 'active'" class="mb-8">
                  <div class="flex items-center gap-3 mb-4">
                    <div
                      class="w-10 h-10 rounded-xl bg-red-500 flex items-center justify-center text-white"
                    >
                      <IconPicker name="clock" color-class="text-white" class="w-5 h-5" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Zostało czasu</h3>
                  </div>
                  <div class="flex items-center justify-center gap-3 mb-3">
                    <div class="text-center">
                      <div
                        :class="isEnding ? 'text-red-600' : 'text-gray-900'"
                        class="text-4xl font-bold"
                      >
                        {{ timeLeftParts.days }}
                      </div>
                      <div class="text-xs text-gray-500 font-medium mt-1">dni</div>
                    </div>
                    <div class="text-3xl font-bold text-gray-400">:</div>
                    <div class="text-center">
                      <div
                        :class="isEnding ? 'text-red-600' : 'text-gray-900'"
                        class="text-4xl font-bold"
                      >
                        {{ timeLeftParts.hours }}
                      </div>
                      <div class="text-xs text-gray-500 font-medium mt-1">godz</div>
                    </div>
                    <div class="text-3xl font-bold text-gray-400">:</div>
                    <div class="text-center">
                      <div
                        :class="isEnding ? 'text-red-600' : 'text-gray-900'"
                        class="text-4xl font-bold"
                      >
                        {{ timeLeftParts.minutes }}
                      </div>
                      <div class="text-xs text-gray-500 font-medium mt-1">min</div>
                    </div>
                    <div class="text-3xl font-bold text-gray-400">:</div>
                    <div class="text-center">
                      <div
                        :class="isEnding ? 'text-red-600' : 'text-gray-900'"
                        class="text-4xl font-bold"
                      >
                        {{ timeLeftParts.seconds }}
                      </div>
                      <div class="text-xs text-gray-500 font-medium mt-1">sek</div>
                    </div>
                  </div>
                  <p class="text-sm text-gray-600">Koniec: {{ formatDateTime(auction.ends_at) }}</p>
                </div>

                <!-- Status for Pending/Rejected Auctions -->
                <div
                  v-else-if="auction.status === 'pending' || auction.status === 'rejected'"
                  class="mb-8"
                >
                  <div class="flex items-center gap-3 mb-4">
                    <div
                      class="w-10 h-10 rounded-xl bg-amber-500 flex items-center justify-center text-white"
                    >
                      <IconPicker name="hourglass" color-class="text-white" class="w-5 h-5" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Status aukcji</h3>
                  </div>
                  <div class="text-amber-600 text-2xl font-bold mb-3">Oczekująca</div>
                  <p class="text-sm text-gray-600">Aukcja oczekuje na akceptację administratora</p>
                </div>

                <!-- Status for Ended Auctions -->
                <div v-else class="mb-8">
                  <div class="flex items-center gap-3 mb-4">
                    <div
                      class="w-10 h-10 rounded-xl bg-gray-500 flex items-center justify-center text-white"
                    >
                      <IconPicker name="check" color-class="text-white" class="w-5 h-5" />
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Status aukcji</h3>
                  </div>
                  <div class="text-gray-600 text-2xl font-bold mb-3">Zakończona</div>
                  <p class="text-sm text-gray-600">Koniec: {{ formatDateTime(auction.ends_at) }}</p>
                </div>

                <!-- Bid Action -->
                <div class="space-y-3">
                  <!-- Kontakt ze sprzedawca (model ogloszeniowy) -->
                  <div
                    v-if="
                      auction.status === 'active' &&
                      authStore.isAuthenticated &&
                      authStore.user?.is_active &&
                      !isOwnAuction &&
                      ['buy_now', 'both'].includes(auction.type)
                    "
                  >
                    <button
                      class="group w-full py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-2xl font-bold text-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                      @click="sendMessage"
                    >
                      <span class="flex items-center justify-center gap-3">
                        <IconPicker name="chat" color-class="text-white" class="w-6 h-6" />
                        Wyślij wiadomość
                      </span>
                    </button>
                    <p class="text-center text-xs text-gray-500 pt-2">
                      Zapytaj o gołębia, ustal cenę i sposób przekazania bezpośrednio ze sprzedawcą.
                    </p>
                  </div>

                  <!-- Licytacje wylaczone (tryb startowy) -->
                  <div
                    v-if="
                      auction.status === 'active' && !biddingEnabled && auction.type === 'auction'
                    "
                    class="p-4 rounded-xl bg-amber-50 border border-amber-200"
                  >
                    <p class="text-sm text-amber-900 font-medium">Licytacje są obecnie wyłączone</p>
                    <p class="text-xs text-amber-800 mt-1">
                      W tej chwili dostępne są wyłącznie oferty „Kup teraz". Zapraszamy wkrótce!
                    </p>
                  </div>

                  <div
                    v-if="
                      auction.status === 'active' &&
                      biddingEnabled &&
                      authStore.isAuthenticated &&
                      authStore.user?.is_active &&
                      !isOwnAuction &&
                      auction.type !== 'buy_now'
                    "
                  >
                    <button
                      class="group w-full py-4 bg-blue-600 text-white rounded-2xl font-bold text-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                      @click="openBidModal"
                    >
                      <span class="flex items-center justify-center gap-3">
                        <IconPicker
                          name="money"
                          color-class="text-white"
                          class="w-6 h-6 transform translate-y-0.5 group-hover:scale-110 transition-transform"
                        />
                        Licytuj
                      </span>
                    </button>

                    <div class="text-center text-xs text-gray-600 pt-2">
                      Minimalna oferta:
                      <span class="font-bold text-gray-900">{{
                        formatPrice(minimumBidAmount)
                      }}</span>
                    </div>
                  </div>

                  <div
                    v-else-if="
                      auction.status === 'active' &&
                      authStore.isAuthenticated &&
                      isOwnAuction &&
                      auction.type === 'buy_now'
                    "
                    class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-center"
                  >
                    <p class="text-sm text-blue-900 font-medium">To Twoja oferta</p>
                    <p class="text-xs text-blue-800 mt-1">
                      Widoczna dla kupujących w cenie {{ formatPrice(auction.current_price || 0) }}.
                    </p>
                  </div>

                  <div
                    v-else-if="
                      auction.status === 'active' && authStore.isAuthenticated && isOwnAuction
                    "
                    class="space-y-3"
                    title="To Twoja aukcja. Nie możesz licytować własnej aukcji."
                  >
                    <button
                      disabled
                      class="w-full py-4 bg-gray-300 text-gray-600 rounded-2xl font-bold text-lg cursor-not-allowed opacity-60"
                    >
                      <span class="flex items-center justify-center gap-3">
                        <IconPicker
                          name="money"
                          color-class="text-gray-600"
                          class="w-6 h-6 translate-y-0.5"
                        />
                        Licytuj
                      </span>
                    </button>
                  </div>

                  <div
                    v-else-if="
                      auction.status === 'active' &&
                      authStore.isAuthenticated &&
                      !authStore.user?.is_active
                    "
                    class="space-y-3"
                  >
                    <button
                      disabled
                      class="w-full py-4 bg-gray-300 text-gray-600 rounded-2xl font-bold text-lg cursor-not-allowed opacity-60"
                    >
                      <span class="flex items-center justify-center gap-3">
                        <IconPicker
                          :name="['buy_now', 'both'].includes(auction.type) ? 'chat' : 'money'"
                          color-class="text-gray-600"
                          class="w-6 h-6 translate-y-0.5"
                        />
                        {{
                          ['buy_now', 'both'].includes(auction.type)
                            ? 'Wyślij wiadomość'
                            : 'Licytuj'
                        }}
                      </span>
                    </button>
                    <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 text-center">
                      <p class="text-sm text-blue-900 font-medium">Konto nieaktywne</p>
                      <p class="text-xs text-blue-800 mt-1">
                        {{
                          ['buy_now', 'both'].includes(auction.type)
                            ? 'Twoje konto czeka na zatwierdzenie administratora. Będziesz mógł napisać do sprzedawcy po aktywacji.'
                            : 'Twoje konto czeka na zatwierdzenie administratora. Będziesz mógł licytować po aktywacji.'
                        }}
                      </p>
                    </div>
                  </div>

                  <div v-else-if="!authStore.isAuthenticated">
                    <button
                      class="w-full py-4 bg-blue-600 text-white rounded-2xl font-bold text-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                      @click="$router.push('/login')"
                    >
                      Zaloguj się
                    </button>
                    <p class="text-center text-xs text-gray-600 pt-2">
                      {{
                        ['buy_now', 'both'].includes(auction.type) || !biddingEnabled
                          ? 'Zaloguj się, aby napisać do sprzedawcy'
                          : 'Aby licytować, musisz się zalogować'
                      }}
                    </p>
                  </div>

                  <div v-else-if="auction.status === 'pending' && isAdmin">
                    <button
                      class="w-full py-4 bg-emerald-600 text-white rounded-2xl font-bold text-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
                      @click="approveAuction"
                    >
                      <span class="flex items-center justify-center gap-3">
                        <font-awesome-icon icon="fa-solid fa-check" class="w-6 h-6" />
                        Akceptuj
                      </span>
                    </button>
                  </div>

                  <div v-else-if="auction.status === 'pending'">
                    <button
                      disabled
                      class="w-full py-4 bg-amber-500 text-white rounded-2xl font-bold text-lg opacity-60 cursor-not-allowed"
                    >
                      Oczekująca
                    </button>
                  </div>

                  <div v-else-if="auction.status !== 'active'">
                    <button
                      disabled
                      class="w-full py-4 bg-gray-400 text-white rounded-2xl font-bold text-lg opacity-60 cursor-not-allowed"
                    >
                      Zakończona
                    </button>
                  </div>
                </div>
              </div>

              <!-- Bottom Info -->
              <div class="bg-gray-50 border-t border-gray-200 p-6">
                <div class="flex items-center justify-center gap-2 text-sm text-gray-600">
                  <font-awesome-icon icon="fa-solid fa-check" class="w-5 h-5 text-green-500" />
                  <span>Gwarancja bezpieczeństwa</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Bids History (tylko aukcje z licytacja) -->
          <div
            v-if="biddingEnabled && auction.type !== 'buy_now'"
            class="bg-white rounded-3xl border border-gray-100 shadow-lg overflow-hidden"
          >
            <div class="p-8">
              <div class="flex items-center gap-3 mb-6">
                <div
                  class="w-10 h-10 rounded-xl bg-orange-500 flex items-center justify-center text-white"
                >
                  <IconPicker name="chart" color-class="text-white" class="w-5 h-5" />
                </div>
                <h3 class="text-xl font-bold text-gray-900">Historia licytacji</h3>
              </div>

              <div v-if="validBids.length === 0" class="text-center py-8">
                <div
                  class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center text-gray-400"
                >
                  <IconPicker name="money" color-class="text-gray-400" class="w-8 h-8" />
                </div>
                <p class="text-gray-600 font-medium">Nikt jeszcze nie złożył oferty</p>
                <p class="text-sm text-gray-500 mt-1">Bądź pierwszy i rozpocznij licytację!</p>
              </div>

              <div v-else class="space-y-4 max-h-96 overflow-y-auto pr-2">
                <div
                  v-for="(bid, index) in validBids"
                  :key="index"
                  class="group bg-white rounded-xl p-4 border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all duration-300"
                >
                  <div class="flex items-center justify-between mb-2">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600 font-bold"
                      >
                        {{ index + 1 }}
                      </div>
                      <div>
                        <p class="font-bold text-gray-900">{{ bid.bidder_name }}</p>
                        <p class="text-xs text-gray-500">{{ formatDateTime(bid.created_at) }}</p>
                      </div>
                    </div>
                    <div class="text-right">
                      <p class="text-2xl font-bold text-emerald-600">
                        {{ formatPrice(bid.amount) }}
                      </p>
                    </div>
                  </div>

                  <div
                    v-if="index === 0"
                    class="mt-2 inline-flex items-center gap-2 px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm"
                  >
                    <font-awesome-icon icon="fa-solid fa-wand-magic-sparkles" class="w-4 h-4" />
                    Aktualnie wygrywa
                  </div>
                </div>
              </div>

              <div v-if="validBids.length > 0" class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex items-center justify-between text-sm text-gray-600">
                  <span>Średnia oferta:</span>
                  <span class="font-bold text-gray-900">
                    {{ formatPrice(averageBid) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Winner Contact (only for ended auctions with bids) -->
          <div
            v-if="auction.status === 'ended' && validBids.length > 0"
            class="bg-white rounded-3xl border border-gray-100 shadow-lg overflow-hidden"
          >
            <div class="p-8">
              <div class="flex items-center gap-3 mb-6">
                <div
                  class="w-10 h-10 rounded-xl bg-emerald-500 flex items-center justify-center text-white"
                >
                  <font-awesome-icon icon="fa-solid fa-trophy" class="w-5 h-5" />
                </div>
                <h3 class="text-xl font-bold text-gray-900">Wygrany</h3>
              </div>

              <!-- Winner Info -->
              <div class="bg-green-50 rounded-xl p-6 border border-green-200">
                <div class="flex items-center gap-4 mb-4">
                  <div
                    class="w-16 h-16 rounded-full bg-emerald-500 flex items-center justify-center text-white text-2xl font-bold"
                  >
                    {{ validBids[0].bidder_name?.charAt(0)?.toUpperCase() || '?' }}
                  </div>
                  <div>
                    <p class="text-lg font-bold text-gray-900">{{ validBids[0].bidder_name }}</p>
                    <p class="text-sm text-gray-600">
                      Wygrał z ofertą
                      <span class="font-bold text-green-600">{{
                        formatPrice(validBids[0].amount)
                      }}</span>
                    </p>
                  </div>
                </div>

                <!-- Contact Details (visible only to auction owner and winner) -->
                <div
                  v-if="user && (auction.user_id === user.id || validBids[0].user_id === user.id)"
                  class="space-y-3 pt-4 border-t border-green-200"
                >
                  <div class="flex items-center gap-3 text-gray-700">
                    <font-awesome-icon icon="fa-solid fa-user" class="w-5 h-5 text-green-600" />
                    <div>
                      <p class="text-sm text-gray-500">Imię i nazwisko</p>
                      <p class="font-semibold">
                        {{ validBids[0].bidder_full_name || validBids[0].bidder_name }}
                      </p>
                    </div>
                  </div>

                  <div class="flex items-center gap-3 text-gray-700">
                    <font-awesome-icon icon="fa-solid fa-phone" class="w-5 h-5 text-green-600" />
                    <div>
                      <p class="text-sm text-gray-500">Numer telefonu</p>
                      <p class="font-semibold">{{ validBids[0].bidder_phone || 'Brak danych' }}</p>
                    </div>
                  </div>

                  <div class="flex items-center gap-3 text-gray-700">
                    <font-awesome-icon icon="fa-solid fa-envelope" class="w-5 h-5 text-green-600" />
                    <div>
                      <p class="text-sm text-gray-500">Email</p>
                      <p class="font-semibold">{{ validBids[0].bidder_email || 'Brak danych' }}</p>
                    </div>
                  </div>

                  <!-- Contact Button -->
                  <div class="pt-4">
                    <a
                      v-if="validBids[0].bidder_phone"
                      :href="`tel:${validBids[0].bidder_phone}`"
                      class="block w-full py-3 bg-green-500 text-white text-center rounded-xl font-semibold hover:bg-green-600 transition-all duration-300 shadow-lg hover:shadow-xl"
                    >
                      <div class="flex items-center justify-center gap-2">
                        <font-awesome-icon icon="fa-solid fa-phone" class="w-5 h-5" />
                        Zadzwoń do {{ auction.user_id === user.id ? 'wygranego' : 'sprzedającego' }}
                      </div>
                    </a>
                  </div>
                </div>

                <!-- Info for non-authorized users -->
                <div v-else class="text-center py-4">
                  <p class="text-sm text-gray-600">
                    Dane kontaktowe są widoczne tylko dla wygranego i właściciela aukcji
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- Share & Save -->
          <div class="bg-white rounded-3xl border border-gray-100 shadow-lg p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
              <!-- Przycisk Zapisz -->
              <button
                class="group relative w-full min-w-0 flex items-center justify-center px-4 py-3 rounded-xl border border-gray-200 hover:border-blue-300 hover:bg-blue-50 transition-all duration-300"
                @click="toggleWishlist"
              >
                <font-awesome-icon
                  icon="fa-solid fa-heart"
                  class="w-5 h-5 flex-shrink-0"
                  :class="
                    isWishlisted ? 'text-blue-600' : 'text-gray-600 group-hover:text-blue-600'
                  "
                />
                <span class="sr-only">Zapisz</span>
                <span
                  class="pointer-events-none absolute -top-11 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-gray-900/90 px-3 py-1.5 text-xs font-medium text-white shadow-lg opacity-0 transition-all duration-200 group-hover:opacity-100 group-hover:-top-12"
                >
                  {{ isWishlisted ? 'Zapisane' : 'Zapisz' }}
                </span>
              </button>

              <!-- Przycisk Udostępnij -->
              <button
                class="group relative w-full min-w-0 flex items-center justify-center px-4 py-3 rounded-xl border border-gray-200 hover:border-purple-300 hover:bg-purple-50 transition-all duration-300"
                @click="shareAuction"
              >
                <font-awesome-icon
                  icon="fa-solid fa-share-nodes"
                  class="w-5 h-5 text-gray-600 group-hover:text-purple-600 flex-shrink-0"
                />
                <span class="sr-only">Udostępnij</span>
                <span
                  class="pointer-events-none absolute -top-11 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-gray-900/90 px-3 py-1.5 text-xs font-medium text-white shadow-lg opacity-0 transition-all duration-200 group-hover:opacity-100 group-hover:-top-12"
                >
                  Udostępnij
                </span>
              </button>

              <!-- Przycisk Zgłoś -->
              <button
                class="group relative w-full min-w-0 flex items-center justify-center px-4 py-3 rounded-xl border border-gray-200 hover:border-red-300 hover:bg-red-50 transition-all duration-300"
                @click="showReportModal = true"
              >
                <font-awesome-icon
                  icon="fa-solid fa-triangle-exclamation"
                  class="w-5 h-5 text-gray-600 group-hover:text-red-600 flex-shrink-0"
                />
                <span class="sr-only">Zgłoś</span>
                <span
                  class="pointer-events-none absolute -top-11 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-lg bg-gray-900/90 px-3 py-1.5 text-xs font-medium text-white shadow-lg opacity-0 transition-all duration-200 group-hover:opacity-100 group-hover:-top-12"
                >
                  Zgłoś
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Not Found -->
    <div v-else class="max-w-2xl mx-auto px-4 py-24 text-center">
      <div class="mb-8">
        <div
          class="w-32 h-32 mx-auto rounded-full bg-gray-100 flex items-center justify-center text-6xl mb-6"
        >
          <IconPicker name="pigeon" color-class="text-gray-400" class="w-32 h-32" />
        </div>
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Aukcja nie znaleziona</h1>
        <p class="text-lg text-gray-600 mb-8">
          Przepraszamy, ale nie możemy znaleźć szukanej przez Ciebie aukcji. Może została zakończona
          lub usunięta.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <router-link
            to="/auctions"
            class="px-8 py-4 bg-blue-600 text-white rounded-xl font-bold hover:shadow-xl hover:-translate-y-1 transition-all duration-300"
          >
            Przejdź do aukcji
          </router-link>
          <router-link
            to="/"
            class="px-8 py-4 border-2 border-gray-200 text-gray-700 rounded-xl font-bold hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300"
          >
            Strona główna
          </router-link>
        </div>
      </div>
    </div>

    <!-- Toast Notifications -->
    <div class="fixed top-4 right-4 z-[9999] space-y-3">
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="min-w-[320px] max-w-md bg-white rounded-xl shadow-2xl border overflow-hidden transform transition-all duration-300 animate-slide-in"
        :class="{
          'border-green-200': toast.type === 'success',
          'border-red-200': toast.type === 'error',
          'border-yellow-200': toast.type === 'warning',
          'border-blue-200': toast.type === 'info',
        }"
      >
        <div class="flex items-start gap-3 p-4">
          <div
            class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
            :class="{
              'bg-green-50': toast.type === 'success',
              'bg-red-50': toast.type === 'error',
              'bg-yellow-50': toast.type === 'warning',
              'bg-gray-100': toast.type === 'info',
            }"
          >
            <font-awesome-icon
              v-if="toast.type === 'success'"
              icon="fa-solid fa-check"
              class="w-6 h-6 text-green-600"
            />
            <font-awesome-icon
              v-else-if="toast.type === 'error'"
              icon="fa-solid fa-xmark"
              class="w-6 h-6 text-red-600"
            />
            <font-awesome-icon
              v-else-if="toast.type === 'warning'"
              icon="fa-solid fa-circle-info"
              class="w-6 h-6 text-yellow-600"
            />
            <font-awesome-icon
              v-else
              icon="fa-solid fa-circle-info"
              class="w-6 h-6 text-blue-600"
            />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-900 break-words">{{ toast.message }}</p>
          </div>
        </div>
        <div
          class="h-1 animate-shrink"
          :class="{
            'bg-emerald-500': toast.type === 'success',
            'bg-red-500': toast.type === 'error',
            'bg-amber-500': toast.type === 'warning',
            'bg-blue-600': toast.type === 'info',
          }"
        ></div>
      </div>
    </div>

    <!-- Message Modal -->
    <div
      v-if="showMessageModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="showMessageModal = false"
    >
      <div
        class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 transform transition-all"
        @click.stop
      >
        <button
          class="absolute top-6 right-6 w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition-colors z-10"
          @click="showMessageModal = false"
        >
          <font-awesome-icon icon="fa-solid fa-xmark" class="w-5 h-5" />
        </button>

        <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
          <span class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
            <font-awesome-icon icon="fa-solid fa-comment" class="w-5 h-5" />
          </span>
          Wyślij wiadomość
        </h3>

        <div class="mb-6">
          <p class="text-gray-600 mb-4">
            Wiadomość do <strong>{{ auction?.seller?.name || 'sprzedawcy' }}</strong>
          </p>

          <!-- Message Content -->
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Wiadomość</label>
            <textarea
              v-model="messageContent"
              rows="5"
              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50 resize-none"
              placeholder="Treść wiadomości..."
            ></textarea>
          </div>
        </div>

        <div class="flex gap-3">
          <button
            class="flex-1 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-all"
            @click="showMessageModal = false"
          >
            Anuluj
          </button>
          <button
            :disabled="!messageContent"
            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            @click="submitMessage"
          >
            Wyślij
          </button>
        </div>
      </div>
    </div>

    <!-- Report Modal -->
    <div
      v-if="showReportModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="showReportModal = false"
    >
      <div
        class="relative bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 transform transition-all"
        @click.stop
      >
        <button
          class="absolute top-6 right-6 w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition-colors z-10"
          @click="showReportModal = false"
        >
          <font-awesome-icon icon="fa-solid fa-xmark" class="w-5 h-5" />
        </button>

        <h3 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-3">
          <span class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
            <IconPicker name="warning" color-class="text-red-600" class="w-5 h-5" />
          </span>
          Zgłoś aukcję
        </h3>

        <div class="mb-6">
          <p class="text-gray-600 mb-4">
            Zgłaszasz aukcję <strong>{{ auction?.title }}</strong>
          </p>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Powód zgłoszenia</label>
            <select
              v-model="reportReason"
              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50"
            >
              <option value="">Wybierz powód</option>
              <option value="spam">Spam</option>
              <option value="fraud">Oszustwo</option>
              <option value="inappropriate">Nieodpowiednia treść</option>
              <option value="illegal">Nielegalna treść</option>
              <option value="other">Inne</option>
            </select>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Opis problemu</label>
            <textarea
              v-model="reportDescription"
              rows="4"
              class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-red-300 focus:ring focus:ring-red-200 focus:ring-opacity-50 resize-none"
              placeholder="Opisz szczegółowo problem..."
            ></textarea>
          </div>
        </div>

        <div class="flex gap-3">
          <button
            class="flex-1 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-all"
            @click="showReportModal = false"
          >
            Anuluj
          </button>
          <button
            :disabled="!reportReason || !reportDescription"
            class="flex-1 px-6 py-3 bg-red-500 text-white rounded-xl font-semibold hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed transition-all"
            @click="submitReport"
          >
            Wyślij zgłoszenie
          </button>
        </div>
      </div>
    </div>

    <!-- Bid Modal -->
    <div
      v-if="showBidModal && auction"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4 backdrop-blur-sm"
    >
      <div class="relative max-w-md w-full">
        <div class="absolute -inset-0.5 bg-blue-600 rounded-3xl blur opacity-30"></div>
        <div class="relative bg-white rounded-3xl p-8">
          <button
            class="absolute top-6 right-6 w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-600 transition-colors"
            @click="closeBidModal"
          >
            <font-awesome-icon icon="fa-solid fa-xmark" class="w-5 h-5" />
          </button>

          <div class="text-center mb-8">
            <div
              class="w-20 h-20 mx-auto mb-4 rounded-2xl bg-blue-600 flex items-center justify-center text-white"
            >
              <IconPicker name="money" color-class="text-white" class="w-10 h-10" />
            </div>
            <h2 class="text-3xl font-bold text-gray-900">Złóż ofertę</h2>
            <p class="text-gray-600 mt-2">Aukcja: {{ auction.title }}</p>
          </div>

          <div class="mb-8 p-6 bg-gray-100 rounded-2xl border border-blue-200">
            <p class="text-sm text-gray-600 mb-2">Aktualna najwyższa oferta</p>
            <p class="text-4xl font-bold text-gray-900">
              {{ formatPrice(auction.current_price) }}
            </p>
            <div v-if="!isAutoBid" class="mt-4 text-sm text-gray-600">
              <div class="flex items-center justify-between">
                <span>Twoja maksymalna oferta:</span>
                <span class="font-bold text-gray-900">{{ formatPrice(bidAmount) }}</span>
              </div>
              <div class="flex items-center justify-between mt-1">
                <span>Różnica:</span>
                <span class="font-bold text-green-600"
                  >+{{ formatPrice(bidAmount - auction.current_price) }}</span
                >
              </div>
            </div>
            <div v-else class="mt-4 text-sm text-purple-700 bg-purple-50 rounded-lg p-3">
              <div class="flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-bolt" class="w-4 h-4" />
                <span class="font-semibold">Automatyczna licytacja włączona</span>
              </div>
            </div>
          </div>

          <div v-if="!isAutoBid" class="mb-8">
            <label for="bid_amount" class="block text-sm font-medium text-gray-700 mb-3">
              Wprowadź kwotę (minimum {{ formatPrice(minimumBidAmount) }})
            </label>
            <div class="relative">
              <input
                id="bid_amount"
                v-model.number="bidAmount"
                type="number"
                :min="minimumBidAmount"
                class="w-full px-6 py-4 pr-24 text-2xl font-bold text-center bg-white rounded-xl border-2 border-gray-200 focus:border-blue-500 focus:ring-4 focus:ring-blue-200 focus:outline-none transition-all"
                placeholder="0.00"
              />
              <div
                class="absolute right-4 top-1/2 transform -translate-y-1/2 text-2xl font-bold text-gray-500"
              >
                zł
              </div>
            </div>
            <div class="mt-4 grid grid-cols-3 gap-3">
              <button
                v-for="increment in [50, 100, 200]"
                :key="increment"
                class="py-3 bg-gray-100 rounded-lg font-medium hover:bg-gray-200 transition-all"
                @click="bidAmount = minimumBidAmount + increment"
              >
                +{{ increment }} zł
              </button>
            </div>
          </div>

          <!-- Auto Bid Section -->
          <div class="mb-6 p-6 bg-blue-50 rounded-2xl border border-purple-200">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center">
                  <font-awesome-icon icon="fa-solid fa-bolt" class="w-5 h-5 text-white" />
                </div>
                <div>
                  <h3 class="font-bold text-gray-900">Automatyczna licytacja</h3>
                  <p class="text-xs text-gray-600">System będzie licytował za Ciebie</p>
                </div>
              </div>
              <button
                :class="isAutoBid ? 'bg-blue-600' : 'bg-gray-200'"
                class="relative w-14 h-8 rounded-full transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-blue-200"
                role="switch"
                :aria-checked="isAutoBid"
                aria-label="Automatyczna licytacja"
                @click="isAutoBid = !isAutoBid"
              >
                <span
                  :class="isAutoBid ? 'translate-x-[1.75rem]' : 'translate-x-1'"
                  class="absolute top-1 left-0 w-6 h-6 bg-white rounded-full shadow-md transition-transform duration-300"
                ></span>
              </button>
            </div>

            <div v-if="isAutoBid" class="space-y-4 animate-slide-in">
              <div class="bg-white rounded-xl p-4 border border-purple-200">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Maksymalna kwota auto licytacji
                </label>
                <div class="relative">
                  <input
                    v-model.number="maxAutoBid"
                    type="number"
                    :min="auction.current_price + 50"
                    class="w-full px-4 py-3 pr-16 text-xl font-bold text-center bg-white rounded-lg border-2 border-purple-200 focus:border-purple-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all"
                    placeholder="0.00"
                  />
                  <div
                    class="absolute right-4 top-1/2 transform -translate-y-1/2 text-xl font-bold text-gray-500"
                  >
                    zł
                  </div>
                </div>
                <div class="mt-3 grid grid-cols-3 gap-2">
                  <button
                    v-for="increment in [500, 1000, 2000]"
                    :key="increment"
                    class="py-2 text-sm bg-blue-50 text-purple-700 rounded-lg font-medium hover:bg-purple-100 border border-purple-200 transition-all"
                    @click="maxAutoBid = auction.current_price + increment"
                  >
                    +{{ increment }} zł
                  </button>
                </div>
              </div>

              <div class="bg-purple-100/50 rounded-lg p-3 text-xs text-purple-900">
                <div class="flex items-start gap-2">
                  <font-awesome-icon
                    icon="fa-solid fa-circle-info"
                    class="w-4 h-4 mt-0.5 flex-shrink-0"
                  />
                  <div>
                    <p class="font-semibold mb-1">Jak działa auto licytacja?</p>
                    <p>
                      System automatycznie podbije Twoją ofertę, gdy ktoś zaoferuje więcej, aż do
                      ustalonego przez Ciebie limitu.
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <button
            :disabled="
              biddingLoading || (isAutoBid && (!maxAutoBid || maxAutoBid <= auction.current_price))
            "
            class="group w-full py-4 bg-blue-600 text-white rounded-xl font-bold text-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 mb-4 disabled:opacity-50 disabled:cursor-not-allowed"
            @click="placeBid"
          >
            <span v-if="!biddingLoading" class="flex items-center justify-center gap-3">
              <font-awesome-icon
                v-if="!isAutoBid"
                icon="fa-solid fa-clock"
                class="w-6 h-6 transform group-hover:scale-110 transition-transform"
              />
              <font-awesome-icon
                v-else
                icon="fa-solid fa-bolt"
                class="w-6 h-6 transform group-hover:scale-110 transition-transform"
              />
              {{
                isAutoBid
                  ? `Ustaw auto licytację (max ${formatPrice(maxAutoBid)})`
                  : `Złóż ofertę ${formatPrice(bidAmount)}`
              }}
            </span>
            <span v-else class="flex items-center justify-center gap-2">
              <font-awesome-icon icon="fa-solid fa-spinner" class="animate-spin h-6 w-6" />
              Wysyłanie oferty...
            </span>
          </button>

          <button
            class="w-full py-4 border-2 border-gray-200 text-gray-700 rounded-xl font-bold hover:border-gray-300 hover:bg-gray-50 transition-all duration-300"
            @click="closeBidModal"
          >
            Anuluj
          </button>

          <div v-if="bidError" class="mt-6 p-4 bg-red-50 border border-red-200 rounded-2xl">
            <div class="flex items-center gap-3">
              <font-awesome-icon
                icon="fa-solid fa-circle-info"
                class="w-5 h-5 text-red-600 flex-shrink-0"
              />
              <p class="text-red-800 text-sm">{{ bidError }}</p>
            </div>
          </div>

          <div class="mt-6 pt-6 border-t border-gray-200">
            <p class="text-xs text-gray-500 text-center">
              Klikając "Złóż ofertę" akceptujesz
              <router-link to="/terms" class="text-blue-600 hover:text-blue-800 underline"
                >regulamin serwisu</router-link
              >
            </p>
          </div>
        </div>
      </div>
    </div>

    <ConfirmModal
      :show="showApproveConfirm"
      title="Zaakceptuj aukcję"
      message="Oferta stanie się publicznie widoczna w serwisie. Kontynuować?"
      confirm-text="Zaakceptuj"
      @confirm="confirmApproveAuction"
      @cancel="showApproveConfirm = false"
    />

    <!-- Lightbox: pełnoekranowy podgląd zdjęcia / rodowodu -->
    <Teleport to="body">
      <div
        v-if="lightboxImage"
        class="fixed inset-0 z-[100] bg-black/90 flex items-center justify-center p-4 cursor-zoom-out"
        @click="lightboxImage = null"
      >
        <button
          class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white"
          @click="lightboxImage = null"
        >
          <font-awesome-icon icon="fa-solid fa-xmark" class="w-6 h-6" />
        </button>
        <img :src="lightboxImage" alt="" class="max-w-full max-h-full object-contain" @click.stop />
      </div>
    </Teleport>
  </div>
</template>

<script setup>
  import { ref, computed, onMounted, onUnmounted } from 'vue'
  import { useRoute, useRouter } from 'vue-router'
  import { useAuctionStore } from '@/stores/auction'
  import { useAuthStore } from '@/stores/auth'
  import { useRealtimeConnection } from '@/composables/useRealtimeConnection'
  import { useSeo } from '@/composables/useSeo'
  import { useFormatters } from '@/composables/useFormatters'
  import api from '@/services/api'
  import { usePlatformSettings } from '@/composables/usePlatformSettings'
  import IconPicker from '@/components/icons/IconPicker.vue'
  import GlobalLoadingOverlay from '@/components/GlobalLoadingOverlay.vue'
  import ConfirmModal from '@/components/ConfirmModal.vue'

  const route = useRoute()
  const router = useRouter()
  const auctionStore = useAuctionStore()
  const authStore = useAuthStore()
  const user = computed(() => authStore.user)
  const { biddingEnabled, platformSettings, loadSettings } = usePlatformSettings()
  loadSettings()

  const minimumBidAmount = computed(() => {
    const current = auction.value?.current_price || 0
    const percentage = platformSettings.value?.bid_increment_percentage
    if (percentage) {
      return Math.round(current * (1 + percentage / 100) * 100) / 100
    }
    return current + 0.01
  })

  // Setup real-time connection
  const { disconnect } = useRealtimeConnection({
    auctionId: route.params.id,
    auctions: true,
  })

  const loading = ref(true)
  const auction = ref(null)
  const bids = ref([])
  const timeLeftParts = ref({ days: '00', hours: '00', minutes: '00', seconds: '00' })
  const showBidModal = ref(false)
  const bidAmount = ref(0)
  const biddingLoading = ref(false)
  const bidError = ref('')
  const isAutoBid = ref(false)
  const maxAutoBid = ref(0)
  const showPhone = ref(false)
  const isWishlisted = ref(false)
  const showReportModal = ref(false)
  const reportReason = ref('')
  const reportDescription = ref('')
  const showMessageModal = ref(false)
  const messageContent = ref('')

  const getAvatarUrl = (path) => {
    if (!path) return ''
    if (path.startsWith('http')) return path
    return `${window.location.origin}/storage/${path}`
  }

  // Toast notifications
  const toasts = ref([])
  let toastId = 0

  const showToast = (message, type = 'success') => {
    const id = toastId++
    toasts.value.push({ id, message, type })
    setTimeout(() => {
      toasts.value = toasts.value.filter((t) => t.id !== id)
    }, 4000)
  }

  const wishlistKey = 'auctionWishlist'
  const MAX_WISHLIST_SIZE = 100

  const getWishlist = () => {
    try {
      const raw = localStorage.getItem(wishlistKey)
      return raw ? JSON.parse(raw) : []
    } catch (e) {
      console.warn('Failed to read wishlist:', e)
      return []
    }
  }

  const setWishlist = (list) => {
    try {
      const limitedList = list.slice(-MAX_WISHLIST_SIZE)
      localStorage.setItem(wishlistKey, JSON.stringify(limitedList))
    } catch (e) {
      console.error('Failed to save wishlist:', e)
      if (e.name === 'QuotaExceededError') {
        try {
          const reducedList = list.slice(-50)
          localStorage.setItem(wishlistKey, JSON.stringify(reducedList))
          showToast('Lista obserwowanych została skrócona do 50 elementów', 'warning')
        } catch (retryError) {
          showToast('Nie można zapisać listy obserwowanych', 'error')
        }
      }
    }
  }

  const syncWishlistState = (auctionId) => {
    if (!auctionId) return
    const list = getWishlist()
    isWishlisted.value = list.includes(auctionId)
  }

  const toggleWishlist = () => {
    if (!auction.value?.id) return
    const list = getWishlist()
    const index = list.indexOf(auction.value.id)
    if (index >= 0) {
      list.splice(index, 1)
      isWishlisted.value = false
      showToast('Usunięto z obserwowanych', 'info')
    } else {
      if (list.length >= MAX_WISHLIST_SIZE) {
        showToast(`Osiągnięto limit ${MAX_WISHLIST_SIZE} obserwowanych aukcji`, 'warning')
        return
      }
      list.push(auction.value.id)
      isWishlisted.value = true
      showToast('Dodano do obserwowanych', 'success')
    }
    setWishlist(list)
  }

  const shareAuction = async () => {
    const url = window.location.href
    try {
      if (navigator.share) {
        await navigator.share({
          title: auction.value?.title || 'Aukcja',
          url,
        })
        return
      }
      await navigator.clipboard.writeText(url)
      showToast('Link skopiowany do schowka', 'success')
    } catch (error) {
      console.error('Share failed:', error)
      showToast('Nie udało się udostępnić linku', 'error')
    }
  }

  const submitReport = async () => {
    if (!auction.value?.id) return
    try {
      await api.post('/reports', {
        auction_id: auction.value.id,
        reason: reportReason.value,
        description: reportDescription.value,
      })
      showToast('Zgłoszenie zostało wysłane', 'success')
      showReportModal.value = false
      reportReason.value = ''
      reportDescription.value = ''
    } catch (error) {
      console.error('Error submitting report:', error)
      showToast(error.response?.data?.message || 'Nie udało się wysłać zgłoszenia', 'error')
    }
  }

  const isEnding = computed(() => {
    if (!auction.value) return false
    const now = new Date()
    const end = new Date(auction.value.ends_at)
    const diff = end - now
    return diff < 1000 * 60 * 60 // Less than 1 hour
  })

  const updateTimeLeft = () => {
    if (!auction.value) return

    const now = new Date()
    const end = new Date(auction.value.ends_at)
    const diff = end - now

    if (diff <= 0) {
      timeLeftParts.value = { days: '00', hours: '00', minutes: '00', seconds: '00' }
      return
    }

    const days = Math.floor(diff / (1000 * 60 * 60 * 24))
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))
    const seconds = Math.floor((diff % (1000 * 60)) / 1000)

    const pad = (value) => String(value).padStart(2, '0')
    timeLeftParts.value = {
      days: pad(days),
      hours: pad(hours),
      minutes: pad(minutes),
      seconds: pad(seconds),
    }
  }

  const isAdmin = computed(() => {
    return authStore.user?.is_admin || false
  })

  const isOwnAuction = computed(() => {
    if (!authStore.isAuthenticated || !auction.value) return false
    const sellerId = auction.value.seller?.id ?? auction.value.user_id
    return !!sellerId && authStore.user?.id === sellerId
  })

  const validBids = computed(() => {
    if (!Array.isArray(bids.value)) return []
    return bids.value.filter((b) => b && b.bidder_name && b.created_at && b.amount)
  })

  const averageBid = computed(() => {
    if (validBids.value.length === 0) return 0
    const sum = validBids.value.reduce((acc, bid) => acc + (bid.amount || 0), 0)
    return sum / validBids.value.length
  })

  const formatPrice = (price) => {
    return composableFormatPrice(price)
  }

  const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pl-PL')
  }

  const formatDateTime = (dateTime) => {
    return new Date(dateTime).toLocaleString('pl-PL')
  }

  const translateStatus = (status) => {
    const statusMap = {
      pending: 'Oczekująca',
      active: 'Aktywna',
      ended: 'Zakończona',
      rejected: 'Odrzucona',
      closed: 'Zamknięta',
    }
    return statusMap[status] || status
  }

  const { formatPrice: composableFormatPrice } = useFormatters()

  const fetchAuction = async () => {
    loading.value = true
    try {
      const response = await auctionStore.fetchAuction(route.params.id)
      auction.value = response
      updateTimeLeft()
      updateSeo()
      syncWishlistState(auction.value?.id)

      const bidsResponse = await auctionStore.getBids(route.params.id)
      bids.value = Array.isArray(bidsResponse) ? bidsResponse : []
    } catch (error) {
      console.error('Error fetching auction:', error)
    } finally {
      loading.value = false
    }
  }

  const openBidModal = () => {
    if (isOwnAuction.value) {
      showToast('Nie możesz licytować własnej aukcji', 'warning')
      return
    }
    bidAmount.value = minimumBidAmount.value
    maxAutoBid.value = auction.value.current_price + 100
    isAutoBid.value = false
    bidError.value = ''
    showBidModal.value = true
  }

  const closeBidModal = () => {
    showBidModal.value = false
    bidAmount.value = 0
    maxAutoBid.value = 0
    isAutoBid.value = false
    bidError.value = ''
  }

  const placeBid = async () => {
    if (!isAutoBid.value && bidAmount.value < minimumBidAmount.value) {
      bidError.value = `Oferta musi wynosić co najmniej ${formatPrice(minimumBidAmount.value)}`
      return
    }

    if (isAutoBid.value && (!maxAutoBid.value || maxAutoBid.value <= auction.value.current_price)) {
      bidError.value = `Maksymalna kwota auto licytacji musi być wyższa niż ${formatPrice(auction.value.current_price)}`
      return
    }

    biddingLoading.value = true
    bidError.value = ''

    try {
      if (isAutoBid.value) {
        await api.post(`/bids/auction/${route.params.id}/auto-bid`, {
          max_amount: maxAutoBid.value,
        })
        showToast('Auto licytacja została ustawiona!', 'success')
      } else {
        await auctionStore.placeBid(route.params.id, bidAmount.value)
        showToast('Oferta została złożona!', 'success')
      }

      await fetchAuction() // Refresh data
      closeBidModal()
    } catch (error) {
      bidError.value = error.response?.data?.message || 'Nie udało się złożyć oferty'
    } finally {
      biddingLoading.value = false
    }
  }

  const togglePhoneVisibility = () => {
    showPhone.value = !showPhone.value
  }

  const copyPhoneToClipboard = async () => {
    if (auction.value?.seller_phone) {
      try {
        await navigator.clipboard.writeText(auction.value.seller_phone)
        showToast('Numer telefonu skopiowany do schowka', 'success')
      } catch (err) {
        console.error('Nie udało się skopiować:', err)
        showToast('Nie udało się skopiować numeru', 'error')
      }
    }
  }

  const sendMessage = () => {
    if (authStore.isAuthenticated && auction.value?.seller?.id) {
      showMessageModal.value = true
    }
  }

  const submitMessage = async () => {
    try {
      await api.post(`/messages/user/${auction.value.seller.id}`, {
        content: messageContent.value,
      })

      showToast('Wiadomość została wysłana pomyślnie!', 'success')
      showMessageModal.value = false
      messageContent.value = ''
    } catch (err) {
      console.error('Error sending message:', err)
      showToast(err.response?.data?.message || 'Nie udało się wysłać wiadomości', 'error')
    }
  }

  const showApproveConfirm = ref(false)

  const lightboxImage = ref(null)
  const openLightbox = (src) => {
    if (src) lightboxImage.value = src
  }

  const approveAuction = () => {
    showApproveConfirm.value = true
  }

  const confirmApproveAuction = async () => {
    showApproveConfirm.value = false
    try {
      await api.post(`/admin/auctions/${route.params.id}/approve`)
      showToast('Aukcja została zaakceptowana', 'success')
      await fetchAuction()
    } catch (error) {
      console.error('Error approving auction:', error)
      showToast('Nie udało się zaakceptować aukcji', 'error')
    }
  }

  // Kept at component scope so that unmounting can stop it: the handle used
  // to live inside onMounted, where nothing could ever clear it, and the
  // countdown went on ticking for every auction page the visitor had opened.
  let countdown = null

  onMounted(() => {
    fetchAuction()
    countdown = setInterval(updateTimeLeft, 1000)
  })

  const updateSeo = () => {
    if (auction.value) {
      useSeo({
        title: auction.value.title || 'Aukcja',
        description:
          auction.value.description?.substring(0, 160) ||
          'Przeglądaj aukcje gołębi. Uczestniczyj w licytacjach i zdobywaj najlepsze ptaki.',
        image:
          auction.value.images?.length > 0
            ? `${window.location.origin}/storage/${auction.value.images[0]}`
            : undefined,
      })
    }
  }

  onUnmounted(() => {
    disconnect()
    if (countdown !== null) {
      clearInterval(countdown)
      countdown = null
    }
  })
</script>

<style scoped>
  @keyframes slide-in {
    from {
      transform: translateX(100%);
      opacity: 0;
    }
    to {
      transform: translateX(0);
      opacity: 1;
    }
  }

  @keyframes shrink {
    from {
      width: 100%;
    }
    to {
      width: 0%;
    }
  }

  .animate-slide-in {
    animation: slide-in 0.3s ease-out;
  }

  .animate-shrink {
    animation: shrink 4s linear;
  }
</style>
