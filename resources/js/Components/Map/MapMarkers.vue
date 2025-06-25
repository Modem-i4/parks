<script setup>
import { ref, watch } from 'vue'
import loader from '@/Helpers/GoogleMapsLoader'
import { CreatePinIcon } from '@/Helpers/CreatePinIcon.js'

const props = defineProps({
  map: Object,
  parks: Array,
  selectedParkId: Number
})

const emit = defineEmits(['select'])
const markers = ref([])

async function renderMarkers() {
  markers.value.forEach(marker => marker.setMap(null))
  markers.value = []

  if (!props.map) return

  const { AdvancedMarkerElement } = await loader.importLibrary('marker')

  for (const park of props.parks) { 
    const feature = JSON.parse(park.geo_json)
    const [lng, lat] = feature.properties.center

    const pin = await CreatePinIcon({ 
      glyph: park.icon?.file_path
    })

    const marker = new AdvancedMarkerElement({
      map: props.map,
      position: { lat, lng },
      title: park.name,
      content: pin.element
    })

    marker.addListener('click', () => {
      emit('select', park)
    })
    markers.value.push({ marker, park })
  }
}

watch(
  () => props.map,
  (mapInstance) => {
    console.log('Map instance changed:', mapInstance)
    if (mapInstance) {
      renderMarkers()
    }
  },
  { immediate: true }
)

async function updateMarkerBackgrounds(newId, oldId) {
  const markersToUpdate = markers.value.filter(
    ({ park }) => park.id === newId || park.id === oldId
  )

  for (const { marker, park } of markersToUpdate) {
    const isSelected = park.id === newId
    const pin = await CreatePinIcon({
      glyph: park.icon?.file_path,
      background: isSelected ? '#FF0000' : '#4285F4'
    })
    marker.content = pin.element
  }
}

watch(
  () => props.selectedParkId,
  (newId, oldId) => {
    updateMarkerBackgrounds(newId, oldId)
  }
)
</script>

<template></template>