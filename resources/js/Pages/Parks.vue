<script setup>
import { onMounted, ref, watch } from 'vue';

import { useParkStore } from '@/Stores/useParkStore.js';

import MapWithPanel from '@/Components/Map/MapWithPanel.vue'
import ParkList from '@/Components/Parks/ParkList.vue';
import ParkDetails from '@/Components/Parks/ParkDetails.vue';
import MapFilters from '@/Components/Filters/MapFilters.vue';
import MarkerDetails from '@/Components/Markers/MarkerDetails.vue';
import Tabs from '@/Components/Custom/Tabs.vue';
import { zoom } from '@/Helpers/Maps/MapHelper';
import { initParkRouteWatcher, setViewToParkMarker } from '@/Helpers/Maps/SetParkView';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ 
  isSingleParkView: Boolean,
  selectedPark: Object,
  selectedMarker: Object,
})
const parkStore = useParkStore()
parkStore.$reset()

parkStore.selectedPark = props.selectedPark;

parkStore.isSingleParkView = props.isSingleParkView;

onMounted(() => {
  if(props.isSingleParkView) {
    if(props.selectedMarker) {
      setViewToParkMarker(parkStore, props.selectedMarker)
    }
  } else {
    if(props.selectedPark) {
      parkStore.showPanel = true
      parkStore.selectedMarker = props.selectedPark
    }
  }
  initParkRouteWatcher(parkStore)
})

watch( 
  () => [parkStore.map], 
  () => {
      if(props.isSingleParkView) {
        const coords = parkStore.selectedPark.coordinates
        parkStore.map.setCenter( { lat: coords[0], lng: coords[1] })
        parkStore.map.setZoom(zoom.singlePark.threshold)
      }
  }
)

const activeGeneralTab = ref('parks')
watch(
  () => parkStore.selectedMarker,
  val => {
    if(parkStore.isSingleParkView) return
    if (val) activeGeneralTab.value = 'parks'
  }
)
</script>

<template>
  <Head title="Парки" />
  <MapWithPanel>
    <template #panelContent>
      <template v-if="!parkStore.isSingleParkView">
        <Tabs v-model="activeGeneralTab" :tabs="[
            { key: 'parks', label: 'Парки' },
            { key: 'filters', label: 'Фільтри' }
        ]">
          <template #parks>
            <ParkList v-show="!parkStore.selectedMarker"/>
            <ParkDetails v-if="parkStore.selectedMarker"/>
          </template>
          <template #filters>
            <MapFilters />
          </template>
        </Tabs>
      </template>
      <template v-if="parkStore.isSingleParkView">
        <MapFilters v-show="!parkStore.selectedMarker" />
        <MarkerDetails v-if="parkStore.selectedMarker" />
      </template>
    </template>
  </MapWithPanel>
</template>
