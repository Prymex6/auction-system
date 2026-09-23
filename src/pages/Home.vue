<template>
  <!-- Loading state -->
  <div v-if="loading" class="w-full h-screen flex items-center justify-center bg-white">
    <GlobalLoadingOverlay message="Ładowanie aukcji..." />
  </div>

  <!-- Main content -->
  <div v-else class="min-h-screen bg-white">
    <!-- ===== HERO ===== -->
    <section class="pt-10 pb-16 md:pt-14 md:pb-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <InactiveAccountAlert class="mb-6" />

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
          <!-- Left: copy -->
          <div>
            <div class="inline-flex items-center gap-2 text-sm text-gray-600 mb-6">
              <font-awesome-icon icon="fa-solid fa-shield-halved" class="w-4 h-4 text-blue-600" />
              <span>Zweryfikowani hodowcy i sprawdzone rodowody</span>
            </div>

            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-gray-900 leading-tight mb-6">
              Najlepsze aukcje gołębi<br />od najlepszych hodowców
            </h1>

            <p class="text-lg text-gray-600 mb-8 max-w-xl leading-relaxed">
              Premium giełda i marketplace dla pasjonatów gołębi pocztowych. Sprawdzone pochodzenie,
              uczciwe aukcje, prawdziwa pasja.
            </p>

            <div class="flex flex-wrap gap-3 mb-10">
              <router-link
                v-if="!authStore.isAuthenticated"
                to="/register"
                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold shadow-sm transition-colors"
              >
                Załóż konto
              </router-link>
              <router-link
                v-else-if="canList"
                to="/create-auction"
                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold shadow-sm transition-colors"
              >
                Wystaw gołębia
              </router-link>
              <router-link
                to="/auctions"
                class="px-6 py-3 border border-gray-300 text-gray-700 rounded-xl font-semibold hover:border-blue-400 hover:text-blue-600 transition-colors"
              >
                Przeglądaj aukcje
              </router-link>
            </div>

            <!-- Stats row -->
            <div class="flex flex-wrap gap-8">
              <div v-for="stat in heroStats" :key="stat.label" class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-full bg-blue-50 flex items-center justify-center">
                  <font-awesome-icon :icon="['fas', stat.icon]" class="w-5 h-5 text-blue-600" />
                </div>
                <div>
                  <div class="text-lg font-bold text-gray-900">{{ stat.value }}</div>
                  <div class="text-xs text-gray-500">{{ stat.label }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right: photo with floating card -->
          <div class="relative">
            <img
              :src="'/images/pigeons/hero-generated.png'"
              alt="Gołąb pocztowy"
              class="w-full h-[420px] lg:h-[480px] object-cover rounded-3xl shadow-lg"
            />
            <div
              class="absolute top-4 right-4 sm:top-6 sm:right-6 bg-white/95 backdrop-blur rounded-2xl shadow-xl border border-gray-100 pl-3 pr-4 py-2.5 flex items-center gap-2.5"
            >
              <img
                :src="'/images/logo-golab.png'"
                alt="Gołębiowy Lot"
                class="w-8 h-8 rounded-full"
              />
              <div class="leading-tight">
                <div class="text-sm font-bold text-gray-900">Gołębiowy Lot</div>
                <div class="text-[11px] text-gray-500">Zweryfikowana platforma</div>
              </div>
            </div>
            <div
              class="hidden lg:block absolute -bottom-6 -left-4 sm:left-6 bg-white rounded-2xl shadow-xl border border-gray-100 p-5 space-y-3"
            >
              <div
                v-for="stat in heroStats"
                :key="'card-' + stat.label"
                class="flex items-center gap-3"
              >
                <font-awesome-icon
                  :icon="['fas', stat.icon]"
                  class="w-4 h-4 text-blue-600 shrink-0"
                />
                <div>
                  <div class="text-sm font-bold text-gray-900 leading-none">{{ stat.value }}</div>
                  <div class="text-[11px] text-gray-500">{{ stat.label }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== WYRÓŻNIONE KATEGORIE ===== -->
    <section v-if="gridCategories.length > 0" class="py-14 bg-gray-50/60">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-2">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Wyróżnione kategorie</h2>
          <router-link
            to="/auctions"
            class="text-sm font-medium text-blue-600 hover:text-blue-700 inline-flex items-center gap-1"
          >
            Zobacz wszystkie aukcje
            <font-awesome-icon icon="fa-solid fa-chevron-right" class="w-4 h-4" />
          </router-link>
        </div>
        <p class="text-gray-600 mb-8">
          Poznaj najlepsze oferty wyselekcjonowane specjalnie dla Ciebie.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
          <div
            v-for="category in gridCategories"
            :key="category.id"
            class="flex flex-col min-w-0 bg-white rounded-2xl border border-gray-200 p-5 hover:shadow-md transition-shadow"
          >
            <div class="flex items-start gap-4 mb-4">
              <img
                :src="categoryThumb(category)"
                :alt="category.name"
                loading="lazy"
                class="w-14 h-14 rounded-2xl object-contain bg-white shrink-0 border border-gray-100 p-0.5"
              />
              <div class="min-w-0">
                <h3 class="font-bold text-gray-900 truncate">{{ category.name }}</h3>
                <p class="text-sm text-gray-500 line-clamp-2 min-h-10">
                  {{ category.description }}
                </p>
              </div>
            </div>

            <!-- Auction thumbnails -->
            <div v-if="category.auctions.length" class="grid grid-cols-4 gap-2 mb-4">
              <router-link
                v-for="auction in category.auctions.slice(0, 4)"
                :key="auction.id"
                :to="`/auctions/${auction.id}`"
                class="block rounded-lg overflow-hidden group"
              >
                <img
                  :src="auctionImage(auction)"
                  :alt="auction.title"
                  loading="lazy"
                  class="w-full h-16 object-contain bg-white group-hover:scale-105 transition-transform duration-300"
                />
              </router-link>
            </div>

            <div class="flex items-center justify-between text-sm mt-auto pt-2">
              <span class="text-gray-500">{{ category.auctions.length }} ofert</span>
              <router-link
                :to="`/auctions?category=${category.slug}`"
                class="font-medium text-blue-600 hover:text-blue-700 inline-flex items-center gap-1"
              >
                Zobacz wszystkie
                <font-awesome-icon icon="fa-solid fa-chevron-right" class="w-3.5 h-3.5" />
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ===== AKTUALNE AUKCJE (tylko gdy licytacje wlaczone) ===== -->
    <section v-if="biddingEnabled" class="py-14">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-2">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Aktualne aukcje</h2>
          <router-link
            to="/auctions"
            class="text-sm font-medium text-blue-600 hover:text-blue-700 inline-flex items-center gap-1"
          >
            Zobacz wszystkie aukcje
            <font-awesome-icon icon="fa-solid fa-chevron-right" class="w-4 h-4" />
          </router-link>
        </div>
        <p class="text-gray-600 mb-6">Wszystkie aukcje na żywo w jednym miejscu.</p>

        <!-- Filter chips -->
        <div class="flex flex-wrap gap-2.5 mb-8">
          <button
            v-for="chip in filterChips"
            :key="chip.id"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition-colors"
            :class="
              activeFilter === chip.id
                ? 'bg-blue-600 border-blue-600 text-white shadow-sm'
                : 'bg-white border-gray-200 text-gray-700 hover:border-blue-300'
            "
            @click="activeFilter = chip.id"
          >
            {{ chip.name }}
            <span
              class="text-[11px] px-1.5 py-0.5 rounded-md font-semibold"
              :class="
                activeFilter === chip.id ? 'bg-white/20 text-white' : 'bg-gray-100 text-gray-500'
              "
              >{{ chip.count }}</span
            >
          </button>
        </div>

        <!-- Auction cards -->
        <div
          v-if="filteredAuctions.length"
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5"
        >
          <router-link
            v-for="auction in filteredAuctions.slice(0, 6)"
            :key="auction.id"
            :to="`/auctions/${auction.id}`"
            class="group flex flex-col bg-white rounded-2xl border border-gray-200 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 overflow-hidden"
          >
            <div class="h-52 relative overflow-hidden bg-white shrink-0">
              <img
                :src="auctionImage(auction)"
                :alt="auction.title"
                loading="lazy"
                class="absolute inset-0 w-full h-full object-contain p-1 group-hover:scale-105 transition-transform duration-300"
              />
              <span
                class="absolute top-3 left-3 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-500 text-white shadow-sm"
              >
                Aukcja
              </span>
              <button
                class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 backdrop-blur flex items-center justify-center shadow-sm hover:scale-110 transition-transform"
                :title="isInWishlist(auction.id) ? 'Usuń z obserwowanych' : 'Obserwuj'"
                @click.prevent.stop="toggleWishlist(auction.id)"
              >
                <font-awesome-icon
                  :icon="isInWishlist(auction.id) ? 'fa-solid fa-heart' : 'fa-regular fa-heart'"
                  class="w-4 h-4"
                  :class="isInWishlist(auction.id) ? 'text-red-500' : 'text-gray-400'"
                />
              </button>
            </div>

            <div class="p-4 flex flex-col flex-1">
              <h3
                class="font-bold text-gray-900 truncate group-hover:text-blue-600 transition-colors mb-2"
              >
                {{ auction.title || 'Bez tytułu' }}
              </h3>

              <div class="flex flex-wrap gap-1.5 mb-3 min-h-6">
                <span
                  v-if="auction.ring_number"
                  class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 self-start whitespace-nowrap"
                >
                  {{ auction.ring_number }}
                </span>
                <span
                  v-if="auction.color"
                  class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 self-start whitespace-nowrap"
                >
                  {{ auction.color }}
                </span>
                <span
                  v-if="auction.gender"
                  class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 self-start whitespace-nowrap"
                >
                  {{ formatGender(auction.gender) }}
                </span>
                <span
                  v-if="auction.breed"
                  class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 self-start whitespace-nowrap"
                >
                  {{ auction.breed }}
                </span>
              </div>

              <p class="text-xl font-bold text-gray-900 mb-2.5 mt-auto">
                {{ formatPrice(auction.current_price || auction.start_price || 0) }}
              </p>

              <div
                class="flex items-center justify-between text-xs text-gray-500 border-t border-gray-100 pt-2.5"
              >
                <span>{{ auction.bids_count || 0 }} licytacji</span>
                <span
                  class="inline-flex items-center gap-1 font-medium"
                  :class="isEndingSoon(auction) ? 'text-red-500' : 'text-gray-600'"
                >
                  <font-awesome-icon icon="fa-solid fa-clock" class="w-3.5 h-3.5" />
                  {{ formatCountdown(auction.ends_at) }}
                </span>
              </div>
            </div>
          </router-link>
        </div>

        <!-- Empty state -->
        <div
          v-else
          class="text-center py-16 bg-gray-50 rounded-2xl border border-dashed border-gray-200"
        >
          <IconPicker name="pigeon" color-class="text-gray-300" class="w-14 h-14 mx-auto mb-4" />
          <h3 class="text-lg font-bold text-gray-900 mb-1">Brak aukcji w tym filtrze</h3>
          <p class="text-gray-500 text-sm">Wybierz inny filtr lub wróć za chwilę.</p>
        </div>
      </div>
    </section>

    <!-- ===== KUP TERAZ ===== -->
    <section v-if="buyNowAuctions.length" class="py-14 bg-gray-50/60">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-2">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Kup teraz</h2>
          <router-link
            to="/auctions"
            class="text-sm font-medium text-blue-600 hover:text-blue-700 inline-flex items-center gap-1"
          >
            Zobacz wszystkie (Kup teraz)
            <font-awesome-icon icon="fa-solid fa-chevron-right" class="w-4 h-4" />
          </router-link>
        </div>
        <p class="text-gray-600 mb-8">Najlepsze gołębie dostępne od ręki, bez licytacji.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <router-link
            v-for="auction in buyNowAuctions.slice(0, 4)"
            :key="auction.id"
            :to="`/auctions/${auction.id}`"
            class="group flex flex-col bg-white rounded-2xl border border-gray-200 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 overflow-hidden"
          >
            <div class="h-44 relative overflow-hidden bg-white shrink-0">
              <img
                :src="auctionImage(auction)"
                :alt="auction.title"
                loading="lazy"
                class="absolute inset-0 w-full h-full object-contain p-1 group-hover:scale-105 transition-transform duration-300"
              />
              <span
                class="absolute top-3 left-3 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-500 text-white shadow-sm"
              >
                Kup teraz
              </span>
              <button
                class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 backdrop-blur flex items-center justify-center shadow-sm hover:scale-110 transition-transform"
                @click.prevent.stop="toggleWishlist(auction.id)"
              >
                <font-awesome-icon
                  :icon="isInWishlist(auction.id) ? 'fa-solid fa-heart' : 'fa-regular fa-heart'"
                  class="w-4 h-4"
                  :class="isInWishlist(auction.id) ? 'text-red-500' : 'text-gray-400'"
                />
              </button>
            </div>

            <div class="p-4 flex flex-col flex-1">
              <h3
                class="font-bold text-gray-900 truncate group-hover:text-blue-600 transition-colors mb-2"
              >
                {{ auction.title || 'Bez tytułu' }}
              </h3>

              <div class="flex flex-wrap gap-1.5 mb-3 min-h-6">
                <span
                  v-if="auction.ring_number"
                  class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 self-start whitespace-nowrap"
                >
                  {{ auction.ring_number }}
                </span>
                <span
                  v-if="auction.color"
                  class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 self-start whitespace-nowrap"
                >
                  {{ auction.color }}
                </span>
                <span
                  v-if="auction.gender"
                  class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 self-start whitespace-nowrap"
                >
                  {{ formatGender(auction.gender) }}
                </span>
                <span
                  v-if="auction.breed"
                  class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 self-start whitespace-nowrap"
                >
                  {{ auction.breed }}
                </span>
              </div>

              <p class="text-xl font-bold text-gray-900 mt-auto">
                {{ formatPrice(auction.current_price || auction.start_price || 0) }}
              </p>
            </div>
          </router-link>
        </div>
      </div>
    </section>

    <!-- ===== KATEGORIE JAKO WŁASNE SEKCJE (ustawienie "Pokaż jako sekcję" w kategorii) ===== -->
    <section
      v-for="cat in sectionCategories"
      :key="'cat-section-' + cat.id"
      class="py-14 bg-gray-50/60"
    >
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-2">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900">{{ cat.name }}</h2>
          <router-link
            :to="`/auctions?category=${cat.slug}`"
            class="text-sm font-medium text-blue-600 hover:text-blue-700 inline-flex items-center gap-1"
          >
            Zobacz wszystkie ({{ cat.name }})
            <font-awesome-icon icon="fa-solid fa-chevron-right" class="w-4 h-4" />
          </router-link>
        </div>
        <p class="text-gray-600 mb-8">{{ cat.description }}</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
          <router-link
            v-for="auction in cat.auctions.slice(0, 4)"
            :key="auction.id"
            :to="`/auctions/${auction.id}`"
            class="group flex flex-col bg-white rounded-2xl border border-gray-200 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 overflow-hidden"
          >
            <div class="h-44 relative overflow-hidden bg-white shrink-0">
              <img
                :src="auctionImage(auction)"
                :alt="auction.title"
                loading="lazy"
                class="absolute inset-0 w-full h-full object-contain p-1 group-hover:scale-105 transition-transform duration-300"
              />
              <span
                class="absolute top-3 left-3 px-2.5 py-1 rounded-lg text-xs font-semibold bg-blue-600 text-white shadow-sm"
              >
                {{ auction.type === 'buy_now' ? 'Kup teraz' : 'Aukcja' }}
              </span>
              <button
                class="absolute top-3 right-3 w-8 h-8 rounded-full bg-white/90 backdrop-blur flex items-center justify-center shadow-sm hover:scale-110 transition-transform"
                @click.prevent.stop="toggleWishlist(auction.id)"
              >
                <font-awesome-icon
                  :icon="isInWishlist(auction.id) ? 'fa-solid fa-heart' : 'fa-regular fa-heart'"
                  class="w-4 h-4"
                  :class="isInWishlist(auction.id) ? 'text-red-500' : 'text-gray-400'"
                />
              </button>
            </div>

            <div class="p-4 flex flex-col flex-1">
              <h3
                class="font-bold text-gray-900 truncate group-hover:text-blue-600 transition-colors mb-2"
              >
                {{ auction.title || 'Bez tytułu' }}
              </h3>

              <div class="flex flex-wrap gap-1.5 mb-3 min-h-6">
                <span
                  v-if="auction.ring_number"
                  class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 self-start whitespace-nowrap"
                >
                  {{ auction.ring_number }}
                </span>
                <span
                  v-if="auction.color"
                  class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 self-start whitespace-nowrap"
                >
                  {{ auction.color }}
                </span>
                <span
                  v-if="auction.gender"
                  class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 self-start whitespace-nowrap"
                >
                  {{ formatGender(auction.gender) }}
                </span>
                <span
                  v-if="auction.breed"
                  class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-gray-100 text-gray-600 self-start whitespace-nowrap"
                >
                  {{ auction.breed }}
                </span>
              </div>

              <p class="text-xl font-bold text-gray-900 mt-auto">
                {{ formatPrice(auction.current_price || auction.start_price || 0) }}
              </p>
            </div>
          </router-link>
        </div>
      </div>
    </section>

    <!-- ===== Z BLOGA ===== -->
    <section v-if="blogs.length" class="py-14">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between mb-2">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900">Z bloga</h2>
          <router-link
            to="/blog"
            class="text-sm font-medium text-blue-600 hover:text-blue-700 inline-flex items-center gap-1"
          >
            Zobacz wszystkie artykuły
            <font-awesome-icon icon="fa-solid fa-chevron-right" class="w-4 h-4" />
          </router-link>
        </div>
        <p class="text-gray-600 mb-8">Porady, aktualności i inspiracje dla hodowców.</p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
          <router-link
            v-for="blog in blogs.slice(0, 3)"
            :key="blog.id"
            :to="`/blog/${blog.slug}`"
            class="group bg-white rounded-2xl border border-gray-200 hover:shadow-lg transition-all duration-300 overflow-hidden"
          >
            <div class="h-44 relative overflow-hidden">
              <img
                v-if="blog.image"
                :src="blog.image"
                :alt="blog.title"
                class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-300"
              />
              <div
                v-else
                class="w-full h-full"
                :style="`background: linear-gradient(135deg, ${getBlogColor(blog, 'color1')}, ${getBlogColor(blog, 'color2')})`"
              >
                <div class="absolute inset-0 flex items-center justify-center">
                  <div
                    class="text-6xl transform group-hover:scale-110 transition-transform duration-300"
                  >
                    {{ getBlogEmoji(blog) }}
                  </div>
                </div>
              </div>
            </div>
            <div class="p-5">
              <div class="flex items-center gap-2 text-xs mb-2">
                <span class="font-semibold text-blue-600 uppercase tracking-wide">{{
                  blog.category || 'Porady'
                }}</span>
                <span class="text-gray-400">{{
                  formatDate(blog.published_at || blog.created_at)
                }}</span>
              </div>
              <h3
                class="font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-blue-600 transition-colors"
              >
                {{ blog.title }}
              </h3>
              <p class="text-sm text-gray-500 line-clamp-2 mb-3">{{ blog.excerpt }}</p>
              <span class="text-sm font-medium text-blue-600 inline-flex items-center gap-1">
                Czytaj więcej
                <font-awesome-icon icon="fa-solid fa-chevron-right" class="w-3.5 h-3.5" />
              </span>
            </div>
          </router-link>
        </div>
      </div>
    </section>

    <!-- ===== CTA ===== -->
    <section class="relative py-20 overflow-hidden">
      <img
        :src="'/images/pigeons/pigeon-07.jpg'"
        alt=""
        loading="lazy"
        class="absolute inset-0 w-full h-full object-cover"
      />
      <div class="absolute inset-0 bg-slate-900/75"></div>
      <div class="relative max-w-3xl mx-auto px-4 text-center">
        <h2 class="text-3xl md:text-4xl font-bold text-white mb-3">
          Dołącz do naszej społeczności
        </h2>
        <p class="text-slate-300 mb-8">
          Załóż konto i rozpocznij swoją przygodę z najlepszymi gołębiami.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
          <router-link
            v-if="!authStore.isAuthenticated"
            to="/register"
            class="px-8 py-3.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl font-semibold shadow-lg transition-colors"
          >
            Załóż konto za darmo
          </router-link>
          <router-link
            to="/auctions"
            class="text-white/90 hover:text-white font-medium inline-flex items-center gap-1.5"
          >
            Przeglądaj aukcje
            <font-awesome-icon icon="fa-solid fa-arrow-right" class="w-4 h-4" />
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
  import { ref, onMounted, computed } from 'vue'
  import { useAuthStore } from '@/stores/auth'
  import { useSeo } from '@/composables/useSeo'
  import { usePlatformSettings } from '@/composables/usePlatformSettings'
  import { useFormatters } from '@/composables/useFormatters'
  import { useBlogFormatting } from '@/composables/useBlogFormatting'
  import api from '@/services/api'
  import InactiveAccountAlert from '@/components/InactiveAccountAlert.vue'
  import IconPicker from '@/components/icons/IconPicker.vue'
  import GlobalLoadingOverlay from '@/components/GlobalLoadingOverlay.vue'

  const { onlyAdminCanList, biddingEnabled, loadSettings } = usePlatformSettings()
  const { formatCountdown, formatGender, formatPrice } = useFormatters()
  const { getBlogEmoji, getBlogColor } = useBlogFormatting()

  useSeo({
    type: 'website',
  })

  const authStore = useAuthStore()
  // wspolnego composable). Regresja: przy dodawaniu wyjatku
  const canList = computed(
    () =>
      !onlyAdminCanList.value ||
      !!authStore.user?.is_admin ||
      !!authStore.user?.can_list_when_restricted
  )
  const auctions = ref([])
  const auctionsTotal = ref(0)
  const featuredCategories = ref([])
  const sectionCategories = computed(() =>
    featuredCategories.value.filter((c) => c.show_as_home_section)
  )
  const gridCategories = computed(() => featuredCategories.value.filter((c) => c.is_featured))
  const blogs = ref([])
  const loading = ref(true)
  const activeFilter = ref('all')
  const wishlist = ref([])

  const localPool = [
    'pigeon-08.jpg',
    'pigeon-09.jpg',
    'pigeon-10.jpg',
    'pigeon-11.jpg',
    'pigeon-14.jpg',
    'pigeon-15.jpg',
    'pigeon-16.jpg',
  ]
  const localImage = (seed) => `/images/pigeons/${localPool[Math.abs(seed) % localPool.length]}`

  const auctionImage = (auction) =>
    auction.pigeon_images?.[0] || auction.images?.[0] || localImage(auction.id)

  const categoryThumb = (category) =>
    category.auctions?.[0]?.pigeon_images?.[0] || localImage(category.id)

  // ===== Statystyki hero =====
  const iconGavel = 'gavel'
  const iconShield = 'shield-halved'
  const iconUsers = 'users'

  const platformStats = ref({ active_auctions: 0, breeders: 0, ended_auctions: 0 })

  const heroStats = computed(() => [
    {
      icon: iconGavel,
      value: (platformStats.value.active_auctions || auctionsTotal.value).toLocaleString('pl-PL'),
      label: 'Aukcje na żywo',
    },
    { icon: iconShield, value: '100%', label: 'Gwarancja pochodzenia' },
    {
      icon: iconUsers,
      value: (platformStats.value.breeders || 0).toLocaleString('pl-PL'),
      label: 'Zaufanych hodowców',
    },
  ])

  const fetchStats = async () => {
    const response = await fetch('/api/stats')
    if (!response.ok) return
    platformStats.value = await response.json()
  }

  // ===== Filtry =====
  const isEndingWithinHours = (auction, hours) => {
    if (!auction?.ends_at) return false
    const diff = new Date(auction.ends_at) - new Date()
    return diff > 0 && diff <= hours * 60 * 60 * 1000
  }

  const isNewWithinDays = (auction, days) => {
    if (!auction?.created_at) return false
    const diff = new Date() - new Date(auction.created_at)
    return diff >= 0 && diff <= days * 24 * 60 * 60 * 1000
  }

  const isEndingSoon = (auction) => isEndingWithinHours(auction, 24)

  const auctionsOnly = computed(() =>
    (auctions.value || []).filter((a) => a.type === 'auction' && a.status === 'active')
  )

  const filterChips = computed(() => {
    const list = auctionsOnly.value
    const chips = [
      { id: 'all', name: 'Wszystkie', count: list.length, alwaysShow: true },
      {
        id: 'ending24',
        name: 'Kończące się 24h',
        count: list.filter((a) => isEndingWithinHours(a, 24)).length,
      },
      {
        id: 'ending72',
        name: 'Kończące się 3 dni',
        count: list.filter((a) => isEndingWithinHours(a, 72)).length,
      },
      { id: 'new', name: 'Nowe (7 dni)', count: list.filter((a) => isNewWithinDays(a, 7)).length },
      {
        id: 'popular',
        name: 'Popularne (≥5)',
        count: list.filter((a) => (a.bids_count ?? 0) >= 5).length,
      },
    ]
    return chips.filter((c) => c.alwaysShow || c.count > 0)
  })

  const filteredAuctions = computed(() => {
    const list = auctionsOnly.value
    switch (activeFilter.value) {
      case 'ending24':
        return list.filter((a) => isEndingWithinHours(a, 24))
      case 'ending72':
        return list.filter((a) => isEndingWithinHours(a, 72))
      case 'new':
        return list.filter((a) => isNewWithinDays(a, 7))
      case 'popular':
        return list.filter((a) => (a.bids_count ?? 0) >= 5)
      default:
        return list
    }
  })

  const buyNowAuctions = computed(() =>
    (auctions.value || [])
      .filter((a) => a.type === 'buy_now' && a.status === 'active')
      .sort((a, b) => (b.bids_count || 0) - (a.bids_count || 0))
  )

  const wishlistKey = 'auctionWishlist'
  const loadWishlist = () => {
    try {
      wishlist.value = JSON.parse(localStorage.getItem(wishlistKey) || '[]')
    } catch {
      wishlist.value = []
    }
  }
  const isInWishlist = (id) => wishlist.value.includes(id)
  const toggleWishlist = (id) => {
    wishlist.value = isInWishlist(id)
      ? wishlist.value.filter((x) => x !== id)
      : [...wishlist.value, id]
    localStorage.setItem(wishlistKey, JSON.stringify(wishlist.value))
  }

  // ===== Fetch =====
  const fetchAuctions = async () => {
    const response = await fetch('/api/auctions?limit=200')
    if (!response.ok) return
    const data = await response.json()
    auctions.value = data.data || []
    auctionsTotal.value = data.meta?.total || auctions.value.length
  }

  const fetchFeaturedCategories = async () => {
    const response = await fetch('/api/categories/featured')
    if (!response.ok) return
    const data = await response.json()
    featuredCategories.value = data.data || []
  }

  const loadBlogs = async () => {
    const response = await api.get('/blogs?published=true')
    blogs.value = response.data.data || []
  }

  const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('pl-PL', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    })
  }

  onMounted(async () => {
    loadWishlist()
    loadSettings()
    const results = await Promise.allSettled([
      fetchAuctions(),
      fetchFeaturedCategories(),
      loadBlogs(),
      fetchStats(),
    ])
    results
      .filter((r) => r.status === 'rejected')
      .forEach((r) => console.error('Home load error:', r.reason))
    loading.value = false
  })
</script>
