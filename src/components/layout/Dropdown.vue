<template>
  <div class="relative inline-block">
    <!-- Trigger -->
    <button :class="['inline-flex items-center gap-2', triggerClass]" @click="isOpen = !isOpen">
      <slot name="trigger">
        {{ label }}
      </slot>
      <font-awesome-icon
        icon="fa-solid fa-arrow-down"
        class="w-4 h-4 transition-transform"
        :class="{ 'rotate-180': isOpen }"
      />
    </button>

    <!-- Dropdown menu -->
    <Transition name="dropdown">
      <div
        v-if="isOpen"
        :class="[
          'absolute top-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg min-w-[200px]',
          positionClass,
        ]"
        @click.stop
      >
        <button
          v-for="(item, index) in items"
          :key="index"
          class="block w-full text-left px-4 py-2 hover:bg-gray-100 transition-colors first:rounded-t-lg last:rounded-b-lg"
          @click="selectItem(item)"
        >
          <slot name="item" :item="item">
            {{ item.label }}
          </slot>
        </button>
      </div>
    </Transition>

    <!-- Backdrop (for closing on outside click) -->
    <div v-if="isOpen" class="fixed inset-0 z-40" @click="isOpen = false"></div>
  </div>
</template>

<script setup>
  import { ref } from 'vue'

  const props = defineProps({
    /**
     * Dropdown label/title
     */
    label: {
      type: String,
      default: 'Menu',
    },
    /**
     * Array of items: { label, value, ... }
     */
    items: {
      type: Array,
      required: true,
    },
    /**
     * Position: 'left', 'right'
     */
    position: {
      type: String,
      default: 'left',
      validator: (v) => ['left', 'right'].includes(v),
    },
    /**
     * Trigger button class
     */
    triggerClass: {
      type: String,
      default: 'btn-neutral',
    },
  })

  const emit = defineEmits(['select'])

  const isOpen = ref(false)

  const selectItem = (item) => {
    emit('select', item)
    isOpen.value = false
  }

  const positionClass =
    {
      left: 'left-0',
      right: 'right-0',
    }[props.position] || 'left-0'
</script>

<style scoped>
  .dropdown-enter-active,
  .dropdown-leave-active {
    transition: all 0.2s ease;
  }

  .dropdown-enter-from {
    opacity: 0;
    transform: translateY(-4px);
  }

  .dropdown-leave-to {
    opacity: 0;
    transform: translateY(-4px);
  }
</style>
