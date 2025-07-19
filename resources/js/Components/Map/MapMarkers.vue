<script setup>
import { ref, watch } from 'vue'
import loader from '@/Helpers/Maps/GoogleMapsLoader'
import { CreatePinIcon } from '@/Helpers/Maps/CreatePinIcon.js'
import { getCoordsFromMarker } from '@/Helpers/Maps/MapHelper.js'
import { useParkStore } from '@/Stores/useParkStore.js'
import { CreateSimpleIcon, getColorByQualityState } from '@/Helpers/Maps/CreateSimpleIcon'

const parkStore = useParkStore()
const mapMarkers = ref([])
const renderCancelled = ref(false)

async function renderMarkers() {
  renderCancelled.value = false
  if (!parkStore.map) return
  const keyOf = m => `${m.id}_${m.type}`
  const currentKeys = new Set(mapMarkers.value.map(m => keyOf(m.marker)))
  const newKeys = new Set(parkStore.markers.map(m => keyOf(m)))
  for (const m of mapMarkers.value) {
    if (!newKeys.has(keyOf(m.marker))) {
      m.mapMarker.setMap(null)
    }
  }
  mapMarkers.value = mapMarkers.value.filter(m => newKeys.has(keyOf(m.marker)))
  const newMarkers = parkStore.markers.filter(m => !currentKeys.has(keyOf(m)))
  addNewMarkers(newMarkers)
}

async function addNewMarkers(newMarkers) {
  const { AdvancedMarkerElement } = await loader.importLibrary('marker')
  for (const marker of newMarkers) {
    const {lng, lat} = getCoordsFromMarker(marker)

    let content
    if (marker.green) { // green markers
      content = await CreateSimpleIcon({ 
        iconPath: `/storage/img/icons/${marker.type || 'tree'}-map_icon.svg`, 
        fill: getColorByQualityState(marker.green?.quality_state) 
      })
    } else { // parks & infrastructure markers
      content = await CreatePinIcon({ glyph: marker.icon?.file_path })
    }
    if(renderCancelled.value) return
    const mapMarker = new AdvancedMarkerElement({
      map: parkStore.map,
      position: { lat, lng },
      title: marker.name,
      content,
      zIndex: Math.round(-lat * 1e6)
    })

    mapMarker.addListener('click', () => {
      parkStore.selectedMarker = marker
    })

    mapMarkers.value.push({ mapMarker, marker })
    await new Promise(r => setTimeout(r, 0))
  }
}

watch(
  () => [parkStore.map, parkStore.markers],
  () => { renderMarkers() },
  { immediate: true }
)

watch(
  () => [parkStore.isSingleParkView],
  () => { renderCancelled.value = true },
  { immediate: true }
)

async function updateMarkerBackgrounds(newId, oldId) {
  const mapMarkersToUpdate = mapMarkers.value.filter(
    ({ marker }) => marker.id === newId || marker.id === oldId
  )

  for (const { mapMarker, marker } of mapMarkersToUpdate) {
    const isSelected = marker.id === newId
    if(marker.green) {
      const highlightClasses = [
        'scale-[3]',
        'transition-transform']
      isSelected
        ? mapMarker.content.classList.add(...highlightClasses)
        : mapMarker.content.classList.remove(...highlightClasses)
    }
    else {
      mapMarker.content = await CreatePinIcon({
        glyph: marker.icon?.file_path,
        background: isSelected ? '#FF0000' : '#4285F4'
      })
    }
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
