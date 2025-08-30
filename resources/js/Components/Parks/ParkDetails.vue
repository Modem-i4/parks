<script setup>
import ImageSlider from '@/Components/Custom/ImageSlider.vue'
import ArrowButton from '@/Components/Custom/ArrowButton.vue'
import { useParkStore } from '@/Stores/useParkStore.js'
import PanelHeader from '@/Components/Custom/PanelHeader.vue'
import { setParkView } from '@/Helpers/Maps/SetParkView'
import MediaPickerModal from '@/Components/Media/MediaPickerModal.vue'
import { computed, ref, watch } from 'vue'
import BtnWhite from '../Custom/BtnWhite.vue'
import axios from 'axios'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import { useAuthStore } from '@/Stores/useAuthStore'
import FormError from '@/Components/Custom/FormError.vue'
import Tooltip from '../Custom/Tooltip.vue'
import { copyToClipboard, copyCompleted } from '@/Helpers/CopyToClipboard'

const parkStore = useParkStore()

// Editing
const authStore = useAuthStore()
const editing = ref(false)
const form = ref({ name: '', description: '', area: '', address: '', operator: '' })
watch(editing, (val) => {
  if (val && parkStore.selectedMarker) {
    const m = parkStore.selectedMarker
    form.value = { name: m.name, description: m.description, area: m.area, address: m.address, operator: m.operator }
  }
})

const errors = ref({})
function getByPath(obj, path) {
  return path.split('.').reduce((acc, k) => acc?.[k], obj)
}
function snapshot(obj) {
  return JSON.parse(JSON.stringify(obj))
}
watch(
  () => snapshot(form.value),
  (newVal, oldVal) => {
    const errKeys = Object.keys(errors.value || {})
    for (const key of errKeys) {
      if (getByPath(oldVal, key) !== getByPath(newVal, key)) {
        delete errors.value[key]
      }
    }
  }
)

function save() {
  errors.value = {}
  axios.patch(`/api/parks/${parkStore.selectedMarker.id}`, form.value)
  .then((res) => {
    Object.assign(parkStore.selectedMarker, res.data)
    editing.value = false
  })
  .catch((e) => {
    errors.value = e.response?.data?.errors || {}
  })
}

const shortCoordinates = computed(() => `
${parkStore.selectedMarker.coordinates[1].toFixed(5)}, 
${parkStore.selectedMarker.coordinates[0].toFixed(5)}
`)


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
      :title="parkStore.selectedMarker.name" :subtitle="`${parkStore.selectedMarker.area} гектарів`" :icon="parkStore.selectedMarker.icon?.file_path"
      :editable="authStore.can.upload"
      @onIconClick="() => { if(authStore.can.upload) startIconChange() }"
    >
      <template #right>
        <SecondaryButton class="ml-auto hidden md:block" @click="parkStore.selectedMarker = null">< Назад</SecondaryButton>
      </template>
    </PanelHeader>

    <ImageSlider :modelId="parkStore.selectedMarker?.id || null" model="parks" class="my-2" ref="imageSliderRef"
      :editable="authStore.can.upload"
      @onImageClick="() => { if(authStore.can.upload) startGalleryChange() }"
      @close="closeImagePicker"/>

    <template v-if="!editing">
      <div class="bg-white rounded px-4 py-2">
        <div class="space-y-2 my-1"
          v-if="!parkStore.isSingleParkView">
          <ArrowButton variant="primary" @click="openInfrastructure">Інфраструктура парку</ArrowButton>
          <ArrowButton variant="secondary" @click="openGreen">Насадження парку</ArrowButton>
          <ArrowButton variant="secondaryBlack" @click="openWorks" v-if="authStore.can.completeWork">Роботи парку</ArrowButton>
        </div>
      </div> 
      <div v-if="parkStore.selectedMarker?.description" class="text-gray-600 space-y-2 mt-2">
        <div class="bg-white rounded p-4 space-y-2">
          <h3 class="text-lg font-semibold">
            Про парк 
            <span class="cursor-pointer"
              v-if="authStore.can.edit" 
              @click="editing = true"
            >✏️</span>
          </h3>
          <p>{{ parkStore.selectedMarker.description }}</p>
        </div>
        <div class="bg-white rounded p-4 space-y-2 mt-2">
          <p v-if="parkStore.selectedMarker.address"><strong>Адресна прив'язка:</strong> {{ parkStore.selectedMarker.address }}</p>
          <p v-if="parkStore.selectedMarker.operator"><strong>Обслуговуюча комапнія:</strong> {{ parkStore.selectedMarker.operator }}</p>
        </div>
        <div
          class="bg-white rounded flex items-center justify-between text-gray-600 my-2 p-4"
        >
          <div>
            <strong class="font-bold">Координати:</strong>
            <div> {{ shortCoordinates }} </div>
          </div>
          <div class="relative group">
            <button
              @click="copyToClipboard(shortCoordinates)"
              @mouseenter="copyCompleted = false"
            >
              <img src="/img/icons/copy-icon.svg" alt="Скопіювати" class="w-5 h-5" />
            </button>
            <Tooltip>
              {{ copyCompleted ? 'Скопійовано!' : 'Скопіювати' }}
            </Tooltip>
        </div>
        </div>
      </div>
    </template>
    <div class="text-gray-600 bg-white rounded px-4 pt-2 pb-4"
      v-if="editing"
    >
      <div class="space-y-2">
        <div>
          <label class="text-sm font-medium text-gray-700">Назва парку</label>
          <input v-model="form.name" class="w-full border border-gray-300 rounded px-2 py-1" />
          <FormError :errors="errors['name']" />
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Площа</label>
          <input v-model="form.area" class="w-full border border-gray-300 rounded px-2 py-1" />
          <FormError :errors="errors['area']" />
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Адреса</label>
          <input v-model="form.address" class="w-full border border-gray-300 rounded px-2 py-1" />
          <FormError :errors="errors['address']" />
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Обслуговуюча комапнія</label>
          <input v-model="form.operator" class="w-full border border-gray-300 rounded px-2 py-1" />
          <FormError :errors="errors['operator']" />
        </div>
        <div>
          <label class="text-sm font-medium text-gray-700">Опис</label>
          <textarea v-model="form.description" class="w-full border border-gray-300 rounded px-2 py-1" rows="6" />
          <FormError :errors="errors['description']" />
        </div>
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
