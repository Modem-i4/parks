<script setup>
import ImageSlider from '@/Components/Custom/ImageSlider.vue'
import ArrowButton from '../Custom/ArrowButton.vue'
import { router } from '@inertiajs/vue3'
import { useParkStore } from '@/Stores/useParkStore.js'

const parkStore = useParkStore()

function back() {
  parkStore.selectedMarker = null
  parkStore.isSingleParkView = false
}
function openInfrastructure() {
  parkStore.showPanel = false
  parkStore.isSingleParkView = true
}
</script>

<template>
  <div class="p-4">
    <button @click="back" class="text-blue-500 mb-2">← Назад</button>

    <div class="flex items-center p-4 space-x-4">
      <div class="flex-shrink-0 w-16 h-16 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center">
        <img v-if="parkStore.selectedMarker?.icon" :src="parkStore.selectedMarker.icon.file_path" alt="Icon" class="w-12 h-12 object-contain" />
        <div v-else class="text-gray-400 text-xl">🌳</div>
      </div>
      <div>
        <h3 class="text-lg font-semibold text-gray-900">{{ parkStore.selectedMarker?.name }}</h3>
        <p class="text-sm text-gray-500">{{ parkStore.selectedMarker?.address }}</p>
      </div>
    </div>

    <ImageSlider :modelId="parkStore.selectedMarker?.id || null" model="parks" class="my-2" />

    <div v-if="parkStore.selectedMarker?.description" class="text-gray-600 bg-white rounded px-4 py-6">
      <h3 class="text-lg font-semibold pb-2">Про парк</h3>
      <p>{{ parkStore.selectedMarker.description }}</p>
    </div>

    <div class="mt-6 space-y-3">
      <ArrowButton @click="openInfrastructure">Інфраструктура парку</ArrowButton>
      <ArrowButton :primary="false" @click="router.get(`/parks/${parkStore.selectedMarker?.id}`, { tab: 'plants' })">
        Насадження парку
      </ArrowButton>
    </div>
  </div>
</template>
