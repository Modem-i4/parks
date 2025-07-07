<script setup>
import { ref, onMounted, watch } from 'vue'
import loader from '@/Helpers/Maps/GoogleMapsLoader'
import MapMarkers from './MapMarkers.vue'
import MapPolygons from './MapPolygons.vue'
import { useParkStore } from '@/Stores/useParkStore.js'
import {
  defaultMapOptions,
  getMapRestrictions,
  getAdjustedCoords,
  getAdjustedCoordsFromMarker,
  tweenCameraTo,
  deviceZoom,
  defaultBounds
} from '@/Helpers/Maps/MapHelper.js'
import { isMobile } from '@/Helpers/isMobileHelper'

const parkStore = useParkStore()
const mapElement = ref(null)

onMounted(async () => {
  parkStore.setMapElement(mapElement.value)
  const { Map } = await loader.importLibrary('maps')

  parkStore.map = new Map(parkStore.mapElement, {
    ...defaultMapOptions,
    center: parkStore.defaultCenter,
    ...getMapRestrictions(parkStore.isSingleParkView)
  })
})

const isParkViewInTransit = ref(false)

// single park view transit
async function handleTransitToSingleParkView(isSingleParkView) {
  if (!parkStore.map) return
  isParkViewInTransit.value = true
  const targetZoom = isSingleParkView ? deviceZoom.value.singlePark : deviceZoom.value.default
  parkStore.map.setOptions({
    minZoom: null,
    maxZoom: null,
    restriction: {latLngBounds: defaultBounds}
  })
  const coords = getAdjustedCoordsFromMarker(parkStore.selectedPark, targetZoom)
  await tweenCameraTo(parkStore.map, coords, targetZoom, 2500)
  parkStore.map.setOptions({
    ...getMapRestrictions(isSingleParkView, parkStore.selectedPark),
  })
  isParkViewInTransit.value = false
}

watch(
  () => parkStore.isSingleParkView,
  (isSingleParkView) => handleTransitToSingleParkView(isSingleParkView),
  { immediate: true }
)
watch(
  () => parkStore.map,
  (map) => {
    if (parkStore.isSingleParkView && map) {
      handleTransitToSingleParkView(true)
    }
  }
)


// centres control
watch(
  () => [parkStore.map, parkStore.markers, parkStore.selectedMarker, parkStore.showPanel],
  async () => {
    if (!parkStore.map || isParkViewInTransit.value) return

    const defaultCenter = parkStore.defaultCenter
    let zoomLevel = deviceZoom.value.default
    let coords = null
    let duration = 1000

    if (parkStore.showPanel && parkStore.selectedMarker) {
      if (parkStore.isSingleParkView) {
        zoomLevel = parkStore.map.getZoom()
        duration = 200
      } else zoomLevel = deviceZoom.value.panelOpen
      
      coords = getAdjustedCoordsFromMarker(parkStore.selectedMarker, zoomLevel)
    } else if (!parkStore.isSingleParkView) {
      if(parkStore.showPanel)
        coords = getAdjustedCoords(defaultCenter, zoomLevel)
      else coords = defaultCenter
    }

    if (coords?.lat && coords?.lng) {
      await tweenCameraTo(parkStore.map, coords, zoomLevel, duration)
    }
  },
  { immediate: true }
)
</script>

<template>
  <div ref="mapElement" class="w-full h-full">
    <MapPolygons />
    <MapMarkers />
  </div>
</template>
