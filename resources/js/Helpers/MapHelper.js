import { isMobile } from "@/Helpers/isMobileHelper"
import { Group, Tween, Easing } from '@tweenjs/tween.js'
import { computed } from "vue"

export const defaultMapOptions = {
  zoom: 13,
  mapId: import.meta.env.VITE_GOOGLE_MAP_ID,
  zoomControl: false,
  streetViewControl: false,
  mapTypeControl: false,
  fullscreenControl: false,
  clickableIcons: false,
  cameraControl: false,
}

const defaultCoords = {
  lng: parseFloat(import.meta.env.VITE_DEFAULT_LNG),
  lat: parseFloat(import.meta.env.VITE_DEFAULT_LAT)
}

export const zoom = {
  mobile: {
    default: 13,
    singlePark: 19,
    panelOpen: 15,
    bounds: {
      min: 12,
      max: 19
    }
  },
  desktop: {
    default: 14,
    singlePark: 20,
    panelOpen: 15,
    bounds: {
      min: 13,
      max: 20
    }
  }
}

export const defaultBounds = {
    north: 48.96906027460897,
    south: 48.89167859502078 - (isMobile.value ? 0.03 : 0),
    east: 24.785674018094288,
    west: 24.654101425875925,
  }

export const deviceZoom = computed(() => zoom[isMobile.value ? 'mobile' : 'desktop'])

export function getCoordsFromMarker(marker) {
  if (marker?.geo_json) {
    try {
      const properties = marker.geo_json.properties
      if (properties?.center) {
        return { lng: properties.center[0], lat: properties.center[1] }
      }
    } catch (e) {
      console.warn(`Не вдалося отримати центр маркера "${marker.name}":`, e)
    }
  }
  if (marker?.coordinates?.length === 2) {
    return { lng: marker.coordinates[0], lat: marker.coordinates[1] }
  }
  return { lng: defaultCoords.lng, lat: defaultCoords.lat }
}

export function getAdjustedCoords(coords, targetZoom) {
  const { lng, lat } = coords
  if (!isMobile.value) return { lng, lat }

  const offsetY = (window.innerHeight - 56) * 0.2
  const scale = Math.pow(2, targetZoom)
  const metersPerPixel = 40075016.686 / (256 * scale)
  const offsetMeters = offsetY * metersPerPixel
  const offsetLat = (offsetMeters / 40075016.686) * 360

  return {
    lng,
    lat: lat - offsetLat
  }
}

export function getAdjustedCoordsFromMarker(marker, targetZoom) {
  const coords = getCoordsFromMarker(marker)
  return getAdjustedCoords(coords, targetZoom)
}

export function getSingleParkMapRestrictions(park) {
  if(park?.geo_json?.geometry?.type === 'Polygon' 
    && Array.isArray(park.geo_json.geometry.coordinates?.[0])) {

    const ring = park.geo_json.geometry.coordinates[0]
    const lngs = ring.map(coord => coord?.[0]).filter(Number.isFinite)
    const lats = ring.map(coord => coord?.[1]).filter(Number.isFinite)

    if (lats.length && lngs.length) {
      const margin = 0.002

      return {
        north: Math.max(...lats) + margin,
        south: Math.min(...lats) - margin - (isMobile.value ? 0.01 : 0),
        east: Math.max(...lngs) + margin,
        west: Math.min(...lngs) - margin,
      }
    }
  }

  return null
}

export function getMapRestrictions(isSingleParkView = false, park = null) {
  const deviceZoom = zoom[isMobile.value ? 'mobile' : 'desktop']
  let bounds = null

  if (isSingleParkView) {
    bounds ??= getSingleParkMapRestrictions(park)
  }

  bounds ??= defaultBounds

  return {
    minZoom: deviceZoom.bounds.min,
    maxZoom: isSingleParkView ? deviceZoom.bounds.max : deviceZoom.panelOpen,
    restriction: {
      latLngBounds: bounds,
    }
  }
}


const tweenGroup = new Group()

export function tweenCameraTo(map, targetLatLng, targetZoom, duration = 1000) {
  return new Promise(resolve => {
    const from = {
      lat: map.getCenter().lat(),
      lng: map.getCenter().lng(),
      zoom: map.getZoom()
    }

    const to = {
      lat: targetLatLng.lat,
      lng: targetLatLng.lng,
      zoom: targetZoom ?? from.zoom
    }

    const tween = new Tween(from, tweenGroup)
      .to(to, duration)
      .easing(Easing.Quadratic.Out)
      .onUpdate(() => {
        map.moveCamera({
          center: { lat: from.lat, lng: from.lng },
          zoom: from.zoom,
        })
      })
      .onComplete(() => {
        map.moveCamera({
          center: { lat: to.lat, lng: to.lng },
          zoom: to.zoom,
        })
        resolve()
      })

    tween.start()

    const animate = (time) => {
      tweenGroup.update(time)
      requestAnimationFrame(animate)
    }

    requestAnimationFrame(animate)
  })
}
