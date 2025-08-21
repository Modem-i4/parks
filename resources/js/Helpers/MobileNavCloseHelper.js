import { onMounted, onBeforeUnmount } from 'vue'

export function useMobileNavClose({ isOpenRef, rootElRef }) {
  if (!isOpenRef) throw new Error('isOpenRef is required')

  function onPointerDown(e) {
    if (!isOpenRef.value) return
    const root = rootElRef?.value
    if (root && !root.contains(e.target)) {
      isOpenRef.value = false
    }
  }

  function onKeyDown(e) {
    if (e.key === 'Escape' && isOpenRef.value) {
      isOpenRef.value = false
    }
  }

  onMounted(() => {
    document.addEventListener('pointerdown', onPointerDown, true)
    document.addEventListener('keydown', onKeyDown)
  })

  onBeforeUnmount(() => {
    document.removeEventListener('pointerdown', onPointerDown, true)
    document.removeEventListener('keydown', onKeyDown)
  })
}
