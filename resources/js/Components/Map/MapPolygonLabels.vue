<script setup>
import { onBeforeUnmount, watch } from 'vue'
import intersect from '@turf/intersect'
import { featureCollection, polygon } from '@turf/helpers'
import polylabel from 'polylabel'
import loader from '@/Helpers/Maps/GoogleMapsLoader'
import { useParkStore } from '@/Stores/useParkStore.js'

const parkStore = useParkStore()
const props = defineProps({
  active: Boolean,
})
const labels = new Map()
const POLYLABEL_PRECISION = 1e-6
const LABEL_REFERENCE_ZOOM = 19
const LABEL_FONT_SIZE = {
  plot: { base: 20, step: 2.5, min: 16, max: 30 },
  subplot: { base: 13, step: 2, min: 11, max: 21 },
}
const LABEL_Z_INDEX = {
  subplot: 2_000_000,
  plot: 2_000_001,
}

let idleListener = null
let renderGeneration = 0

function geometryOf(geoJson) {
  if (geoJson?.type === 'Feature') return geoJson.geometry
  if (geoJson?.type === 'Polygon' || geoJson?.type === 'MultiPolygon') return geoJson
  return null
}

function featureOf(geoJson) {
  const geometry = geometryOf(geoJson)
  return geometry ? { type: 'Feature', properties: {}, geometry } : null
}

function polygonParts(geometry) {
  if (geometry?.type === 'Polygon') return [geometry.coordinates]
  if (geometry?.type === 'MultiPolygon') return geometry.coordinates
  return []
}

function findLabelCandidates(geometry) {
  return polygonParts(geometry).map(coordinates => {
    const point = polylabel(coordinates, POLYLABEL_PRECISION)
    return {
      point: [point[0], point[1]],
      clearance: point.distance ?? 0,
    }
  })
}

function findBestLabelPoint(geometry) {
  return findLabelCandidates(geometry)
    .sort((a, b) => b.clearance - a.clearance)[0]?.point ?? null
}

function findVisibleLabelPoint(geometry, preferredPoint) {
  const candidates = findLabelCandidates(geometry)
  if (!candidates.length) return null

  return candidates.sort((a, b) => {
    const distanceA = squaredDistance(a.point, preferredPoint)
    const distanceB = squaredDistance(b.point, preferredPoint)
    return distanceA - distanceB || b.clearance - a.clearance
  })[0].point
}

function squaredDistance([lngA, latA], [lngB, latB]) {
  return (lngA - lngB) ** 2 + (latA - latB) ** 2
}

function geometryBounds(geometry) {
  let west = Infinity
  let south = Infinity
  let east = -Infinity
  let north = -Infinity

  for (const polygonCoordinates of polygonParts(geometry)) {
    for (const ring of polygonCoordinates) {
      for (const [lng, lat] of ring) {
        west = Math.min(west, lng)
        south = Math.min(south, lat)
        east = Math.max(east, lng)
        north = Math.max(north, lat)
      }
    }
  }

  return Number.isFinite(west) ? { west, south, east, north } : null
}

function boundsIntersect(a, b) {
  return a && b
    && a.west <= b.east
    && a.east >= b.west
    && a.south <= b.north
    && a.north >= b.south
}

function pointInBounds([lng, lat], bounds) {
  return lng >= bounds.west
    && lng <= bounds.east
    && lat >= bounds.south
    && lat <= bounds.north
}

function getLabelFontSize(type, zoom) {
  const { base, step, min, max } = LABEL_FONT_SIZE[type]
  return Math.min(max, Math.max(min, base + (zoom - LABEL_REFERENCE_ZOOM) * step))
}

function getSafeViewportBounds(map, paddingPx) {
  const bounds = map.getBounds()
  const projection = map.getProjection()
  const zoom = map.getZoom()
  if (!bounds || !projection || zoom == null) return null

  const northEast = projection.fromLatLngToPoint(bounds.getNorthEast())
  const southWest = projection.fromLatLngToPoint(bounds.getSouthWest())
  const inset = paddingPx / (2 ** zoom)
  const safeNorthEast = projection.fromPointToLatLng(new google.maps.Point(
    northEast.x - inset,
    northEast.y + inset,
  ))
  const safeSouthWest = projection.fromPointToLatLng(new google.maps.Point(
    southWest.x + inset,
    southWest.y - inset,
  ))

  const safeBounds = {
    west: safeSouthWest.lng(),
    south: safeSouthWest.lat(),
    east: safeNorthEast.lng(),
    north: safeNorthEast.lat(),
  }

  if (safeBounds.west >= safeBounds.east || safeBounds.south >= safeBounds.north) return null
  return safeBounds
}

function viewportFeature(bounds) {
  const { west, south, east, north } = bounds
  return polygon([[
    [west, south],
    [east, south],
    [east, north],
    [west, north],
    [west, south],
  ]])
}

function createLabelContent(text, type) {
  const element = document.createElement('div')
  element.textContent = text
  element.setAttribute('aria-hidden', 'true')
  Object.assign(element.style, {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    color: '#111827',
    fontFamily: 'Arial, sans-serif',
    fontWeight: type === 'plot' ? '800' : '600',
    lineHeight: '1',
    pointerEvents: 'none',
    userSelect: 'none',
    whiteSpace: 'nowrap',
    textShadow: [
      '-1px -1px 0 #fff',
      '1px -1px 0 #fff',
      '-1px 1px 0 #fff',
      '1px 1px 0 #fff',
      '0 0 3px #fff',
    ].join(', '),
  })

  if (type === 'plot') {
    Object.assign(element.style, {
      backgroundColor: 'rgba(255, 255, 255, 0.35)',
      border: '1px solid rgba(17, 24, 39, 0.35)',
      borderRadius: '50%',
      boxSizing: 'border-box',
    })
  }

  return element
}

function polygonLabelData() {
  const plots = parkStore.selectedPark?.plots ?? []
  return plots.flatMap(plot => {
    const plotLabel = {
      key: `plot:${plot.id}`,
      type: 'plot',
      text: plot.name,
      geoJson: plot.coordinates,
    }
    const subplotLabels = (plot.subplots ?? []).map(subplot => ({
      key: `subplot:${subplot.id}`,
      type: 'subplot',
      text: subplot.name,
      geoJson: subplot.coordinates,
    }))
    return [plotLabel, ...subplotLabels]
  }).filter(label => label.text != null && label.text !== '')
}

function clearLabels() {
  for (const label of labels.values()) label.marker.map = null
  labels.clear()
}

function removeIdleListener() {
  idleListener?.remove()
  idleListener = null
}

function updateLabelPositions() {
  const map = parkStore.map
  if (!props.active || !map) return

  const zoom = map.getZoom()
  if (zoom == null) return

  const fontSizes = {
    plot: getLabelFontSize('plot', zoom),
    subplot: getLabelFontSize('subplot', zoom),
  }
  const viewportPadding = Math.max(20, fontSizes.plot * 1.25)
  const safeBounds = getSafeViewportBounds(map, viewportPadding)
  if (!safeBounds) return
  const viewport = viewportFeature(safeBounds)

  for (const label of labels.values()) {
    let position = null
    label.content.style.fontSize = `${fontSizes[label.type]}px`
    if (label.type === 'plot') {
      const backgroundSize = fontSizes.plot * 1.8
      label.content.style.width = `${backgroundSize}px`
      label.content.style.height = `${backgroundSize}px`
    }

    if (boundsIntersect(label.bounds, safeBounds)) {
      if (pointInBounds(label.preferredPoint, safeBounds)) {
        position = label.preferredPoint
      } else {
        try {
          const visibleFeature = intersect(featureCollection([label.feature, viewport]))
          position = visibleFeature
            ? findVisibleLabelPoint(visibleFeature.geometry, label.preferredPoint)
            : null
        } catch (error) {
          console.warn(`Не вдалося розмістити підпис зони ${label.text}:`, error)
        }
      }
    }

    label.marker.position = position
      ? { lng: position[0], lat: position[1] }
      : null
    label.marker.map = position ? map : null
  }
}

async function renderLabels() {
  const generation = ++renderGeneration
  removeIdleListener()
  clearLabels()

  const map = parkStore.map
  if (!props.active || !map || !parkStore.selectedPark) return

  const { AdvancedMarkerElement } = await loader.importLibrary('marker')
  if (generation !== renderGeneration) return

  for (const data of polygonLabelData()) {
    const feature = featureOf(data.geoJson)
    const preferredPoint = feature ? findBestLabelPoint(feature.geometry) : null
    const bounds = feature ? geometryBounds(feature.geometry) : null
    if (!feature || !preferredPoint || !bounds) continue

    const content = createLabelContent(String(data.text), data.type)
    const marker = new AdvancedMarkerElement({
      anchorLeft: '-50%',
      anchorTop: '-50%',
      content,
      collisionBehavior: google.maps.CollisionBehavior.REQUIRED,
      zIndex: LABEL_Z_INDEX[data.type],
    })

    labels.set(data.key, {
      ...data,
      feature,
      preferredPoint,
      bounds,
      marker,
      content,
    })
  }

  updateLabelPositions()
  idleListener = map.addListener('idle', updateLabelPositions)
}

watch(
  () => [props.active, parkStore.map, parkStore.selectedPark],
  renderLabels,
  { immediate: true },
)

onBeforeUnmount(() => {
  renderGeneration += 1
  removeIdleListener()
  clearLabels()
})
</script>

<template></template>
