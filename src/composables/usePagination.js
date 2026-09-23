import { ref, computed } from 'vue'

export const usePagination = (initialPage = 1, itemsPerPage = 12) => {
  const currentPage = ref(initialPage)
  const total = ref(0)
  const perPage = ref(itemsPerPage)
  const lastPage = ref(1)

  const totalPages = computed(() => Math.ceil(total.value / perPage.value))

  const from = computed(() => (currentPage.value - 1) * perPage.value + 1)

  const to = computed(() => Math.min(currentPage.value * perPage.value, total.value))

  const visiblePages = computed(() => {
    const pages = []
    const maxPages = 5
    let start = Math.max(1, currentPage.value - 2)
    let end = Math.min(totalPages.value, start + maxPages - 1)

    if (end - start < maxPages - 1) {
      start = Math.max(1, end - maxPages + 1)
    }

    for (let i = start; i <= end; i++) {
      pages.push(i)
    }

    return pages
  })

  const hasNextPage = computed(() => currentPage.value < totalPages.value)

  const hasPreviousPage = computed(() => currentPage.value > 1)

  const setTotal = (newTotal) => {
    total.value = newTotal
    lastPage.value = totalPages.value
  }

  const goToPage = (page) => {
    if (page >= 1 && page <= totalPages.value) {
      currentPage.value = page
    }
  }

  const nextPage = () => {
    if (hasNextPage.value) {
      currentPage.value++
    }
  }

  const previousPage = () => {
    if (hasPreviousPage.value) {
      currentPage.value--
    }
  }

  const firstPage = () => {
    currentPage.value = 1
  }

  const lastPageNumber = () => {
    currentPage.value = totalPages.value
  }

  const reset = () => {
    currentPage.value = 1
    total.value = 0
    lastPage.value = 1
  }

  return {
    currentPage,
    total,
    perPage,
    lastPage,
    totalPages,
    from,
    to,
    visiblePages,
    hasNextPage,
    hasPreviousPage,
    setTotal,
    goToPage,
    nextPage,
    previousPage,
    firstPage,
    lastPageNumber,
    reset,
  }
}
