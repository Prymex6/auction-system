<template>
  <div class="space-y-3">
    <!-- Image Container -->
    <div v-if="src" class="relative group">
      <img
        :src="src"
        :alt="alt"
        :class="['w-full rounded-lg border border-gray-200 object-cover', `h-${height}`]"
      />

      <!-- Overlay on Hover -->
      <div
        v-if="removable"
        class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all rounded-lg flex items-center justify-center gap-2"
      >
        <button
          type="button"
          class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
          title="Remove image"
          @click="$emit('remove')"
        >
          <font-awesome-icon icon="fa-solid fa-xmark" class="w-4 h-4" />
        </button>

        <button
          v-if="downloadable"
          type="button"
          class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
          title="Download image"
          @click="downloadImage"
        >
          <font-awesome-icon icon="fa-solid fa-download" class="w-4 h-4" />
        </button>
      </div>

      <!-- Badge -->
      <div v-if="badge" class="absolute top-2 left-2">
        <Badge :variant="badge.variant" :size="badge.size">
          {{ badge.text }}
        </Badge>
      </div>
    </div>

    <!-- Metadata -->
    <div v-if="showMetadata && metadata" class="text-xs text-gray-600 space-y-1">
      <p v-if="metadata.size"><strong>Size:</strong> {{ (metadata.size / 1024).toFixed(2) }} KB</p>
      <p v-if="metadata.dimensions"><strong>Dimensions:</strong> {{ metadata.dimensions }}</p>
      <p v-if="metadata.uploadedAt">
        <strong>Uploaded:</strong> {{ formatDate(metadata.uploadedAt) }}
      </p>
    </div>
  </div>
</template>

<script setup>
  import { Badge } from '@/components'

  const props = defineProps({
    /**
     * Image source URL
     */
    src: {
      type: String,
      required: true,
    },
    /**
     * Alt text
     */
    alt: {
      type: String,
      default: 'Image',
    },
    /**
     * Height: '32', '48', '64', '80', '96', etc.
     */
    height: {
      type: String,
      default: '64',
    },
    /**
     * Can remove image
     */
    removable: {
      type: Boolean,
      default: false,
    },
    /**
     * Can download image
     */
    downloadable: {
      type: Boolean,
      default: false,
    },
    /**
     * Badge object: { text, variant, size }
     */
    badge: {
      type: Object,
      default: null,
    },
    /**
     * Show metadata (size, dimensions, etc.)
     */
    showMetadata: {
      type: Boolean,
      default: false,
    },
    /**
     * Metadata object: { size, dimensions, uploadedAt }
     */
    metadata: {
      type: Object,
      default: null,
    },
  })

  defineEmits(['remove'])

  /**
   * Format date
   */
  const formatDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('pl-PL')
  }

  /**
   * Download image
   */
  const downloadImage = () => {
    const link = document.createElement('a')
    link.href = props.src
    link.download = props.alt || 'image'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }
</script>
