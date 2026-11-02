import { ref } from 'vue'

export const copyCompleted = ref(false)

export async function copyToClipboard(text) {
  try {
    await navigator.clipboard.writeText(text)
    copyCompleted.value = true
    return true
  } catch {
    // Local HTTP deployments and older browsers may not expose Clipboard API.
    const focused = document.activeElement
    const field = document.createElement('textarea')
    field.value = text
    field.readOnly = true
    field.style.cssText = 'position:fixed;top:0;left:-9999px;'
    ;(document.fullscreenElement || document.body).appendChild(field)
    try {
      field.select()
      field.setSelectionRange(0, field.value.length)
      const copied = document.execCommand('copy')
      copyCompleted.value = copied
      return copied
    } catch {
      return false
    } finally {
      field.remove()
      focused?.focus?.({ preventScroll: true })
    }
  }
}
