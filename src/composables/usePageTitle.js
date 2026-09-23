import { ref } from 'vue'

// z licznikiem nieprzeczytanych - App.vue dokleja prefiks reaktywnie.
export const basePageTitle = ref(document.title || 'Gołębiowy Lot')

export function setBasePageTitle(title) {
  basePageTitle.value = title
}
