<script setup>
import { ref, watch } from 'vue'
import loader from '@/Helpers/Maps/GoogleMapsLoader'
import { CreatePinIcon } from '@/Helpers/Maps/CreatePinIcon.js'
import { getCoordsFromMarker } from '@/Helpers/Maps/MapHelper.js'
import { useParkStore } from '@/Stores/useParkStore.js'
import { CreateSimpleIcon, getColorByQualityState } from '@/Helpers/Maps/CreateSimpleIcon'
import { zoom, isTweening } from '@/Helpers/Maps/MapHelper.js'

const parkStore = useParkStore()
const mapMarkers = ref([])
let currentCancelToken = { cancelled: false }
const showZoomNotice = ref(false)

function resetCancelToken() {
  currentCancelToken.cancelled = true
  currentCancelToken = { cancelled: false }
}

function debounce(fn, delay = 200) {
  let timeout
  return (...args) => {
    clearTimeout(timeout)
    timeout = setTimeout(() => fn(...args), delay)
  }
}

function keyOf(m) {
  return `${m.id}_${m.type}`
}

let lastVisibleMarkers = 0
function updateVisibleMarkersCount(bounds) {
  if (!bounds) {
    lastVisibleMarkers = 0
    return
  }
  lastVisibleMarkers = parkStore.markers.filter(marker => {
    const coords = getCoordsFromMarker(marker)
    return bounds.contains(new google.maps.LatLng(coords.lat, coords.lng))
  }).length
  parkStore.messageBoxConditions.areMarkersLimited = areMarkersLimited(parkStore.map.getZoom(), lastVisibleMarkers)
}

function areMarkersLimited(currentZoom, lastVisibleMarkers) {
  const thresholdZoom = currentZoom <= zoom.singlePark.threshold
  const thresholdCount = lastVisibleMarkers > 125
  return thresholdZoom && thresholdCount
}

function isMarkerHidden(marker, currentZoom) {
  const limitMarkers = areMarkersLimited(currentZoom, lastVisibleMarkers)
  const isHiddenType = marker.type !== 'infrastructure' && marker.type !== 'park'
  return limitMarkers && isHiddenType
}


function distanceSquared(a, b) {
  return (a.lat - b.lat) ** 2 + (a.lng - b.lng) ** 2
}

async function createMarker(marker, lat, lng, cancelToken) {
  const { AdvancedMarkerElement } = await loader.importLibrary('marker')
  const content = marker.green
    ? await CreateSimpleIcon({
        iconPath: `/storage/img/icons/${marker.type || 'tree'}-map_icon.svg`,
        fill: getColorByQualityState(marker.green?.quality_state)
      })
    : await CreatePinIcon({ glyph: marker.icon?.file_path })
  
  if (cancelToken.cancelled) return
  return new AdvancedMarkerElement({
    map: parkStore.map,
    position: { lat, lng },
    title: marker.name,
    content,
    zIndex: Math.round(-lat * 1e6)
  })
}

/////// Viewport markers
async function updateMarkersInViewport(cancelToken = currentCancelToken) {
  if (!parkStore.map || cancelToken.cancelled) return
  if (parkStore.isSingleParkView && isTweening.value) return

  if (parkStore.markers.length === 0) {
    clearAllMapMarkers()
    return
  }

  const bounds = parkStore.map.getBounds()
  const center = parkStore.map.getCenter().toJSON()
  const currentZoom = parkStore.map.getZoom()

  updateZoomNotice(currentZoom)

  if (parkStore.isSingleParkView) {
    filterVisibleMarkers(currentZoom)
  }

  const sortedMarkers = sortMarkersByTypeAndDistance(center)

  renderSortedMarkers(sortedMarkers, bounds, currentZoom, cancelToken)
}

async function clearAllMapMarkers() {
  for (const { mapMarker } of mapMarkers.value) {
    mapMarker.setMap(null)
  }
  mapMarkers.value = []
}
function updateZoomNotice(currentZoom) {
  if (parkStore.isSingleParkView) {
    showZoomNotice.value = parkStore.markers.some(
      marker => isMarkerHidden(marker, currentZoom)
    )
  } else {
    showZoomNotice.value = false
  }
}
function filterVisibleMarkers(currentZoom) {
  const keySet = new Set(parkStore.markers.map(keyOf))
  mapMarkers.value = mapMarkers.value.filter(({ mapMarker, marker }) => {
    const keep =
      keySet.has(keyOf(marker)) &&
      !isMarkerHidden(marker, currentZoom)

    if (!keep) mapMarker.setMap(null)
    return keep
  })
}
function sortMarkersByTypeAndDistance(center) {
  return [...parkStore.markers]
    .map(marker => {
      const coords = getCoordsFromMarker(marker)
      const dist = distanceSquared(coords, center)
      const isInfra = marker.type === 'infrastructure' ? 0 : 1
      return { marker, coords, dist, isInfra }
    })
    .sort((a, b) => {
      if (a.isInfra !== b.isInfra) return a.isInfra - b.isInfra
      return a.dist - b.dist
    })
}
async function renderSortedMarkers(sortedMarkers, bounds, currentZoom, cancelToken) {
  let i = 0

  async function renderNext() {
    if (cancelToken.cancelled || i >= sortedMarkers.length) return

    const { marker, coords } = sortedMarkers[i++]
    const { lat, lng } = coords
    const key = keyOf(marker)

    const exists = mapMarkers.value.some(m => keyOf(m.marker) === key)
    const inBounds = bounds?.contains(new google.maps.LatLng(lat, lng))
    const hiddenByLimit = isMarkerHidden(marker, currentZoom)
    const shouldRender =
      !parkStore.isSingleParkView || (!exists && inBounds && !hiddenByLimit)

    if (shouldRender) {
      const mapMarker = await createMarker(marker, lat, lng, cancelToken)
      if(cancelToken.cancelled) return

      mapMarker.addListener('click', () => {
        parkStore.setSelectedMarker(marker) // with validation
      })

      mapMarker.setMap(parkStore.map)
      mapMarkers.value.push({ mapMarker, marker })
    }

    requestAnimationFrame(renderNext)
  }

  renderNext()
  updateVisibleMarkersCount(bounds)
}

function setupViewportFilter() {
  if (!parkStore.map) return

  const update = () => {
    resetCancelToken()
    updateMarkersInViewport(currentCancelToken)
  }

  google.maps.event.clearListeners(parkStore.map, 'bounds_changed')
  parkStore.map.addListener('bounds_changed', debounce(update, 150))
}

watch(
  () => [parkStore.map, parkStore.markers],
  () => {
    resetCancelToken()
    updateMarkersInViewport(currentCancelToken)
    setupViewportFilter()
  },
  { immediate: true }
)

watch(
  () => parkStore.isSingleParkView,
  () => {
    resetCancelToken()
    if(!parkStore.isSingleParkView)
      clearAllMapMarkers()
  },
  { immediate: true }
)

async function updateMarkerBackgrounds(newId, oldId) {
  const mapMarkersToUpdate = mapMarkers.value.filter(
    ({ marker }) => marker.id === newId || marker.id === oldId
  )

  for (const { mapMarker, marker } of mapMarkersToUpdate) {
    const isSelected = marker.id === newId
    if (marker.green) {
      const highlightClasses = ['scale-[3]', 'transition-transform']
      isSelected
        ? mapMarker.content.classList.add(...highlightClasses)
        : mapMarker.content.classList.remove(...highlightClasses)
    } else {
      mapMarker.content = await CreatePinIcon({
        glyph: marker.icon?.file_path,
        background: isSelected ? '#FF0000' : '#4285F4'
      })
    }
  }
}

watch(
  () => parkStore.selectedMarker?.id,
  (newId, oldId) => updateMarkerBackgrounds(newId, oldId)
)
watch(
  () => parkStore.selectedMarker?.edited,
  async (edited) => {
    if(!edited) return
    const marker = parkStore.selectedMarker
    if (!marker) return
    const newMapMarker = await createMarker(marker, marker.coordinates[1], marker.coordinates[0], currentCancelToken)
    newMapMarker.addListener('click', () => {
      parkStore.selectedMarker = marker
    })
    if (currentCancelToken.cancelled) return
    newMapMarker.setMap(parkStore.map)
    const index = mapMarkers.value.findIndex(m => m.marker.id === marker.id)
    if (index !== -1) {
      mapMarkers.value[index].mapMarker.setMap(null)
      mapMarkers.value.splice(index, 1, { marker, mapMarker: newMapMarker })
    } else {
      mapMarkers.value.push({ marker, mapMarker: newMapMarker })
    }
    parkStore.selectedMarker.edited = false
    parkStore.selectedMarker = null
    await new Promise(resolve => setTimeout(resolve, 0))
    parkStore.selectedMarker = marker
  }
)
</script>

<template></template>

