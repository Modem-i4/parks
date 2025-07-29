import { ref, computed } from 'vue'

export function useSearchFilter(sourceRef, getFields = ['name']) {
  const query = ref('')

  function match(item, q) {
    const lower = q.toLowerCase()
    return getFields.some(field => item[field]?.toLowerCase().includes(lower))
  }

  const filtered = computed(() => {
    const q = query.value.trim()
    return q
      ? sourceRef.value.filter(item => match(item, q))
      : sourceRef.value
  })

  return {
    query,
    filtered
  }
}
