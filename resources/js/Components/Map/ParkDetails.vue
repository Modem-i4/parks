<script setup>
import ImageSlider from '@/Components/Custom/ImageSlider.vue'
import ArrowButton from '../Custom/ArrowButton.vue'
import { useParkStore } from '@/Stores/useParkStore.js'
import PanelHeader from '../Custom/PanelHeader.vue'
import { setParkView } from '@/Helpers/Maps/SetParkView'
import MediaPickerModal from '../Media/MediaPickerModal.vue'
import { ref } from 'vue'

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
function openWorks() {
  setParkView(parkStore, 'single', 'works')
}

// Image pickers
const imageSliderRef = ref(null)
const showPicker = ref(false)
const pickerType = ref(null)

function startIconChange() {
  pickerType.value = 'icon'
  showPicker.value = true
}

function startGalleryChange() {
  pickerType.value = 'image'
  showPicker.value = true
}

function closeImagePicker() {
  pickerType.value = null
  showPicker.value = false
}

function pickerSaved(newImages) {
  if(pickerType.value === 'image') {
    imageSliderRef.value.update(parkStore.selectedMarker.id)
  }
  if(pickerType.value === 'icon') {
    parkStore.selectedMarker.icon = newImages[0]
  }
  closeImagePicker()
}
</script>

<template>
  <div class="p-4">
    <button @click="back" class="text-blue-500 mb-2">← Назад</button>

    <PanelHeader
      :title="parkStore.selectedMarker.name" :subtitle="parkStore.selectedMarker.address" :icon="parkStore.selectedMarker.icon?.file_path"
      :editable="true"
      @onIconClick="startIconChange"
    />

    <ImageSlider :modelId="parkStore.selectedMarker?.id || null" model="parks" class="my-2" ref="imageSliderRef"
      :editable="true"
      @onImageClick="startGalleryChange"
      @close="closeImagePicker"/>

    <div v-if="parkStore.selectedMarker?.description" class="text-gray-600 bg-white rounded px-4 py-6">
      <h3 class="text-lg font-semibold pb-2">Про парк</h3>
      <p>{{ parkStore.selectedMarker.description }}</p>
    </div>
    <div class="mt-6 space-y-3"
      v-if="!parkStore.isSingleParkView">
      <ArrowButton variant="secondaryBlack" @click="openWorks">Роботи парку</ArrowButton>
      <ArrowButton variant="primary" @click="openInfrastructure">Інфраструктура парку</ArrowButton>
      <ArrowButton variant="secondary" @click="openGreen">Насадження парку</ArrowButton>
    </div>

  </div>
  <MediaPickerModal
    v-if="showPicker"
    :type="pickerType"
    modelType="App\Models\Park"
    :modelId="parkStore.selectedMarker.id"
    @close="closeImagePicker"
    @saved="pickerSaved"
  />
</template>
