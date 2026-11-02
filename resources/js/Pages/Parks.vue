<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';

import { useParkStore } from '@/Stores/useParkStore.js';

import MapWithPanel from '@/Components/Map/MapWithPanel.vue'
import ParkList from '@/Components/Parks/ParkList.vue';
import ParkDetails from '@/Components/Parks/ParkDetails.vue';
import MapFilters from '@/Components/Filters/MapFilters.vue';
import MarkerDetails from '@/Components/Markers/MarkerDetails.vue';
import Tabs from '@/Components/Custom/Tabs.vue';
import { zoom } from '@/Helpers/Maps/MapHelper';
import { initParkRouteWatcher, setViewToParkMarker } from '@/Helpers/Maps/SetParkView';
import { decodeReportFilters, clearReportFilterUrl } from '@/Helpers/Filters/ReportFilterLink'
import { Head, router, usePage } from '@inertiajs/vue3';

const props = defineProps({ 
  isSingleParkView: Boolean,
  selectedPark: Object,
  selectedMarker: Object,
})
const parkStore = useParkStore()
parkStore.$reset()

parkStore.selectedPark = props.selectedPark;

const reportUrl = new URL(usePage().url, window.location.origin)
const reportSnapshot = decodeReportFilters(reportUrl.searchParams.get('rf'))
parkStore.isSingleParkView = props.isSingleParkView || !!(reportSnapshot && props.selectedPark);
if (reportSnapshot) {
  parkStore.savedMarkerFilters = reportSnapshot.filters
  parkStore.singleParkContentMode = reportSnapshot.mode
}

// Inertia writes the initial URL asynchronously after setup/mount. Clean its
// page state after navigation so a later history write cannot restore `rf`.
const stopReportUrlCleanup = router.on('navigate', event => {
  if (event.detail.page.component !== 'Parks') return
  clearReportFilterUrl(event.detail.page.url, window.location.origin, router,
    reportSnapshot && parkStore.isSingleParkView ? parkStore.selectedPark?.id : null)
})
onUnmounted(stopReportUrlCleanup)

onMounted(() => {
  if(parkStore.isSingleParkView) {
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
      if(parkStore.isSingleParkView) {
        const coords = parkStore.selectedPark.coordinates
        parkStore.map.setCenter( { lat: coords[0], lng: coords[1] })
        parkStore.map.setZoom(zoom.singlePark.threshold)
      }
  }
)

const activeGeneralTab = ref(reportSnapshot ? 'filters' : 'parks')
watch(
  () => parkStore.selectedMarker,
  val => {
    if(parkStore.isSingleParkView) return
    if (val) activeGeneralTab.value = 'parks'
  }
)
watch(
  () => parkStore.isSingleParkView,
  val => {
    if(!val && parkStore.savedMarkerFilters)  activeGeneralTab.value = 'filters'
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
