<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-3">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>

    <!-- Upload Area -->
    <div class="relative">
      <div
        class="border-2 border-dashed border-gray-300 rounded-2xl p-8 text-center hover:border-blue-400 transition-colors duration-300 cursor-pointer bg-gray-50"
        :class="{ 'opacity-60 pointer-events-none': uploading }"
        @click="$refs.fileInput?.click()"
      >
        <div
          class="w-16 h-16 mx-auto mb-3 rounded-2xl bg-gray-100 flex items-center justify-center"
        >
          <font-awesome-icon icon="fa-solid fa-plus" class="w-8 h-8 text-blue-500" />
        </div>
        <p class="text-gray-700 font-medium mb-1">
          {{ uploading ? 'Wysyłanie...' : uploadText }}
        </p>
        <p class="text-sm text-gray-500">{{ formatHint }}</p>
      </div>

      <input
        ref="fileInput"
        type="file"
        class="hidden"
        :accept="accept"
        :multiple="multiple"
        @change="handleFileChange"
      />
    </div>

    <!-- Error Message -->
    <p v-if="error" class="mt-2 text-sm text-red-600">{{ error }}</p>

    <!-- Images Preview -->
    <div
      v-if="modelValue && modelValue.length > 0"
      class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4"
    >
      <div v-for="(image, index) in modelValue" :key="index" class="relative group">
        <img
          :src="image"
          :alt="label"
          class="w-full h-32 object-cover rounded-xl border border-gray-200"
        />
        <button
          type="button"
          class="absolute top-2 right-2 w-8 h-8 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 transition-colors opacity-0 group-hover:opacity-100"
          @click="removeImage(index)"
        >
          <font-awesome-icon icon="fa-solid fa-xmark" class="w-4 h-4" />
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { ref } from 'vue'
  import api from '@/services/api'

  const props = defineProps({
    modelValue: {
      type: Array,
      default: () => [],
    },
    label: {
      type: String,
      required: true,
    },
    uploadText: {
      type: String,
      default: 'Dodaj zdjęcia (możesz wybrać kilka)',
    },
    formatHint: {
      type: String,
      default: 'PNG, JPG, GIF do 10MB każde',
    },
    accept: {
      type: String,
      default: 'image/*',
    },
    required: {
      type: Boolean,
      default: false,
    },
    multiple: {
      type: Boolean,
      default: true,
    },
    maxSizePerFile: {
      type: Number,
      default: 15,
    },
    uploadType: {
      type: String,
      default: 'auction',
    },
  })

  const emit = defineEmits(['update:modelValue'])

  const fileInput = ref(null)
  const error = ref('')
  const uploading = ref(false)

  const handleFileChange = async (event) => {
    error.value = ''
    const files = Array.from(event.target.files || [])

    if (files.length === 0) return

    // Validate file sizes
    for (const file of files) {
      const sizeInMB = file.size / (1024 * 1024)
      if (sizeInMB > props.maxSizePerFile) {
        error.value = `Plik ${file.name} jest za duży (max ${props.maxSizePerFile}MB)`
        return
      }
    }

    uploading.value = true
    try {
      for (const file of files) {
        const formData = new FormData()
        formData.append('image', file)
        formData.append('type', props.uploadType)
        const res = await api.post('/images/upload', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })
        const newImages = [...(props.modelValue || [])]
        newImages.push(res.data.url)
        emit('update:modelValue', newImages)
      }
    } catch (e) {
      const fieldErrors = e.response?.data?.errors
      const firstError = fieldErrors ? Object.values(fieldErrors)[0]?.[0] : null
      error.value = firstError || e.response?.data?.message || 'Błąd wysyłania zdjęcia'
    } finally {
      uploading.value = false
      if (fileInput.value) fileInput.value.value = ''
    }
  }

  const removeImage = (index) => {
    const newImages = props.modelValue.filter((_, i) => i !== index)
    emit('update:modelValue', newImages)
  }
</script>
