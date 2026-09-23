<template>
  <div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <HeroSection
      badge="Panel administratora"
      title="Zarządzaj"
      gradient="platformą"
      description="Monitoruj aktywność, zarządzaj użytkownikami i konfiguruj ustawienia platformy"
    />

    <!-- Main Content -->
    <section class="py-8 bg-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
          <StatCard label="Użytkownicy" :value="stats.users" icon="users" color="blue" />
          <StatCard label="Aukcje" :value="stats.auctions" icon="auctions" color="purple" />
          <StatCard label="Zgłoszenia" :value="reports.length" icon="reports" color="pink" />
          <StatCard label="Zabanowani" :value="stats.banned" icon="banned" color="orange" />
        </div>

        <!-- Tabs -->
        <div class="flex flex-wrap gap-2 mb-8">
          <button
            class="group px-6 py-3 rounded-xl font-semibold transition-all duration-300 flex items-center gap-2"
            :class="
              activeTab === 'analytics'
                ? 'bg-blue-600 text-white shadow-md'
                : 'bg-white border border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50/50'
            "
            @click="activeTab = 'analytics'"
          >
            <font-awesome-icon icon="fa-solid fa-chart-line" class="w-5 h-5" />
            Statystyki
          </button>
          <button
            class="group px-6 py-3 rounded-xl font-semibold transition-all duration-300 flex items-center gap-2"
            :class="
              activeTab === 'users'
                ? 'bg-blue-600 text-white shadow-md'
                : 'bg-white border border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50/50'
            "
            @click="activeTab = 'users'"
          >
            <font-awesome-icon icon="fa-solid fa-users" class="w-5 h-5" />
            Użytkownicy
          </button>
          <button
            class="group px-6 py-3 rounded-xl font-semibold transition-all duration-300 flex items-center gap-2"
            :class="
              activeTab === 'auctions'
                ? 'bg-blue-600 text-white shadow-md'
                : 'bg-white border border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50/50'
            "
            @click="activeTab = 'auctions'"
          >
            <font-awesome-icon icon="fa-solid fa-circle-check" class="w-5 h-5" />
            Aukcje
          </button>
          <button
            class="group px-6 py-3 rounded-xl font-semibold transition-all duration-300 flex items-center gap-2"
            :class="
              activeTab === 'reports'
                ? 'bg-blue-600 text-white shadow-md'
                : 'bg-white border border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50/50'
            "
            @click="activeTab = 'reports'"
          >
            <font-awesome-icon icon="fa-solid fa-list-ul" class="w-5 h-5" />
            Zgłoszenia
          </button>
          <button
            class="group px-6 py-3 rounded-xl font-semibold transition-all duration-300 flex items-center gap-2"
            :class="
              activeTab === 'blogs'
                ? 'bg-blue-600 text-white shadow-md'
                : 'bg-white border border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50/50'
            "
            @click="activeTab = 'blogs'"
          >
            <font-awesome-icon icon="fa-solid fa-pen-to-square" class="w-5 h-5" />
            Blogi
          </button>

          <button
            class="group px-6 py-3 rounded-xl font-semibold transition-all duration-300 flex items-center gap-2"
            :class="
              activeTab === 'categories'
                ? 'bg-blue-600 text-white shadow-md'
                : 'bg-white border border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50/50'
            "
            @click="activeTab = 'categories'"
          >
            <font-awesome-icon icon="fa-solid fa-tag" class="w-5 h-5" />
            Kategorie
          </button>

          <button
            class="group px-6 py-3 rounded-xl font-semibold transition-all duration-300 flex items-center gap-2"
            :class="
              activeTab === 'pages'
                ? 'bg-blue-600 text-white shadow-md'
                : 'bg-white border border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50/50'
            "
            @click="activeTab = 'pages'"
          >
            <font-awesome-icon icon="fa-solid fa-file-lines" class="w-5 h-5" />
            Strony
          </button>

          <button
            class="group px-6 py-3 rounded-xl font-semibold transition-all duration-300 flex items-center gap-2"
            :class="
              activeTab === 'settings'
                ? 'bg-blue-600 text-white shadow-md'
                : 'bg-white border border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50/50'
            "
            @click="activeTab = 'settings'"
          >
            <font-awesome-icon icon="fa-solid fa-gear" class="w-5 h-5" />
            Ustawienia
          </button>

          <button
            class="group px-6 py-3 rounded-xl font-semibold transition-all duration-300 flex items-center gap-2"
            :class="
              activeTab === 'errors'
                ? 'bg-blue-600 text-white shadow-md'
                : 'bg-white border border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50/50'
            "
            @click="activeTab = 'errors'"
          >
            <font-awesome-icon icon="fa-solid fa-triangle-exclamation" class="w-5 h-5" />
            Błędy
          </button>
        </div>

        <!-- Analytics Tab -->
        <div v-if="activeTab === 'analytics'" class="space-y-6">
          <div class="bg-white rounded-2xl border border-gray-100 shadow-xl p-6">
            <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
              <h2 class="text-xl font-bold text-gray-900">Statystyki platformy</h2>
              <div class="flex flex-wrap gap-2">
                <button
                  v-for="opt in analyticsPeriodOptions"
                  :key="opt.value"
                  class="px-4 py-2 rounded-lg text-sm font-semibold transition-colors"
                  :class="
                    analyticsPeriod === opt.value
                      ? 'bg-blue-600 text-white'
                      : 'bg-gray-50 text-gray-600 hover:bg-gray-100'
                  "
                  @click="analyticsPeriod = opt.value"
                >
                  {{ opt.label }}
                </button>
              </div>
            </div>

            <div v-if="analyticsLoading" class="py-16 flex justify-center">
              <Spinner />
            </div>

            <template v-else-if="analyticsData">
              <!-- KPI cards -->
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
                <button
                  v-for="metric in analyticsMetricCards"
                  :key="metric.key"
                  class="text-left p-4 rounded-xl border-2 transition-colors"
                  :class="
                    analyticsChartMetric === metric.key
                      ? 'border-blue-400 bg-blue-50/50'
                      : 'border-gray-100 hover:border-gray-200'
                  "
                  @click="analyticsChartMetric = metric.key"
                >
                  <p class="text-xs font-medium text-gray-500 mb-1">{{ metric.label }}</p>
                  <p class="text-2xl font-bold text-gray-900">
                    {{ metric.value.toLocaleString('pl-PL') }}
                  </p>
                  <p
                    class="text-xs font-semibold mt-1 flex items-center gap-1"
                    :class="metric.changePct >= 0 ? 'text-green-600' : 'text-red-600'"
                  >
                    <font-awesome-icon
                      :icon="['fas', metric.changePct >= 0 ? 'arrow-trend-up' : 'arrow-trend-down']"
                      class="w-3 h-3"
                    />
                    {{ metric.changePct >= 0 ? '+' : '' }}{{ metric.changePct }}% vs poprzedni okres
                  </p>
                </button>
              </div>

              <!-- Chart -->
              <div class="border border-gray-100 rounded-2xl p-5 mb-8">
                <p class="text-sm font-semibold text-gray-700 mb-4">
                  {{ analyticsMetricCards.find((m) => m.key === analyticsChartMetric)?.label }} w
                  czasie
                </p>
                <svg viewBox="0 0 300 100" preserveAspectRatio="none" class="w-full h-48">
                  <defs>
                    <linearGradient id="analyticsChartGradient" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="0%" stop-color="#2563eb" stop-opacity="0.25" />
                      <stop offset="100%" stop-color="#2563eb" stop-opacity="0" />
                    </linearGradient>
                  </defs>
                  <polygon :points="analyticsAreaPoints" fill="url(#analyticsChartGradient)" />
                  <polyline
                    :points="analyticsLinePoints"
                    fill="none"
                    stroke="#2563eb"
                    stroke-width="2"
                    vector-effect="non-scaling-stroke"
                  />
                </svg>
                <div class="flex justify-between text-xs text-gray-400 mt-2">
                  <span>{{ formatChartDate(analyticsData.series[0]?.date) }}</span>
                  <span>{{
                    formatChartDate(analyticsData.series[analyticsData.series.length - 1]?.date)
                  }}</span>
                </div>
              </div>

              <!-- Top pages -->
              <div>
                <p class="text-sm font-semibold text-gray-700 mb-3">
                  Najczęściej odwiedzane strony
                </p>
                <div v-if="!analyticsData.top_pages.length" class="text-sm text-gray-400">
                  Brak danych w tym okresie.
                </div>
                <div v-else class="space-y-2">
                  <div
                    v-for="page in analyticsData.top_pages"
                    :key="page.path"
                    class="flex items-center justify-between px-4 py-2.5 rounded-xl bg-gray-50"
                  >
                    <span class="text-sm font-medium text-gray-700 truncate">{{ page.path }}</span>
                    <span class="text-sm font-semibold text-gray-900">{{
                      page.views.toLocaleString('pl-PL')
                    }}</span>
                  </div>
                </div>
              </div>
            </template>
          </div>
        </div>

        <!-- Users Tab -->
        <div
          v-if="activeTab === 'users'"
          class="bg-white rounded-2xl border border-gray-100 shadow-xl overflow-hidden"
        >
          <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900">Zarządzanie użytkownikami</h2>
            <button
              class="px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2"
              @click="openAddUserModal"
            >
              <font-awesome-icon icon="fa-solid fa-plus" class="w-5 h-5" />
              Dodaj użytkownika
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Imię
                  </th>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Email
                  </th>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Aukcji
                  </th>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Reputacja
                  </th>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Status
                  </th>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Akcje
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-if="!users.length" class="hover:bg-gray-50/50">
                  <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center gap-2">
                      <font-awesome-icon icon="fa-solid fa-users" class="w-8 h-8 text-gray-300" />
                      <p class="text-sm font-medium">Brak użytkowników</p>
                    </div>
                  </td>
                </tr>
                <tr
                  v-for="user in users"
                  :key="user.id"
                  class="hover:bg-gray-50/50 transition-colors duration-200"
                >
                  <td class="px-3 py-3">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center text-sm font-semibold"
                      >
                        <img
                          v-if="user.avatar"
                          :src="getAvatarUrl(user.avatar)"
                          alt="Avatar"
                          class="w-full h-full object-cover rounded-full"
                        />
                        <span v-else>{{ user.name.charAt(0).toUpperCase() }}</span>
                      </div>
                      <div>
                        <a
                          :href="`/profile/${user.id}`"
                          class="font-medium text-blue-600 hover:text-blue-800 hover:underline cursor-pointer"
                        >
                          {{ user.name }}
                        </a>
                        <p class="text-xs text-gray-500">
                          {{ user.first_name }} {{ user.last_name }}
                        </p>
                      </div>
                    </div>
                  </td>
                  <td class="px-3 py-3 text-gray-700">{{ user.email }}</td>
                  <td class="px-3 py-3">
                    <span
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-sm font-medium"
                    >
                      {{ user.auctions_count }}
                    </span>
                  </td>
                  <td class="px-3 py-3">
                    <span class="inline-flex items-center gap-1 text-sm font-medium text-gray-700">
                      <font-awesome-icon icon="fa-solid fa-star" class="w-4 h-4 text-yellow-400" />
                      {{ user.reputation }}
                    </span>
                  </td>
                  <td class="px-3 py-3">
                    <span
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium"
                      :class="
                        user.is_active
                          ? 'bg-green-100 text-green-800'
                          : 'bg-yellow-100 text-yellow-800'
                      "
                    >
                      <span
                        class="w-1.5 h-1.5 rounded-full"
                        :class="user.is_active ? 'bg-green-500' : 'bg-yellow-500'"
                      ></span>
                      {{ user.is_active ? 'Aktywny' : 'Oczekuje aktywacji' }}
                    </span>
                  </td>
                  <td class="px-3 py-3 flex gap-2">
                    <button
                      v-if="!user.is_active"
                      :disabled="!user.email_verified_at"
                      :title="
                        !user.email_verified_at
                          ? 'Użytkownik musi najpierw potwierdzić adres e-mail'
                          : ''
                      "
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border text-sm font-medium transition-all duration-300"
                      :class="
                        user.email_verified_at
                          ? 'border-green-200 text-green-700 hover:border-green-300 hover:bg-green-50/50'
                          : 'border-gray-200 text-gray-400 cursor-not-allowed'
                      "
                      @click="activateUser(user)"
                    >
                      <font-awesome-icon icon="fa-solid fa-circle-check" class="w-4 h-4" />
                      Aktywuj
                    </button>
                    <button
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300 text-sm font-medium"
                      @click="editUser(user)"
                    >
                      <font-awesome-icon icon="fa-solid fa-pen-to-square" class="w-4 h-4" />
                      Edytuj
                    </button>
                    <button
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-red-200 text-red-700 hover:border-red-300 hover:bg-red-50/50 transition-all duration-300 text-sm font-medium"
                      @click="banUser(user)"
                    >
                      <font-awesome-icon icon="fa-solid fa-certificate" class="w-4 h-4" />
                      Ban
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Auctions Tab -->
        <div
          v-if="activeTab === 'auctions'"
          class="bg-white rounded-2xl border border-gray-100 shadow-xl overflow-hidden"
        >
          <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900">Zarządzanie aukcjami</h2>
            <button
              class="px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2"
              @click="openAddAuctionModal"
            >
              <font-awesome-icon icon="fa-solid fa-plus" class="w-5 h-5" />
              Dodaj aukcję
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Tytuł
                  </th>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Hodowca
                  </th>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Rasa
                  </th>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Licytacje
                  </th>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Status
                  </th>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Akcje
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-if="!auctions.length" class="hover:bg-gray-50/50">
                  <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center gap-2">
                      <font-awesome-icon
                        icon="fa-solid fa-circle-check"
                        class="w-8 h-8 text-gray-300"
                      />
                      <p class="text-sm font-medium">Brak aukcji</p>
                    </div>
                  </td>
                </tr>
                <tr
                  v-for="auction in auctions"
                  :key="auction.id"
                  class="hover:bg-gray-50/50 transition-colors duration-200"
                >
                  <td class="px-3 py-3">
                    <div class="flex items-center gap-3">
                      <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center">
                        <font-awesome-icon
                          icon="fa-solid fa-circle-check"
                          class="w-5 h-5 text-blue-600"
                        />
                      </div>
                      <a
                        :href="`/auctions/${auction.id}`"
                        class="font-medium text-blue-600 hover:text-blue-800 hover:underline cursor-pointer"
                      >
                        {{ auction.title || 'Bez tytułu' }}
                      </a>
                    </div>
                  </td>
                  <td class="px-3 py-3">
                    <a
                      v-if="auction.seller"
                      :href="`/profile/${auction.seller.id}`"
                      class="text-blue-600 hover:text-blue-800 hover:underline cursor-pointer"
                    >
                      {{ auction.seller.name }}
                    </a>
                    <span v-else class="text-gray-400">-</span>
                  </td>
                  <td class="px-3 py-3">
                    <span
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-purple-50 text-purple-700 text-sm font-medium"
                    >
                      {{ auction.breed }}
                    </span>
                  </td>
                  <td class="px-3 py-3">
                    <span
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-pink-50 text-pink-700 text-sm font-medium"
                    >
                      {{ auction.bids_count }}
                    </span>
                  </td>
                  <td class="px-3 py-3">
                    <span
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium"
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
                        :class="{
                          'bg-green-500': auction.status === 'active',
                          'bg-gray-500': auction.status === 'ended',
                          'bg-yellow-500': auction.status === 'pending',
                        }"
                      ></span>
                      {{
                        auction.status === 'active'
                          ? 'Aktywna'
                          : auction.status === 'ended'
                            ? 'Zakończona'
                            : 'Oczekuje'
                      }}
                    </span>
                  </td>
                  <td class="px-3 py-3 flex gap-2">
                    <button
                      v-if="auction.status === 'pending'"
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-green-200 text-green-700 hover:border-green-300 hover:bg-green-50/50 transition-all duration-300 text-sm font-medium"
                      @click="activateAuction(auction)"
                    >
                      <font-awesome-icon icon="fa-solid fa-circle-check" class="w-4 h-4" />
                      Aktywuj
                    </button>
                    <button
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300 text-sm font-medium"
                      @click="editAuction(auction)"
                    >
                      <font-awesome-icon icon="fa-solid fa-pen-to-square" class="w-4 h-4" />
                      Edytuj
                    </button>
                    <button
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-red-200 text-red-700 hover:border-red-300 hover:bg-red-50/50 transition-all duration-300 text-sm font-medium"
                      @click="deleteAuctionConfirm(auction)"
                    >
                      <font-awesome-icon icon="fa-solid fa-trash" class="w-4 h-4" />
                      Usuń
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Reports Tab -->
        <div v-if="activeTab === 'reports'" class="space-y-6">
          <div
            v-if="!reports.length"
            class="bg-white rounded-2xl border border-gray-100 shadow-xl p-12 text-center"
          >
            <div class="flex flex-col items-center gap-3">
              <font-awesome-icon icon="fa-solid fa-circle-check" class="w-12 h-12 text-gray-300" />
              <p class="text-lg font-medium text-gray-500">Brak zgłoszeń</p>
              <p class="text-sm text-gray-400">Wszystkie zgłoszenia zostały rozpatrzone</p>
            </div>
          </div>
          <div
            v-for="report in reports"
            :key="report.id"
            class="bg-white rounded-2xl border border-gray-100 shadow-xl p-6 hover:shadow-2xl hover:-translate-y-1 transition-all duration-300"
          >
            <div class="flex justify-between items-start mb-6">
              <div>
                <h3 class="font-bold text-xl text-gray-900 mb-2">Powód: {{ report.reason }}</h3>
                <div class="flex flex-wrap gap-4">
                  <div class="flex items-center gap-2">
                    <font-awesome-icon icon="fa-solid fa-user" class="w-4 h-4 text-gray-400" />
                    <span class="text-sm text-gray-600">
                      Zgłaszający:
                      <a
                        v-if="report.reporter"
                        :href="`/profile/${report.reporter.id}`"
                        class="font-bold text-blue-600 hover:text-blue-800 hover:underline cursor-pointer"
                      >
                        {{ report.reporter.name }}
                      </a>
                      <strong v-else class="text-gray-900">Nieznany</strong>
                    </span>
                  </div>
                  <div class="flex items-center gap-2">
                    <font-awesome-icon icon="fa-solid fa-calendar" class="w-4 h-4 text-gray-400" />
                    <span class="text-sm text-gray-600">
                      Data:
                      <strong class="text-gray-900">{{ formatDate(report.created_at) }}</strong>
                    </span>
                  </div>
                </div>
              </div>
              <span
                class="inline-flex items-center gap-1 px-4 py-2 rounded-full text-sm font-medium"
                :class="
                  report.status === 'pending'
                    ? 'bg-red-100 text-red-800'
                    : report.status === 'reviewing'
                      ? 'bg-yellow-100 text-yellow-800'
                      : 'bg-green-100 text-green-800'
                "
              >
                <span
                  class="w-1.5 h-1.5 rounded-full"
                  :class="{
                    'bg-red-500': report.status === 'pending',
                    'bg-yellow-500': report.status === 'reviewing',
                    'bg-green-500': report.status === 'resolved',
                  }"
                ></span>
                {{
                  report.status === 'pending'
                    ? 'Nowe'
                    : report.status === 'reviewing'
                      ? 'W toku'
                      : 'Rozwiązane'
                }}
              </span>
            </div>

            <div
              v-if="report.reported_user"
              class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200"
            >
              <p class="text-sm text-gray-700">
                <strong>Zgłoszony użytkownik:</strong>
                <a
                  :href="`/profile/${report.reported_user.id}`"
                  class="text-blue-600 hover:text-blue-800 hover:underline cursor-pointer font-medium"
                >
                  {{ report.reported_user.name }}
                </a>
                ({{ report.reported_user.email }})
                <span
                  v-if="report.reported_user.is_banned"
                  class="ml-2 px-2 py-1 bg-red-100 text-red-700 text-xs rounded-full font-medium"
                  >ZABANOWANY</span
                >
              </p>
            </div>

            <p class="text-gray-700 mb-6 bg-gray-50/50 p-4 rounded-xl">{{ report.description }}</p>

            <div class="flex gap-3">
              <button
                class="group inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:shadow-md transition-all"
                @click="handleReport(report)"
              >
                <font-awesome-icon icon="fa-solid fa-check" class="w-4 h-4" />
                Rozpatrz
              </button>
              <button
                class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 text-gray-700 rounded-lg font-medium hover:border-gray-300 hover:bg-gray-50/50 transition-all"
                @click="dismissReport(report)"
              >
                <font-awesome-icon icon="fa-solid fa-xmark" class="w-4 h-4" />
                Odrzuć
              </button>
            </div>
          </div>
        </div>

        <!-- Blogs Tab -->
        <div
          v-if="activeTab === 'blogs'"
          class="bg-white rounded-2xl border border-gray-100 shadow-xl overflow-hidden"
        >
          <div class="p-6 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900">Zarządzanie artykułami bloga</h2>
            <button
              class="px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2"
              @click="openAddBlogModal"
            >
              <font-awesome-icon icon="fa-solid fa-plus" class="w-5 h-5" />
              Dodaj artykuł
            </button>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Tytuł
                  </th>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Kategoria
                  </th>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Status
                  </th>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Wyświetlenia
                  </th>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Data
                  </th>
                  <th
                    class="px-3 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide"
                  >
                    Akcje
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-if="!blogs.length" class="hover:bg-gray-50/50">
                  <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                    <div class="flex flex-col items-center gap-2">
                      <font-awesome-icon
                        icon="fa-solid fa-circle-check"
                        class="w-8 h-8 text-gray-300"
                      />
                      <p class="text-sm font-medium">Brak artykułów</p>
                    </div>
                  </td>
                </tr>
                <tr
                  v-for="blog in blogs"
                  :key="blog.id"
                  class="hover:bg-gray-50/50 transition-colors duration-200"
                >
                  <td class="px-3 py-3">
                    <div class="flex items-center gap-3">
                      <div
                        class="w-8 h-8 rounded-lg bg-gradient-to-br flex items-center justify-center text-sm font-semibold"
                        :style="`background: linear-gradient(135deg, ${getBlogColor(blog, 'color1')}, ${getBlogColor(blog, 'color2')})`"
                      >
                        {{ getBlogEmoji(blog) }}
                      </div>
                      <p class="font-medium text-gray-900">{{ blog.title }}</p>
                    </div>
                  </td>
                  <td class="px-3 py-3">
                    <span
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold"
                      :style="`background: ${getBlogColor(blog, 'tagBg')}; color: ${getBlogColor(blog, 'tagColor')}`"
                    >
                      {{ blog.category }}
                    </span>
                  </td>
                  <td class="px-3 py-3">
                    <span
                      v-if="blog.published"
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-green-50 text-green-700 text-xs font-semibold"
                    >
                      <span class="w-1.5 h-1.5 bg-green-500 rounded-full"></span>
                      Opublikowany
                    </span>
                    <span
                      v-else
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-gray-50 text-gray-700 text-xs font-semibold"
                    >
                      <span class="w-1.5 h-1.5 bg-gray-500 rounded-full"></span>
                      Wersja robocza
                    </span>
                  </td>
                  <td class="px-3 py-3">
                    <span class="text-gray-700 font-medium">{{ blog.views || 0 }}</span>
                  </td>
                  <td class="px-3 py-3">
                    <span class="text-gray-600 text-sm">{{ formatDate(blog.published_at) }}</span>
                  </td>
                  <td class="px-3 py-3 flex gap-2">
                    <button
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-gray-200 text-gray-700 hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300 text-sm font-medium"
                      @click="editBlog(blog)"
                    >
                      <font-awesome-icon icon="fa-solid fa-pen-to-square" class="w-4 h-4" />
                      Edytuj
                    </button>
                    <button
                      class="inline-flex items-center gap-1 px-3 py-1 rounded-lg border border-red-200 text-red-700 hover:border-red-300 hover:bg-red-50/50 transition-all duration-300 text-sm font-medium"
                      @click="deleteBlogConfirm(blog)"
                    >
                      <font-awesome-icon icon="fa-solid fa-trash" class="w-4 h-4" />
                      Usuń
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Categories Tab -->
        <div v-if="activeTab === 'categories'">
          <CategoriesManagement />
        </div>

        <!-- Pages Tab -->
        <div v-if="activeTab === 'pages'">
          <PagesManagement />
        </div>

        <!-- Settings Tab -->
        <div
          v-if="activeTab === 'settings'"
          class="bg-white rounded-2xl border border-gray-100 shadow-xl p-8"
        >
          <div class="flex justify-between items-center mb-8">
            <h2 class="text-3xl font-bold text-gray-900">Ustawienia platformy</h2>
            <button
              class="px-4 py-2 text-sm border border-red-200 text-red-600 rounded-lg hover:bg-red-50 transition-all"
              @click="resetSettings"
            >
              Resetuj do domyślnych
            </button>
          </div>

          <!-- Settings Tabs -->
          <div class="flex flex-wrap gap-2 mb-8 border-b border-gray-200 pb-4">
            <button
              v-for="section in settingsSections"
              :key="section.id"
              :class="[
                'px-4 py-2 rounded-lg font-medium transition-all',
                activeSettingsSection === section.id
                  ? 'bg-blue-100 text-blue-700'
                  : 'bg-gray-100 text-gray-600 hover:bg-gray-200',
              ]"
              @click="activeSettingsSection = section.id"
            >
              {{ section.label }}
            </button>
          </div>

          <!-- General Settings -->
          <div v-if="activeSettingsSection === 'general'" class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              <font-awesome-icon icon="fa-solid fa-circle-info" class="w-5 h-5 text-blue-600" />
              Ogólne
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Nazwa platformy</label>
                <input
                  v-model="platformSettings.platform_name"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input
                  v-model="platformSettings.platform_email"
                  type="email"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                <input
                  v-model="platformSettings.platform_phone"
                  type="text"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">URL Logo</label>
                <input
                  v-model="platformSettings.platform_logo_url"
                  type="url"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Opis platformy</label>
              <textarea
                v-model="platformSettings.platform_description"
                rows="4"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              />
            </div>
          </div>

          <!-- Auction Settings -->
          <div v-if="activeSettingsSection === 'auctions'" class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              <font-awesome-icon icon="fa-solid fa-link" class="w-5 h-5 text-green-600" />
              Aukcje
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Domyślny czas trwania (dni)</label
                >
                <input
                  v-model.number="platformSettings.default_auction_duration"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Procent wzrostu bida (%)</label
                >
                <input
                  v-model.number="platformSettings.bid_increment_percentage"
                  type="number"
                  min="1"
                  max="50"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Security Settings -->
          <div v-if="activeSettingsSection === 'security'" class="space-y-6">
            <div class="mb-8">
              <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-envelope" class="w-5 h-5 text-blue-600" />
                Newsletter
                <span
                  class="ml-1 px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-600"
                >
                  {{ newsletterTotal }} zapisanych
                </span>
              </h3>
              <div class="p-4 bg-gray-50 rounded-lg">
                <button
                  type="button"
                  class="px-4 py-2 text-sm font-medium border border-gray-200 rounded-lg text-gray-700 hover:border-blue-300 transition-colors"
                  @click="showNewsletterList = !showNewsletterList"
                >
                  {{ showNewsletterList ? 'Ukryj listę' : 'Pokaż listę adresów' }}
                </button>
                <div v-if="showNewsletterList" class="mt-4 max-h-64 overflow-y-auto space-y-1">
                  <div
                    v-for="sub in newsletterSubscribers"
                    :key="sub.id"
                    class="flex items-center justify-between text-sm py-1.5 px-3 bg-white rounded-lg border border-gray-100"
                  >
                    <span class="text-gray-700">{{ sub.email }}</span>
                    <span class="text-xs text-gray-400">{{
                      new Date(sub.created_at).toLocaleDateString('pl-PL')
                    }}</span>
                  </div>
                  <p v-if="!newsletterSubscribers.length" class="text-sm text-gray-500 py-2">
                    Brak zapisanych adresów.
                  </p>
                </div>
              </div>
            </div>

            <div class="mb-8">
              <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-bolt" class="w-5 h-5 text-blue-600" />
                Tryb platformy (start)
              </h3>
              <div class="space-y-4">
                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                  <input
                    id="only_admin_can_list"
                    v-model="platformSettings.only_admin_can_list"
                    type="checkbox"
                    class="w-5 h-5 text-blue-600 rounded"
                  />
                  <label for="only_admin_can_list" class="text-sm font-medium text-gray-700">
                    Wystawianie aukcji tylko dla administracji (użytkownicy nie mogą wystawiać)
                  </label>
                </div>
                <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                  <input
                    id="bidding_enabled"
                    v-model="platformSettings.bidding_enabled"
                    type="checkbox"
                    class="w-5 h-5 text-blue-600 rounded"
                  />
                  <label for="bidding_enabled" class="text-sm font-medium text-gray-700">
                    Licytacje włączone (wyłączone = działa tylko „Kup teraz")
                  </label>
                </div>
                <div class="p-4 bg-gray-50 rounded-lg">
                  <label
                    for="google_analytics_id"
                    class="block text-sm font-medium text-gray-700 mb-2"
                  >
                    Google Analytics ID (GA4)
                  </label>
                  <input
                    id="google_analytics_id"
                    v-model="platformSettings.google_analytics_id"
                    type="text"
                    placeholder="G-XXXXXXXXXX"
                    class="w-full max-w-xs px-4 py-2.5 border border-gray-200 rounded-xl font-mono text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400"
                  />
                  <p class="text-xs text-gray-500 mt-2">
                    Skrypt Analytics ładuje się dopiero po zgodzie użytkownika na cookies
                    analityczne (RODO).
                  </p>
                </div>
              </div>
            </div>

            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              <font-awesome-icon icon="fa-solid fa-lock" class="w-5 h-5 text-red-600" />
              Bezpieczeństwo
            </h3>
            <div class="space-y-4">
              <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                <input
                  id="require_2fa"
                  v-model="platformSettings.require_2fa"
                  type="checkbox"
                  class="w-5 h-5 text-blue-600 rounded"
                />
                <label for="require_2fa" class="text-sm font-medium text-gray-700">
                  Wymagaj autentykacji dwuskładnikowej
                </label>
              </div>
              <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                <input
                  id="require_email_verification"
                  v-model="platformSettings.require_email_verification"
                  type="checkbox"
                  class="w-5 h-5 text-blue-600 rounded"
                />
                <label for="require_email_verification" class="text-sm font-medium text-gray-700">
                  Wymagaj weryfikacji email
                </label>
              </div>
              <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                <input
                  id="require_phone_verification"
                  v-model="platformSettings.require_phone_verification"
                  type="checkbox"
                  class="w-5 h-5 text-blue-600 rounded"
                />
                <label for="require_phone_verification" class="text-sm font-medium text-gray-700">
                  Wymagaj weryfikacji telefonu
                </label>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Maksymalnie logowań przed zablokowaniem</label
                >
                <input
                  v-model.number="platformSettings.max_login_attempts"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Czas blokady (minuty)</label
                >
                <input
                  v-model.number="platformSettings.lockout_duration_minutes"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- User Settings -->
          <div v-if="activeSettingsSection === 'users'" class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              <font-awesome-icon icon="fa-solid fa-users" class="w-5 h-5 text-purple-600" />
              Użytkownicy
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Maksymalnie aukcji dziennie</label
                >
                <input
                  v-model.number="platformSettings.max_auctions_per_day"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Maksymalnie aukcji tygodniowo</label
                >
                <input
                  v-model.number="platformSettings.max_auctions_per_week"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Maksymalnie aukcji miesięcznie</label
                >
                <input
                  v-model.number="platformSettings.max_auctions_per_month"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Limit aukcji dla FREE użytkownika</label
                >
                <input
                  v-model.number="platformSettings.free_user_auction_limit"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Limit aukcji dla PREMIUM użytkownika</label
                >
                <input
                  v-model.number="platformSettings.premium_user_auction_limit"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Moderation Settings -->
          <div v-if="activeSettingsSection === 'moderation'" class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              <font-awesome-icon icon="fa-solid fa-circle-check" class="w-5 h-5 text-yellow-600" />
              Moderacja
            </h3>
            <div class="space-y-4">
              <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                <input
                  id="enable_user_reports"
                  v-model="platformSettings.enable_user_reports"
                  type="checkbox"
                  class="w-5 h-5 text-blue-600 rounded"
                />
                <label for="enable_user_reports" class="text-sm font-medium text-gray-700">
                  Włącz zgłaszanie użytkowników
                </label>
              </div>
              <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                <input
                  id="require_auction_approval"
                  v-model="platformSettings.require_auction_approval"
                  type="checkbox"
                  class="w-5 h-5 text-blue-600 rounded"
                />
                <label for="require_auction_approval" class="text-sm font-medium text-gray-700">
                  Wymagaj zatwierdzenia aukcji
                </label>
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Próg zgłoszeń do bana</label
                >
                <input
                  v-model.number="platformSettings.auto_ban_reports_threshold"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Czas bana (godziny)</label
                >
                <input
                  v-model.number="platformSettings.auto_ban_duration_hours"
                  type="number"
                  min="1"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Notifications Settings -->
          <div v-if="activeSettingsSection === 'notifications'" class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              <font-awesome-icon icon="fa-solid fa-bell" class="w-5 h-5 text-cyan-600" />
              Powiadomienia
            </h3>
            <div class="space-y-4">
              <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                <input
                  id="send_auction_ending"
                  v-model="platformSettings.send_auction_ending_notification"
                  type="checkbox"
                  class="w-5 h-5 text-blue-600 rounded"
                />
                <label for="send_auction_ending" class="text-sm font-medium text-gray-700">
                  Powiadom o kończących się aukcjach
                </label>
              </div>
              <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                <input
                  id="send_outbid"
                  v-model="platformSettings.send_outbid_notification"
                  type="checkbox"
                  class="w-5 h-5 text-blue-600 rounded"
                />
                <label for="send_outbid" class="text-sm font-medium text-gray-700">
                  Powiadom gdy przebita licytacja
                </label>
              </div>
              <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                <input
                  id="send_won_auction"
                  v-model="platformSettings.send_won_auction_notification"
                  type="checkbox"
                  class="w-5 h-5 text-blue-600 rounded"
                />
                <label for="send_won_auction" class="text-sm font-medium text-gray-700">
                  Powiadom o wygranych aukcjach
                </label>
              </div>
              <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                <input
                  id="send_email_digest"
                  v-model="platformSettings.send_email_digest"
                  type="checkbox"
                  class="w-5 h-5 text-blue-600 rounded"
                />
                <label for="send_email_digest" class="text-sm font-medium text-gray-700">
                  Wysyłaj digesty email
                </label>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2"
                >Częstotliwość digestu</label
              >
              <select
                v-model="platformSettings.email_digest_frequency"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              >
                <option value="daily">Codziennie</option>
                <option value="weekly">Tygodniowo</option>
                <option value="monthly">Miesięcznie</option>
              </select>
            </div>
          </div>

          <!-- System Messages -->
          <div v-if="activeSettingsSection === 'messages'" class="space-y-6">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              <font-awesome-icon icon="fa-solid fa-comment" class="w-5 h-5 text-indigo-600" />
              Wiadomości systemowe
            </h3>
            <div class="space-y-4">
              <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                <input
                  id="announcement_active"
                  v-model="platformSettings.announcement_active"
                  type="checkbox"
                  class="w-5 h-5 text-blue-600 rounded"
                />
                <label for="announcement_active" class="text-sm font-medium text-gray-700">
                  Aktywuj ogłoszenie systemowe
                </label>
              </div>
              <div v-if="platformSettings.announcement_active">
                <label class="block text-sm font-medium text-gray-700 mb-2">Typ ogłoszenia</label>
                <select
                  v-model="platformSettings.announcement_type"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                >
                  <option value="info">Informacja</option>
                  <option value="warning">Ostrzeżenie</option>
                  <option value="success">Sukces</option>
                  <option value="danger">Niebezpieczeństwo</option>
                </select>
              </div>
              <div v-if="platformSettings.announcement_active">
                <label class="block text-sm font-medium text-gray-700 mb-2">Treść ogłoszenia</label>
                <textarea
                  v-model="platformSettings.system_announcement"
                  rows="4"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg">
                <input
                  id="maintenance_mode"
                  v-model="platformSettings.maintenance_mode"
                  type="checkbox"
                  class="w-5 h-5 text-blue-600 rounded"
                />
                <label for="maintenance_mode" class="text-sm font-medium text-gray-700">
                  Włącz tryb konserwacji
                </label>
              </div>
              <div v-if="platformSettings.maintenance_mode">
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Wiadomość konserwacji</label
                >
                <textarea
                  v-model="platformSettings.maintenance_message"
                  rows="4"
                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
          </div>

          <!-- Save Button -->
          <div class="mt-8 flex gap-3 sticky bottom-0 bg-white pt-4 border-t border-gray-200">
            <button
              class="group relative flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden"
              :disabled="savingSettings"
              @click="savePlatformSettings"
            >
              <div
                class="absolute inset-0 bg-blue-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
              ></div>
              <span class="relative flex items-center justify-center gap-2">
                <font-awesome-icon
                  v-if="!savingSettings"
                  icon="fa-solid fa-check"
                  class="w-5 h-5"
                />
                <div
                  v-else
                  class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"
                ></div>
                {{ savingSettings ? 'Zapisywanie...' : 'Zapisz ustawienia' }}
              </span>
            </button>
          </div>
        </div>

        <!-- Errors Tab -->
        <div v-if="activeTab === 'errors'" class="space-y-6">
          <div class="flex justify-between items-center">
            <h2 class="text-3xl font-bold text-gray-900">Błędy aplikacji</h2>
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
              <input v-model="errorLogsShowResolved" type="checkbox" class="w-4 h-4 rounded" />
              Pokaż też rozwiązane
            </label>
          </div>

          <div
            v-if="errorLogsLoading"
            class="bg-white rounded-2xl border border-gray-100 shadow-xl p-12 text-center"
          >
            <font-awesome-icon
              icon="fa-solid fa-spinner"
              class="w-8 h-8 text-gray-300 animate-spin"
            />
          </div>

          <div
            v-else-if="!errorLogs.length"
            class="bg-white rounded-2xl border border-gray-100 shadow-xl p-12 text-center"
          >
            <div class="flex flex-col items-center gap-3">
              <font-awesome-icon icon="fa-solid fa-circle-check" class="w-12 h-12 text-gray-300" />
              <p class="text-lg font-medium text-gray-500">Brak zarejestrowanych błędów</p>
              <p class="text-sm text-gray-400">Wszystko działa poprawnie</p>
            </div>
          </div>

          <div
            v-for="log in errorLogs"
            :key="log.id"
            class="bg-white rounded-2xl border border-gray-100 shadow-xl p-6"
            :class="{ 'opacity-60': log.resolved }"
          >
            <div class="flex justify-between items-start gap-4 mb-3">
              <div class="min-w-0">
                <p class="font-bold text-lg text-gray-900 break-all">{{ log.exception_class }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ log.message }}</p>
              </div>
              <span
                class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold"
                :class="log.resolved ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
              >
                {{ log.resolved ? 'Rozwiązany' : 'Aktywny' }}
              </span>
            </div>

            <div class="flex flex-wrap gap-4 text-xs text-gray-500 mb-4">
              <span
                ><font-awesome-icon icon="fa-solid fa-location-dot" class="w-3 h-3 mr-1" />{{
                  log.file
                }}:{{ log.line }}</span
              >
              <span
                ><font-awesome-icon icon="fa-solid fa-arrows-rotate" class="w-3 h-3 mr-1" />{{
                  log.occurrences
                }}x</span
              >
              <span
                ><font-awesome-icon icon="fa-solid fa-clock" class="w-3 h-3 mr-1" />ostatnio:
                {{ formatDate(log.last_seen_at) }}</span
              >
            </div>

            <div class="flex gap-3">
              <button
                class="inline-flex items-center gap-2 px-4 py-2 border border-gray-200 text-gray-700 rounded-lg font-medium hover:border-gray-300 hover:bg-gray-50/50 transition-all text-sm"
                @click="toggleErrorLogTrace(log)"
              >
                {{ expandedErrorLogId === log.id ? 'Ukryj szczegóły' : 'Pokaż szczegóły' }}
              </button>
              <button
                v-if="!log.resolved"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg font-medium hover:shadow-md transition-all text-sm"
                @click="resolveErrorLog(log)"
              >
                <font-awesome-icon icon="fa-solid fa-check" class="w-4 h-4" />
                Oznacz jako rozwiązany
              </button>
              <button
                class="inline-flex items-center gap-2 px-4 py-2 border border-red-200 text-red-600 rounded-lg font-medium hover:bg-red-50 transition-all text-sm"
                @click="deleteErrorLog(log)"
              >
                <font-awesome-icon icon="fa-solid fa-trash" class="w-4 h-4" />
                Usuń
              </button>
            </div>

            <pre
              v-if="expandedErrorLogId === log.id"
              class="mt-4 p-4 bg-gray-900 text-gray-100 rounded-xl text-xs overflow-x-auto whitespace-pre-wrap"
              >{{ log.trace }}</pre
            >
          </div>
        </div>
      </div>
    </section>

    <!-- User Modal (Add/Edit) -->
    <div
      v-if="showEditUserModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-8 max-h-[90vh] overflow-y-auto relative overflow-hidden"
      >
        <div
          class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -translate-y-16 translate-x-16"
        ></div>
        <div
          class="absolute bottom-0 left-0 w-32 h-32 bg-purple-500/5 rounded-full translate-y-16 -translate-x-16"
        ></div>

        <div class="relative">
          <div class="flex justify-between items-center mb-6 sticky top-0 bg-white pb-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                <font-awesome-icon icon="fa-solid fa-user" class="w-5 h-5 text-blue-600" />
              </div>
              <h2 class="text-2xl font-bold text-gray-900">
                {{ userModalMode === 'add' ? 'Dodaj użytkownika' : 'Edytuj użytkownika' }}
              </h2>
            </div>
            <button
              class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-colors"
              @click="showEditUserModal = false"
            >
              <font-awesome-icon icon="fa-solid fa-xmark" class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-6">
            <!-- Informacje podstawowe -->
            <div class="border-b border-gray-200 pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-circle-info" class="w-5 h-5 text-blue-500" />
                Informacje podstawowe
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Login (ksywka) *</label
                  >
                  <input
                    v-model="editingUser.name"
                    type="text"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    placeholder="nazwa_uzytkownika"
                    pattern="[a-zA-Z0-9_-]+"
                  />
                  <p class="mt-1 text-xs text-gray-500">
                    Tylko litery, cyfry, podkreślnik i myślnik
                  </p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
                  <input
                    v-model="editingUser.email"
                    type="email"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Imię</label>
                  <input
                    v-model="editingUser.first_name"
                    type="text"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Nazwisko</label>
                  <input
                    v-model="editingUser.last_name"
                    type="text"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Telefon</label>
                  <input
                    v-model="editingUser.phone"
                    type="tel"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Rola</label>
                  <select
                    v-model="editingUser.role"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  >
                    <option value="user">Użytkownik</option>
                    <option value="moderator">Moderator</option>
                    <option value="admin">Admin</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Email zweryfikowany</label
                  >
                  <input
                    v-model="editingUser.email_verified_at"
                    type="datetime-local"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Telefon zweryfikowany</label
                  >
                  <input
                    v-model="editingUser.phone_verified_at"
                    type="datetime-local"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
                <div class="md:col-span-2">
                  <div
                    class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl border border-gray-200"
                  >
                    <div class="flex items-center gap-3">
                      <div
                        class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center"
                      >
                        <font-awesome-icon
                          icon="fa-solid fa-shield-halved"
                          class="w-5 h-5 text-indigo-600"
                        />
                      </div>
                      <div>
                        <p class="font-semibold text-gray-900">Administrator</p>
                        <p class="text-sm text-gray-600">Pełne uprawnienia administracyjne</p>
                      </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input v-model="editingUser.is_admin" type="checkbox" class="sr-only peer" />
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"
                      ></div>
                    </label>
                  </div>
                </div>
                <div class="md:col-span-2">
                  <div
                    class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl border border-gray-200"
                  >
                    <div class="flex items-center gap-3">
                      <div
                        class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center"
                      >
                        <font-awesome-icon
                          icon="fa-solid fa-circle-check"
                          class="w-5 h-5 text-green-600"
                        />
                      </div>
                      <div>
                        <p class="font-semibold text-gray-900">Status konta</p>
                        <p class="text-sm text-gray-600">Aktywne lub nieaktywne</p>
                      </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input v-model="editingUser.is_active" type="checkbox" class="sr-only peer" />
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"
                      ></div>
                    </label>
                  </div>
                </div>
                <div class="md:col-span-2">
                  <div
                    class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl border border-gray-200"
                  >
                    <div class="flex items-center gap-3">
                      <div
                        class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center"
                      >
                        <font-awesome-icon
                          icon="fa-solid fa-bolt"
                          class="w-5 h-5 text-orange-600"
                        />
                      </div>
                      <div>
                        <p class="font-semibold text-gray-900">
                          Wyjątek: może wystawiać mimo blokady
                        </p>
                        <p class="text-sm text-gray-600">
                          Gdy "tylko admin może wystawiać" jest włączone, ten user i tak może sam
                          wystawić aukcję - wyłącznie w trybie "Kup teraz".
                        </p>
                      </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input
                        v-model="editingUser.can_list_when_restricted"
                        type="checkbox"
                        class="sr-only peer"
                      />
                      <div
                        class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-orange-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-600"
                      ></div>
                    </label>
                  </div>
                </div>
              </div>
              <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                <textarea
                  v-model="editingUser.bio"
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  rows="3"
                />
              </div>
            </div>

            <!-- Zdjęcie profilowe -->
            <div class="border-b border-gray-200 pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-image" class="w-5 h-5 text-blue-500" />
                Zdjęcie profilowe
              </h3>
              <div class="space-y-4">
                <div
                  v-if="!editingUser.avatar"
                  class="border-2 border-dashed border-gray-300 rounded-2xl p-8 text-center cursor-pointer bg-gray-50 hover:border-blue-400 hover:bg-blue-50 transition-all duration-300"
                  @click="$refs.avatarInput.click()"
                >
                  <div
                    class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-100 flex items-center justify-center"
                  >
                    <font-awesome-icon icon="fa-solid fa-plus" class="w-8 h-8 text-blue-600" />
                  </div>
                  <p class="text-sm font-medium text-gray-700 mb-1">Dodaj zdjęcie profilowe</p>
                  <p class="text-xs text-gray-500">PNG, JPG do 5MB</p>
                </div>

                <div v-if="editingUser.avatar" class="relative group">
                  <img
                    :src="editingUser.avatar"
                    alt="Avatar"
                    class="w-full h-48 object-cover rounded-2xl"
                  />
                  <button
                    type="button"
                    class="absolute top-3 right-3 bg-red-500 text-white p-2 rounded-xl opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-all duration-300 shadow-lg"
                    @click.stop="removeAvatar"
                  >
                    <font-awesome-icon icon="fa-solid fa-trash" class="w-5 h-5" />
                  </button>
                </div>

                <input
                  ref="avatarInput"
                  type="file"
                  accept="image/*"
                  class="hidden"
                  @change="handleAvatarUpload"
                />
              </div>
            </div>

            <!-- Adres -->
            <div class="border-b border-gray-200 pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon
                  icon="fa-solid fa-location-dot"
                  class="w-5 h-5 text-indigo-500"
                />
                Adres
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Ulica</label>
                  <input
                    v-model="editingUser.address"
                    type="text"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Miasto</label>
                  <input
                    v-model="editingUser.city"
                    type="text"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Kod pocztowy</label>
                  <input
                    v-model="editingUser.postcode"
                    type="text"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Kraj</label>
                  <select
                    v-model="editingUser.country"
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
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Kategoria</label>
                  <select
                    v-model.number="editingUser.category_id"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  >
                    <option value="">Brak kategorii</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                      {{ cat.name }}
                    </option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Status i reputacja -->
            <div class="border-b border-gray-200 pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon
                  icon="fa-solid fa-circle-check"
                  class="w-5 h-5 text-purple-500"
                />
                Status i reputacja
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Reputacja (ocena)
                    <span class="font-normal text-gray-400"
                      >— liczona automatycznie ze średniej recenzji, niedostępna do edycji</span
                    >
                  </label>
                  <div
                    class="w-full px-4 py-3 rounded-xl border border-gray-100 bg-gray-50 text-gray-700 flex items-center gap-2"
                  >
                    <font-awesome-icon icon="fa-solid fa-star" class="w-4 h-4 text-yellow-400" />
                    {{ editingUser.reputation ?? 0 }}
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Darmowe ogłoszenia</label
                  >
                  <input
                    v-model.number="editingUser.listings_free_count"
                    type="number"
                    min="0"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
              </div>
            </div>

            <!-- Plan premium -->
            <div class="border-b border-gray-200 pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-gift" class="w-5 h-5 text-yellow-500" />
                Plan premium
              </h3>
              <div class="space-y-4">
                <div
                  class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl border border-gray-200"
                >
                  <div class="flex items-center gap-3">
                    <div
                      class="w-10 h-10 rounded-xl bg-yellow-100 flex items-center justify-center"
                    >
                      <font-awesome-icon
                        icon="fa-solid fa-wand-magic-sparkles"
                        class="w-5 h-5 text-yellow-600"
                      />
                    </div>
                    <div>
                      <p class="font-semibold text-gray-900">Użytkownik premium</p>
                      <p class="text-sm text-gray-600">Dostęp do dodatkowych funkcji</p>
                    </div>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input v-model="editingUser.is_premium" type="checkbox" class="sr-only peer" />
                    <div
                      class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-yellow-600"
                    ></div>
                  </label>
                </div>
                <div
                  v-if="editingUser.is_premium"
                  class="grid grid-cols-1 md:grid-cols-2 gap-4 ml-8"
                >
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Plan</label>
                    <select
                      v-model="editingUser.premium_plan"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    >
                      <option value="free">Darmowy</option>
                      <option value="1month">1 miesiąc</option>
                      <option value="3months">3 miesiące</option>
                      <option value="12months">12 miesięcy</option>
                    </select>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Premium do</label>
                    <input
                      v-model="editingUser.premium_until"
                      type="datetime-local"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    />
                  </div>
                </div>

                <!-- Profil publiczny -->
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                  <div class="flex items-center gap-3">
                    <div
                      class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center shrink-0"
                    >
                      <font-awesome-icon icon="fa-solid fa-eye" class="w-5 h-5 text-green-600" />
                    </div>
                    <div>
                      <p class="font-semibold text-gray-900">Profil publiczny</p>
                      <p class="text-sm text-gray-600">Wszyscy mogą oglądać profil</p>
                    </div>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input v-model="editingUser.is_public" type="checkbox" class="sr-only peer" />
                    <div
                      class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"
                    ></div>
                  </label>
                </div>
              </div>
            </div>

            <!-- Bezpieczeństwo -->
            <div class="border-b border-gray-200 pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-lock" class="w-5 h-5 text-red-500" />
                Bezpieczeństwo
              </h3>
              <div class="space-y-4">
                <div
                  class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl border border-gray-200"
                >
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                      <font-awesome-icon icon="fa-solid fa-ban" class="w-5 h-5 text-red-600" />
                    </div>
                    <div>
                      <p class="font-semibold text-gray-900">Zablokuj użytkownika</p>
                      <p class="text-sm text-gray-600">Uniemożliwia dostęp do platformy</p>
                    </div>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input v-model="editingUser.is_banned" type="checkbox" class="sr-only peer" />
                    <div
                      class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"
                    ></div>
                  </label>
                </div>
                <div v-if="editingUser.is_banned" class="space-y-4 ml-8">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"
                      >Powód blokady</label
                    >
                    <input
                      v-model="editingUser.ban_reason"
                      type="text"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Blokada do</label>
                    <input
                      v-model="editingUser.ban_until"
                      type="datetime-local"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    />
                  </div>
                </div>
                <div
                  class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl border border-gray-200"
                >
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center">
                      <font-awesome-icon
                        icon="fa-solid fa-shield-halved"
                        class="w-5 h-5 text-green-600"
                      />
                    </div>
                    <div>
                      <p class="font-semibold text-gray-900">2FA włączone</p>
                      <p class="text-sm text-gray-600">Autentykacja dwuetapowa</p>
                    </div>
                  </div>
                  <label class="relative inline-flex items-center cursor-pointer">
                    <input
                      v-model="editingUser.two_factor_enabled"
                      type="checkbox"
                      class="sr-only peer"
                    />
                    <div
                      class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"
                    ></div>
                  </label>
                </div>
                <div class="space-y-2">
                  <label class="block text-sm font-medium text-gray-700">
                    {{ userModalMode === 'add' ? 'Hasło *' : 'Nowe hasło' }}
                  </label>
                  <div class="flex gap-2">
                    <input
                      v-model="editingUser.password"
                      type="text"
                      :placeholder="
                        userModalMode === 'add' ? 'Wymagane' : 'Pozostaw puste, aby nie zmieniać'
                      "
                      class="flex-1 px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    />
                    <button
                      type="button"
                      class="px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 flex items-center gap-2"
                      @click="generatePassword"
                    >
                      <font-awesome-icon icon="fa-solid fa-plus" class="w-5 h-5" />
                      Generuj
                    </button>
                  </div>
                  <p v-if="editingUser.password" class="text-sm text-gray-600">
                    Nowe hasło:
                    <span class="font-mono font-semibold text-purple-600">{{
                      editingUser.password
                    }}</span>
                  </p>
                </div>
              </div>
            </div>

            <!-- Ostatnia aktywność -->
            <div class="pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-clock" class="w-5 h-5 text-gray-500" />
                Ostatnia aktywność
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Ostatnie logowanie</label
                  >
                  <input
                    v-model="editingUser.last_login_at"
                    type="datetime-local"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >IP ostatniego logowania</label
                  >
                  <input
                    v-model="editingUser.last_login_ip"
                    type="text"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
              </div>
            </div>

            <div class="flex gap-3 pt-6 sticky bottom-0 bg-white">
              <button
                class="group relative flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                @click="saveEditUser"
              >
                <div
                  class="absolute inset-0 bg-blue-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                ></div>
                <span class="relative flex items-center justify-center gap-2">
                  <font-awesome-icon icon="fa-solid fa-check" class="w-5 h-5" />
                  {{ userModalMode === 'add' ? 'Dodaj użytkownika' : 'Zapisz zmiany' }}
                </span>
              </button>
              <button
                class="flex-1 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300"
                @click="showEditUserModal = false"
              >
                Anuluj
              </button>
              <button
                v-if="userModalMode === 'edit'"
                title="Usuń konto użytkownika"
                class="px-4 py-3 border-2 border-red-200 text-red-700 rounded-xl font-semibold hover:border-red-300 hover:bg-red-50/50 transition-all duration-300"
                @click="deleteUserConfirm(editingUser)"
              >
                <font-awesome-icon icon="fa-solid fa-trash" class="w-5 h-5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Auction Modal (Add/Edit) -->
    <div
      v-if="showEditAuctionModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-8 max-h-[90vh] overflow-y-auto relative overflow-hidden"
      >
        <div
          class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -translate-y-16 translate-x-16"
        ></div>
        <div
          class="absolute bottom-0 left-0 w-32 h-32 bg-purple-500/5 rounded-full translate-y-16 -translate-x-16"
        ></div>

        <div class="relative">
          <div class="flex justify-between items-center mb-6 sticky top-0 bg-white pb-4">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                <font-awesome-icon icon="fa-solid fa-circle-check" class="w-5 h-5 text-blue-600" />
              </div>
              <h2 class="text-2xl font-bold text-gray-900">
                {{ auctionModalMode === 'add' ? 'Dodaj aukcję' : 'Edytuj aukcję' }}
              </h2>
            </div>
            <button
              class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-colors"
              @click="showEditAuctionModal = false"
            >
              <font-awesome-icon icon="fa-solid fa-xmark" class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-6">
            <!-- Status i podstawowe info -->
            <div class="border-b border-gray-200 pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-circle-info" class="w-5 h-5 text-blue-500" />
                Status i podstawowe
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Typ aukcji</label>
                  <select
                    v-model="editingAuction.auction_type"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  >
                    <option value="auction">Licytacja</option>
                    <option value="buy_now">Kup teraz</option>
                    <option value="both">Oba</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                  <select
                    v-model="editingAuction.status"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  >
                    <option value="active">Aktywna</option>
                    <option value="pending">Oczekuje</option>
                    <option value="ended">Zakończona</option>
                    <option value="cancelled">Anulowana</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Tytuł gołębia</label>
                  <input
                    v-model="editingAuction.title"
                    type="text"
                    placeholder="np. Biały Samiec Dresden"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Rasa</label>
                  <input
                    v-model="editingAuction.breed"
                    type="text"
                    placeholder="np. Mondain, Saksoman, Posłaniec"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Kategoria</label>
                  <select
                    v-model.number="editingAuction.category_id"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  >
                    <option value="">Brak kategorii</option>
                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                      {{ cat.name }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Sprzedawca</label>
                  <select
                    v-model.number="editingAuction.user_id"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  >
                    <option v-if="editingAuction.seller" :value="editingAuction.user_id">
                      {{ editingAuction.seller.name }} - {{ editingAuction.seller.first_name }}
                      {{ editingAuction.seller.last_name }} ({{ editingAuction.seller.email }})
                    </option>
                    <option v-for="user in users" :key="user.id" :value="user.id">
                      {{ user.name }} - {{ user.first_name }} {{ user.last_name }} ({{
                        user.email
                      }})
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Zwycięzca</label>
                  <select
                    v-model.number="editingAuction.winner_id"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  >
                    <option :value="null">Brak zwycięzcy</option>
                    <option v-if="editingAuction.winner" :value="editingAuction.winner_id">
                      {{ editingAuction.winner.name }} - {{ editingAuction.winner.first_name }}
                      {{ editingAuction.winner.last_name }} ({{ editingAuction.winner.email }})
                    </option>
                    <option v-for="user in users" :key="user.id" :value="user.id">
                      {{ user.name }} - {{ user.first_name }} {{ user.last_name }} ({{
                        user.email
                      }})
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Najwyższy licytant</label
                  >
                  <select
                    v-model.number="editingAuction.winner_id"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  >
                    <option :value="null">Brak licytanta</option>
                    <option v-if="editingAuction.winner" :value="editingAuction.winner_id">
                      {{ editingAuction.winner.name }} - {{ editingAuction.winner.first_name }}
                      {{ editingAuction.winner.last_name }} ({{ editingAuction.winner.email }})
                    </option>
                    <option v-for="user in users" :key="user.id" :value="user.id">
                      {{ user.name }} - {{ user.first_name }} {{ user.last_name }} ({{
                        user.email
                      }})
                    </option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Ceny -->
            <div class="border-b border-gray-200 pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-sack-dollar" class="w-5 h-5 text-green-500" />
                Ceny
                <span
                  v-if="editingAuction.auction_type === 'buy_now'"
                  class="text-sm font-normal text-gray-500"
                  >(Kup teraz)</span
                >
                <span
                  v-else-if="editingAuction.auction_type === 'auction'"
                  class="text-sm font-normal text-gray-500"
                  >(Licytacja)</span
                >
                <span v-else class="text-sm font-normal text-gray-500">(Oba tryby)</span>
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Cena startowa</label>
                  <div class="relative">
                    <input
                      v-model.number="editingAuction.start_price"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm pl-10"
                    />
                    <span class="absolute left-3 top-3 text-gray-400">zł</span>
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Obecna cena</label>
                  <div class="relative">
                    <input
                      v-model.number="editingAuction.current_price"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm pl-10"
                    />
                    <span class="absolute left-3 top-3 text-gray-400">zł</span>
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Current Bid (alternatywna)</label
                  >
                  <div class="relative">
                    <input
                      v-model.number="editingAuction.current_bid"
                      type="number"
                      step="0.01"
                      min="0"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm pl-10"
                    />
                    <span class="absolute left-3 top-3 text-gray-400">zł</span>
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Liczba licytacji</label
                  >
                  <input
                    v-model.number="editingAuction.bids_count"
                    type="number"
                    min="0"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
              </div>
            </div>

            <!-- Czas trwania - tylko dla licytacji -->
            <div
              v-if="editingAuction.auction_type !== 'buy_now'"
              class="border-b border-gray-200 pb-6"
            >
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-clock" class="w-5 h-5 text-purple-500" />
                Czas trwania
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Data startu</label>
                  <input
                    v-model="editingAuction.started_at"
                    type="datetime-local"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Data zakończenia</label
                  >
                  <input
                    v-model="editingAuction.ends_at"
                    type="datetime-local"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Oryginalna data zakończenia</label
                  >
                  <input
                    v-model="editingAuction.original_ends_at"
                    type="datetime-local"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2"
                    >Ile razy przedłużona</label
                  >
                  <input
                    v-model.number="editingAuction.times_extended"
                    type="number"
                    min="0"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
              </div>
            </div>

            <!-- Ochrona anti-sniper - tylko dla licytacji -->
            <div v-if="editingAuction.auction_type !== 'buy_now'" class="pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-cloud" class="w-5 h-5 text-red-500" />
                Ochrona anti-sniper
              </h3>
              <div class="space-y-4">
                <div
                  class="flex items-center gap-3 p-4 bg-blue-50/50 rounded-xl border border-blue-200"
                >
                  <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                    <font-awesome-icon
                      icon="fa-solid fa-shield-halved"
                      class="w-5 h-5 text-blue-600"
                    />
                  </div>
                  <div>
                    <p class="font-semibold text-gray-900">Ochrona zawsze włączona</p>
                    <p class="text-sm text-gray-600">Automatyczne przedłużanie aukcji</p>
                  </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"
                      >Próg (minuty)</label
                    >
                    <input
                      v-model.number="editingAuction.sniper_threshold"
                      type="number"
                      min="1"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"
                      >Przedłużenie (minuty)</label
                    >
                    <input
                      v-model.number="editingAuction.extension_minutes"
                      type="number"
                      min="1"
                      class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                    />
                  </div>
                </div>
              </div>
            </div>

            <!-- Dane gołębia -->
            <div class="border-b border-gray-200 pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-download" class="w-5 h-5 text-amber-500" />
                Dane gołębia
              </h3>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Rok urodzenia</label>
                  <input
                    v-model.number="editingAuction.year"
                    type="number"
                    :min="new Date().getFullYear() - 50"
                    :max="new Date().getFullYear()"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  />
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Płeć</label>
                  <select
                    v-model="editingAuction.gender"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  >
                    <option value="">Wybierz płeć</option>
                    <option value="samica">Samica</option>
                    <option value="samiec">Samiec</option>
                    <option value="golab_mlody">Gołąb młody</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Kolor</label>
                  <select
                    v-model="editingAuction.color"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  >
                    <option value="">Wybierz kolor</option>
                    <option value="NIEB">Niebieska (NIEB 1)</option>
                    <option value="NNAK">Niebieska nakrapiana (NNAK 2)</option>
                    <option value="CNAK">Ciemna nakrapiana (CNAK 9)</option>
                    <option value="CIEM">Ciemna (CIEM 3)</option>
                    <option value="CZAR">Czarna (CZAR 6)</option>
                    <option value="CZEN">Czerwona nakrapiana (CZEN 8)</option>
                    <option value="CZER">Czerwona (CZER 4)</option>
                    <option value="PLOW">Płowa (PLOW 5)</option>
                    <option value="BIAL">Biała (BIAL 10)</option>
                    <option value="SZPA">Szpakowata (SZPA 7)</option>
                    <option value="NIEP">Niebieska pstra (NIEP 11)</option>
                    <option value="NNAP">Niebieska nakrapiana pstra (NNAP 12)</option>
                    <option value="CNAP">Ciemna nakrapiana pstra (CNAP 19)</option>
                    <option value="CIEP">Ciemna pstra (CIEP 13)</option>
                    <option value="CZAP">Czarna pstra (CZAP 16)</option>
                    <option value="CZNP">Czerwona nakrapiana pstra (CZNP 18)</option>
                    <option value="CZEP">Czerwona pstra (CZEP 14)</option>
                    <option value="PLOP">Płowa pstra (PLOP 15)</option>
                    <option value="SZPP">Szpakowata pstra (SZPP 17)</option>
                    <option value="CZES">Czerwona szpakowata (CZES 20)</option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Rozmiar</label>
                  <select
                    v-model="editingAuction.size"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                  >
                    <option value="">Wybierz rozmiar</option>
                    <option value="mały">Mały</option>
                    <option value="średni">Średni</option>
                    <option value="duży">Duży</option>
                  </select>
                </div>
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-2">Opis</label>
                  <textarea
                    v-model="editingAuction.description"
                    placeholder="Opisz cechy, pochodzenie i stan gołębia..."
                    rows="4"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm resize-none"
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Zdjęcia gołębia -->
            <div class="border-b border-gray-200 pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-image" class="w-5 h-5 text-blue-500" />
                Zdjęcia gołębia
              </h3>
              <div class="space-y-4">
                <div
                  class="border-2 border-dashed border-gray-300 rounded-2xl p-8 text-center cursor-pointer bg-gray-50 hover:border-blue-400 hover:bg-blue-50 transition-all duration-300"
                  @click="$refs.pigeonImagesInput.click()"
                >
                  <div
                    class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-100 flex items-center justify-center"
                  >
                    <font-awesome-icon icon="fa-solid fa-plus" class="w-8 h-8 text-blue-600" />
                  </div>
                  <p class="text-sm font-medium text-gray-700 mb-1">Dodaj zdjęcia gołębia</p>
                  <p class="text-xs text-gray-500">PNG, JPG do 5MB każde</p>
                </div>

                <div
                  v-if="editingAuction.pigeon_images?.length > 0"
                  class="grid grid-cols-2 md:grid-cols-4 gap-4"
                >
                  <div
                    v-for="(image, index) in editingAuction.pigeon_images"
                    :key="index"
                    class="relative group"
                  >
                    <img
                      :src="image"
                      alt="Zdjęcie gołębia"
                      class="w-full h-32 object-cover rounded-xl"
                    />
                    <button
                      type="button"
                      class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-xl opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-all duration-300 shadow-lg"
                      @click.stop="removePigeonImage(index)"
                    >
                      <font-awesome-icon icon="fa-solid fa-trash" class="w-4 h-4" />
                    </button>
                  </div>
                </div>

                <input
                  ref="pigeonImagesInput"
                  type="file"
                  accept="image/*"
                  multiple
                  class="hidden"
                  @change="handlePigeonImageUpload"
                />
              </div>
            </div>

            <!-- Zdjęcia rodowodu -->
            <div class="pb-6">
              <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <font-awesome-icon icon="fa-solid fa-file-lines" class="w-5 h-5 text-purple-500" />
                Zdjęcia rodowodu
              </h3>
              <div class="space-y-4">
                <div
                  class="border-2 border-dashed border-gray-300 rounded-2xl p-8 text-center cursor-pointer bg-blue-50 hover:border-purple-400 hover:bg-purple-50 transition-all duration-300"
                  @click="$refs.pedigreeImagesInput.click()"
                >
                  <div
                    class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-gray-100 flex items-center justify-center"
                  >
                    <font-awesome-icon icon="fa-solid fa-plus" class="w-8 h-8 text-purple-600" />
                  </div>
                  <p class="text-sm font-medium text-gray-700 mb-1">Dodaj zdjęcia rodowodu</p>
                  <p class="text-xs text-gray-500">PNG, JPG do 5MB każde</p>
                </div>

                <div
                  v-if="editingAuction.pedigree_images?.length > 0"
                  class="grid grid-cols-2 md:grid-cols-4 gap-4"
                >
                  <div
                    v-for="(image, index) in editingAuction.pedigree_images"
                    :key="index"
                    class="relative group"
                  >
                    <img
                      :src="image"
                      alt="Zdjęcie rodowodu"
                      class="w-full h-32 object-cover rounded-xl"
                    />
                    <button
                      type="button"
                      class="absolute top-2 right-2 bg-red-500 text-white p-2 rounded-xl opacity-0 group-hover:opacity-100 hover:bg-red-600 transition-all duration-300 shadow-lg"
                      @click.stop="removePedigreeImage(index)"
                    >
                      <font-awesome-icon icon="fa-solid fa-trash" class="w-4 h-4" />
                    </button>
                  </div>
                </div>

                <input
                  ref="pedigreeImagesInput"
                  type="file"
                  accept="image/*"
                  multiple
                  class="hidden"
                  @change="handlePedigreeImageUpload"
                />
              </div>
            </div>

            <div class="flex gap-3 pt-6 sticky bottom-0 bg-white">
              <button
                class="group relative flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                @click="saveEditAuction"
              >
                <div
                  class="absolute inset-0 bg-blue-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                ></div>
                <span class="relative flex items-center justify-center gap-2">
                  <font-awesome-icon icon="fa-solid fa-check" class="w-5 h-5" />
                  {{ auctionModalMode === 'add' ? 'Dodaj aukcję' : 'Zapisz zmiany' }}
                </span>
              </button>
              <button
                class="flex-1 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:border-blue-300 hover:bg-blue-50/50 transition-all duration-300"
                @click="showEditAuctionModal = false"
              >
                Anuluj
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Ban User Modal -->
    <div
      v-if="showBanModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 relative overflow-hidden">
        <div
          class="absolute top-0 right-0 w-32 h-32 bg-red-500/5 rounded-full -translate-y-16 translate-x-16"
        ></div>

        <div class="relative">
          <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-3">
              <div
                class="w-10 h-10 rounded-xl bg-red-500 flex items-center justify-center text-white"
              >
                <font-awesome-icon icon="fa-solid fa-ban" class="w-5 h-5" />
              </div>
              <h2 class="text-2xl font-bold text-gray-900">Zabanuj użytkownika</h2>
            </div>
            <button
              class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-colors"
              @click="showBanModal = false"
            >
              <font-awesome-icon icon="fa-solid fa-xmark" class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Powód</label>
              <textarea
                v-model="banData.reason"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                rows="3"
              ></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Czas banu (godzin)</label>
              <div class="relative">
                <input
                  v-model.number="banData.hours"
                  type="number"
                  min="1"
                  max="8760"
                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm pr-12"
                />
                <span class="absolute right-3 top-3 text-gray-400">h</span>
              </div>
              <p class="text-xs text-gray-500 mt-2">Maksymalnie 8760 godzin (1 rok)</p>
            </div>

            <div class="flex gap-3 pt-4">
              <button
                class="group relative flex-1 px-6 py-3 bg-red-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                @click="saveTemporaryBan"
              >
                <div
                  class="absolute inset-0 bg-red-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                ></div>
                <span class="relative flex items-center justify-center gap-2">
                  <font-awesome-icon icon="fa-solid fa-ban" class="w-5 h-5" />
                  Zabanuj
                </span>
              </button>
              <button
                class="flex-1 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50/50 transition-all duration-300"
                @click="showBanModal = false"
              >
                Anuluj
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Handle Report Modal -->
    <div
      v-if="showHandleReportModal"
      class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4"
    >
      <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 relative overflow-hidden">
        <div
          class="absolute top-0 right-0 w-32 h-32 bg-blue-500/5 rounded-full -translate-y-16 translate-x-16"
        ></div>
        <div
          class="absolute bottom-0 left-0 w-32 h-32 bg-purple-500/5 rounded-full translate-y-16 -translate-x-16"
        ></div>

        <div class="relative">
          <div class="flex justify-between items-center mb-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
                <font-awesome-icon icon="fa-solid fa-circle-check" class="w-5 h-5 text-blue-600" />
              </div>
              <h2 class="text-2xl font-bold text-gray-900">Rozpatrz zgłoszenie</h2>
            </div>
            <button
              class="w-8 h-8 rounded-lg border border-gray-200 flex items-center justify-center text-gray-400 hover:text-gray-600 hover:border-gray-300 transition-colors"
              @click="showHandleReportModal = false"
            >
              <font-awesome-icon icon="fa-solid fa-xmark" class="w-4 h-4" />
            </button>
          </div>

          <div class="space-y-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Notatka admina</label>
              <textarea
                v-model="reportData.notes"
                class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm"
                rows="3"
              ></textarea>
            </div>

            <div
              v-if="reportData.reported_user_id"
              class="flex items-center justify-between p-4 bg-gray-50/50 rounded-xl border border-gray-200"
            >
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center">
                  <font-awesome-icon icon="fa-solid fa-ban" class="w-5 h-5 text-red-600" />
                </div>
                <div>
                  <p class="font-semibold text-gray-900">Zabanuj zgłoszonego użytkownika</p>
                  <p class="text-sm text-gray-600">Tymczasowe zawieszenie konta</p>
                </div>
              </div>
              <label class="relative inline-flex items-center cursor-pointer">
                <input v-model="reportData.ban_user" type="checkbox" class="sr-only peer" />
                <div
                  class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-red-600"
                ></div>
              </label>
            </div>

            <div v-if="reportData.ban_user" class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2"
                  >Czas banu (godzin)</label
                >
                <div class="relative">
                  <input
                    v-model.number="reportData.ban_hours"
                    type="number"
                    min="1"
                    max="8760"
                    class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-red-500 focus:ring-2 focus:ring-red-200 focus:outline-none transition-all duration-300 bg-white/90 backdrop-blur-sm pr-12"
                  />
                  <span class="absolute right-3 top-3 text-gray-400">h</span>
                </div>
              </div>
            </div>

            <div class="flex gap-3 pt-4">
              <button
                class="group relative flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden"
                @click="saveHandleReport"
              >
                <div
                  class="absolute inset-0 bg-blue-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                ></div>
                <span class="relative flex items-center justify-center gap-2">
                  <font-awesome-icon icon="fa-solid fa-check" class="w-5 h-5" />
                  Rozwiąż
                </span>
              </button>
              <button
                class="flex-1 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50/50 transition-all duration-300"
                @click="showHandleReportModal = false"
              >
                Anuluj
              </button>
            </div>
          </div>
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
              icon="fa-solid fa-triangle-exclamation"
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

    <!-- Confirmation Modal -->
    <div
      v-if="confirmModal.show"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="handleCancel"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 transform transition-all"
        @click.stop
      >
        <div class="flex items-start gap-4 mb-6">
          <div
            class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center"
            :class="{
              'bg-red-50': confirmModal.type === 'danger',
              'bg-yellow-50': confirmModal.type === 'warning',
              'bg-gray-100': confirmModal.type === 'info',
            }"
          >
            <font-awesome-icon
              v-if="confirmModal.type === 'danger'"
              icon="fa-solid fa-trash"
              class="w-7 h-7 text-red-600"
            />
            <font-awesome-icon
              v-else
              icon="fa-solid fa-triangle-exclamation"
              class="w-7 h-7"
              :class="{
                'text-yellow-600': confirmModal.type === 'warning',
                'text-blue-600': confirmModal.type === 'info',
              }"
            />
          </div>
          <div class="flex-1">
            <h3 class="text-xl font-bold text-gray-900 mb-2">{{ confirmModal.title }}</h3>
            <p class="text-gray-600 leading-relaxed">{{ confirmModal.message }}</p>
          </div>
        </div>

        <div class="flex gap-3">
          <button
            class="flex-1 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:bg-gray-50 transition-all"
            @click="handleCancel"
          >
            {{ confirmModal.cancelText }}
          </button>
          <button
            class="flex-1 px-6 py-3 rounded-xl font-semibold text-white transition-all"
            :class="{
              'bg-red-500 hover:shadow-lg': confirmModal.type === 'danger',
              'bg-amber-500 hover:shadow-lg': confirmModal.type === 'warning',
              'bg-blue-600 hover:shadow-lg': confirmModal.type === 'info',
            }"
            @click="handleConfirm"
          >
            {{ confirmModal.confirmText }}
          </button>
        </div>
      </div>
    </div>

    <!-- Blog Management Modal -->
    <div
      v-if="showEditBlogModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50 p-4"
      @click.self="showEditBlogModal = false"
    >
      <div
        class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full p-8 transform transition-all max-h-[90vh] overflow-y-auto"
        @click.stop
      >
        <div class="flex items-center justify-between mb-6">
          <h2 class="text-2xl font-bold text-gray-900">
            {{ blogModalMode === 'add' ? 'Dodaj Blog' : 'Edytuj Blog' }}
          </h2>
          <button
            class="p-2 hover:bg-gray-100 rounded-xl transition-colors"
            @click="showEditBlogModal = false"
          >
            <font-awesome-icon icon="fa-solid fa-xmark" class="w-6 h-6 text-gray-500" />
          </button>
        </div>

        <form class="space-y-6" @submit.prevent="saveBlog">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Tytuł *</label>
              <input
                v-model="editingBlog.title"
                type="text"
                placeholder="Wpisz tytuł artykułu"
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none transition-colors placeholder-gray-400"
                required
              />
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Kategoria *</label>
              <select
                v-model="editingBlog.category"
                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none transition-colors"
                required
              >
                <option value="">Wybierz kategorię</option>
                <option value="Poradnik">Poradnik</option>
                <option value="Zdrowie">Zdrowie</option>
                <option value="Strategie">Strategie</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Treść *</label>
            <textarea
              v-model="editingBlog.content"
              placeholder="Wpisz zawartość artykułu"
              rows="6"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none transition-colors placeholder-gray-400 font-mono text-sm"
              required
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2"
              >Opis/Streszczenie *</label
            >
            <textarea
              v-model="editingBlog.excerpt"
              placeholder="Krótki opis artykułu"
              rows="3"
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none transition-colors placeholder-gray-400"
              required
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">URL Obrazu</label>
            <input
              v-model="editingBlog.image"
              type="url"
              placeholder="https://..."
              class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none transition-colors placeholder-gray-400"
            />
            <p class="text-xs text-gray-500 mt-1">Bezpośredni link do obrazu (opcjonalnie)</p>
          </div>

          <div
            class="flex items-center gap-3 p-4 bg-blue-50/50 rounded-xl border-2 border-blue-100"
          >
            <input
              id="published"
              v-model="editingBlog.published"
              type="checkbox"
              class="w-5 h-5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
            />
            <label for="published" class="text-sm font-medium text-gray-700 cursor-pointer">
              Opublikuj artykuł
            </label>
          </div>

          <div class="flex gap-3 pt-4">
            <button
              type="submit"
              class="group relative flex-1 px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden"
              :disabled="savingBlog"
            >
              <div
                class="absolute inset-0 bg-blue-700 opacity-0 group-hover:opacity-100 transition-opacity duration-300"
              ></div>
              <span class="relative flex items-center justify-center gap-2">
                <font-awesome-icon v-if="!savingBlog" icon="fa-solid fa-check" class="w-5 h-5" />
                <div
                  v-else
                  class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"
                ></div>
                {{ savingBlog ? 'Zapisywanie...' : 'Zapisz' }}
              </span>
            </button>
            <button
              type="button"
              class="flex-1 px-6 py-3 border-2 border-gray-200 text-gray-700 rounded-xl font-semibold hover:border-gray-400 hover:bg-gray-50/50 transition-all duration-300"
              @click="showEditBlogModal = false"
            >
              Anuluj
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, computed, onMounted, watch } from 'vue'
  import { useRoute } from 'vue-router'
  import api from '@/services/api'
  import { useSeo } from '@/composables/useSeo'
  import { countryOptions } from '@/constants/countries'
  import CategoriesManagement from '@/components/CategoriesManagement.vue'
  import PagesManagement from '@/components/PagesManagement.vue'
  import HeroSection from '@/components/layout/HeroSection.vue'
  import StatCard from '@/components/atomic/StatCard.vue'
  import Spinner from '@/components/feedback/Spinner.vue'

  const route = useRoute()
  const activeTab = ref('users')
  const loading = ref(false)

  // ===== Statystyki (Analytics) =====
  const analyticsPeriodOptions = [
    { value: 'today', label: 'Dziś' },
    { value: '7d', label: '7 dni' },
    { value: '30d', label: '30 dni' },
    { value: '90d', label: '90 dni' },
    { value: '365d', label: 'Rok' },
  ]
  const analyticsPeriod = ref('30d')
  const analyticsData = ref(null)
  const analyticsLoading = ref(false)
  const analyticsChartMetric = ref('visits')

  const analyticsMetricCards = computed(() => {
    if (!analyticsData.value) return []
    const s = analyticsData.value.summary
    return [
      { key: 'visits', label: 'Odwiedziny', value: s.visits.value, changePct: s.visits.change_pct },
      {
        key: 'unique_visitors',
        label: 'Unikalni odwiedzający',
        value: s.unique_visitors.value,
        changePct: s.unique_visitors.change_pct,
      },
      {
        key: 'new_accounts',
        label: 'Nowe konta',
        value: s.new_accounts.value,
        changePct: s.new_accounts.change_pct,
      },
      {
        key: 'new_auctions',
        label: 'Nowe aukcje',
        value: s.new_auctions.value,
        changePct: s.new_auctions.change_pct,
      },
      { key: 'bids', label: 'Oferty', value: s.bids.value, changePct: s.bids.change_pct },
    ]
  })

  const analyticsSeriesField = computed(() =>
    analyticsChartMetric.value === 'new_accounts' ? 'new_accounts' : analyticsChartMetric.value
  )

  const analyticsLinePoints = computed(() => {
    const series = analyticsData.value?.series ?? []
    if (!series.length) return ''
    const values = series.map((d) => d[analyticsSeriesField.value] ?? 0)
    const max = Math.max(...values, 1)
    const stepX = series.length > 1 ? 300 / (series.length - 1) : 0
    return values.map((v, i) => `${i * stepX},${100 - (v / max) * 95}`).join(' ')
  })

  const analyticsAreaPoints = computed(() => {
    const series = analyticsData.value?.series ?? []
    if (!series.length) return ''
    const stepX = series.length > 1 ? 300 / (series.length - 1) : 0
    const lastX = (series.length - 1) * stepX
    return `0,100 ${analyticsLinePoints.value} ${lastX},100`
  })

  const formatChartDate = (dateStr) => {
    if (!dateStr) return ''
    const d = new Date(dateStr)
    return d.toLocaleDateString('pl-PL', { day: 'numeric', month: 'short' })
  }

  const loadAnalytics = async () => {
    analyticsLoading.value = true
    try {
      const response = await api.get('/admin/analytics', {
        params: { period: analyticsPeriod.value },
      })
      analyticsData.value = response.data.data
    } catch (error) {
      console.error('Error loading analytics:', error)
      showToast('Nie udało się załadować statystyk', 'error')
    } finally {
      analyticsLoading.value = false
    }
  }

  watch(analyticsPeriod, loadAnalytics)
  watch(activeTab, (tab) => {
    if (tab === 'analytics' && !analyticsData.value) {
      loadAnalytics()
    }
  })

  const stats = ref({
    users: 0,
    auctions: 0,
    bids: 0,
    revenue: '$0',
    banned: 0,
  })

  const users = ref([])
  const auctions = ref([])
  const reports = ref([])
  const availableAuctions = ref([])
  const categories = ref([])

  const activeSettingsSection = ref('general')
  const savingSettings = ref(false)

  const newsletterTotal = ref(0)
  const newsletterSubscribers = ref([])
  const showNewsletterList = ref(false)
  const loadNewsletter = async () => {
    try {
      const response = await api.get('/admin/newsletter')
      newsletterTotal.value = response.data.total || 0
      newsletterSubscribers.value = response.data.data || []
    } catch (e) {
      /* sekcja opcjonalna */
    }
  }
  loadNewsletter()

  const errorLogs = ref([])
  const errorLogsLoading = ref(false)
  const errorLogsShowResolved = ref(false)
  const expandedErrorLogId = ref(null)

  const loadErrorLogs = async () => {
    errorLogsLoading.value = true
    try {
      const response = await api.get('/admin/error-logs', {
        params: { unresolved_only: errorLogsShowResolved.value ? undefined : true },
      })
      errorLogs.value = response.data.data.data || []
    } catch (e) {
      showToast('Nie udało się załadować listy błędów', 'error')
    } finally {
      errorLogsLoading.value = false
    }
  }

  const resolveErrorLog = async (log) => {
    try {
      await api.patch(`/admin/error-logs/${log.id}/resolve`)
      showToast('Błąd oznaczony jako rozwiązany', 'success')
      await loadErrorLogs()
    } catch (e) {
      showToast('Nie udało się zaktualizować błędu', 'error')
    }
  }

  const deleteErrorLog = async (log) => {
    try {
      await api.delete(`/admin/error-logs/${log.id}`)
      showToast('Wpis usunięty', 'success')
      await loadErrorLogs()
    } catch (e) {
      showToast('Nie udało się usunąć wpisu', 'error')
    }
  }

  const toggleErrorLogTrace = (log) => {
    expandedErrorLogId.value = expandedErrorLogId.value === log.id ? null : log.id
  }

  watch(activeTab, (tab) => {
    if (tab === 'errors' && errorLogs.value.length === 0) {
      loadErrorLogs()
    }
  })
  watch(errorLogsShowResolved, () => loadErrorLogs())

  const settingsSections = [
    { id: 'general', label: 'Ogólne' },
    { id: 'auctions', label: 'Aukcje' },
    { id: 'security', label: 'Bezpieczeństwo' },
    { id: 'users', label: 'Użytkownicy' },
    { id: 'moderation', label: 'Moderacja' },
    { id: 'notifications', label: 'Powiadomienia' },
    { id: 'messages', label: 'Wiadomości' },
  ]

  const platformSettings = ref({
    platform_name: 'Gołębiowy Lot',
    platform_email: 'kontakt@example.com',
    platform_phone: '',
    platform_description: '',
    platform_logo_url: '',

    default_auction_duration: 7,
    bid_increment_percentage: 5,

    only_admin_can_list: false,
    bidding_enabled: true,
    google_analytics_id: '',
    require_2fa: false,
    require_email_verification: true,
    require_phone_verification: false,
    max_login_attempts: 5,
    lockout_duration_minutes: 15,

    max_auctions_per_day: 10,
    max_auctions_per_week: 50,
    max_auctions_per_month: 200,
    free_user_auction_limit: 5,
    premium_user_auction_limit: 999,

    auto_ban_reports_threshold: 5,
    auto_ban_duration_hours: 24,
    enable_user_reports: true,
    require_auction_approval: true,

    send_auction_ending_notification: true,
    send_outbid_notification: true,
    send_won_auction_notification: true,
    send_email_digest: true,
    email_digest_frequency: 'daily',

    system_announcement: '',
    announcement_active: false,
    announcement_type: 'info',
    maintenance_message: '',
    maintenance_mode: false,

    max_image_upload_size_mb: 10,
    max_images_per_auction: 10,
  })

  const showEditUserModal = ref(false)
  const showEditAuctionModal = ref(false)
  const showBanModal = ref(false)
  const showHandleReportModal = ref(false)

  const userModalMode = ref('add') // 'add' or 'edit'
  const auctionModalMode = ref('add') // 'add' or 'edit'

  const editingUser = ref({
    avatar: null,
  })
  const avatarFile = ref(null)
  const editingAuction = ref({
    pigeon_images: [],
    pedigree_images: [],
  })
  const banData = ref({
    userId: null,
    reason: '',
    hours: 24,
  })
  const reportData = ref({
    reportId: null,
    notes: '',
    ban_user: false,
    ban_hours: 24,
    reported_user_id: null,
  })

  // Blog states
  const blogs = ref([])
  const showEditBlogModal = ref(false)
  const blogModalMode = ref('add') // 'add' or 'edit'
  const savingBlog = ref(false)
  const editingBlog = ref({
    id: null,
    title: '',
    content: '',
    excerpt: '',
    image: '',
    category: '',
    published: false,
  })

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

  // Confirmation modal
  const confirmModal = ref({
    show: false,
    title: '',
    message: '',
    onConfirm: null,
    confirmText: 'Potwierdź',
    cancelText: 'Anuluj',
    type: 'danger',
  })

  const showConfirm = (message, title = 'Potwierdź akcję', onConfirm, type = 'danger') => {
    confirmModal.value = {
      show: true,
      title,
      message,
      onConfirm,
      confirmText: type === 'danger' ? 'Usuń' : 'Potwierdź',
      cancelText: 'Anuluj',
      type,
    }
  }

  const handleConfirm = () => {
    if (confirmModal.value.onConfirm) {
      confirmModal.value.onConfirm()
    }
    confirmModal.value.show = false
  }

  const handleCancel = () => {
    confirmModal.value.show = false
  }

  const formatDate = (date) => {
    return new Date(date).toLocaleDateString('pl-PL')
  }

  const getAvatarUrl = (path) => {
    if (!path) return ''
    if (path.startsWith('http')) return path
    return `${window.location.origin}/storage/${path}`
  }

  const loadDashboardData = async () => {
    try {
      loading.value = true

      const dashboardRes = await api.get('/admin/dashboard')
      if (dashboardRes.data.stats) {
        stats.value = {
          users: dashboardRes.data.stats.total_users || 0,
          auctions: dashboardRes.data.stats.total_auctions || 0,
          bids: 0,
          revenue: '$0',
          banned: dashboardRes.data.stats.banned_users || 0,
        }
      }

      const usersRes = await api.get('/admin/users')
      if (usersRes.data.data) {
        users.value = usersRes.data.data
      }

      const auctionsRes = await api.get('/admin/auctions')
      if (auctionsRes.data.data) {
        auctions.value = auctionsRes.data.data.map((auction) => ({
          ...auction,
          title: auction.title || 'N/A',
        }))
      }

      const reportsRes = await api.get('/admin/reports')
      if (reportsRes.data.data) {
        reports.value = reportsRes.data.data
      }

      try {
        const blogsRes = await api.get('/blogs')
        if (blogsRes.data.data) {
          blogs.value = blogsRes.data.data
        }
      } catch (blogErr) {
        // Silent error handling - blogs failed to load
      }

      try {
        const auctionsRes = await api.get('/auctions')
        if (auctionsRes.data.data) {
          availableAuctions.value = auctionsRes.data.data
        }
      } catch (err) {
        // Silent error handling - auctions failed to load
      }

      try {
        const categoriesRes = await api.get('/admin/categories')
        if (categoriesRes.data.data) {
          categories.value = categoriesRes.data.data
        }
      } catch (catErr) {
        // Silent error handling - categories failed to load
      }

      await loadSettingsFromServer()
    } catch (err) {
      console.error('Error loading admin data:', err)
    } finally {
      loading.value = false
    }
  }

  // Funkcja pomocnicza do konwersji daty z ISO na format datetime-local
  const formatDateForInput = (dateString) => {
    if (!dateString) return ''
    try {
      const date = new Date(dateString)
      if (isNaN(date.getTime())) return ''
      // Format: YYYY-MM-DDTHH:mm
      const year = date.getFullYear()
      const month = String(date.getMonth() + 1).padStart(2, '0')
      const day = String(date.getDate()).padStart(2, '0')
      const hours = String(date.getHours()).padStart(2, '0')
      const minutes = String(date.getMinutes()).padStart(2, '0')
      return `${year}-${month}-${day}T${hours}:${minutes}`
    } catch (e) {
      return ''
    }
  }

  const editUser = (user) => {
    userModalMode.value = 'edit'
    avatarFile.value = null
    editingUser.value = {
      ...user,
      avatar: null,
      // Konwersja dat do formatu datetime-local
      premium_until: formatDateForInput(user.premium_until),
      ban_until: formatDateForInput(user.ban_until),
      last_login_at: formatDateForInput(user.last_login_at),
      email_verified_at: formatDateForInput(user.email_verified_at),
      phone_verified_at: formatDateForInput(user.phone_verified_at),
    }
    showEditUserModal.value = true
  }

  const openAddUserModal = () => {
    userModalMode.value = 'add'
    avatarFile.value = null
    editingUser.value = {
      name: '',
      email: '',
      first_name: '',
      last_name: '',
      phone: '',
      password: '',
      role: 'user',
      is_admin: false,
      is_active: false,
      can_list_when_restricted: false,
      is_premium: false,
      is_public: true,
      premium_plan: 'free',
      avatar: null,
      bio: '',
      address: '',
      city: '',
      postcode: '',
      country: '',
      reputation: 0,
      listings_free_count: 0,
      is_banned: false,
      ban_reason: '',
      two_factor_enabled: false,
    }
    showEditUserModal.value = true
  }

  const handleAvatarUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
      avatarFile.value = file
      const reader = new FileReader()
      reader.onload = (e) => {
        editingUser.value.avatar = e.target.result
      }
      reader.readAsDataURL(file)
    }
  }

  const removeAvatar = () => {
    editingUser.value.avatar = null
    avatarFile.value = null
  }

  // Synchronizacja roli z is_admin
  watch(
    () => editingUser.value?.role,
    (newRole) => {
      if (editingUser.value) {
        editingUser.value.is_admin = newRole === 'admin'
      }
    }
  )

  watch(
    () => editingUser.value?.is_admin,
    (newIsAdmin) => {
      if (editingUser.value && newIsAdmin) {
        editingUser.value.role = 'admin'
      } else if (editingUser.value && !newIsAdmin && editingUser.value.role === 'admin') {
        editingUser.value.role = 'user'
      }
    }
  )

  watch(
    () => editingUser.value?.premium_plan,
    (newPlan) => {
      if (editingUser.value && newPlan && newPlan !== 'free') {
        const now = new Date()
        let months = 0

        switch (newPlan) {
          case '1month':
            months = 1
            break
          case '3months':
            months = 3
            break
          case '12months':
            months = 12
            break
        }

        if (months > 0) {
          now.setMonth(now.getMonth() + months)
          editingUser.value.premium_until = formatDateForInput(now.toISOString())
        }
      }
    }
  )

  const generatePassword = () => {
    const length = 12
    const charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*'
    let password = ''
    for (let i = 0; i < length; i++) {
      password += charset.charAt(Math.floor(Math.random() * charset.length))
    }
    if (editingUser.value) {
      editingUser.value.password = password
    }
  }

  const saveEditUser = async () => {
    try {
      const payload = {
        name: editingUser.value.name,
        email: editingUser.value.email,
        first_name: editingUser.value.first_name,
        last_name: editingUser.value.last_name,
        phone: editingUser.value.phone,
        role: editingUser.value.role,
        is_admin: editingUser.value.is_admin,
        is_active: editingUser.value.is_active,
        can_list_when_restricted: editingUser.value.can_list_when_restricted,
        is_premium: editingUser.value.is_premium,
        is_public: editingUser.value.is_public,
        premium_plan: editingUser.value.premium_plan,
        premium_until: editingUser.value.premium_until,
        bio: editingUser.value.bio,
        address: editingUser.value.address,
        city: editingUser.value.city,
        postcode: editingUser.value.postcode,
        country: editingUser.value.country,
        listings_free_count: editingUser.value.listings_free_count,
        is_banned: editingUser.value.is_banned,
        ban_reason: editingUser.value.ban_reason,
        two_factor_enabled: editingUser.value.two_factor_enabled,
      }

      if (editingUser.value.password && editingUser.value.password.trim() !== '') {
        payload.password = editingUser.value.password
      }

      if (editingUser.value.email_verified_at) {
        payload.email_verified_at = editingUser.value.email_verified_at
      }
      if (editingUser.value.phone_verified_at) {
        payload.phone_verified_at = editingUser.value.phone_verified_at
      }
      if (editingUser.value.ban_until) {
        payload.ban_until = editingUser.value.ban_until
      }
      if (editingUser.value.last_login_at) {
        payload.last_login_at = editingUser.value.last_login_at
      }
      if (editingUser.value.last_login_ip) {
        payload.last_login_ip = editingUser.value.last_login_ip
      }

      if (avatarFile.value) {
        const formData = new FormData()
        Object.entries(payload).forEach(([key, value]) => {
          if (value !== null && value !== undefined) {
            formData.append(key, typeof value === 'boolean' ? (value ? '1' : '0') : value)
          }
        })
        formData.append('avatar', avatarFile.value)

        if (userModalMode.value === 'add') {
          await api.post('/admin/users', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
          })
          showToast('Użytkownik został utworzony pomyślnie', 'success')
        } else {
          formData.append('_method', 'PATCH')
          await api.post(`/admin/users/${editingUser.value.id}`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
          })
          showToast('Użytkownik został zaktualizowany pomyślnie', 'success')
        }
      } else if (userModalMode.value === 'add') {
        await api.post('/admin/users', payload)
        showToast('Użytkownik został utworzony pomyślnie', 'success')
      } else {
        await api.patch(`/admin/users/${editingUser.value.id}`, payload)
        showToast('Użytkownik został zaktualizowany pomyślnie', 'success')
      }

      showEditUserModal.value = false
      avatarFile.value = null
      await loadDashboardData()
    } catch (err) {
      console.error('Error saving user:', err)
      showToast(err.response?.data?.message || 'Nie można zapisać użytkownika', 'error')
    }
  }

  const activateUser = async (user) => {
    try {
      await api.patch(`/admin/users/${user.id}/activate`, {
        is_active: true,
      })
      await loadDashboardData()
      showToast('Użytkownik został aktywowany', 'success')
    } catch (err) {
      console.error('Error activating user:', err)
      showToast(err.response?.data?.message || 'Nie można aktywować użytkownika', 'error')
    }
  }

  const deleteUserConfirm = (user) => {
    showConfirm(
      `Czy na pewno chcesz usunąć konto użytkownika "${user.name}"? Dane osobowe zostaną zanonimizowane, a konto trwale zablokowane. Ta operacja nie może być cofnięta.`,
      'Usuń konto użytkownika',
      async () => {
        try {
          await api.delete(`/admin/users/${user.id}`)
          showEditUserModal.value = false
          await loadDashboardData()
          showToast('Konto zostało usunięte', 'success')
        } catch (err) {
          console.error('Error deleting user:', err)
          showToast(err.response?.data?.message || 'Nie można usunąć konta', 'error')
        }
      },
      'danger'
    )
  }

  const banUser = (user) => {
    banData.value = {
      userId: user.id,
      reason: '',
      hours: 24,
    }
    showBanModal.value = true
  }

  const saveTemporaryBan = async () => {
    try {
      await api.post(`/admin/users/${banData.value.userId}/ban-temporary`, {
        reason: banData.value.reason,
        hours: banData.value.hours,
      })
      showBanModal.value = false
      await loadDashboardData()
      showToast(`Użytkownik został zabanowany na ${banData.value.hours} godzin`, 'success')
    } catch (err) {
      console.error('Error banning user:', err)
      showToast(err.response?.data?.message || 'Nie można zabanować użytkownika', 'error')
    }
  }

  const editAuction = (auction) => {
    auctionModalMode.value = 'edit'
    editingAuction.value = {
      ...auction,
      seller: auction.seller,
      winner: auction.winner || auction.highest_bidder,
      auction_type: auction.type || auction.auction_type || 'auction',
      sniper_threshold: auction.sniper_threshold || 2,
      extension_minutes: auction.extension_minutes || 5,
      buy_now_price: auction.buy_now_price || null,
      pigeon_images: [],
      pedigree_images: [],
      title: auction.title || '',
      breed: auction.breed || '',
      year: auction.year || new Date().getFullYear(),
      gender: auction.gender || '',
      color: auction.color || '',
      size: auction.size || '',
      description: auction.description || '',
      // Konwersja dat do formatu datetime-local
      started_at: formatDateForInput(auction.started_at),
      ends_at: formatDateForInput(auction.ends_at),
      original_ends_at: formatDateForInput(auction.original_ends_at),
    }
    showEditAuctionModal.value = true
  }

  const openAddAuctionModal = () => {
    auctionModalMode.value = 'add'
    editingAuction.value = {
      user_id: null,
      status: 'pending',
      auction_type: 'auction',
      start_price: 0,
      buy_now_price: null,
      bid_increment: 1,
      duration_days: 7,
      sniper_threshold: 2,
      extension_minutes: 5,
      started_at: null,
      ends_at: null,
      pigeon_images: [],
      pedigree_images: [],
      title: '',
      breed: '',
      year: new Date().getFullYear(),
      gender: '',
      color: '',
      size: '',
      description: '',
    }
    showEditAuctionModal.value = true
  }

  const uploadingPigeonImages = ref(false)
  const uploadingPedigreeImages = ref(false)

  const uploadImageFile = async (file, type = 'auction') => {
    const formData = new FormData()
    formData.append('image', file)
    formData.append('type', type)
    const res = await api.post('/images/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    return res.data.url
  }

  const extractUploadError = (e, fallback) => {
    const fieldErrors = e.response?.data?.errors
    const firstError = fieldErrors ? Object.values(fieldErrors)[0]?.[0] : null
    return firstError || e.response?.data?.message || fallback
  }

  const handlePigeonImageUpload = async (event) => {
    const files = Array.from(event.target.files)
    if (!editingAuction.value.pigeon_images) editingAuction.value.pigeon_images = []
    uploadingPigeonImages.value = true
    try {
      for (const file of files) {
        const url = await uploadImageFile(file, 'auction')
        editingAuction.value.pigeon_images.push(url)
      }
    } catch (e) {
      alert(extractUploadError(e, 'Błąd wysyłania zdjęcia gołębia'))
    } finally {
      uploadingPigeonImages.value = false
      event.target.value = ''
    }
  }

  const handlePedigreeImageUpload = async (event) => {
    const files = Array.from(event.target.files)
    if (!editingAuction.value.pedigree_images) editingAuction.value.pedigree_images = []
    uploadingPedigreeImages.value = true
    try {
      for (const file of files) {
        const url = await uploadImageFile(file, 'auction')
        editingAuction.value.pedigree_images.push(url)
      }
    } catch (e) {
      alert(extractUploadError(e, 'Błąd wysyłania rodowodu'))
    } finally {
      uploadingPedigreeImages.value = false
      event.target.value = ''
    }
  }

  const removePigeonImage = (index) => {
    editingAuction.value.pigeon_images.splice(index, 1)
  }

  const removePedigreeImage = (index) => {
    editingAuction.value.pedigree_images.splice(index, 1)
  }

  const saveEditAuction = async () => {
    try {
      const payload = {
        user_id: editingAuction.value.user_id,
        status: editingAuction.value.status,
        type: editingAuction.value.auction_type ?? editingAuction.value.type ?? 'auction',
        start_price: editingAuction.value.start_price,
        sniper_threshold: editingAuction.value.sniper_threshold,
        extension_minutes: editingAuction.value.extension_minutes,
      }

      payload.title = editingAuction.value.title
      payload.breed = editingAuction.value.breed
      payload.year = editingAuction.value.year
      payload.gender = editingAuction.value.gender
      payload.color = editingAuction.value.color
      payload.size = editingAuction.value.size
      payload.description = editingAuction.value.description

      if (editingAuction.value.pigeon_images && editingAuction.value.pigeon_images.length > 0) {
        payload.pigeon_images = editingAuction.value.pigeon_images
      }
      if (editingAuction.value.pedigree_images && editingAuction.value.pedigree_images.length > 0) {
        payload.pedigree_images = editingAuction.value.pedigree_images
      }

      if (auctionModalMode.value === 'add') {
        payload.duration_days = editingAuction.value.duration_days
        if (editingAuction.value.started_at) {
          payload.started_at = editingAuction.value.started_at
        }
        if (editingAuction.value.ends_at) {
          payload.ends_at = editingAuction.value.ends_at
        }

        await api.post('/admin/auctions-with-listing', payload)
        showToast('Aukcja została utworzona pomyślnie', 'success')
      } else {
        if (editingAuction.value.current_price !== undefined) {
          payload.current_price = editingAuction.value.current_price
        }
        if (editingAuction.value.winner_id !== undefined) {
          payload.winner_id = editingAuction.value.winner_id
        }
        if (editingAuction.value.current_bid !== undefined) {
          payload.current_price = editingAuction.value.current_bid
        }
        if (editingAuction.value.started_at) {
          payload.started_at = editingAuction.value.started_at
        }
        if (editingAuction.value.ends_at) {
          payload.ends_at = editingAuction.value.ends_at
        }
        if (editingAuction.value.times_extended !== undefined) {
          payload.times_extended = editingAuction.value.times_extended
        }

        await api.patch(`/admin/auctions/${editingAuction.value.id}`, payload)
      }

      showEditAuctionModal.value = false
      await loadDashboardData()
    } catch (err) {
      console.error('Error saving auction:', err)
      showToast(err.response?.data?.message || 'Nie można zapisać aukcji', 'error')
    }
  }

  const activateAuction = async (auction) => {
    showConfirm(
      `Czy na pewno chcesz aktywować aukcję "${auction.title || 'Bez tytułu'}"? Data zakończenia zostanie ustawiona na +7 dni.`,
      'Aktywuj aukcję',
      async () => {
        try {
          await api.post(`/admin/auctions/${auction.id}/activate`)
          await loadDashboardData()
          showToast('Aukcja została aktywowana', 'success')
        } catch (err) {
          console.error('Error activating auction:', err)
          showToast(err.response?.data?.message || 'Nie można aktywować aukcji', 'error')
        }
      },
      'warning'
    )
  }

  const deleteAuctionConfirm = async (auction) => {
    showConfirm(
      `Czy na pewno chcesz usunąć aukcję "${auction.title || 'Bez tytułu'}"? Ta operacja nie może być cofnięta.`,
      'Usuń aukcję',
      async () => {
        try {
          await api.delete(`/admin/auctions/${auction.id}`)
          await loadDashboardData()
          showToast('Aukcja została usunięta', 'success')
        } catch (err) {
          console.error('Error deleting auction:', err)
          showToast(err.response?.data?.message || 'Nie można usunąć aukcji', 'error')
        }
      },
      'danger'
    )
  }

  const handleReport = (report) => {
    reportData.value = {
      reportId: report.id,
      notes: report.notes || '',
      ban_user: false,
      ban_hours: 24,
      reported_user_id: report.reported_user?.id || null,
    }
    showHandleReportModal.value = true
  }

  const dismissReport = async (report) => {
    try {
      await api.patch(`/admin/reports/${report.id}`, {
        status: 'dismissed',
        notes: 'Odrzucono',
      })
      await loadDashboardData()
      showToast('Zgłoszenie zostało odrzucone', 'success')
    } catch (err) {
      console.error('Error dismissing report:', err)
      showToast(err.response?.data?.message || 'Nie można odrzucić zgłoszenia', 'error')
    }
  }

  const saveHandleReport = async () => {
    try {
      await api.patch(`/admin/reports/${reportData.value.reportId}`, {
        status: 'resolved',
        notes: reportData.value.notes,
        ban_user: reportData.value.ban_user,
        ban_hours: reportData.value.ban_hours,
      })
      showHandleReportModal.value = false
      await loadDashboardData()
      showToast('Zgłoszenie zostało rozpatrzone', 'success')
    } catch (err) {
      console.error('Error handling report:', err)
      showToast(err.response?.data?.message || 'Nie można rozpatrzyć zgłoszenia', 'error')
    }
  }

  const savePlatformSettings = async () => {
    try {
      savingSettings.value = true
      await api.patch('/admin/settings', platformSettings.value)
      showToast('Ustawienia platformy zostały zapisane', 'success')
    } catch (err) {
      console.error('Error saving settings:', err)
      showToast(err.response?.data?.message || 'Nie można zapisać ustawień', 'error')
    } finally {
      savingSettings.value = false
    }
  }

  const resetSettings = async () => {
    showConfirm(
      'Czy na pewno chcesz zresetować wszystkie ustawienia do domyślnych? Ta operacja nie może być cofnięta.',
      'Resetuj ustawienia',
      async () => {
        try {
          await api.post('/admin/settings/reset')
          await loadSettingsFromServer()
          showToast('Ustawienia zostały zresetowane do domyślnych', 'success')
        } catch (err) {
          console.error('Error resetting settings:', err)
          showToast('Nie można zresetować ustawień', 'error')
        }
      },
      'danger'
    )
  }

  const loadSettingsFromServer = async () => {
    try {
      const res = await api.get('/settings')
      if (res.data.data) {
        platformSettings.value = res.data.data
      }
    } catch (err) {
      console.error('Could not load settings:', err)
    }
  }

  // Blog functions
  const openAddBlogModal = () => {
    blogModalMode.value = 'add'
    editingBlog.value = {
      id: null,
      title: '',
      content: '',
      excerpt: '',
      image: '',
      category: '',
      published: false,
    }
    showEditBlogModal.value = true
  }

  const editBlog = (blog) => {
    blogModalMode.value = 'edit'
    editingBlog.value = {
      ...blog,
    }
    showEditBlogModal.value = true
  }

  const saveBlog = async () => {
    try {
      if (
        !editingBlog.value.title ||
        !editingBlog.value.content ||
        !editingBlog.value.excerpt ||
        !editingBlog.value.category
      ) {
        showToast('Wypełnij wszystkie wymagane pola', 'warning')
        return
      }

      savingBlog.value = true

      const payload = {
        title: editingBlog.value.title,
        content: editingBlog.value.content,
        excerpt: editingBlog.value.excerpt,
        category: editingBlog.value.category,
        image: editingBlog.value.image || null,
        published: editingBlog.value.published,
      }

      if (blogModalMode.value === 'add') {
        const response = await api.post('/admin/blogs', payload)
        blogs.value.push(response.data.data)
        showToast('Blog dodany pomyślnie', 'success')
      } else {
        const response = await api.patch(`/admin/blogs/${editingBlog.value.slug}`, payload)
        const index = blogs.value.findIndex((b) => b.id === editingBlog.value.id)
        if (index > -1) {
          blogs.value[index] = response.data.data
        }
        showToast('Blog zaktualizowany pomyślnie', 'success')
      }

      showEditBlogModal.value = false
    } catch (err) {
      console.error('Error saving blog:', err)
      showToast(err.response?.data?.message || 'Nie można zapisać bloga', 'error')
    } finally {
      savingBlog.value = false
    }
  }

  const deleteBlogConfirm = (blog) => {
    confirmModal.value = {
      show: true,
      type: 'danger',
      title: 'Usuń Blog',
      message: `Czy na pewno chcesz usunąć blog "${blog.title}"? To działanie jest nieodwracalne.`,
      confirmText: 'Usuń',
      onConfirm: () => deleteBlog(blog),
    }
  }

  const deleteBlog = async (blog) => {
    try {
      confirmModal.value.show = false
      await api.delete(`/admin/blogs/${blog.slug}`)
      blogs.value = blogs.value.filter((b) => b.id !== blog.id)
      showToast('Blog usunięty pomyślnie', 'success')
    } catch (err) {
      console.error('Error deleting blog:', err)
      showToast(err.response?.data?.message || 'Nie można usunąć bloga', 'error')
    }
  }

  // Blog helper functions
  const getBlogIcon = (blog) => {
    const iconMap = {
      Poradnik: 'book',
      Zdrowie: 'health',
      Strategie: 'strategy',
    }
    return iconMap[blog.category] || 'book'
  }

  const getBlogColor = (blog, type) => {
    const colorMap = {
      Poradnik: {
        color1: '#0ea5e9',
        color2: '#0284c7',
        tagBg: '#e0f2fe',
        tagColor: '#0c4a6e',
      },
      Zdrowie: {
        color1: '#22c55e',
        color2: '#16a34a',
        tagBg: '#dcfce7',
        tagColor: '#15803d',
      },
      Strategie: {
        color1: '#f97316',
        color2: '#ea580c',
        tagBg: '#fed7aa',
        tagColor: '#92400e',
      },
    }
    const colors = colorMap[blog.category] || colorMap['Poradnik']
    return colors[type] || '#0284c7'
  }

  const getBlogEmoji = (blog) => {
    const emojiMap = {
      Poradnik: '📚',
      Zdrowie: '🏥',
      Strategie: '🎯',
    }
    return emojiMap[blog.category] || '📝'
  }

  onMounted(() => {
    useSeo({
      title: 'Panel Administracyjny',
      description: 'Zarządzaj użytkownikami, aukcjami, blogiem i ustawieniami platformy.',
    })

    const validTabs = [
      'analytics',
      'users',
      'auctions',
      'reports',
      'blogs',
      'categories',
      'pages',
      'settings',
      'errors',
    ]
    if (validTabs.includes(route.query.tab)) {
      activeTab.value = route.query.tab
    }

    loadDashboardData()
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
