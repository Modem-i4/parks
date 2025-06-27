<script setup>
import { ref, onMounted, watch } from 'vue'
import loader from '@/Helpers/GoogleMapsLoader'
import MapMarkers from './MapMarkers.vue'
import MapPolygons from './MapPolygons.vue'
import getCoordsFromMarker from '@/Helpers/GetCoordsFromMarker'
import { useParkStore } from '@/Stores/useParkStore.js'

const parkStore = useParkStore()

const mapElement = ref(null)

function calculateZoom(width) {
  if (width >= 1200) return 14
  if (width >= 768) return 13.4
  return 12.6
}

onMounted(async () => {
  const { Map } = await loader.importLibrary('maps')

  parkStore.map =new Map(mapElement.value, {
    mapId: import.meta.env.VITE_GOOGLE_MAP_ID,
    center: parkStore.defaultCenter,
    zoom: calculateZoom(window.innerWidth),
    draggable: false,
    zoomControl: false,
    scrollwheel: false,
    disableDoubleClickZoom: true,
    streetViewControl: false,
    mapTypeControl: false,
    fullscreenControl: false,
    gestureHandling: 'none',
    clickableIcons: false,
    rotateControl: false,
    cameraControl: false
  })
})

watch(
  () => [parkStore.map, parkStore.markers, parkStore.selectedMarker?.id, parkStore.showPanel],
  () => {
    if (!parkStore.map) return

    const isMobile = window.innerWidth <= 768
    const adjustment = isMobile && parkStore.showPanel ? -0.06 : 0

    const selectedId = parkStore.selectedMarker?.id
    if (!selectedId) {
      const adjustedLat = parkStore.defaultCenter.lat + adjustment
      parkStore.map.panTo({ lat: adjustedLat, lng: parkStore.defaultCenter.lng })
      return
    }

    const marker = parkStore.markers.find(p => p.id === selectedId)
    if (!marker) return

    const [lng, lat] = getCoordsFromMarker(marker)

    if (lat && lng) {
      const adjustedLat = lat + adjustment
      parkStore.map.panTo({ lat: adjustedLat, lng })
    }
  },
  { immediate: true }
)

watch(
  () => [parkStore.isSingleParkView, parkStore.map],
  () => {
    if (!parkStore.map) return

    if (parkStore.isSingleParkView) {
      smoothZoom(parkStore.map, 16)
    } else {
      smoothZoom(parkStore.map, 12)
    }
  },
  { immediate: true }
)

async function smoothZoom(map, targetZoom, step = 1, delay = 10) {
  const currentZoom = map.getZoom()
  if (currentZoom === targetZoom) return

  const delta = targetZoom > currentZoom ? step : -step
  let zoom = currentZoom

  while (zoom !== targetZoom) {
    zoom += delta

    if ((delta > 0 && zoom > targetZoom) || (delta < 0 && zoom < targetZoom)) {
      zoom = targetZoom
    }

    map.setZoom(zoom)
    await new Promise(r => setTimeout(r, delay))
  }
}
</script>

<template>
  <div ref="mapElement" class="w-full h-full">
    <MapPolygons/>
    <MapMarkers/>
  </div>
</template>
