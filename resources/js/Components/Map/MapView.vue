<script setup>
import { ref, onMounted, watch } from 'vue'
import loader from '@/Helpers/GoogleMapsLoader'
import MapMarkers from './MapMarkers.vue'
import MapPolygons from './MapPolygons.vue'
import { useParkStore } from '@/Stores/useParkStore.js'
import { defaultMapOptions, getMapRestrictions, smoothZoomToPark, getAdjustedCoords, getAdjustedCoordsFromMarker } from '@/Helpers/MapHelper.js'

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

async function handleTransitToSingleParkView(isSingleParkView) {
  if (!parkStore.map) return
  parkStore.lockMapChange = true
  await smoothZoomToPark(parkStore.map, isSingleParkView, parkStore.selectedMarker)
  parkStore.lockMapChange = false
}

watch(
  () => [parkStore.isSingleParkView, parkStore.map],
  ([isSingleParkView]) => handleTransitToSingleParkView(isSingleParkView),
  { immediate: true }
)


watch(
  () => [parkStore.map, parkStore.markers, parkStore.selectedMarker, parkStore.showPanel],
  () => {
    if (!parkStore.map || parkStore.lockMapChange) return
    
    const defaultCenterArray = [parkStore.defaultCenter.lng, parkStore.defaultCenter.lat]

    const [lng, lat] = parkStore.showPanel && parkStore.selectedMarker
      ? getAdjustedCoordsFromMarker(parkStore.map, parkStore.selectedMarker) 
      : parkStore.isSingleParkView
      ? getAdjustedCoordsFromMarker(parkStore.map, parkStore.selectedPark) 
      : parkStore.showPanel
      ? getAdjustedCoords(parkStore.map, defaultCenterArray)
      : defaultCenterArray

    if(lat && lng)
      parkStore.map.panTo({ lat, lng })
    
  },
  { immediate: true }
)

</script>

<template>
  <div ref="mapElement" class="w-full h-full">
    <MapPolygons/>
    <MapMarkers/>
  </div>
</template>
