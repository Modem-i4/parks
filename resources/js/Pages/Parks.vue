<script setup>
import { onMounted, watch } from 'vue';
import axios from 'axios';

import { useParkStore } from '@/Stores/useParkStore.js';

import ResolveLayout from '@/Helpers/ResolveLayout.js';
import MapWithPanel from '@/Components/Map/MapWithPanel.vue'
import ParkList from '@/Components/Map/ParkList.vue';
import ParkDetails from '@/Components/Map/ParkDetails.vue';
import MapFilters from '@/Components/Filters/MapFilters.vue';
import MarkerDetails from '@/Components/Markers/MarkerDetails.vue';

defineOptions({
  layout: ResolveLayout,
});

const props = defineProps({ 
  isSingleParkView: Boolean,
  selectedMarker: Object
})

const parkStore = useParkStore()
parkStore.$reset()

parkStore.isSingleParkView = props.isSingleParkView;
parkStore.selectedPark = props.selectedMarker;

function getParks() {
  axios.get(`/api/parks`)
    .then(response => {
      parkStore.markers = response.data;
    })
    .catch(error => {
      console.error('Error fetching markers:', error);
    });
}

watch(
  () => [parkStore.isSingleParkView],
  () => {
    if(!parkStore.isSingleParkView)
      getParks()
  },
  { immediate: true }
)
</script>

<template>
  <MapWithPanel>
    <template #panelContent>
      <template v-if="!parkStore.isSingleParkView">
        <ParkList v-if="!parkStore.selectedMarker"/>
        <ParkDetails v-else />
      </template>
      <template v-if="parkStore.isSingleParkView">
        <MapFilters v-show="!parkStore.selectedMarker"/>
        <MarkerDetails v-if="parkStore.selectedMarker" />
      </template>
    </template>
  </MapWithPanel>
</template>
