<script setup>
import { onBeforeUnmount, onUnmounted, ref, watch } from 'vue'
import loader from '@/Helpers/Maps/GoogleMapsLoader'
import { CreatePinIcon } from '@/Helpers/Maps/CreatePinIcon.js'
import { getCoordsFromMarker, getLinePathFromMarker, isFiniteCoords } from '@/Helpers/Maps/MapHelper.js'
import { useParkStore } from '@/Stores/useParkStore.js'
import { CreateSimpleIcon, getColorByGreenState } from '@/Helpers/Maps/CreateSimpleIcon'
import { zoom, isTweening } from '@/Helpers/Maps/MapHelper.js'
import { CreateCustomPinIcon } from '@/Helpers/Maps/CreateCustomPinIcon'
import { isMobile } from '@/Helpers/isMobileHelper'

const parkStore = useParkStore()
const mapMarkers = ref([])
const mapLines = new Set()
const hedgeLinesByMarkerKey = new Map()
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

onUnmounted(resetCancelToken)

let lastVisibleMarkers = 0
function updateVisibleMarkersCount(bounds) {
  if (!bounds) {
    lastVisibleMarkers = 0
    return
  }
  lastVisibleMarkers = parkStore.markers.filter(marker => {
    const coords = getCoordsFromMarker(marker)
    if (!isFiniteCoords(coords)) return false
    return bounds.contains(new google.maps.LatLng(coords.lat, coords.lng))
  }).length
  parkStore.markerStates.areLimited = areMarkersLimited(parkStore.map.getZoom(), lastVisibleMarkers)
}

function areMarkersLimited(currentZoom, lastVisibleMarkers) {
  if(!isMobile.value) return false
  const thresholdZoom = currentZoom <= zoom.singlePark.threshold
  const thresholdCount = lastVisibleMarkers > 200
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

function getParkMarkerCount(marker) {
  if (parkStore.markerCountsByPark === null) return null
  return Number(parkStore.markerCountsByPark?.[marker.id] ?? 0)
}

async function createParkMarkerContent(marker, isSelected = false) {
  return await CreateCustomPinIcon({
    glyph: marker.icon?.file_path,
    label: marker.name,
    color: isSelected ? '#007c00' : '#007c57',
    count: getParkMarkerCount(marker),
    countColor: isSelected ? '#006e4d' : '#00a271'
  })
}

async function createMarker(marker, lat, lng, cancelToken) {
  if (!isFiniteCoords({ lat, lng })) return null

  const { AdvancedMarkerElement } = await loader.importLibrary('marker')
  const content = marker.type === 'park'
    ? await createParkMarkerContent(marker, marker.id === parkStore.selectedMarker?.id)
    : marker.green
      ? await CreateSimpleIcon({
          type: marker.type || 'all',
          fill: getColorByGreenState(marker.green?.green_state)
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

function createMarkerLine(marker) {
  const path = getLinePathFromMarker(marker)
  if (!path?.length) return null

  const line = new google.maps.Polyline({
    path,
    geodesic: true,
    strokeColor: marker.green
      ? getColorByGreenState(marker.green?.green_state)
      : '#00a271',
    strokeOpacity: 1,
    strokeWeight: 6,
    clickable: true,
    zIndex: 1000,
  })

  return line
}

/////// Viewport markers
async function updateMarkersInViewport(cancelToken = currentCancelToken) {
  if (!parkStore.map || cancelToken.cancelled) return
  if (parkStore.isSingleParkView && isTweening.value) return
  if (!parkStore.isSingleParkView) clearAllMapLines()

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
  } else {
    removeMissingMarkers()
  }

  const sortedMarkers = sortMarkersByTypeAndDistance(center)
  
  renderSortedMarkers(sortedMarkers, bounds, currentZoom, cancelToken)
}

async function clearAllMapMarkers() {
  for (const renderedMarker of mapMarkers.value) {
    clearRenderedMarker(renderedMarker)
  }
  mapMarkers.value = []
  clearAllMapLines()
}
function attachMapLine(mapMarker, mapLine, marker) {
  if (!mapLine) return

  mapLines.add(mapLine)

  if (marker.type === 'hedge') {
    const markerKey = keyOf(marker)
    clearMapLine(hedgeLinesByMarkerKey.get(markerKey))
    hedgeLinesByMarkerKey.set(markerKey, mapLine)
    mapMarker.hedgeLine = mapLine
    mapLine.hedgeMarkerKey = markerKey
  }

  mapLine.setMap(parkStore.map)
}
function clearMapLine(mapLine) {
  if (!mapLine) return
  mapLine.setMap(null)
  mapLines.delete(mapLine)
  if (mapLine.hedgeMarkerKey) {
    hedgeLinesByMarkerKey.delete(mapLine.hedgeMarkerKey)
    delete mapLine.hedgeMarkerKey
  }
}
function clearAllMapLines() {
  for (const mapLine of mapLines) {
    mapLine.setMap(null)
  }
  mapLines.clear()
  hedgeLinesByMarkerKey.clear()
}
function clearRenderedMarker({ mapMarker, mapLine, marker }) {
  mapMarker.setMap(null)

  if (marker.type === 'hedge') {
    clearMapLine(mapMarker.hedgeLine)
    clearMapLine(hedgeLinesByMarkerKey.get(keyOf(marker)))
    delete mapMarker.hedgeLine
  }

  clearMapLine(mapLine)
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
  const markerByKey = new Map(parkStore.markers.map(marker => [keyOf(marker), marker]))
  const selectedId = parkStore.selectedMarker?.id
  mapMarkers.value = mapMarkers.value.filter((renderedMarker) => {
    const { marker } = renderedMarker
    const currentMarker = markerByKey.get(keyOf(marker))
    const keep =
      currentMarker &&
      (marker.type !== 'hedge' || currentMarker === marker) &&
      (!isMarkerHidden(marker, currentZoom) || marker.id === selectedId)

    if (!keep) {
      clearRenderedMarker(renderedMarker)
    }
    return keep
  })
}
function removeMissingMarkers() {
  const markerByKey = new Map(parkStore.markers.map(marker => [keyOf(marker), marker]))
  mapMarkers.value = mapMarkers.value.filter((renderedMarker) => {
    const { marker } = renderedMarker
    const currentMarker = markerByKey.get(keyOf(marker))
    const keep = currentMarker && (marker.type !== 'hedge' || currentMarker === marker)
    if (!keep) {
      clearRenderedMarker(renderedMarker)
    }
    return keep
  })
}
function sortMarkersByTypeAndDistance(center) {
  const selectedId = parkStore.selectedMarker?.id
  return [...parkStore.markers]
    .map(marker => {
      const coords = getCoordsFromMarker(marker)
      const dist = distanceSquared(coords, center)
      const isInfra = marker.type === 'infrastructure' ? 0 : 1
      const isSelected = marker.id === selectedId ? 0 : 1
      return { marker, coords, dist, isInfra, isSelected }
    })
    .filter(({ coords }) => isFiniteCoords(coords))
    .sort((a, b) => {
      if (a.isSelected !== b.isSelected) return a.isSelected - b.isSelected
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
    const hasLine = !!getLinePathFromMarker(marker)
    const inBounds = bounds?.contains(new google.maps.LatLng(lat, lng))
    const hiddenByLimit = isMarkerHidden(marker, currentZoom)
    const selected = marker.id === parkStore.selectedMarker?.id
    const shouldRender =
      !exists && (
        !parkStore.isSingleParkView ||
        selected ||
        ((hasLine || inBounds) && !hiddenByLimit)
      )

    if (shouldRender) {
      const mapMarker = await createMarker(marker, lat, lng, cancelToken)
      if(cancelToken.cancelled || !mapMarker) return
      const mapLine = createMarkerLine(marker)

      const selectMarker = () => {
        parkStore.setSelectedMarker(marker) // with validation
      }

      mapMarker.addListener('click', selectMarker)
      mapLine?.addListener('click', selectMarker)

      const renderedMarker = { mapMarker, mapLine, marker }
      mapMarkers.value.push(renderedMarker)
      mapMarker.setMap(parkStore.map)
      attachMapLine(mapMarker, mapLine, marker)

      if(selected) updateMarkerBackgrounds(marker.id)
    }

    requestAnimationFrame(renderNext)
  }

  renderNext()
  updateVisibleMarkersCount(bounds)
}

function setupViewportFilter() {
  if (!parkStore.map) return

  google.maps.event.clearListeners(parkStore.map, 'idle')
  google.maps.event.clearListeners(parkStore.map, 'bounds_changed')
  parkStore.map.addListener('idle', refreshMarkersInViewport)
  parkStore.map.addListener('bounds_changed', debounce(refreshMarkersInViewport, 150))
}

function refreshMarkersInViewport() {
  resetCancelToken()
  updateMarkersInViewport(currentCancelToken)
}

watch(
  () => parkStore.map,
  (map) => {
    setupViewportFilter()
    setupMapClick(map)
  },
  { immediate: true }
)

watch(
  () => [parkStore.map, parkStore.markers],
  () => refreshMarkersInViewport(),
  { immediate: true }
)

watch(
  () => isTweening.value,
  (isMapTweening) => {
    if (!isMapTweening && parkStore.isSingleParkView) {
      refreshMarkersInViewport()
    }
  }
)

watch(
  () => parkStore.isSingleParkView,
  (val) => {
    resetCancelToken()
    if(!val)
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
      const highlightClasses = ['scale-[2.3]', 'transition-transform']
      isSelected
        ? mapMarker.content.classList.add(...highlightClasses)
        : mapMarker.content.classList.remove(...highlightClasses)
    } else if(marker.type === 'park') {
      mapMarker.content = await createParkMarkerContent(marker, isSelected)
    } else {
      mapMarker.content = await CreatePinIcon({
        glyph: marker.icon?.file_path,
        background: isSelected ? '#007c00' : '#4285F4'
      })
    }
  }
}

async function updateParkMarkerCounts() {
  for (const { mapMarker, marker } of mapMarkers.value) {
    if (marker.type !== 'park') continue
    mapMarker.content = await createParkMarkerContent(marker, marker.id === parkStore.selectedMarker?.id)
  }
}

watch(
  () => parkStore.selectedMarker?.id,
  (newId, oldId) => updateMarkerBackgrounds(newId, oldId)
)

watch(
  () => parkStore.markerCountsByPark,
  () => updateParkMarkerCounts()
)

watch(
  () => parkStore.selectedMarker?.edited,
  async (edited) => {
    if(!edited) return
    const marker = parkStore.selectedMarker
    if (!marker) return
    const coords = getCoordsFromMarker(marker)
    const newMapMarker = await createMarker(marker, coords.lat, coords.lng, currentCancelToken)
    if (currentCancelToken.cancelled || !newMapMarker) return
    const newMapLine = createMarkerLine(marker)
    newMapMarker.addListener('click', () => {
      parkStore.selectedMarker = marker
    })
    newMapLine?.addListener('click', () => {
      parkStore.selectedMarker = marker
    })
    if (currentCancelToken.cancelled) return
    const index = mapMarkers.value.findIndex(m => m.marker.id === marker.id)
    if (index !== -1) {
      clearRenderedMarker(mapMarkers.value[index])
      mapMarkers.value.splice(index, 1, { marker, mapMarker: newMapMarker, mapLine: newMapLine })
    } else {
      mapMarkers.value.push({ marker, mapMarker: newMapMarker, mapLine: newMapLine })
    }
    newMapMarker.setMap(parkStore.map)
    attachMapLine(newMapMarker, newMapLine, marker)
    parkStore.selectedMarker.edited = false
    parkStore.selectedMarker = null
    await new Promise(resolve => setTimeout(resolve, 0))
    parkStore.selectedMarker = marker
  }
)
watch(
  () => parkStore.selectedMarker?.deleted,
  (deleted) => {
    if (!deleted) return
    const marker = parkStore.selectedMarker
    if (!marker) return
    const parkStoreIndex = parkStore.markers.findIndex(m => m.id === marker.id)
    if (parkStoreIndex !== -1) parkStore.markers.splice(parkStoreIndex, 1)

    const mapMarkersIndex = mapMarkers.value.findIndex(m => m.marker.id === marker.id)
    if (mapMarkersIndex !== -1) {
      clearRenderedMarker(mapMarkers.value[mapMarkersIndex])
      mapMarkers.value.splice(mapMarkersIndex, 1)
    }

    parkStore.selectedMarker = null
  }
)

let clearOnMapClickListener
function setupMapClick(map) {
  if(!map) return
  clearOnMapClickListener = map.addListener('click', () => {
    parkStore.selectedMarker = null
  })
}
onBeforeUnmount(() => {
  clearOnMapClickListener?.remove()
  clearAllMapMarkers()
})
</script>

<template></template>
