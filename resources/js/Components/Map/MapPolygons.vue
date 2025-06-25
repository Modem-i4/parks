<script setup>
import { watch } from 'vue'

const props = defineProps({
  map: Object,
  parks: Array,
  selectedParkId: Number
})

const emit = defineEmits(['select'])

function renderPolygons() {
  if (!props.map) return

  props.map.data.forEach(feature => props.map.data.remove(feature))

  props.parks.forEach(park => {
    try {
      const feature = JSON.parse(park.geo_json)
      props.map.data.addGeoJson(feature).forEach(f => {
        f.setProperty('id', park.id)
      })
    } catch (e) {
      console.warn(`Не вдалося обробити парк ${park.name}:`, e)
    }
  })

  setMapStyle()
}

function setMapStyle() {
  props.map.data.setStyle(feature => ({
    fillColor: feature.getProperty('id') === props.selectedParkId ? '#4285F4' : '#9CCC65',
    strokeColor: '#333',
    strokeWeight: 1,
    fillOpacity: feature.getProperty('id') === props.selectedParkId ? 0.8 : 0.6,
    clickable: true,
  }))
}

watch(() => [props.map, props.parks, props.selectedParkId], () => {
  if (props.map) {
    renderPolygons()
  }
}, { immediate: true })

watch(() => props.map, () => {
  if (props.map) {
    props.map.data.addListener('click', event => {
      const id = event.feature.getProperty('id')
      const park = props.parks.find(p => p.id === id)
      if (park) emit('select', park)
    })
  }
}, { immediate: true })
</script>

<template></template>