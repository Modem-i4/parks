<script setup>
import { watch } from 'vue'
import { useParkStore } from '@/Stores/useParkStore.js'

const parkStore = useParkStore()

function clearPolygons() {
  parkStore.map.data.forEach(feature => parkStore.map.data.remove(feature))
}

function addGeoJson(geoJson, properties = {}) {
  parkStore.map.data.addGeoJson(geoJson).forEach(feature => {
    Object.entries(properties).forEach(([key, value]) => {
      feature.setProperty(key, value)
    })
  })
}

function renderParkPolygons() {
  const markersToRender = parkStore.isSingleParkView
    ? [parkStore.selectedPark]
    : parkStore.markers

  markersToRender?.forEach(marker => {
    try {
      addGeoJson(marker.geo_json, {
        featureType: 'park',
        id: marker.id,
      })
    } catch (e) {
      console.warn(`Не вдалося обробити маркер ${marker.name}:`, e)
    }
  })
}

function renderPlotPolygons() {
  parkStore.selectedPark?.plots?.forEach(plot => {
    plot.subplots?.forEach(subplot => {
      addGeoJson(subplot.coordinates, {
        featureType: 'subplot',
        plotId: plot.id,
        subplotId: subplot.id,
      })
    })

    addGeoJson(plot.coordinates, {
      featureType: 'plot',
      plotId: plot.id,
    })
  })
}

function renderPolygons() {
  if (!parkStore.map) return

  clearPolygons()

  renderParkPolygons()

  if (parkStore.isSingleParkView) renderPlotPolygons()

  setMapStyle()
}

function setMapStyle() {
  parkStore.map.data.setStyle(feature => {
    const featureType = feature.getProperty('featureType')

    if (featureType === 'subplot') {
      return {
        fillColor: '#000000',
        fillOpacity: 0.2,
        strokeColor: '#000000',
        strokeOpacity: 0.9,
        strokeWeight: 1,
        clickable: false,
        zIndex: 1,
      }
    }

    if (featureType === 'plot') {
      return {
        fillOpacity: 0,
        strokeColor: '#000000',
        strokeOpacity: 1,
        strokeWeight: 3,
        clickable: false,
        zIndex: 2,
      }
    }

    const fillColor = !parkStore.isSingleParkView
      && feature.getProperty('id') === parkStore.selectedMarker?.id
      ? '#4285F4' : '#bae9c0'

    const fillOpacity = !parkStore.isSingleParkView
      ? feature.getProperty('id') === parkStore.selectedMarker?.id ? 0.8 : 0.6
      : 0.4

    return {
      fillColor,
      fillOpacity,
      strokeColor: '#333',
      strokeWeight: 1,
      clickable: true,
      zIndex: 0,
    }
  })
}

watch(() => [
  parkStore.map,
  parkStore.markers,
  parkStore.selectedMarker?.id,
  parkStore.selectedPark,
  parkStore.isSingleParkView,
], () => {
  if (parkStore.map) {
    renderPolygons()
  }
}, { immediate: true })

watch(() => parkStore.map, () => {
  if (parkStore.map) {
    parkStore.map.data.addListener('click', event => {
      if(parkStore.isSingleParkView) {
        parkStore.setSelectedMarker(null) // with validation
      }
      else {
        const id = event.feature.getProperty('id')
        const marker = parkStore.markers.find(p => p.id === id)
        if (marker) parkStore.selectedMarker = marker
      }
    })
  }
}, { immediate: true })
</script>

<template></template>
