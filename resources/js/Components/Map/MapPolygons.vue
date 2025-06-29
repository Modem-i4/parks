<script setup>
import { onMounted, watch } from 'vue'
import { useParkStore } from '@/Stores/useParkStore.js'

const parkStore = useParkStore()

function renderPolygons() {
  if (!parkStore.map) return

  parkStore.map.data.forEach(feature => parkStore.map.data.remove(feature))

  parkStore.markers?.forEach(marker => {
    try {
      parkStore.map.data.addGeoJson(marker.geo_json).forEach(f => {
        f.setProperty('id', marker.id)
      })
    } catch (e) {
      console.warn(`Не вдалося обробити маркер ${marker.name}:`, e)
    }
  })

  setMapStyle()
}

function setMapStyle() {
  parkStore.map.data.setStyle(feature => ({
    fillColor: feature.getProperty('id') === parkStore.selectedMarker?.id ? '#4285F4' : '#9CCC65',
    strokeColor: '#333',
    strokeWeight: 1,
    fillOpacity: feature.getProperty('id') === parkStore.selectedMarker?.id ? 0.8 : 0.6,
    clickable: true,
  }))
}

watch(() => [parkStore.map, parkStore.markers, parkStore.selectedMarker?.id], () => {
  if (parkStore.map) {
    renderPolygons()
  }
}, { immediate: true })

watch(() => parkStore.map, () => {
  if (parkStore.map) {
    parkStore.map.data.addListener('click', event => {
      const id = event.feature.getProperty('id')
      const marker = parkStore.markers.find(p => p.id === id)
      if (marker) parkStore.selectedMarker = marker
    })
  }
}, { immediate: true })
</script>

<template></template>
