export function calculateZoom(width) {
  if (width >= 1200) return 14
  if (width >= 768) return 13.4
  return 12.6
}

export const defaultMapOptions = {
  zoom: calculateZoom(window.innerWidth),
  mapId: import.meta.env.VITE_GOOGLE_MAP_ID,
  zoomControl: false,
  streetViewControl: false,
  mapTypeControl: false,
  fullscreenControl: false,
  clickableIcons: false,
  cameraControl: false,
}

export const isMobile = window.innerWidth <= 768
const defaultCoords = [
      parseFloat(import.meta.env.VITE_DEFAULT_LNG),
      parseFloat(import.meta.env.VITE_DEFAULT_LAT)
    ]

export function getCoordsFromMarker(marker) {
  if (marker?.geo_json) {
    try {
      const properties = marker.geo_json.properties
      if (properties?.center) {
        return properties.center
      }
    } catch (e) {
      console.warn(`Не вдалося центрувати маркер "${marker.name}":`, e)
    }
    return defaultCoords
  }
  return marker?.coordinates ?? defaultCoords
}
export function getAdjustedCoords(map, [lng, lat]) {
  if (!map || !isMobile) return [lng, lat];

  const offsetY = (window.innerHeight - 56) * (0.25);

  const zoom = map.getZoom();
  const scale = Math.pow(2, zoom);
  const metersPerPixel = 40075016.686 / (256 * scale);

  const offsetMeters = offsetY * metersPerPixel;
  const offsetLat = (offsetMeters / 40075016.686) * 360;
  return [lng, lat - offsetLat];
}


export function getAdjustedCoordsFromMarker(map, marker) {
  const markerCoords = getCoordsFromMarker(marker)
  return getAdjustedCoords(map, markerCoords)
}

export function getMapRestrictions(isSingleParkView = false) {
  const zoom = isSingleParkView 
  ? {
    minZoom: 13,
    maxZoom: 20,
  }
  : {
    minZoom: 14,
    maxZoom: 16,
  }
  const bounds = isSingleParkView
    ? {
      north: 48.94906027460897,
      south: 48.89167859502078,
      east: 24.745674018094288,
      west: 24.674101425875925
    }
    : {
      north: 48.94906027460897,
      south: 48.89167859502078,
      east: 24.745674018094288,
      west: 24.674101425875925
    }

    if(isMobile) {
      bounds.south -= 0.02
    }

  return {
    ...zoom,
    restriction: {
      latLngBounds: bounds,
    }
  }
}


export async function smoothZoomToPark(map, isSingleParkView, selectedMarker) {
  const targetZoom = isSingleParkView ? 22 : 12

  map.setOptions({
    minZoom: null,
    maxZoom: null,
    restriction: null,
    draggable: false,
    scrollwheel: false,
    disableDoubleClickZoom: true,
    gestureHandling: 'none',
    rotateControl: false
  })

  const [lng, lat] = getCoordsFromMarker(selectedMarker)
  if(map.getZoom() < 15) 
    map.panTo({ lat, lng })
  await smoothZoom(map, targetZoom)

  map.setOptions({
    ...getMapRestrictions(isSingleParkView),
    draggable: true,
    scrollwheel: true,
    disableDoubleClickZoom: false,
    gestureHandling: 'auto',
    rotateControl: true
  })
}

// TODO: make smooth finish
export function smoothZoom(map, targetZoom, delay = 16) {
  return new Promise(resolve => {
    const delta = targetZoom > map.getZoom() ? 0.1 : -0.1
    let currentZoom = map.getZoom()

    function step() {
      currentZoom += delta

      if (Math.abs(targetZoom - currentZoom) <= Math.abs(delta*10)) {
        map.setZoom(targetZoom)
        resolve()
        return
      }

      map.setZoom(currentZoom)
      setTimeout(() => requestAnimationFrame(step), delay)
    }

    step()
  })
}

