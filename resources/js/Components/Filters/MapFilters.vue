<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import axios from 'axios'
import FilterNode from './FilterNode.vue'
import { useParkStore } from '@/Stores/useParkStore'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import { isMobile } from '@/Helpers/isMobileHelper'
import PanelHeader from '@/Components/Custom/PanelHeader.vue'
import { setParkView } from '@/Helpers/Maps/SetParkView'

import Modal from '@/Components/Default/Modal.vue'
import GroupAssign from '@/Components/WorkHistory/GroupAssign.vue'
import ExportImportPanel from '@/Components/Export/ExportImportPanel.vue'
import { useAuthStore } from '@/Stores/useAuthStore'
import { tweenCameraTo } from '@/Helpers/Maps/MapHelper'

const parkStore = useParkStore()
const filtersConfig = ref([])
const filters = ref({})
const renderKey = ref(0)
const authStore = useAuthStore()

const filterPresets = {
  green: { green: {}, infrastructure: {} },
  infrastructure: { infrastructure: {} },
  works: { green: { works: { completion: ["uncompleted"] } } },
  nothing: {}
}

const showModal = ref({
  groupAssign: false,
  export: false
})

const areFiltersDefault = computed(
  () => Object.values(filterPresets)
  .some( preset => JSON.stringify(preset) === JSON.stringify(filters.value) )
)

const getFilters = async () => {
  try {
    const response = await axios.get(`/api/markers/filters-config?mode=${parkStore.singleParkContentMode}`)
    filtersConfig.value = response.data
  } catch (error) {
    console.error('Помилка завантаження конфігурації фільтрів:', error)
  }
}

function setPreset(preset = 'all') {
  const filter = preset === 'all' ? parkStore.singleParkContentMode : preset
  filters.value = structuredClone(filterPresets[filter])
  renderKey.value++
  filterMarkers()
}

const filterMarkers = async () => {
  parkStore.markerStates.isLoading = true
  try {
    const response = await axios.post(`/api/parks/${parkStore.selectedPark.id}/markers`, {
      filters: filters.value
    })
    parkStore.markers = response.data
  } catch (error) {
    console.error('Помилка фільтрації парків:', error)
  } finally {
    parkStore.markerStates.isLoading = false
    parkStore.markerStates.areLoaded = true

    if (parkStore.markers.length > 0 && parkStore.markers.length < 200 && !areFiltersDefault.value) {
      const c = parkStore.map.getCenter()
      const cLat = c.lat()
      const cLng = c.lng()
      const closestMarker = parkStore.markers
        .map(m => {
          const pos = {lng: m.coordinates[0], lat: m.coordinates[1] }
          const dLat = pos.lat - cLat
          const dLng = (pos.lng - cLng) * Math.cos(cLat * Math.PI / 180)
          return { ...pos, dist: Math.pow(dLat,2) + Math.pow(dLng,2) }
        })
        .sort((a, b) => a.dist - b.dist)[0]

        parkStore.showPanel = false
        tweenCameraTo(parkStore.map, { lat: closestMarker.lat, lng: closestMarker.lng })
    }

  }
}

watch(() => parkStore.singleParkContentMode,
 () => getFilters(),
 { immediate:true }
)

watch(() => filters,
  () => { if (!('green' in filters.value)) filterMarkers() },
  { deep:true }
)

onMounted(() => {
  setPreset()
})
</script>

<template>
  <div>
    <PanelHeader v-if="!isMobile" 
      :title="parkStore.selectedPark.name" :subtitle="`${parkStore.selectedPark.area} га`" :icon="parkStore.selectedPark.icon?.file_path"
    >
      <template #right>
        <SecondaryButton class="ml-auto" @click="setParkView(parkStore, 'parks')">← парки</SecondaryButton>
      </template>
    </PanelHeader>
    <div class="flex items-center justify-between border-b px-5 py-3 border-gray-200">
      <div class="text-lg font-medium text-gray-700">
        Фільтри та легенда
      </div>
      <div class="flex items-center gap-2">
        <PrimaryButton @click="setPreset('all')">Все</PrimaryButton>
        <SecondaryButton @click="setPreset('nothing')">Нічого</SecondaryButton>
      </div>
    </div>

    <div class="p-4 space-y-2"> 
      <FilterNode
        v-for="item in filtersConfig"
        :key="item.slug"
        :node="item"
        :filters="filters"
        :path="[]"
        :renderKey
      />
    </div>
    <div class="sticky bottom-0 p-4 pt-1 bg-white md:bg-[#f3f4f6]">
      <div class="italic text-center">записів: {{ parkStore.markers.length }}</div>
      <div class="flex space-x-1">
        <PrimaryButton @click="filterMarkers" class="flex flex-1">
          Застосувати фільтри
        </PrimaryButton>
        <SecondaryButton size="sm" @click="showModal.export = true" v-if="authStore.can.view">
          ⏬
        </SecondaryButton>
        <SecondaryButton size="sm" @click="showModal.groupAssign = true" v-if="authStore.can.assignWork">
          👷
        </SecondaryButton>
      </div>
    </div>
    <Modal :show="showModal.groupAssign" maxWidth="xl" @close="showModal.groupAssign = false">
      <GroupAssign
        @close="showModal.groupAssign = false"
        assignMode="filtered"
      />
    </Modal>
    <Modal :show="showModal.export" maxWidth="xl" @close="showModal.export = false">
      <ExportImportPanel
        @close="showModal.export = false"
        @update="filterMarkers"
      />
    </Modal>
  </div>
</template>
