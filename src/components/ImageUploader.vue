<template>
  <div class="space-y-4">
    <!-- Dropzone or File Input -->
    <div
      :class="[
        'border-2 border-dashed rounded-lg p-8 transition-colors text-center cursor-pointer',
        isDragging ? 'border-primary-600 bg-primary-50' : 'border-gray-300 hover:border-gray-400',
      ]"
      @drop.prevent="handleDrop"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
    >
      <input
        ref="fileInput"
        type="file"
        :accept="accept"
        class="hidden"
        @change="handleFileSelect"
      />

      <button type="button" class="w-full" @click="fileInput?.click()">
        <font-awesome-icon
          v-if="!hasFile"
          icon="fa-solid fa-plus"
          class="mx-auto h-12 w-12 text-gray-400"
        />

        <div v-if="!hasFile" class="mt-2">
          <p class="text-lg font-medium text-gray-900">Kliknij lub przeciągnij obraz</p>
          <p class="text-sm text-gray-500">PNG, JPG, WebP do {{ maxSize }}MB</p>
        </div>
      </button>
    </div>

    <!-- Preview -->
    <div v-if="preview" class="space-y-3">
      <div class="relative">
        <img
          :src="preview"
          :alt="label"
          class="w-full h-64 object-cover rounded-lg border border-gray-200"
        />

        <!-- Remove Button -->
        <button
          type="button"
          class="absolute top-2 right-2 bg-red-500 hover:bg-red-600 text-white p-2 rounded-full transition-colors"
          @click="handleRemove"
        >
          <font-awesome-icon icon="fa-solid fa-xmark" class="w-4 h-4" />
        </button>
      </div>

      <!-- File Info -->
      <div class="text-sm text-gray-600 space-y-1">
        <p><strong>Nazwa:</strong> {{ file?.name }}</p>
        <p><strong>Rozmiar:</strong> {{ fileSizeInMB }} MB</p>
      </div>

      <!-- Upload Progress -->
      <div v-if="uploading" class="space-y-2">
        <div class="flex justify-between text-sm">
          <span>Uploading...</span>
          <span>{{ progress }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
          <div
            class="bg-primary-600 h-2 rounded-full transition-all"
            :style="{ width: `${progress}%` }"
          ></div>
        </div>
      </div>
    </div>

    <!-- Error Message -->
    <Alert v-if="error" variant="danger" :message="error" closable @click="error = ''" />

    <!-- Upload Button -->
    <Button
      v-if="hasFile && !uploading"
      :loading="uploading"
      loading-text="Uploading..."
      full-width
      @click="handleUpload"
    >
      Upload Image
    </Button>
  </div>
</template>

<script setup>
  import { ref, computed } from 'vue'
  import { useImageUpload } from '@/composables/useImageUpload'
  import { Button, Alert } from '@/components'

  const props = defineProps({
    label: {
      type: String,
      default: 'Image',
    },
    maxSize: {
      type: Number,
      default: 5,
    },
    /**
     * Accept MIME types
     */
    accept: {
      type: String,
      default: 'image/jpeg,image/png,image/webp',
    },
    /**
     * API endpoint do uploadu
     */
    endpoint: {
      type: String,
      required: true,
    },
    additionalData: {
      type: Object,
      default: () => ({}),
    },
  })

  const emit = defineEmits(['upload:start', 'upload:success', 'upload:error', 'upload:progress'])

  const fileInput = ref(null)
  const isDragging = ref(false)

  const {
    file,
    preview,
    progress,
    uploading,
    error,
    fileSizeInMB,
    hasFile,
    selectFile,
    removeFile,
    uploadFile,
  } = useImageUpload({
    maxSize: props.maxSize,
  })

  /**
   * Handle file select
   */
  const handleFileSelect = async (e) => {
    const selectedFile = e.target.files?.[0]
    if (!selectedFile) return

    const success = await selectFile(selectedFile)
    if (!success) return

    // Reset input
    if (fileInput.value) fileInput.value.value = ''
  }

  /**
   * Handle drag and drop
   */
  const handleDrop = async (e) => {
    isDragging.value = false

    const droppedFile = e.dataTransfer?.files?.[0]
    if (!droppedFile) return

    const success = await selectFile(droppedFile)
    if (!success) return
  }

  /**
   * Handle remove
   */
  const handleRemove = () => {
    removeFile()
  }

  /**
   * Handle upload
   */
  const handleUpload = async () => {
    emit('upload:start')

    try {
      const response = await uploadFile(props.endpoint, props.additionalData)

      if (response) {
        emit('upload:success', response)
        removeFile()
      } else {
        emit('upload:error', error.value)
      }
    } catch (err) {
      emit('upload:error', err.message)
    }
  }

  // Watch progress
  const watchProgress = computed(() => progress.value)
</script>
