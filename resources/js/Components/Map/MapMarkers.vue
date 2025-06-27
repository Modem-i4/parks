<script setup>
import { ref, watch } from 'vue'
import loader from '@/Helpers/GoogleMapsLoader'
import { CreatePinIcon } from '@/Helpers/CreatePinIcon.js'
import getCoordsFromMarker from '@/Helpers/GetCoordsFromMarker'
import { useParkStore } from '@/Stores/useParkStore.js'

const parkStore = useParkStore()
const mapMarkers = ref([])

async function renderMarkers() {
  mapMarkers.value.forEach(m => m.mapMarker.setMap(null))
  mapMarkers.value = []

  if (!parkStore.map) return

  const { AdvancedMarkerElement } = await loader.importLibrary('marker')

  for (const marker of parkStore.markers) {
    const [lng, lat] = getCoordsFromMarker(marker)

    const pin = await CreatePinIcon({
      glyph: marker.icon?.file_path
    })

    const mapMarker = new AdvancedMarkerElement({
      map: parkStore.map,
      position: { lat, lng },
      title: marker.name,
      content: pin.element
    })

    mapMarker.addListener('click', () => {
      parkStore.selectedMarker = marker
    })
    mapMarkers.value.push({ mapMarker, marker })
  }
}

watch(
  () => [parkStore.map, parkStore.markers],
  () => { 
    renderMarkers()
  },
  { immediate: true }
)

async function updateMarkerBackgrounds(newId, oldId) {
  const mapMarkersToUpdate = mapMarkers.value.filter(
    ({ marker }) => marker.id === newId || marker.id === oldId
  )

  for (const { mapMarker, marker } of mapMarkersToUpdate) {
    const isSelected = marker.id === newId
    const pin = await CreatePinIcon({
      glyph: marker.icon?.file_path,
      background: isSelected ? '#FF0000' : '#4285F4'
    })
    mapMarker.content = pin.element
  }
}

watch(
  () => parkStore.selectedMarker?.id,
  (newId, oldId) => {
    updateMarkerBackgrounds(newId, oldId)
  }
)
</script>

<template></template>
