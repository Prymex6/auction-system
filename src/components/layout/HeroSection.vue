<template>
  <section class="pt-24 pb-12 md:pt-28 md:pb-16 relative overflow-hidden" :class="computedClasses">
    <div class="absolute inset-0 bg-white"></div>
    <div class="absolute top-20 right-20 w-96 h-96 bg-blue-100/30 rounded-full blur-3xl"></div>
    <div class="absolute bottom-20 left-20 w-96 h-96 bg-purple-100/30 rounded-full blur-3xl"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
      <div class="text-center max-w-4xl mx-auto">
        <!-- Badge -->
        <div
          class="inline-flex items-center gap-2 px-4 py-2 bg-white rounded-full shadow-sm border border-gray-100 mb-8"
        >
          <font-awesome-icon
            v-if="showEliteBadgeIcon || props.showBadgeDot"
            icon="fa-solid fa-circle"
            :class="props.showBadgeDot ? 'w-2 h-2 text-blue-500' : 'w-1 h-1 text-gray-500'"
            aria-hidden="true"
          />
          <span class="text-sm font-medium text-gray-700">{{ badge }}</span>
        </div>

        <!-- Main Title -->
        <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 mb-6">
          <slot name="title">
            {{ title }}
            <span v-if="gradient" class="block text-gray-900">
              {{ gradient }}
            </span>
          </slot>
        </h1>

        <!-- Description -->
        <p class="text-xl text-gray-600 mb-10 max-w-2xl mx-auto leading-relaxed">
          <slot name="description">{{ description }}</slot>
        </p>

        <!-- Additional Content Slot -->
        <slot name="extra"></slot>
      </div>
    </div>
  </section>
</template>

<script setup>
  import { computed } from 'vue'

  const props = defineProps({
    badge: {
      type: String,
      required: true,
    },
    title: {
      type: String,
      required: true,
    },
    gradient: {
      type: String,
      default: '',
    },
    description: {
      type: String,
      default: '',
    },
    extraPadding: {
      type: Boolean,
      default: false,
    },
    showBadgeDot: {
      type: Boolean,
      default: false,
    },
  })

  const computedClasses = computed(() => {
    return {
      'pt-32': true, // Extra padding for account not active alert
    }
  })

  const showEliteBadgeIcon = computed(() => {
    return props.badge === 'Elitarne aukcje gołębi pocztowych'
  })
</script>
