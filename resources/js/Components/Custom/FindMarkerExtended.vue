<template>
  <div class="p-4 space-y-4">
    <h2 class="text-lg font-semibold text-center text-gray-700">
      🔍 Пошук та вибір насаджень
    </h2>

    <div class="flex gap-2">
      <input
        v-model="search"
        type="text"
        placeholder="Введіть інвентарний номер"
        class="min-w-0 flex-1 px-4 py-2 border rounded-lg shadow focus:outline-none focus:ring focus:border-blue-300 text-center"
        @keyup.enter="addMarker"
      />
      <SecondaryButton
        size="sm"
        title="Знайти на мапі"
        :disabled="loading"
        @click="goToMarker"
      >
        🔍
      </SecondaryButton>
      <PrimaryButton
        title="Додати до обраних маркерів"
        :disabled="loading"
        @click="addMarker"
      >
        +
      </PrimaryButton>
    </div>

    <p v-if="errorMessage" class="text-sm text-red-500 text-center">
      {{ errorMessage }}
    </p>

    <div v-if="parkStore.pickedMarkers.length > 0" class="max-h-[15rem] overflow-y-auto overflow-x-clip">
      <h3 class="font-semibold text-gray-800 text-center">Обрані маркери:</h3>

      <PanelHeader
        v-for="marker in parkStore.pickedMarkers"
        :key="marker.id"
        :title="`${getMarkerTitle(marker)} (${marker.green?.inventory_number})`"
        :subtitle="typeUkr[marker.type]"
        :icon="marker.icon?.file_path"
        variant="sm"
        class="bg-gray-100 border p-0 rounded-full px-5 my-1"
      >
        <template #right>
          <GreenStateIndicator :green="marker.green" />
          <button
            class="ml-2 text-gray-400 hover:text-red-600 text-lg font-bold leading-none"
            title="Видалити"
            @click="removePickedMarker(marker)"
          >
            ×
          </button>
        </template>
      </PanelHeader>
    </div>

    <div class="flex justify-center">
      <PrimaryButton
        :disabled="parkStore.pickedMarkers.length === 0"
        @click="applyPickedMarkers"
      >
        Застосувати
      </PrimaryButton>
    </div>
  </div>
</template>

<script setup>
import { useParkStore } from '@/Stores/useParkStore'
import { useFindMarker } from '@/Helpers/Maps/FindMarkerHelper'
import { getMarkerTitle, typeUkr } from '@/Helpers/Maps/GetMarkerTitle'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import PanelHeader from '@/Components/Custom/PanelHeader.vue'
import GreenStateIndicator from '@/Components/Markers/View/GreenStateIndicator.vue'

const emit = defineEmits(['close'])
const parkStore = useParkStore()
const { search, errorMessage, loading, findMarker, showMarker } = useFindMarker(parkStore)

async function goToMarker() {
  const marker = await findMarker()
  if (!marker) return

  showMarker(marker)
  emit('close')
}

async function addMarker() {
  const marker = await findMarker()
  if (!marker) return

  if (!parkStore.pickedMarkers.some(picked => picked.id === marker.id)) {
    parkStore.pickedMarkers.push(marker)
  }

  search.value = ''
}

function applyPickedMarkers() {
  parkStore.activeMarkerPreset = 'picked'
  parkStore.markerFilterRevision++
  emit('close')
}

function removePickedMarker(marker) {
  parkStore.pickedMarkers = parkStore.pickedMarkers.filter(picked => picked.id !== marker.id)
}
</script>
