import { ref } from 'vue'

export const copyCompleted = ref(false)

export async function copyToClipboard(text) {
  try {
    await navigator.clipboard.writeText(text)
    copyCompleted.value = true
  } catch (err) {
    console.error('Не вдалося скопіювати:', err)
  }
}
