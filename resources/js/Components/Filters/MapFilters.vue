<script setup>
import { ref, onMounted, watch } from 'vue'
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

const parkStore = useParkStore()
const filtersConfig = ref([])
const filters = ref({})
const renderKey = ref(0)

const filterPresets = {
  all: { green: {}, infrastructure: {} },
  nothing: {}
}

const showModal = ref({
  groupAssign: false
})

const getFilters = async () => {
  try {
    const response = await axios.get(`/api/markers/filters-config?mode=${parkStore.singleParkContentMode}`)
    filtersConfig.value = response.data
  } catch (error) {
    console.error('Помилка завантаження конфігурації фільтрів:', error)
  }
}

function setPreset(preset = 'all') {
  filters.value = structuredClone(filterPresets[preset])
  renderKey.value++
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
  }
}

watch(() => parkStore.singleParkContentMode,
 () => getFilters(),
 { immediate:true }
)

onMounted(() => {
  setPreset()
  filterMarkers()
})
</script>

<template>
  <div>
    <PanelHeader v-if="!isMobile" 
      :title="parkStore.selectedPark.name" :subtitle="parkStore.selectedPark.address" :icon="parkStore.selectedPark.icon?.file_path"
    >
      <template #right>
        <SecondaryButton class="ml-auto" @click="setParkView(parkStore, 'parks')">< Назад</SecondaryButton>
      </template>
    </PanelHeader>
    <div class="flex items-center justify-between border-b px-5 py-3 border-gray-200">
      <div class="text-lg font-medium text-gray-700">
        Фільтр
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
        <SecondaryButton size="sm"> <!-- TODO: export -->
          ⏬
        </SecondaryButton>
        <SecondaryButton size="sm" @click="showModal.groupAssign = true">
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
  </div>
</template>
