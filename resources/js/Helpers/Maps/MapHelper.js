import { isMobile } from "@/Helpers/isMobileHelper"
import { Group, Tween, Easing } from '@tweenjs/tween.js'
import { ref } from 'vue'

export const defaultMapOptions = {
  zoom: 13,
  mapId: import.meta.env.VITE_GOOGLE_MAP_ID,
  strictBounds: false,
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
    parks : {
      default: 13,
      panelOpen: 15,
      min: 12,
      max: 19
    },
    singlePark: {
      default: 19.5,
      panelOpen: 20.5,
      min: 16,
      max: 23,
    },
  },
  desktop: {
    parks : {
      default: 14,
      panelOpen: 15,
      min: 13,
      max: 20
    },
    singlePark: {
      default: 20,
      panelOpen: 21,
      min: 16,
      max: 23,
    },
  },
  singlePark: {
    threshold: 20.5 // disable green at
  }
}

export const defaultBounds = {
    north: 48.96906027460897,
    south: 48.89167859502078 - (isMobile.value ? 0.06 : 0),
    east: 24.835674018094288,
    west: 24.654101425875925,
  }

export const getDevicePageZoom = (isSingleParkView) => zoom[isMobile.value ? 'mobile' : 'desktop'][isSingleParkView ? 'singlePark' : 'parks' ];

export function distanceInMeters(a, b) {
  const latDist = (a.lat - b.lat) * 111_000
  const lngDist = (a.lng - b.lng) * 111_000 * Math.cos((a.lat + b.lat) * Math.PI / 360)
  return Math.hypot(latDist, lngDist)
}
const coordsCache = new WeakMap()

export function getCoordsFromMarker(marker) {
  if (!marker) return defaultCoords
  return coordsCache.get(marker) ?? cacheMarkerCoords(marker)
}

export function cacheMarkerCoords(marker) {
  const cacheVal = getNewCoordsFromMarker(marker)
  coordsCache.set(marker, cacheVal)
  return cacheVal
}

export function getNewCoordsFromMarker(marker) {
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
  let bounds = null

  if (isSingleParkView) {
    bounds = getSingleParkMapRestrictions(park)
  }

  bounds ??= defaultBounds
  
  const devicePageZoom = getDevicePageZoom(isSingleParkView)

  return {
    minZoom: devicePageZoom.min,
    maxZoom: devicePageZoom.max,
    restriction: {
      latLngBounds: bounds,
    }
  }
}

export const isTweening = ref(false)
const tweenGroup = new Group()
let currentTween = null

export async function tweenCameraTo(map, targetLatLng, targetZoom, duration = 1000) {
  isTweening.value = true

  if (currentTween) {
    tweenGroup.remove(currentTween)
    currentTween = null
  }

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

  return new Promise(resolve => {
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
        map.moveCamera({ center: to, zoom: to.zoom })
        isTweening.value = false
        currentTween = null
        resolve()
      })

    currentTween = tween
    tween.start()

    const animate = (time) => {
      if (currentTween) {
        tweenGroup.update(time)
        requestAnimationFrame(animate)
      }
    }

    requestAnimationFrame(animate)
  })
}
