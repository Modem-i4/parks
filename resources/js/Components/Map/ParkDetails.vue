ParkDetails

<script setup>
import ImageSlider from '@/Components/Custom/ImageSlider.vue'
import ArrowButton from '@/Components/Custom/ArrowButton.vue'
import { useParkStore } from '@/Stores/useParkStore.js'
import PanelHeader from '@/Components/Custom/PanelHeader.vue'
import { setParkView } from '@/Helpers/Maps/SetParkView'
import MediaPickerModal from '@/Components/Media/MediaPickerModal.vue'
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3';
import { UserRole } from '@/Helpers/UserRole.js';
import BtnWhite from '../Custom/BtnWhite.vue'
import axios from 'axios'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'

const parkStore = useParkStore()

// Editing
const page = usePage()
const role = page.props.auth.user?.role
const canEdit = UserRole.atLeast(role, 'admin')
const editing = ref(false)
const form = ref({ name: '', description: '', address: '' })
watch(editing, (val) => {
  if (val && parkStore.selectedMarker) {
    const m = parkStore.selectedMarker
    form.value = { name: m.name, description: m.description, address: m.address }
  }
})

function save() {
  axios.patch(`/api/parks/${parkStore.selectedMarker.id}`, form.value)
  .then((res) => {
    Object.assign(parkStore.selectedMarker, res.data)
    editing.value = false
  })
}

// Navigation
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
  <div class="p-4 pt-1">
    <PanelHeader
      :title="parkStore.selectedMarker.name" :subtitle="parkStore.selectedMarker.address" :icon="parkStore.selectedMarker.icon?.file_path"
      :editable="true"
      @onIconClick="startIconChange"
    >
      <template #right>
        <SecondaryButton class="ml-auto hidden md:block" @click="parkStore.selectedMarker = null">< Назад</SecondaryButton>
      </template>
    </PanelHeader>

    <ImageSlider :modelId="parkStore.selectedMarker?.id || null" model="parks" class="my-2" ref="imageSliderRef"
      :editable="true"
      @onImageClick="startGalleryChange"
      @close="closeImagePicker"/>

    <template v-if="!editing">
      <div v-if="parkStore.selectedMarker?.description" class="text-gray-600 bg-white rounded px-4 py-6">
        <h3 class="text-lg font-semibold pb-2">
          Про парк 
          <span class="cursor-pointer"
            v-if="canEdit" 
            @click="editing = true"
          >✏️</span>
        </h3>
        <p>{{ parkStore.selectedMarker.description }}</p>
      </div>
      <div class="mt-6 space-y-3"
        v-if="!parkStore.isSingleParkView">
        <ArrowButton variant="secondaryBlack" @click="openWorks">Роботи парку</ArrowButton>
        <ArrowButton variant="primary" @click="openInfrastructure">Інфраструктура парку</ArrowButton>
        <ArrowButton variant="secondary" @click="openGreen">Насадження парку</ArrowButton>
      </div>
    </template>
    <div class="text-gray-600 bg-white rounded px-4 pt-2 pb-4"
      v-if="editing"
    >
      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Назва парку</label>
        <input v-model="form.name" class="w-full border border-gray-300 rounded px-2 py-1" />
      </div>
      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Адреса</label>
        <input v-model="form.address" class="w-full border border-gray-300 rounded px-2 py-1" />
      </div>
      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Опис</label>
        <textarea v-model="form.description" class="w-full border border-gray-300 rounded px-2 py-1" rows="6" />
      </div>
      <div class="absolute right-[4.5rem] z-[3]">
        <div class="fixed bottom-2">
          <BtnWhite
            @click="editing = false"
            class="p-[0.7rem] ms-auto"
          >❌</BtnWhite>
          <BtnWhite
            @click="save"
          >✔️</BtnWhite>
        </div>
      </div>
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
