<script setup>
import { ref, onMounted, watch } from 'vue'
import loader from '@/Helpers/GoogleMapsLoader'
import MapMarkers from './MapMarkers.vue'
import MapPolygons from './MapPolygons.vue'
import { useParkStore } from '@/Stores/useParkStore.js'
import { defaultMapOptions, getMapRestrictions, smoothZoomToPark, getCoordsFromMarker, getAdjustedCoords, getAdjustedCoordsFromMarker } from '@/Helpers/MapHelper.js'

const parkStore = useParkStore()
const mapElement = ref(null)
const lockMapChange = ref(false)

onMounted(async () => {
  parkStore.setMapElement(mapElement.value)
  const { Map } = await loader.importLibrary('maps')

  parkStore.map = new Map(parkStore.mapElement, {
    ...defaultMapOptions,
    center: parkStore.defaultCenter,
    ...getMapRestrictions(parkStore.isSingleParkView)
  })
})

watch(
  () => [parkStore.map, parkStore.markers, parkStore.selectedMarker, parkStore.showPanel],
  () => {
    if (!parkStore.map || lockMapChange.value) return
    
    const defaultCenterArray = [parkStore.defaultCenter.lng, parkStore.defaultCenter.lat]
    const [lng, lat] = parkStore.showPanel && parkStore.selectedMarker
      ? getAdjustedCoordsFromMarker(parkStore.map, parkStore.selectedMarker) 
      : parkStore.showPanel
      ? getAdjustedCoords(parkStore.map, defaultCenterArray)
      : defaultCenterArray

    if(lat && lng)
      parkStore.map.panTo({ lat, lng })
    
  },
  { immediate: true }
)

async function handleTransitToSingleParkView(isSingleParkView) {
  console.warn('a')
  if (!isSingleParkView || !parkStore.map || !parkStore.selectedMarker) return
  console.warn('b')
  lockMapChange.value = true
  await smoothZoomToPark(parkStore.map, isSingleParkView, parkStore.selectedMarker)
  lockMapChange.value = false
}

watch(
  () => [parkStore.isSingleParkView, parkStore.map],
  ([isSingleParkView]) => handleTransitToSingleParkView(isSingleParkView),
  { immediate: true }
)

</script>

<template>
  <div ref="mapElement" class="w-full h-full">
    <MapPolygons/>
    <MapMarkers/>
  </div>
</template>
