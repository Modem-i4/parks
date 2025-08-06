<script setup>
import { onMounted, ref, watch } from 'vue';

import { useParkStore } from '@/Stores/useParkStore.js';

import ResolveLayout from '@/Helpers/ResolveLayout.js';
import MapWithPanel from '@/Components/Map/MapWithPanel.vue'
import ParkList from '@/Components/Map/ParkList.vue';
import ParkDetails from '@/Components/Map/ParkDetails.vue';
import MapFilters from '@/Components/Filters/MapFilters.vue';
import MarkerDetails from '@/Components/Markers/MarkerDetails.vue';
import { zoom } from '@/Helpers/Maps/MapHelper';
import { setViewToParkMarker } from '@/Helpers/Maps/SetParkView';

defineOptions({
  layout: ResolveLayout,
});

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
  if(props.selectedMarker) {
    setViewToParkMarker(parkStore, props.selectedMarker)
  }
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
</script>

<template>
  <MapWithPanel>
    <template #panelContent>
      <template v-if="!parkStore.isSingleParkView">
        <ParkList v-show="!parkStore.selectedMarker"/>
        <ParkDetails v-if="parkStore.selectedMarker"/>
      </template>
      <template v-if="parkStore.isSingleParkView">
        <MapFilters v-show="!parkStore.selectedMarker" />
        <MarkerDetails v-if="parkStore.selectedMarker" />
      </template>
    </template>
  </MapWithPanel>
</template>
