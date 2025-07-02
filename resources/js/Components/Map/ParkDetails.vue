<script setup>
import ImageSlider from '@/Components/Custom/ImageSlider.vue'
import ArrowButton from '../Custom/ArrowButton.vue'
import { useParkStore } from '@/Stores/useParkStore.js'
import ParkHeader from '../Custom/ParkHeader.vue'
import { setParkView } from '@/Helpers/SetParkView'

const parkStore = useParkStore()

function back() {
  parkStore.selectedMarker = null
}

function openInfrastructure() {
  setParkView(parkStore, 'single')
}
function openGreen() {
  setParkView(parkStore, 'single')
}
</script>

<template>
  <div class="p-4">
    <button @click="back" class="text-blue-500 mb-2">← Назад</button>

    <ParkHeader :park="parkStore.selectedMarker" />

    <ImageSlider :modelId="parkStore.selectedMarker?.id || null" model="parks" class="my-2" />

    <div v-if="parkStore.selectedMarker?.description" class="text-gray-600 bg-white rounded px-4 py-6">
      <h3 class="text-lg font-semibold pb-2">Про парк</h3>
      <p>{{ parkStore.selectedMarker.description }}</p>
    </div>
    <div class="mt-6 space-y-3 transform transition-transform duration-300"
      :class="{
        '-translate-x-[120%]': parkStore.isSingleParkView
      }"
      v-if="!parkStore.isSingleParkView || parkStore.lockMapChange">
      <ArrowButton @click="openInfrastructure">Інфраструктура парку</ArrowButton>
      <ArrowButton :primary="false" @click="openGreen">Насадження парку</ArrowButton>
    </div>

  </div>
</template>
