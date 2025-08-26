<script setup>
import { useParkStore } from '@/Stores/useParkStore.js'
import { computed, onMounted, ref, watch } from 'vue'
import axios from 'axios'
import BtnWhite from '@/Components/Custom/BtnWhite.vue'
import MarkerDetailsView from './View/MarkerDetailsView.vue'
import MarkerDetailsEdit from './Edit/MarkerDetailsEdit.vue';
import PanelHeader from '../Custom/PanelHeader.vue';
import GreenStateIndicator from './View/GreenStateIndicator.vue';
import MediaPickerModal from '../Media/MediaPickerModal.vue';
import { getMarkerTitle } from '@/Helpers/Maps/GetMarkerTitle';

import Modal from '@/Components/Default/Modal.vue'
import GroupAssign from '@/Components/WorkHistory/GroupAssign.vue'
import { useAuthStore } from '@/Stores/useAuthStore'

const parkStore = useParkStore()
const marker = ref(null)
const loading = ref(true)
const isAddingNew = computed(() => !!parkStore.selectedMarker?.isDraft)
const editing = ref(false)
watch(() => isAddingNew.value, (newVal) => {
  editing.value = newVal
}, { immediate: true })

const authStore = useAuthStore()
const editRef = ref(null)
const viewRef = ref(null)

const showModal = ref({
  groupAssign: false
})

function back() {
  parkStore.selectedMarker = null
}

watch(
  () => parkStore.selectedMarker,
  update,
  { immediate: true, deep: true }
)

async function update(newVal) {
  marker.value = newVal
  if (!newVal || newVal.isDraft) return
  loading.value = true
  try {
    const { data } = await axios.get(`/api/markers/${marker.value.id}`)
    marker.value = data
  } catch (e) {
    console.error('Не вдалося довантажити маркер:', e)
  } finally {
    loading.value = false
  }
}

watch(editing, (newVal) => {
  parkStore.selectedMarkerLocked = newVal
})

const title = computed(
  () => {
    if(isAddingNew.value) {
      return 'Додавання маркера'
    }
    return getMarkerTitle(marker.value)
  }
)

const description = computed(() => {
  if(isAddingNew.value) 
    return 'Введіть властивості нижче'
  if(loading.value) 
    return 'Завантаження...'
  if(marker.value.green && marker.value.green?.species?.genus?.name_ukr)
    return `${marker.value.green?.species?.genus?.name_ukr} / ${marker.value.green?.species?.genus?.family?.name_ukr}`
  if(marker.value.infrastructure) 
    return 'Інфраструктура'
  return ''
})

// Image pickers
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
    viewRef.value.forceImageUpdate(newImages)
  }
  if(pickerType.value === 'icon') {
    parkStore.selectedMarker.icon = newImages[0]
  }
  closeImagePicker()
}

// Delete marker
function deleteMarker() {
  axios.delete(`/api/markers/${marker.value.id}`)
    .then(() => {parkStore.selectedMarker.deleted = true})
}
</script>

<template>
  <div class="p-4 pt-1 md:pt-4 overflow-x-hidden" v-if="marker">
    <button @click="back" class="text-blue-500 mb-2 md:hidden" v-if="!parkStore.selectedMarkerLocked">← Назад</button>
    <PanelHeader
      :title="title"
      :subtitle="description" 
      :icon="marker.icon?.file_path"
      @onIconClick="() => { if(authStore.can.upload && !editing) startIconChange() }"
      :editable="authStore.can.edit && !editing"
      >
      <template #right>
        <GreenStateIndicator :green="marker.green" />
      </template>
    </PanelHeader>

    <template v-if="!editing">
      <MarkerDetailsView :marker="marker" ref="viewRef" 
        @onImageClick="() => { if(authStore.can.edit) startGalleryChange() }" 
        @deleteMarker="deleteMarker"
      />
        <div class="absolute right-[4.5rem] z-[3]">
          <div class="fixed bottom-2">
          <BtnWhite
            @click="showModal.groupAssign = true"
            class="p-[0.7rem] ms-auto"
            v-if="authStore.can.assignWork"
          >👷</BtnWhite>
          <BtnWhite v-if="authStore.can.edit"
            @click="editing = true"
          >✏️</BtnWhite>
        </div>
      </div>
    </template>
    <template v-if="editing">
      <MarkerDetailsEdit :marker="marker" ref="editRef" />
      <div class="absolute right-[4.5rem] z-[3]">
        <div class="fixed bottom-2">
          <BtnWhite
            @click="editing = false"
            class="p-[0.7rem] ms-auto"
          >❌</BtnWhite>
          <BtnWhite
            @click="() => { editRef.save(); editing = false }"
          >✔️</BtnWhite>
        </div>
      </div>
    </template>
  </div>
  <MediaPickerModal
    v-if="showPicker"
    :type="pickerType"
    modelType="App\Models\Marker"
    :modelId="marker.id"
    @close="closeImagePicker"
    @saved="pickerSaved"
  />
  <Modal :show="showModal.groupAssign" maxWidth="xl" @close="showModal.groupAssign = false">
    <GroupAssign
      @close="showModal.groupAssign = false"
      assignMode="picked"
      @update="update(parkStore.selectedMarker)"
    />
  </Modal>
</template>
