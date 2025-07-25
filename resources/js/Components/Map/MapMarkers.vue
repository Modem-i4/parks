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
  mapMarkers.value.forEach(m => m.mapMarker.setMap(null))
  mapMarkers.value = []

  if (!parkStore.map) return
  const { AdvancedMarkerElement } = await loader.importLibrary('marker')

  for (const marker of parkStore.markers) {
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
      content
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
