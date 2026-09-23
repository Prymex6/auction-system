<template>
  <div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <HeroSection
      badge="Mój profil"
      :show-badge-dot="true"
      title="Witaj,"
      :gradient="`${user?.first_name && user?.last_name ? `${user.first_name} ${user.last_name}` : user?.name || 'Hodowco'}`"
      description="Zarządzaj swoim kontem, aukcjami i preferencjami w jednym miejscu"
    />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <InactiveAccountAlert />
    </div>

    <!-- Main Content -->
    <section class="py-8 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
          <!-- Sidebar -->
          <div class="lg:col-span-1">
            <div
              class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 sticky top-8 relative overflow-hidden"
            >
              <div
                class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -translate-y-16 translate-x-16"
              ></div>
              <div
                class="absolute bottom-0 left-0 w-32 h-32 bg-purple-500/5 rounded-full translate-y-16 -translate-x-16"
              ></div>

              <!-- Profile Avatar -->
              <div class="text-center mb-8">
                <div class="w-36 h-36 mx-auto mb-2">
                  <div
                    class="w-36 h-36 rounded-2xl bg-blue-500 flex items-center justify-center shadow-lg overflow-hidden"
                    :class="avatarPreview || user?.avatar ? 'border border-blue-100' : ''"
                  >
                    <img
                      v-if="avatarPreview || user?.avatar"
                      :src="avatarPreview || getAvatarUrl(user?.avatar)"
                      alt="Zdjęcie profilowe"
                      class="w-full h-full object-cover"
                    />
                    <font-awesome-icon
                      v-else
                      icon="fa-solid fa-user"
                      class="w-16 h-16 text-blue-600"
                    />
                  </div>
                </div>
                <button
                  type="button"
                  class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-blue-600 text-white shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all mb-2"
                  @click="$refs.avatarInput.click()"
                >
                  <font-awesome-icon icon="fa-solid fa-plus" class="w-4 h-4" />
                  Zmień zdjęcie
                </button>
                <div v-if="avatarPreview" class="flex items-center justify-center gap-2 mb-4">
                  <button
                    type="button"
                    class="text-xs text-gray-600 hover:text-gray-900 underline"
                    @click="removeAvatar"
                  >
                    Usuń wybrane
                  </button>
                </div>
                <input
                  ref="avatarInput"
                  type="file"
                  class="hidden"
                  accept="image/*"
                  @change="handleAvatarChange"
                />
                <h2 class="text-2xl font-bold text-gray-900 mb-3">{{ user?.name }}</h2>
                <p class="text-gray-600 mb-4">{{ user?.email }}</p>
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-blue-50 rounded-full">
                  <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                  <span class="text-sm font-medium text-gray-700">Aktywny</span>
                </div>
              </div>

              <!-- Stats -->
              <div class="space-y-4 mb-8">
                <div class="p-4 bg-blue-50 rounded-xl border border-blue-100 text-center">
                  <p class="text-sm text-gray-600 mb-2">Reputacja</p>
                  <p class="text-3xl font-bold text-blue-600 mb-2">
                    {{ stats?.average_rating?.toFixed(1) || '5.0' }}
                  </p>
                  <div class="flex justify-center gap-1">
                    <IconPicker name="star" color-class="text-yellow-400" class="w-5 h-5" />
                    <IconPicker name="star" color-class="text-yellow-400" class="w-5 h-5" />
                    <IconPicker name="star" color-class="text-yellow-400" class="w-5 h-5" />
                    <IconPicker name="star" color-class="text-yellow-400" class="w-5 h-5" />
                    <IconPicker name="star" color-class="text-yellow-400" class="w-5 h-5" />
                  </div>
                </div>

                <div class="p-4 bg-blue-50 rounded-xl border border-purple-100 text-center">
                  <p class="text-sm text-gray-600 mb-2">Aukcji</p>
                  <p class="text-3xl font-bold text-purple-600">
                    {{ stats?.active_auctions || 0 }}
                  </p>
                </div>

                <div
                  v-if="biddingEnabled"
                  class="p-4 bg-red-50 rounded-xl border border-pink-100 text-center"
                >
                  <p class="text-sm text-gray-600 mb-2">Wygrane licytacje</p>
                  <p class="text-3xl font-bold text-pink-600">{{ stats?.won_auctions || 0 }}</p>
                </div>
              </div>

              <!-- Navigation -->
              <nav class="space-y-2">
                <button
                  class="group w-full text-left px-4 py-3 rounded-xl transition-all duration-300 flex items-center gap-3"
                  :class="
                    activeTab === 'info'
                      ? 'bg-blue-600 text-white shadow-md'
                      : 'hover:bg-gray-50 border border-gray-100'
                  "
                  @click="activeTab = 'info'"
                >
                  <font-awesome-icon
                    icon="fa-solid fa-user"
                    class="w-5 h-5"
                    :class="activeTab === 'info' ? 'text-white' : 'text-gray-400'"
                  />
                  <span class="font-medium">Informacje</span>
                </button>

                <button
                  class="group w-full text-left px-4 py-3 rounded-xl transition-all duration-300 flex items-center gap-3"
                  :class="
                    activeTab === 'auctions'
                      ? 'bg-blue-600 text-white shadow-md'
                      : 'hover:bg-gray-50 border border-gray-100'
                  "
                  @click="activeTab = 'auctions'"
                >
                  <font-awesome-icon
                    icon="fa-solid fa-circle-check"
                    class="w-5 h-5"
                    :class="activeTab === 'auctions' ? 'text-white' : 'text-gray-400'"
                  />
                  <span class="font-medium">Moje aukcje</span>
                </button>

                <button
                  v-if="biddingEnabled"
                  class="group w-full text-left px-4 py-3 rounded-xl transition-all duration-300 flex items-center gap-3"
                  :class="
                    activeTab === 'bids'
                      ? 'bg-blue-600 text-white shadow-md'
                      : 'hover:bg-gray-50 border border-gray-100'
                  "
                  @click="activeTab = 'bids'"
                >
                  <font-awesome-icon
                    icon="fa-solid fa-sack-dollar"
                    class="w-5 h-5"
                    :class="activeTab === 'bids' ? 'text-white' : 'text-gray-400'"
                  />
                  <span class="font-medium">Moje licytacje</span>
                </button>

                <button
                  class="group w-full text-left px-4 py-3 rounded-xl transition-all duration-300 flex items-center gap-3"
                  :class="
                    activeTab === 'settings'
                      ? 'bg-blue-600 text-white shadow-md'
                      : 'hover:bg-gray-50 border border-gray-100'
                  "
                  @click="activeTab = 'settings'"
                >
                  <font-awesome-icon
                    icon="fa-solid fa-gear"
                    class="w-5 h-5"
                    :class="activeTab === 'settings' ? 'text-white' : 'text-gray-400'"
                  />
                  <span class="font-medium">Ustawienia</span>
                </button>
              </nav>
            </div>
          </div>

          <!-- Main Content -->
          <div class="lg:col-span-3">
            <!-- Info Tab -->
            <div v-if="activeTab === 'info'" class="space-y-8">
              <div
                class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 relative overflow-hidden"
              >
                <div
                  class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -translate-y-16 translate-x-16"
                ></div>

                <h3 class="text-2xl font-bold text-gray-900 mb-6">Informacje osobiste</h3>

                <form class="space-y-6" @submit.prevent="updateInfo">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2"> Imię </label>
                      <input
                        v-model="form.first_name"
                        type="text"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                        placeholder="Jan"
                      />
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2"> Nazwisko </label>
                      <input
                        v-model="form.last_name"
                        type="text"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                        placeholder="Kowalski"
                      />
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Nazwa wyświetlana
                      </label>
                      <input
                        v-model="form.name"
                        type="text"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                        placeholder="Jan Kowalski"
                      />
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2"> Email </label>
                      <input
                        v-model="form.email"
                        type="email"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-gray-500 cursor-not-allowed"
                        disabled
                      />
                    </div>
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2"> Telefon </label>
                      <input
                        v-model="form.phone"
                        type="tel"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                        placeholder="+48 123 456 789"
                      />
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2"> Kraj </label>
                      <select
                        v-model="form.country"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                      >
                        <option value="" disabled>Wybierz kraj</option>
                        <option
                          v-for="option in countryOptions"
                          :key="option.value"
                          :value="option.value"
                        >
                          {{ option.label }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"> Adres </label>
                    <input
                      v-model="form.address"
                      type="text"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                      placeholder="ul. Przykładowa 123"
                    />
                  </div>

                  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2"> Miasto </label>
                      <input
                        v-model="form.city"
                        type="text"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                        placeholder="Warszawa"
                      />
                    </div>

                    <div>
                      <label class="block text-sm font-medium text-gray-700 mb-2">
                        Kod pocztowy
                      </label>
                      <input
                        v-model="form.postcode"
                        type="text"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                        placeholder="00-000"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"> O mnie </label>
                    <textarea
                      v-model="form.bio"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm h-32"
                      placeholder="Opowiedz coś o sobie, swoich doświadczeniach hodowlanych..."
                    ></textarea>
                  </div>

                  <button
                    type="submit"
                    class="group relative px-8 py-4 bg-blue-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                  >
                    <div
                      class="absolute inset-0 bg-blue-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                    ></div>
                    <span class="relative flex items-center justify-center gap-2">
                      <font-awesome-icon icon="fa-solid fa-check" class="w-5 h-5" />
                      Zapisz zmiany
                    </span>
                  </button>
                </form>
              </div>
            </div>

            <!-- Auctions Tab -->
            <div v-if="activeTab === 'auctions'" class="space-y-6">
              <div class="flex justify-between items-center">
                <h3 class="text-2xl font-bold text-gray-900">Moje aukcje</h3>
                <router-link
                  v-if="authStore.user?.is_active && canList"
                  to="/create-auction"
                  class="group relative px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                >
                  <div
                    class="absolute inset-0 bg-blue-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                  ></div>
                  <span class="relative flex items-center justify-center gap-2">
                    <font-awesome-icon icon="fa-solid fa-plus" class="w-4 h-4" />
                    Nowa aukcja
                  </span>
                </router-link>
                <button
                  v-else
                  disabled
                  :title="cannotListReason"
                  class="px-6 py-3 bg-gray-300 text-gray-600 rounded-xl font-semibold cursor-not-allowed opacity-60 flex items-center gap-2"
                >
                  <font-awesome-icon icon="fa-solid fa-plus" class="w-4 h-4" />
                  Nowa aukcja
                </button>
              </div>

              <div v-if="myAuctions.length === 0" class="text-center py-16">
                <div
                  class="w-24 h-24 mx-auto mb-6 rounded-2xl bg-gray-100 flex items-center justify-center"
                >
                  <img :src="'/images/logo-golab.png'" alt="Gołębiowy Lot" class="w-12 h-12" />
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Brak aktywnych aukcji</h3>
                <p class="text-gray-600 mb-6">Rozpocznij sprzedaż swoich gołębi</p>
                <button
                  v-if="authStore.user?.is_active && canList"
                  class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl font-medium hover:shadow-md transition-all"
                  @click="$router.push('/create-auction')"
                >
                  Utwórz pierwszą aukcję
                  <font-awesome-icon icon="fa-solid fa-chevron-right" class="w-4 h-4" />
                </button>
                <button
                  v-else
                  disabled
                  :title="cannotListReason"
                  class="inline-flex items-center gap-2 px-6 py-3 bg-gray-300 text-gray-600 rounded-xl font-medium cursor-not-allowed opacity-60"
                >
                  Utwórz pierwszą aukcję
                </button>
              </div>

              <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <router-link
                  v-for="auction in myAuctions"
                  :key="auction.id"
                  :to="`/auctions/${auction.id}`"
                  class="group bg-white rounded-2xl border border-gray-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                >
                  <!-- Image -->
                  <div class="h-80 relative overflow-hidden bg-white">
                    <img
                      v-if="auction.pigeon_images?.[0] || auction.images?.[0]"
                      :src="auction.pigeon_images?.[0] || auction.images?.[0]"
                      :alt="auction.title"
                      class="absolute inset-0 w-full h-full object-contain p-2 group-hover:scale-110 transition-transform duration-300"
                    />
                    <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
                      <IconPicker
                        name="pigeon"
                        color-class="text-gray-300"
                        class="w-24 h-24 transform group-hover:scale-110 transition-transform duration-300"
                      />
                    </div>
                    <div class="absolute top-2 left-2 z-10">
                      <span
                        class="inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-xs font-semibold whitespace-nowrap"
                        :class="
                          auction.status === 'active'
                            ? 'bg-green-100 text-green-800'
                            : auction.status === 'ended'
                              ? 'bg-gray-100 text-gray-800'
                              : 'bg-yellow-100 text-yellow-800'
                        "
                      >
                        <span
                          class="w-1.5 h-1.5 rounded-full"
                          :class="
                            auction.status === 'active'
                              ? 'bg-green-500'
                              : auction.status === 'ended'
                                ? 'bg-gray-500'
                                : 'bg-yellow-500'
                          "
                        ></span>
                        {{
                          auction.status === 'active'
                            ? 'Aktywna'
                            : auction.status === 'ended'
                              ? 'Zakończona'
                              : 'Oczekująca'
                        }}
                      </span>
                    </div>
                  </div>

                  <!-- Content -->
                  <div class="p-6">
                    <h3
                      class="font-bold text-gray-900 text-lg mb-2 group-hover:text-blue-600 transition-colors duration-300 truncate"
                    >
                      {{ auction.title }}
                    </h3>
                    <div class="flex flex-wrap gap-1.5 mb-4">
                      <span
                        v-if="auction.ring_number"
                        class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 whitespace-nowrap"
                      >
                        {{ auction.ring_number }}
                      </span>
                      <span
                        v-if="auction.color"
                        class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 whitespace-nowrap"
                      >
                        {{ auction.color }}
                      </span>
                      <span
                        v-if="auction.gender"
                        class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 whitespace-nowrap"
                      >
                        {{ formatGender(auction.gender) }}
                      </span>
                      <span
                        v-if="auction.breed"
                        class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 whitespace-nowrap"
                      >
                        {{ auction.breed }}
                      </span>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-xl mb-4">
                      <p class="text-xs text-gray-500 mb-1">Aktualna cena</p>
                      <p class="text-2xl font-bold text-gray-900">
                        {{ formatPrice(auction.current_price || auction.start_price) }}
                      </p>
                    </div>

                    <template v-if="auction.type !== 'buy_now'">
                      <div class="flex items-center justify-between text-sm mb-4">
                        <div class="flex items-center gap-1 text-gray-600">
                          <font-awesome-icon icon="fa-solid fa-clipboard" class="w-4 h-4" />
                          <span>{{ auction.bids_count || 0 }} licytacji</span>
                        </div>
                        <span v-if="auction.status === 'active'" class="text-gray-600">{{
                          getTimeLeft(auction.ends_at)
                        }}</span>
                        <span
                          v-else-if="auction.status === 'pending' || auction.status === 'rejected'"
                          class="text-yellow-600 text-xs"
                          >Jeszcze się nie rozpoczęła</span
                        >
                        <span v-else class="text-gray-600">{{ getTimeLeft(auction.ends_at) }}</span>
                      </div>

                      <!-- Progress Bar -->
                      <div class="mb-4">
                        <div class="w-full bg-gray-200 rounded-full h-2">
                          <div
                            class="bg-blue-600 h-2 rounded-full"
                            :style="{ width: `${getTimeProgressPercent(auction)}%` }"
                          ></div>
                        </div>
                      </div>
                    </template>

                    <!-- Actions -->
                    <div v-if="auction.status === 'pending'" class="flex gap-3" @click.prevent>
                      <button
                        class="flex-1 py-3 px-4 text-center border border-blue-200 text-blue-700 rounded-xl font-medium hover:border-blue-300 hover:bg-blue-50 transition-all text-sm"
                        @click="editAuction(auction)"
                      >
                        Edytuj
                      </button>
                      <button
                        class="flex-1 py-3 px-4 text-center border border-red-200 text-red-700 rounded-xl font-medium hover:border-red-300 hover:bg-red-50 transition-all text-sm"
                        @click="deleteAuction(auction)"
                      >
                        Usuń
                      </button>
                    </div>
                    <button
                      v-else-if="auction.type !== 'auction' || auction.status !== 'active'"
                      class="w-full py-3 px-4 text-center border border-gray-200 text-gray-700 rounded-xl font-medium hover:border-blue-300 hover:bg-blue-50 transition-all text-sm"
                      @click.prevent="manageAuction(auction)"
                    >
                      Zarządzaj aukcją
                    </button>
                  </div>
                </router-link>
              </div>
            </div>

            <!-- Bids Tab -->
            <div v-if="activeTab === 'bids' && biddingEnabled" class="space-y-6">
              <h3 class="text-2xl font-bold text-gray-900">Moje licytacje</h3>

              <div v-if="myBids.length === 0" class="text-center py-16">
                <div
                  class="w-24 h-24 mx-auto mb-6 rounded-2xl bg-yellow-100 flex items-center justify-center"
                >
                  <IconPicker name="money" color-class="text-yellow-600" class="w-12 h-12" />
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Brak aktywnych licytacji</h3>
                <p class="text-gray-600 mb-6">Rozpocznij licytację na interesujących aukcjach</p>
                <router-link
                  to="/auctions"
                  class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 text-white rounded-xl font-medium hover:shadow-md transition-all"
                >
                  Przeglądaj aukcje
                  <font-awesome-icon icon="fa-solid fa-chevron-right" class="w-4 h-4" />
                </router-link>
              </div>

              <div v-else class="space-y-4">
                <div
                  v-for="bid in myBids"
                  :key="bid.id"
                  class="bg-white rounded-2xl border border-gray-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 p-6"
                >
                  <div class="flex justify-between items-start">
                    <div>
                      <h4 class="font-bold text-gray-900 text-lg mb-2">{{ bid.auction_title }}</h4>
                      <p class="text-sm text-gray-600 mb-4">{{ bid.breed || 'Gołąb pocztowy' }}</p>
                      <div class="flex items-center gap-4">
                        <div>
                          <p class="text-xs text-gray-500">Twoja oferta</p>
                          <p class="text-xl font-bold text-blue-600">
                            {{ formatPrice(bid.amount) }}
                          </p>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500">Aktualnie wygrywa</p>
                          <p class="text-lg font-semibold">{{ formatPrice(bid.highest_bid) }}</p>
                        </div>
                      </div>
                    </div>
                    <div>
                      <span
                        class="px-4 py-2 rounded-xl font-semibold text-sm inline-flex items-center gap-2"
                        :class="
                          bid.is_winning ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        "
                      >
                        <font-awesome-icon
                          :icon="bid.is_winning ? 'fa-solid fa-crown' : 'fa-solid fa-hourglass'"
                          class="w-4 h-4"
                        />
                        {{ bid.is_winning ? 'Wygrywam' : 'Przegrywam' }}
                      </span>
                    </div>
                  </div>

                  <div class="mt-6 pt-6 border-t border-gray-100">
                    <div class="flex justify-between items-center text-sm">
                      <span class="text-gray-600">{{ getTimeLeft(bid.auction_ends_at) }}</span>
                      <router-link
                        :to="`/auctions/${bid.auction_id}`"
                        class="text-blue-600 hover:text-blue-700 font-medium inline-flex items-center gap-1.5"
                      >
                        Przejdź do aukcji
                        <font-awesome-icon icon="fa-solid fa-chevron-right" class="w-3 h-3" />
                      </router-link>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Settings Tab -->
            <div v-if="activeTab === 'settings'" class="space-y-8">
              <!-- 2FA Settings -->
              <div
                class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 relative overflow-hidden"
              >
                <div
                  class="absolute top-0 right-0 w-32 h-32 bg-green-500/5 rounded-full -translate-y-16 translate-x-16"
                ></div>

                <h3 class="text-2xl font-bold text-gray-900 mb-6">Bezpieczeństwo konta</h3>

                <div
                  class="flex flex-col md:flex-row md:items-center justify-between p-6 bg-green-50 rounded-xl border border-green-100 mb-6"
                >
                  <div class="mb-4 md:mb-0">
                    <div class="flex items-center gap-3 mb-2">
                      <div
                        class="w-12 h-12 rounded-xl bg-emerald-500 flex items-center justify-center text-white"
                      >
                        <font-awesome-icon icon="fa-solid fa-lock" class="w-5 h-5" />
                      </div>
                      <div>
                        <p class="font-semibold text-gray-900">Autentykacja dwuetapowa (2FA)</p>
                        <p class="text-sm text-gray-600 mt-1">
                          {{ user?.two_factor_enabled ? 'Aktywna' : 'Nieaktywna' }}
                        </p>
                      </div>
                    </div>
                  </div>
                  <button
                    class="group px-6 py-3 rounded-xl font-semibold transition-all duration-300"
                    :class="
                      user?.two_factor_enabled
                        ? 'border-2 border-gray-200 text-gray-700 hover:border-red-300 hover:bg-red-50/50'
                        : 'bg-emerald-600 text-white shadow-lg hover:shadow-xl hover:-translate-y-1'
                    "
                    @click="toggle2FA"
                  >
                    {{ user?.two_factor_enabled ? 'Wyłącz 2FA' : 'Włącz 2FA' }}
                  </button>
                </div>

                <div class="text-sm text-gray-600 space-y-2">
                  <p class="flex items-center gap-2">
                    <font-awesome-icon icon="fa-solid fa-check" class="w-4 h-4 text-green-500" />
                    Dodatkowa warstwa bezpieczeństwa
                  </p>
                  <p class="flex items-center gap-2">
                    <font-awesome-icon icon="fa-solid fa-check" class="w-4 h-4 text-green-500" />
                    Wymagany kod z aplikacji autentykacyjnej
                  </p>
                  <p class="flex items-center gap-2">
                    <font-awesome-icon icon="fa-solid fa-check" class="w-4 h-4 text-green-500" />
                    Chroni przed nieautoryzowanym dostępem
                  </p>
                </div>
              </div>

              <!-- Change Password -->
              <div
                class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 relative overflow-hidden"
              >
                <div
                  class="absolute top-0 right-0 w-32 h-32 bg-orange-500/5 rounded-full -translate-y-16 translate-x-16"
                ></div>

                <h3 class="text-2xl font-bold text-gray-900 mb-6">Zmień hasło</h3>

                <form class="space-y-6" @submit.prevent="changePassword">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Obecne hasło
                    </label>
                    <input
                      v-model="passwordForm.current_password"
                      type="password"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                      placeholder="••••••••"
                      required
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"> Nowe hasło </label>
                    <input
                      v-model="passwordForm.new_password"
                      type="password"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                      placeholder="••••••••"
                      required
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      Potwierdź nowe hasło
                    </label>
                    <input
                      v-model="passwordForm.new_password_confirmation"
                      type="password"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                      placeholder="••••••••"
                      required
                    />
                  </div>

                  <button
                    type="submit"
                    class="group relative px-8 py-4 bg-orange-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                  >
                    <div
                      class="absolute inset-0 bg-orange-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                    ></div>
                    <span class="relative flex items-center justify-center gap-2">
                      <font-awesome-icon icon="fa-solid fa-key" class="w-5 h-5" />
                      Zmień hasło
                    </span>
                  </button>
                </form>
              </div>

              <div
                class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 relative overflow-hidden"
              >
                <div
                  class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -translate-y-16 translate-x-16"
                ></div>

                <h3 class="text-2xl font-bold text-gray-900 mb-6">Preferencje i prywatność</h3>

                <div class="space-y-6">
                  <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center"
                      >
                        <font-awesome-icon
                          icon="fa-solid fa-envelope"
                          class="w-5 h-5 text-blue-600"
                        />
                      </div>
                      <div>
                        <p class="font-semibold text-gray-900">Powiadomienia e-mail</p>
                        <p class="text-sm text-gray-600">
                          Otrzymuj informacje o nowych ofertach i aukcjach
                        </p>
                      </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input
                        v-model="preferences.email_notifications"
                        type="checkbox"
                        class="sr-only peer"
                        :disabled="preferencesLoading"
                        @change="savePreferences"
                      />
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600 peer-disabled:opacity-40 peer-disabled:cursor-not-allowed"
                      ></div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center"
                      >
                        <font-awesome-icon
                          icon="fa-solid fa-bell"
                          class="w-5 h-5 text-purple-600"
                        />
                      </div>
                      <div>
                        <p class="font-semibold text-gray-900">Powiadomienia push</p>
                        <p class="text-sm text-gray-600">
                          Natychmiastowe alerty w przeglądarce / na telefonie
                        </p>
                      </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input
                        v-model="preferences.push_notifications"
                        type="checkbox"
                        class="sr-only peer"
                        :disabled="preferencesLoading"
                        @change="onTogglePushNotifications"
                      />
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600 peer-disabled:opacity-40 peer-disabled:cursor-not-allowed"
                      ></div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center"
                      >
                        <font-awesome-icon
                          icon="fa-solid fa-gavel"
                          class="w-5 h-5 text-amber-600"
                        />
                      </div>
                      <div>
                        <p class="font-semibold text-gray-900">Powiadomienia o licytacjach</p>
                        <p class="text-sm text-gray-600">Gdy ktoś przebije Twoją ofertę</p>
                      </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input
                        v-model="preferences.bid_notifications"
                        type="checkbox"
                        class="sr-only peer"
                        :disabled="preferencesLoading"
                        @change="savePreferences"
                      />
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-amber-600 peer-disabled:opacity-40 peer-disabled:cursor-not-allowed"
                      ></div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center"
                      >
                        <font-awesome-icon
                          icon="fa-solid fa-comment-dots"
                          class="w-5 h-5 text-teal-600"
                        />
                      </div>
                      <div>
                        <p class="font-semibold text-gray-900">Powiadomienia o wiadomościach</p>
                        <p class="text-sm text-gray-600">Gdy otrzymasz nową wiadomość</p>
                      </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input
                        v-model="preferences.message_notifications"
                        type="checkbox"
                        class="sr-only peer"
                        :disabled="preferencesLoading"
                        @change="savePreferences"
                      />
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-teal-600 peer-disabled:opacity-40 peer-disabled:cursor-not-allowed"
                      ></div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center"
                      >
                        <font-awesome-icon
                          icon="fa-solid fa-hourglass"
                          class="w-5 h-5 text-orange-600"
                        />
                      </div>
                      <div>
                        <p class="font-semibold text-gray-900">Powiadomienia o końcu aukcji</p>
                        <p class="text-sm text-gray-600">Gdy Twoja aukcja się kończy</p>
                      </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input
                        v-model="preferences.auction_end_notifications"
                        type="checkbox"
                        class="sr-only peer"
                        :disabled="preferencesLoading"
                        @change="savePreferences"
                      />
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-600 peer-disabled:opacity-40 peer-disabled:cursor-not-allowed"
                      ></div>
                    </label>
                  </div>

                  <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center"
                      >
                        <font-awesome-icon icon="fa-solid fa-eye" class="w-5 h-5 text-green-600" />
                      </div>
                      <div>
                        <p class="font-semibold text-gray-900">Publiczny profil</p>
                        <p class="text-sm text-gray-600">
                          Inni użytkownicy mogą zobaczyć Twój profil i aukcje
                        </p>
                      </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input v-model="settings.is_public" type="checkbox" class="sr-only peer" />
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"
                      ></div>
                    </label>
                  </div>

                  <!-- <div class="p-4 bg-gray-50/50 rounded-xl">
                    <label class="block text-sm font-medium text-gray-700 mb-3">
                      Język interfejsu
                    </label>
                    <select
                      v-model="settings.language"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    >
                      <option value="pl">🇵🇱 Polski</option>
                      <option value="en">🇬🇧 English</option>
                      <option value="de">🇩🇪 Deutsch</option>
                      <option value="fr">🇫🇷 Français</option>
                    </select>
                  </div> -->

                  <button
                    class="group relative px-8 py-4 bg-blue-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                    @click="saveSettings"
                  >
                    <div
                      class="absolute inset-0 bg-blue-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                    ></div>
                    <span class="relative flex items-center justify-center gap-2">
                      Zapisz preferencje
                    </span>
                  </button>
                </div>
              </div>

              <!-- Aktywne urzadzenia -->
              <div
                class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 relative overflow-hidden"
              >
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Aktywne urządzenia</h3>
                <p class="text-gray-600 mb-6">
                  Zarządzaj urządzeniami, które mają dostęp do Twojego konta.
                </p>

                <div v-if="devicesLoading" class="py-8 flex justify-center">
                  <Spinner />
                </div>
                <div v-else class="space-y-3">
                  <div
                    v-for="device in devices"
                    :key="device.id"
                    class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl"
                  >
                    <div class="flex items-center gap-3 min-w-0">
                      <div
                        class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center shrink-0"
                      >
                        <font-awesome-icon
                          :icon="
                            device.device_type === 'web'
                              ? 'fa-solid fa-desktop'
                              : 'fa-solid fa-mobile-screen-button'
                          "
                          class="w-5 h-5 text-gray-600"
                        />
                      </div>
                      <div class="min-w-0">
                        <p class="font-semibold text-gray-900 truncate">
                          {{ device.device_name }}
                          <span
                            v-if="device.is_current"
                            class="ml-2 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-xs font-medium"
                            >Obecne</span
                          >
                        </p>
                        <p class="text-sm text-gray-600 truncate">{{ device.ip_address }}</p>
                        <p class="text-xs text-gray-500">
                          Ostatnia aktywność: {{ device.last_activity_at }}
                        </p>
                      </div>
                    </div>
                    <button
                      v-if="!device.is_current"
                      :disabled="deviceLogoutBusy === device.id"
                      class="shrink-0 px-3 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition-colors text-sm font-medium disabled:opacity-50"
                      @click="logoutDevice(device.id)"
                    >
                      Wyloguj
                    </button>
                  </div>

                  <p v-if="devices.length === 0" class="text-center py-6 text-gray-500">
                    Brak aktywnych urządzeń
                  </p>

                  <div v-if="devices.length > 1" class="pt-4 border-t border-gray-100">
                    <button
                      :disabled="logoutAllBusy"
                      class="px-5 py-2.5 rounded-xl bg-red-600 text-white font-semibold hover:bg-red-700 transition-colors disabled:opacity-50"
                      @click="logoutAllDevices"
                    >
                      Wyloguj wszystkie pozostałe urządzenia
                    </button>
                  </div>
                </div>
              </div>

              <!-- Eksport danych (RODO, art. 20) -->
              <div
                class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8 relative overflow-hidden"
              >
                <h3 class="text-2xl font-bold text-gray-900 mb-2">Twoje dane</h3>
                <p class="text-gray-600 mb-6">
                  Pobierz kopię wszystkich swoich danych przechowywanych na platformie (dane konta,
                  aukcje, wiadomości, recenzje, oferty licytacji) w formacie JSON.
                </p>
                <button
                  :disabled="exportBusy"
                  class="group relative px-8 py-4 bg-gray-900 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:translate-y-0"
                  @click="exportMyData"
                >
                  <span class="relative flex items-center justify-center gap-2">
                    <font-awesome-icon icon="fa-solid fa-download" class="w-4 h-4" />
                    {{ exportBusy ? 'Przygotowywanie...' : 'Eksportuj moje dane' }}
                  </span>
                </button>
              </div>

              <!-- Dangerous Zone -->
              <div
                class="bg-red-50 rounded-2xl border border-red-200 shadow-xl p-8 relative overflow-hidden"
              >
                <div
                  class="absolute top-0 right-0 w-32 h-32 bg-red-500/5 rounded-full -translate-y-16 translate-x-16"
                ></div>

                <h3 class="text-2xl font-bold text-red-900 mb-6">Niebezpieczna strefa</h3>
                <p class="text-gray-700 mb-6">
                  Te akcje mogą mieć trwały wpływ na twoje konto. Upewnij się, że wiesz co robisz.
                </p>

                <div class="space-y-4">
                  <button
                    class="group w-full flex items-center justify-between p-4 bg-white rounded-xl border border-red-200 hover:border-red-300 hover:shadow-md transition-all duration-300"
                    @click="logout"
                  >
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                        <font-awesome-icon
                          icon="fa-solid fa-arrow-right-from-bracket"
                          class="w-5 h-5 text-red-600"
                        />
                      </div>
                      <div class="text-left">
                        <p class="font-semibold text-gray-900">Wyloguj się</p>
                        <p class="text-sm text-gray-600">Zakończ aktualną sesję</p>
                      </div>
                    </div>
                    <font-awesome-icon
                      icon="fa-solid fa-chevron-right"
                      class="w-5 h-5 text-gray-400 group-hover:text-red-500 transition-colors"
                    />
                  </button>

                  <button
                    class="group w-full flex items-center justify-between p-4 bg-white rounded-xl border border-red-200 hover:border-red-300 hover:shadow-md transition-all duration-300"
                    @click="openDeleteAccount"
                  >
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                        <font-awesome-icon icon="fa-solid fa-trash" class="w-5 h-5 text-red-600" />
                      </div>
                      <div class="text-left">
                        <p class="font-semibold text-gray-900">Usuń konto</p>
                        <p class="text-sm text-gray-600">
                          Trwale usuń swoje konto i wszystkie dane
                        </p>
                      </div>
                    </div>
                    <font-awesome-icon
                      icon="fa-solid fa-chevron-right"
                      class="w-5 h-5 text-gray-400 group-hover:text-red-500 transition-colors"
                    />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Potwierdzenie usunięcia aukcji -->
    <ConfirmModal
      :show="auctionToDelete !== null"
      title="Usuń aukcję"
      :message="
        auctionToDelete
          ? `Czy na pewno chcesz usunąć aukcję „${auctionToDelete.title}”? Tej operacji nie można cofnąć.`
          : ''
      "
      confirm-text="Usuń"
      danger
      @confirm="confirmDeleteAuction"
      @cancel="auctionToDelete = null"
    />

    <!-- 2FA: włączanie -->
    <div
      v-if="twoFactorModal === 'setup'"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="twoFactorModal = null"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-8 max-h-[90vh] overflow-y-auto"
        @click.stop
      >
        <h3 class="text-xl font-bold text-gray-900 mb-2">Włącz weryfikację dwuetapową</h3>
        <p class="text-sm text-gray-600 mb-4">
          Zeskanuj poniższy kod QR aplikacją uwierzytelniającą (np. Google Authenticator, Authy), a
          następnie wpisz wygenerowany kod.
        </p>
        <div
          v-if="twoFactorQrCode"
          class="flex justify-center mb-4 p-4 bg-white rounded-xl border border-gray-200"
        >
          <qrcode-vue :value="twoFactorQrCode" :size="180" level="M" />
        </div>
        <div class="mb-4">
          <p class="text-xs font-semibold text-gray-500 uppercase mb-1">Lub wpisz sekret ręcznie</p>
          <p class="font-mono text-sm bg-gray-100 rounded-lg px-3 py-2 break-all select-all">
            {{ twoFactorSecret || 'brak' }}
          </p>
        </div>
        <div v-if="twoFactorBackupCodes.length" class="mb-5">
          <p class="text-xs font-semibold text-gray-500 uppercase mb-1">
            Kody zapasowe — zapisz je w bezpiecznym miejscu
          </p>
          <div class="grid grid-cols-2 gap-2">
            <span
              v-for="code in twoFactorBackupCodes"
              :key="code"
              class="font-mono text-sm bg-gray-100 rounded-lg px-3 py-1.5 text-center select-all"
              >{{ code }}</span
            >
          </div>
        </div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Kod z aplikacji</label>
        <input
          v-model="twoFactorCode"
          type="text"
          inputmode="numeric"
          maxlength="6"
          placeholder="123456"
          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all mb-5 font-mono tracking-widest text-center"
          @keyup.enter="confirm2FASetup"
        />
        <div class="flex gap-3">
          <button
            :disabled="twoFactorBusy || !twoFactorCode"
            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            @click="confirm2FASetup"
          >
            {{ twoFactorBusy ? 'Weryfikacja...' : 'Włącz 2FA' }}
          </button>
          <button
            class="flex-1 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50 transition-all"
            @click="twoFactorModal = null"
          >
            Anuluj
          </button>
        </div>
      </div>
    </div>

    <!-- 2FA: wyłączanie -->
    <div
      v-if="twoFactorModal === 'disable'"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="twoFactorModal = null"
    >
      <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8" @click.stop>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Wyłącz weryfikację dwuetapową</h3>
        <p class="text-sm text-gray-600 mb-5">Ze względów bezpieczeństwa potwierdź hasłem.</p>
        <label class="block text-sm font-medium text-gray-700 mb-2">Hasło</label>
        <input
          v-model="twoFactorPassword"
          type="password"
          placeholder="••••••••"
          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all mb-5"
          @keyup.enter="confirm2FADisable"
        />
        <div class="flex gap-3">
          <button
            :disabled="twoFactorBusy || !twoFactorPassword"
            class="flex-1 px-6 py-3 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            @click="confirm2FADisable"
          >
            {{ twoFactorBusy ? 'Wyłączanie...' : 'Wyłącz 2FA' }}
          </button>
          <button
            class="flex-1 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50 transition-all"
            @click="twoFactorModal = null"
          >
            Anuluj
          </button>
        </div>
      </div>
    </div>

    <!-- Usuwanie konta -->
    <div
      v-if="deleteAccountModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="deleteAccountModal = false"
    >
      <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8" @click.stop>
        <h3 class="text-xl font-bold text-gray-900 mb-2">Usuń konto</h3>
        <p class="text-sm text-gray-600 mb-5">
          Ta operacja jest nieodwracalna. Twoje dane osobowe zostaną trwale usunięte, a konto
          zablokowane. Historia aukcji, wiadomości i recenzji zostanie zachowana dla innych
          użytkowników, ale bez Twoich danych osobowych. Potwierdź hasłem, aby kontynuować.
        </p>
        <label class="block text-sm font-medium text-gray-700 mb-2">Hasło</label>
        <input
          v-model="deleteAccountPassword"
          type="password"
          placeholder="••••••••"
          class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-200 focus:outline-none transition-all mb-5"
          @keyup.enter="confirmDeleteAccount"
        />
        <div class="flex gap-3">
          <button
            :disabled="deleteAccountBusy || !deleteAccountPassword"
            class="flex-1 px-6 py-3 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
            @click="confirmDeleteAccount"
          >
            {{ deleteAccountBusy ? 'Usuwanie...' : 'Usuń konto' }}
          </button>
          <button
            class="flex-1 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50 transition-all"
            @click="deleteAccountModal = false"
          >
            Anuluj
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, computed, onMounted, watch } from 'vue'
  import { useRouter, useRoute } from 'vue-router'
  import { useAuthStore } from '@/stores/auth'
  import { HeroSection } from '@/components'
  import api from '@/services/api'
  import { useSeo } from '@/composables/useSeo'
  import { usePlatformSettings } from '@/composables/usePlatformSettings'
  import { useFormatters } from '@/composables/useFormatters'
  import { countryOptions } from '@/constants/countries'
  import InactiveAccountAlert from '@/components/InactiveAccountAlert.vue'
  import IconPicker from '@/components/icons/IconPicker.vue'
  import ConfirmModal from '@/components/ConfirmModal.vue'
  import { useToast } from '@/composables/useToast'
  import QrcodeVue from 'qrcode.vue'
  import { usePushNotifications } from '@/composables/usePushNotifications'
  import Spinner from '@/components/feedback/Spinner.vue'

  const router = useRouter()
  const route = useRoute()
  const authStore = useAuthStore()
  const {
    onlyAdminCanList,
    biddingEnabled,
    loadSettings: loadPlatformSettings,
  } = usePlatformSettings()
  loadPlatformSettings()
  // composable). Regresja: przy dodawaniu wyjatku can_list_when_restricted
  const canList = computed(
    () =>
      !onlyAdminCanList.value ||
      !!authStore.user?.is_admin ||
      !!authStore.user?.can_list_when_restricted
  )
  // prawdziwy powod blokady to zupelnie co innego (only_admin_can_list) -
  // myloco usera co do faktycznego stanu jego konta.
  const cannotListReason = computed(() => {
    if (!authStore.user?.is_active) return 'Konto musi być potwierdzone przez administratora'
    if (!canList.value)
      return 'Wystawianie aukcji jest obecnie dostępne wyłącznie dla administracji serwisu'
    return ''
  })
  const { getPlatformName } = usePlatformSettings()
  const { formatPrice, formatGender } = useFormatters()
  const { showSuccess, showError } = useToast()
  const push = usePushNotifications()

  const activeTab = ref('info')

  function applyTabFromQuery() {
    if (route.query.tab === 'my-auctions') {
      activeTab.value = 'auctions'
    } else if (route.query.tab === 'settings') {
      activeTab.value = 'settings'
    } else {
      activeTab.value = 'info'
    }
  }
  watch(() => route.query.tab, applyTabFromQuery)

  const user = ref(null)
  const myAuctions = ref([])
  const myBids = ref([])
  const stats = ref(null)
  const avatarFile = ref(null)
  const avatarPreview = ref('')
  const form = ref({
    first_name: '',
    last_name: '',
    name: '',
    email: '',
    phone: '',
    country: '',
    address: '',
    city: '',
    postcode: '',
    bio: '',
  })
  const passwordForm = ref({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
  })
  const settings = ref({
    is_public: true,
    language: 'pl',
  })
  const preferences = ref({
    email_notifications: true,
    sound_notifications: true,
    push_notifications: true,
    bid_notifications: true,
    message_notifications: true,
    auction_end_notifications: true,
  })
  const preferencesLoading = ref(true)
  const devices = ref([])
  const devicesLoading = ref(false)
  const deviceLogoutBusy = ref(null)
  const logoutAllBusy = ref(false)

  const getTimeLeft = (endsAt) => {
    if (!endsAt) return ''
    const end = new Date(endsAt)
    const now = new Date()
    const diff = end - now
    if (diff <= 0) return 'Skończona'

    const days = Math.floor(diff / (1000 * 60 * 60 * 24))
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))

    if (days > 0) return `${days}d ${hours}h`
    return `${hours}h ${minutes}m`
  }

  const getTimeProgressPercent = (auction) => {
    if (!auction.started_at || !auction.ends_at) return 0

    const start = new Date(auction.started_at)
    const end = new Date(auction.ends_at)
    const now = new Date()

    const totalTime = end - start
    const remainingTime = end - now

    if (remainingTime <= 0) return 0
    if (totalTime <= 0) return 100

    return Math.max(0, (remainingTime / totalTime) * 100)
  }

  const editAuction = (auction) => {
    router.push(`/auctions/${auction.id}/edit`)
  }

  const auctionToDelete = ref(null)

  const deleteAuction = (auction) => {
    auctionToDelete.value = auction
  }

  const confirmDeleteAuction = async () => {
    const auction = auctionToDelete.value
    auctionToDelete.value = null
    if (!auction) return

    try {
      await api.delete(`/auctions/${auction.id}`)
      myAuctions.value = myAuctions.value.filter((a) => a.id !== auction.id)
      showSuccess('Aukcja została usunięta')
    } catch (err) {
      showError(err.response?.data?.message || 'Błąd podczas usuwania aukcji')
      console.error(err)
    }
  }

  const manageAuction = (auction) => {
    router.push(`/auctions/${auction.id}/edit`)
  }

  const updateInfo = async () => {
    try {
      if (avatarFile.value) {
        const formData = new FormData()
        Object.entries(form.value).forEach(([key, value]) => {
          if (value !== null && value !== undefined) {
            formData.append(key, value)
          }
        })
        formData.append('avatar', avatarFile.value)
        formData.append('_method', 'PATCH')
        await api.post('/profile', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })
      } else {
        await api.patch('/profile', form.value)
      }
      showSuccess('Profil został zaktualizowany!')
      const response = await api.get('/auth/me')
      authStore.user = response.data.user
      user.value = response.data.user
      avatarFile.value = null
      avatarPreview.value = ''
    } catch (error) {
      console.error('Error updating profile:', error)
      showError(error.response?.data?.message || 'Błąd podczas aktualizacji profilu')
    }
  }

  const getAvatarUrl = (path) => {
    if (!path) return ''
    if (path.startsWith('http')) return path
    return `${window.location.origin}/storage/${path}`
  }

  const handleAvatarChange = (event) => {
    const file = event.target.files?.[0]
    if (!file) return
    avatarFile.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      avatarPreview.value = e.target?.result || ''
    }
    reader.readAsDataURL(file)
  }

  const removeAvatar = () => {
    avatarFile.value = null
    avatarPreview.value = ''
  }

  const changePassword = async () => {
    if (passwordForm.value.new_password !== passwordForm.value.new_password_confirmation) {
      showError('Nowe hasła nie są identyczne!')
      return
    }

    try {
      await api.post('/profile/change-password', passwordForm.value)
      showSuccess('Hasło zostało zmienione!')
      passwordForm.value = {
        current_password: '',
        new_password: '',
        new_password_confirmation: '',
      }
    } catch (error) {
      console.error('Error changing password:', error)
      showError(error.response?.data?.message || 'Błąd podczas zmiany hasła')
    }
  }

  const twoFactorModal = ref(null) // null | 'setup' | 'disable'
  const twoFactorSecret = ref('')
  const twoFactorQrCode = ref('')
  const twoFactorBackupCodes = ref([])
  const twoFactorCode = ref('')
  const twoFactorPassword = ref('')
  const twoFactorBusy = ref(false)

  const toggle2FA = async () => {
    if (user.value?.two_factor_enabled) {
      twoFactorPassword.value = ''
      twoFactorModal.value = 'disable'
      return
    }
    try {
      const setupRes = await api.post('/auth/2fa/setup')
      twoFactorSecret.value = setupRes.data?.secret || ''
      twoFactorQrCode.value = setupRes.data?.qr_code || ''
      twoFactorBackupCodes.value = setupRes.data?.backup_codes || []
      twoFactorCode.value = ''
      twoFactorModal.value = 'setup'
    } catch (error) {
      console.error('Error setting up 2FA:', error)
      showError(error.response?.data?.message || 'Błąd podczas konfiguracji 2FA')
    }
  }

  const refresh2FAUser = async () => {
    const response = await api.get('/auth/me')
    authStore.user = response.data.user
    user.value = response.data.user
  }

  const confirm2FASetup = async () => {
    if (!twoFactorCode.value) return
    twoFactorBusy.value = true
    try {
      await api.post('/auth/2fa/confirm', { code: twoFactorCode.value })
      showSuccess('Weryfikacja dwuetapowa została włączona')
      twoFactorModal.value = null
      await refresh2FAUser()
    } catch (error) {
      console.error('Error confirming 2FA:', error)
      showError(error.response?.data?.message || 'Nieprawidłowy kod 2FA')
    } finally {
      twoFactorBusy.value = false
    }
  }

  const confirm2FADisable = async () => {
    if (!twoFactorPassword.value) return
    twoFactorBusy.value = true
    try {
      await api.post('/auth/2fa/disable', { password: twoFactorPassword.value })
      showSuccess('Weryfikacja dwuetapowa została wyłączona')
      twoFactorModal.value = null
      await refresh2FAUser()
    } catch (error) {
      console.error('Error disabling 2FA:', error)
      showError(error.response?.data?.message || 'Nie udało się wyłączyć 2FA')
    } finally {
      twoFactorBusy.value = false
    }
  }

  const saveSettings = async () => {
    try {
      await api.patch('/profile', { is_public: settings.value.is_public })
      if (authStore.user) {
        authStore.user.is_public = settings.value.is_public
      }
      showSuccess('Preferencje zapisane')
    } catch (error) {
      console.error('Error saving preferences:', error)
      showError(error.response?.data?.message || 'Błąd podczas zapisu preferencji')
    }
  }

  const fetchPreferences = async () => {
    try {
      const response = await api.get('/profile/notification-preferences')
      if (response.data?.data) {
        preferences.value = response.data.data
      }
    } catch (error) {
      console.error('Error fetching notification preferences:', error)
    }
  }

  const savePreferences = async () => {
    try {
      await api.patch('/profile/notification-preferences', preferences.value)
    } catch (error) {
      console.error('Error saving notification preferences:', error)
      showError('Nie udało się zapisać preferencji powiadomień')
    }
  }

  const onTogglePushNotifications = async () => {
    if (preferences.value.push_notifications) {
      const ok = await push.subscribe()
      if (!ok) {
        preferences.value.push_notifications = false
        showError(
          push.isSupported
            ? 'Musisz zezwolić na powiadomienia w przeglądarce'
            : 'Twoja przeglądarka nie wspiera powiadomień push'
        )
        return
      }
      showSuccess('Powiadomienia push włączone na tym urządzeniu')
    } else {
      await push.unsubscribe()
    }
    await savePreferences()
  }

  const fetchDevices = async () => {
    devicesLoading.value = true
    try {
      const response = await api.get('/devices')
      devices.value = response.data.data
    } catch (error) {
      console.error('Error fetching devices:', error)
    } finally {
      devicesLoading.value = false
    }
  }

  const logoutDevice = async (deviceId) => {
    deviceLogoutBusy.value = deviceId
    try {
      await api.delete(`/devices/${deviceId}`)
      devices.value = devices.value.filter((d) => d.id !== deviceId)
      showSuccess('Urządzenie zostało wylogowane')
    } catch (error) {
      console.error('Error logging out device:', error)
      showError('Nie udało się wylogować urządzenia')
    } finally {
      deviceLogoutBusy.value = null
    }
  }

  const logoutAllDevices = async () => {
    logoutAllBusy.value = true
    try {
      const response = await api.post('/devices/logout-others')
      showSuccess(response.data.message)
      await fetchDevices()
    } catch (error) {
      console.error('Error logging out devices:', error)
      showError('Nie udało się wylogować urządzeń')
    } finally {
      logoutAllBusy.value = false
    }
  }

  const logout = async () => {
    await authStore.logout()
    router.push('/login')
  }

  // Eksport danych (prawo do przenoszenia danych - RODO, art. 20)
  const exportBusy = ref(false)

  const exportMyData = async () => {
    exportBusy.value = true
    try {
      const response = await api.get('/profile/export', { responseType: 'blob' })
      const blobUrl = window.URL.createObjectURL(
        new Blob([response.data], { type: 'application/json' })
      )
      const link = document.createElement('a')
      link.href = blobUrl
      link.download = `moje-dane-golebiowylot-${user.value?.id || ''}.json`
      document.body.appendChild(link)
      link.click()
      link.remove()
      window.URL.revokeObjectURL(blobUrl)
    } catch (error) {
      console.error('Error exporting data:', error)
      showError('Nie udało się pobrać danych')
    } finally {
      exportBusy.value = false
    }
  }

  const deleteAccountModal = ref(false)

  // Always open the dialog with an empty password box, never with whatever
  // was typed the last time it was opened.
  const openDeleteAccount = () => {
    deleteAccountPassword.value = ''
    deleteAccountModal.value = true
  }
  const deleteAccountPassword = ref('')
  const deleteAccountBusy = ref(false)

  const confirmDeleteAccount = async () => {
    if (!deleteAccountPassword.value) return
    deleteAccountBusy.value = true
    try {
      await api.delete('/profile', { data: { password: deleteAccountPassword.value } })
      deleteAccountModal.value = false
      await authStore.logout()
      router.push('/')
      showSuccess('Twoje konto zostało usunięte')
    } catch (error) {
      console.error('Error deleting account:', error)
      showError(error.response?.data?.message || 'Nie udało się usunąć konta')
    } finally {
      deleteAccountBusy.value = false
    }
  }

  onMounted(async () => {
    const { getPlatformName } = usePlatformSettings()
    useSeo({
      title: `Mój profil - ${getPlatformName.value}`,
      description: 'Zarządzaj swoim kontem, aukcjami i preferencjami.',
      keywords: 'profil, aukcje, hodowcy',
      ogTitle: `Mój profil - ${getPlatformName.value}`,
      ogDescription: 'Zarządzaj swoim kontem i aukcjami',
      canonical: window.location.origin + '/profile',
    })

    user.value = authStore.user
    if (user.value) {
      form.value = {
        first_name: user.value.first_name || '',
        last_name: user.value.last_name || '',
        name: user.value.name || '',
        email: user.value.email || '',
        phone: user.value.phone || '',
        country: user.value.country || '',
        address: user.value.address || '',
        city: user.value.city || '',
        postcode: user.value.postcode || '',
        bio: user.value.bio || '',
      }
      settings.value.is_public = user.value.is_public !== false

      api
        .get(`/users/${user.value.id}/stats`)
        .then((res) => {
          if (res.data.data) {
            stats.value = res.data.data
          }
        })
        .catch((err) => console.error('Error loading stats:', err))

      api
        .get('/auctions/my')
        .then((res) => {
          myAuctions.value = res.data.data || []
        })
        .catch((err) => console.error('Error loading auctions:', err))
    }

    applyTabFromQuery()

    fetchDevices()
    try {
      await fetchPreferences()
    } finally {
      preferencesLoading.value = false
    }
    await push.checkSubscription()
    preferences.value.push_notifications = push.isSubscribed.value
  })
</script>
<style scoped>
  .line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    line-clamp: 1;
    overflow: hidden;
  }
</style>
