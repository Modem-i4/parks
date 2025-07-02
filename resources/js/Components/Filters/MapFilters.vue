<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import FilterNode from './FilterNode.vue'
import { useParkStore } from '@/Stores/useParkStore'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import { isMobile } from '@/Helpers/isMobileHelper'
import ParkHeader from '@/Components/Custom/ParkHeader.vue'
import { setParkView } from '@/Helpers/SetParkView'

const parkStore = useParkStore()
const filtersConfig = ref([])
const filters = ref({})
const renderKey = ref(0)

const filterPresets = {
  all: { green: {}, infrastructure: {} },
  nothing: {}
}

const getFilters = async () => {
  try {
    const response = await axios.get('/api/markers/filters-config')
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
  try {
    const response = await axios.post(`/api/parks/${parkStore.selectedPark.id}/markers`, {
      filters: filters.value
    })
    parkStore.markers = response.data
  } catch (error) {
    console.error('Помилка фільтрації парків:', error)
  }
}

onMounted(() => {
  getFilters()
  setPreset()
  filterMarkers()
})
</script>

<template>
  <ParkHeader v-if="!isMobile" :park="parkStore.selectedPark">
    <template #right>
      <SecondaryButton class="ml-auto" @click="setParkView(parkStore, 'parks')">< Назад</SecondaryButton>
    </template>
  </ParkHeader>
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
    <PrimaryButton @click="filterMarkers" >Застосувати фільтри</PrimaryButton>
  </div>
</template>
