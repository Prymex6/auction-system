<template>
  <div
    v-if="showUnverified"
    class="mb-6 p-5 rounded-2xl border border-amber-200 bg-amber-50 shadow-sm"
  >
    <div class="flex items-start gap-3">
      <div
        class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0"
      >
        <font-awesome-icon icon="fa-solid fa-envelope" class="w-5 h-5" />
      </div>
      <div class="flex-1">
        <h3 class="font-semibold text-amber-900">Potwierdź swój adres e-mail</h3>
        <p class="text-sm text-amber-800 mt-1">
          Wysłaliśmy link weryfikacyjny na Twój adres e-mail. Dopiero po jego potwierdzeniu
          administrator będzie mógł zaakceptować Twoje konto.
        </p>
        <button
          :disabled="resending || resent"
          class="mt-3 text-sm font-medium text-amber-900 underline hover:no-underline disabled:opacity-60 disabled:cursor-not-allowed"
          @click="resend"
        >
          {{
            resent
              ? 'Link wysłany ponownie'
              : resending
                ? 'Wysyłanie...'
                : failed
                  ? 'Nie udało się wysłać — spróbuj ponownie'
                  : 'Wyślij link ponownie'
          }}
        </button>
      </div>
    </div>
  </div>

  <div
    v-else-if="showPendingApproval"
    class="mb-6 p-5 rounded-2xl border border-blue-200 bg-blue-50 shadow-sm"
  >
    <div class="flex items-start gap-3">
      <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center">
        <font-awesome-icon icon="fa-solid fa-circle-exclamation" class="w-5 h-5" />
      </div>
      <div>
        <h3 class="font-semibold text-blue-900">Konto oczekuje na akceptację</h3>
        <p class="text-sm text-blue-800 mt-1">
          {{ message }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref, computed } from 'vue'
  import { useAuthStore } from '@/stores/auth'
  import api from '@/services/api'

  defineProps({
    message: {
      type: String,
      default:
        'Twoje konto oczekuje na akceptację administratora. Do tego czasu nie możesz wystawiać ani licytować aukcji.',
    },
  })

  const authStore = useAuthStore()

  const showUnverified = computed(() => {
    return authStore.isAuthenticated && authStore.user && authStore.user.email_verified_at == null
  })

  const showPendingApproval = computed(() => {
    return (
      authStore.isAuthenticated &&
      authStore.user &&
      authStore.user.email_verified_at != null &&
      authStore.user.is_active === false
    )
  })

  const resending = ref(false)
  const resent = ref(false)
  const failed = ref(false)

  const resend = async () => {
    resending.value = true
    failed.value = false
    try {
      await api.post('/auth/email/verification-notification')
      resent.value = true
    } catch {
      // Say so. Swallowing this left the button spinning down to nothing and
      // the person with no idea whether the message had gone out.
      failed.value = true
    } finally {
      resending.value = false
    }
  }
</script>
