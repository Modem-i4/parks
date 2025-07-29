export async function handleLoading(loadingRef, fn) {
  loadingRef.value = true
  try {
    await fn()
  } finally {
    loadingRef.value = false
  }
}
