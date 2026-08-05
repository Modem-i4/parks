<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue'

const props = defineProps({
  target: {
    type: Object,
    default: null,
  },
})

const isFullscreen = ref(false)

function updateFullscreenState() {
  isFullscreen.value = document.fullscreenElement === props.target
}

async function toggleFullscreen() {
  if (isFullscreen.value) {
    await document.exitFullscreen()
  } else {
    await props.target?.requestFullscreen()
  }
}

onMounted(() => document.addEventListener('fullscreenchange', updateFullscreenState))
onBeforeUnmount(() => document.removeEventListener('fullscreenchange', updateFullscreenState))
</script>

<template>
  <button
    type="button"
    :aria-label="isFullscreen ? 'Вийти з повноекранного режиму' : 'Відкрити на весь екран'"
    :title="isFullscreen ? 'Вийти з повноекранного режиму' : 'На весь екран'"
    @click="toggleFullscreen"
  >
    <svg
      v-if="isFullscreen"
      aria-hidden="true"
      viewBox="0 0 24 24"
      class="h-4 w-4"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
    >
      <path d="M9 3v6H3M15 3v6h6M9 21v-6H3M15 21v-6h6" />
    </svg>
    <svg
      v-else
      aria-hidden="true"
      viewBox="0 0 24 24"
      class="h-4 w-4"
      fill="none"
      stroke="currentColor"
      stroke-width="2"
    >
      <path d="M9 3H3v6M15 3h6v6M9 21H3v-6M15 21h6v-6" />
    </svg>
  </button>
</template>
