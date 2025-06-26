<script setup>
import { ref, onMounted, watch, shallowRef } from 'vue'
import loader from '@/Helpers/GoogleMapsLoader'
import MapMarkers from './MapMarkers.vue'
import MapPolygons from './MapPolygons.vue'

const props = defineProps({
  parks: {
    type: Array,
    required: true
  },
  selectedParkId: {
    type: Number,
    default: null
  },
  showPanel: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['select'])

const mapElement = ref(null)
const map = shallowRef(null)
const defaultCenter = { lat: 48.918, lng: 24.7137 }

function calculateZoom(width) {
  if (width >= 1200) return 14
  if (width >= 768) return 13.4
  return 12.6
}

onMounted(async () => {
  const { Map } = await loader.importLibrary('maps')

  map.value = new Map(mapElement.value, {
    mapId: import.meta.env.VITE_GOOGLE_MAP_ID,
    center: defaultCenter,
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
  () => [map.value, props.parks, props.selectedParkId, props.showPanel],
  () => {
    const mapInstance = map.value
    if (!mapInstance) return

    const isMobile = window.innerWidth <= 768
    const adjustment = isMobile && props.showPanel ? -0.03 : 0

    const selectedId = props.selectedParkId
    if (!selectedId) {
      const adjustedLat = defaultCenter.lat + adjustment
      mapInstance.panTo({ lat: adjustedLat, lng: defaultCenter.lng })
      return
    }

    const park = props.parks.find(p => p.id === selectedId)
    if (!park) return

    try {
      const { properties } = JSON.parse(park.geo_json)
      const [lng, lat] = properties?.center || []
      if (lat && lng) {
        const adjustedLat = lat + adjustment
        mapInstance.panTo({ lat: adjustedLat, lng })
      }
    } catch (e) {
      console.warn(`Не вдалося центрувати парк "${park.name}":`, e)
    }
  },
  { immediate: true }
)

</script>

<template>
  <div ref="mapElement" class="w-full h-full">
    <MapPolygons :map="map" :parks="parks" :selectedParkId="selectedParkId" @select="emit('select', $event)" />
    <MapMarkers :map="map" :parks="parks" :selectedParkId="selectedParkId" @select="emit('select', $event)" />
  </div>
</template>