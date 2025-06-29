<script setup>
import MapWithPanel from '@/Components/Map/MapWithPanel.vue'
import ParkList from '@/Components/Map/ParkList.vue'
import ParkDetails from '@/Components/Map/ParkDetails.vue'
import MapView from '@/Components/Map/MapView.vue'
import ResolveLayout from '@/Helpers/ResolveLayout.js';
import { onMounted, watch } from 'vue';
import axios from 'axios';
import { useParkStore } from '@/Stores/useParkStore.js';

defineOptions({
  layout: ResolveLayout,
});

const props = defineProps({ 
  isSingleParkView: Boolean,
  selectedMarker: Object
})

const parkStore = useParkStore()

parkStore.isSingleParkView = props.isSingleParkView;
parkStore.selectedMarker = props.selectedMarker;
parkStore.selectedPark = props.selectedMarker;

function getMarkers($type = null) {
  const markersSource = parkStore.isSingleParkView ? `/api/parks/${parkStore.selectedMarker.id}/markers` : `/api/parks`

  axios.get(markersSource)
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
    getMarkers()
  },
  { immediate: true }
)
</script>

<template>
  <MapWithPanel>
    <template #sidebar>
      <template v-if="!parkStore.isSingleParkView">
        <ParkList
          v-if="!parkStore.selectedMarker"
        />
      </template>
      <ParkDetails
        v-if="parkStore.selectedMarker"
      />
      <template v-if="parkStore.isSingleParkView">
        <!-- Filters -->
      </template>
    </template>

    <template #map>
      <MapView/>
    </template>

    <template #panel>
      <ParkDetails
        v-if="parkStore.selectedMarker"
      />
      <template v-if="!parkStore.isSingleParkView">
        <ParkList
          v-if="!parkStore.selectedMarker"
        />
      </template>
      <template v-else>
        <!-- Filters -->
      </template>
    </template>
  </MapWithPanel>
</template>
