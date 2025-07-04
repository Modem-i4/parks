<script setup>
import ImageSlider from '@/Components/Custom/ImageSlider.vue'
import ArrowButton from '../Custom/ArrowButton.vue'
import { useParkStore } from '@/Stores/useParkStore.js'
import PanelHeader from '../Custom/PanelHeader.vue'
import { setParkView } from '@/Helpers/SetParkView'

const parkStore = useParkStore()

function back() {
  parkStore.selectedMarker = null
}

function openInfrastructure() {
  setParkView(parkStore, 'single', 'infrastructure')
}
function openGreen() {
  setParkView(parkStore, 'single', 'green')
}
</script>

<template>
  <div class="p-4">
    <button @click="back" class="text-blue-500 mb-2">← Назад</button>

    <PanelHeader
      :title="parkStore.selectedMarker.name" :subtitle="parkStore.selectedMarker.address" :icon="parkStore.selectedMarker.icon?.file_path"
    />

    <ImageSlider :modelId="parkStore.selectedMarker?.id || null" model="parks" class="my-2" />

    <div v-if="parkStore.selectedMarker?.description" class="text-gray-600 bg-white rounded px-4 py-6">
      <h3 class="text-lg font-semibold pb-2">Про парк</h3>
      <p>{{ parkStore.selectedMarker.description }}</p>
    </div>
    <div class="mt-6 space-y-3"
      v-if="!parkStore.isSingleParkView">
      <ArrowButton @click="openInfrastructure">Інфраструктура парку</ArrowButton>
      <ArrowButton :primary="false" @click="openGreen">Насадження парку</ArrowButton>
    </div>

  </div>
</template>
