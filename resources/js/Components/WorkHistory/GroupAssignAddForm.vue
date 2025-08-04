<template>
  <label class="flex items-center space-x-2 cursor-pointer">
    <input
      type="checkbox"
      :checked="isSelected"
      @change="toggle"
      class="accent-blue-600 w-5 h-5"
    />
    <span class="text-sm text-gray-700">Додати до 👷: групового призначення робіт</span>
  </label>
</template>

<script setup>
import { computed } from 'vue'
import { useParkStore } from '@/Stores/useParkStore'

const parkStore = useParkStore()

const isSelected = computed(() =>
  parkStore.pickedMarkers.some(m => m.id === parkStore.selectedMarker.id)
)

function toggle(event) {
  const checked = event.target.checked
  const exists = isSelected.value

  if (checked && !exists) {
    parkStore.pickedMarkers.push(parkStore.selectedMarker)
  } else if (!checked && exists) {
    parkStore.pickedMarkers = parkStore.pickedMarkers.filter(m => m.id !== parkStore.selectedMarker.id)
  }
}
</script>
