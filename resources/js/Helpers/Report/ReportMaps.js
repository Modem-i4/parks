import { groupMarkersByPark } from './ReportData.js'

const reportMapGeometryToleranceMeters = 1
const staticMapMaxUrlLength = 15000

function isCoordinatePair(coords) {
  return Array.isArray(coords)
    && coords.length === 2
    && Number.isFinite(Number(coords[0]))
    && Number.isFinite(Number(coords[1]))
}

function coordsFromPair(coords) {
  const lng = Number(coords[0])
  const lat = Number(coords[1])

  if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return null

  return { lat, lng }
}

function coordinatePath(coords) {
  if (!Array.isArray(coords)) return null
  if (coords.length && coords.every(isCoordinatePair)) return coords.map(coordsFromPair).filter(Boolean)

  for (const item of coords) {
    const path = coordinatePath(item)
    if (path?.length) return path
  }

  return null
}

function coordsFromGeoJson(marker) {
  const center = marker?.geo_json?.properties?.center
  if (isCoordinatePair(center)) return coordsFromPair(center)

  const geometryCoords = marker?.geo_json?.geometry?.coordinates
  if (isCoordinatePair(geometryCoords)) return coordsFromPair(geometryCoords)

  const path = coordinatePath(geometryCoords)
  return path?.length ? mapCenter(path) : null
}

function markerCoords(marker) {
  return coordsFromGeoJson(marker)
    || (isCoordinatePair(marker?.coordinates) ? coordsFromPair(marker.coordinates) : null)
    || (() => {
      const path = coordinatePath(marker?.coordinates)
      return path?.length ? mapCenter(path) : null
    })()
}

function mapCenter(coords) {
  if (!coords.length) return null

  const sum = coords.reduce((acc, item) => ({
    lat: acc.lat + item.lat,
    lng: acc.lng + item.lng,
  }), { lat: 0, lng: 0 })

  return {
    lat: sum.lat / coords.length,
    lng: sum.lng / coords.length,
  }
}

function geometryRings(geoJson) {
  if (!geoJson || typeof geoJson !== 'object') return []

  if (geoJson.type === 'Feature') return geometryRings(geoJson.geometry)
  if (geoJson.type === 'FeatureCollection') {
    return (geoJson.features || []).flatMap(geometryRings)
  }
  if (geoJson.type === 'GeometryCollection') {
    return (geoJson.geometries || []).flatMap(geometryRings)
  }
  if (geoJson.type === 'Polygon') {
    return (geoJson.coordinates || [])
      .map(ring => ring.map(coordsFromPair).filter(Boolean))
      .filter(ring => ring.length > 1)
  }
  if (geoJson.type === 'MultiPolygon') {
    return (geoJson.coordinates || []).flatMap(polygon => geometryRings({
      type: 'Polygon',
      coordinates: polygon,
    }))
  }

  return []
}

function encodeSignedNumber(value) {
  let encoded = value < 0 ? ~(value << 1) : value << 1
  let output = ''

  while (encoded >= 0x20) {
    output += String.fromCharCode((0x20 | (encoded & 0x1f)) + 63)
    encoded >>= 5
  }

  return output + String.fromCharCode(encoded + 63)
}

function encodePath(path) {
  let previousLat = 0
  let previousLng = 0

  return path.map(point => {
    const lat = Math.round(point.lat * 1e5)
    const lng = Math.round(point.lng * 1e5)
    const encoded = encodeSignedNumber(lat - previousLat) + encodeSignedNumber(lng - previousLng)
    previousLat = lat
    previousLng = lng
    return encoded
  }).join('')
}

function samePoint(first, second) {
  return first?.lat === second?.lat && first?.lng === second?.lng
}

function pointSegmentDistanceMeters(point, start, end) {
  const latitudeRadians = ((start.lat + end.lat + point.lat) / 3) * Math.PI / 180
  const metersPerLngDegree = 111320 * Math.cos(latitudeRadians)
  const toXY = coordinate => ({
    x: coordinate.lng * metersPerLngDegree,
    y: coordinate.lat * 111320,
  })
  const target = toXY(point)
  const from = toXY(start)
  const to = toXY(end)
  const dx = to.x - from.x
  const dy = to.y - from.y
  const lengthSquared = dx * dx + dy * dy
  const ratio = lengthSquared === 0
    ? 0
    : Math.max(0, Math.min(1, ((target.x - from.x) * dx + (target.y - from.y) * dy) / lengthSquared))

  return Math.hypot(
    target.x - (from.x + ratio * dx),
    target.y - (from.y + ratio * dy),
  )
}

function simplifyOpenPath(path, toleranceMeters) {
  if (path.length <= 2) return path

  let farthestDistance = 0
  let farthestIndex = 0

  for (let index = 1; index < path.length - 1; index += 1) {
    const distance = pointSegmentDistanceMeters(path[index], path[0], path[path.length - 1])
    if (distance > farthestDistance) {
      farthestDistance = distance
      farthestIndex = index
    }
  }

  if (farthestDistance <= toleranceMeters) return [path[0], path[path.length - 1]]

  const before = simplifyOpenPath(path.slice(0, farthestIndex + 1), toleranceMeters)
  const after = simplifyOpenPath(path.slice(farthestIndex), toleranceMeters)
  return [...before.slice(0, -1), ...after]
}

function simplifyRing(ring, toleranceMeters) {
  if (ring.length <= 4) return ring

  const points = samePoint(ring[0], ring[ring.length - 1]) ? ring.slice(0, -1) : [...ring]
  let splitIndex = 1
  let farthestDistance = 0

  for (let index = 1; index < points.length; index += 1) {
    const distance = pointSegmentDistanceMeters(points[index], points[0], points[0])
    if (distance > farthestDistance) {
      farthestDistance = distance
      splitIndex = index
    }
  }

  const firstArc = simplifyOpenPath(points.slice(0, splitIndex + 1), toleranceMeters)
  const secondArc = simplifyOpenPath([...points.slice(splitIndex), points[0]], toleranceMeters)
  const simplified = [...firstArc.slice(0, -1), ...secondArc]

  return simplified.length >= 4 ? simplified : ring
}

function appendGeometryPaths(params, park) {
  const plots = park?.plots || []

  for (const plot of plots) {
    for (const subplot of plot.subplots || []) {
      for (const ring of geometryRings(subplot.coordinates)) {
        const simplified = simplifyRing(ring, reportMapGeometryToleranceMeters)
        params.append('path', `color:0x000000cc|weight:1|fillcolor:0x00000020|enc:${encodePath(simplified)}`)
      }
    }

    for (const ring of geometryRings(plot.coordinates)) {
      const simplified = simplifyRing(ring, reportMapGeometryToleranceMeters)
      params.append('path', `color:0x000000ff|weight:3|fillcolor:0x00000000|enc:${encodePath(simplified)}`)
    }
  }
}

function mapBounds(coords) {
  if (!coords.length) return null

  return coords.reduce((bounds, item) => ({
    minLat: Math.min(bounds.minLat, item.lat),
    maxLat: Math.max(bounds.maxLat, item.lat),
    minLng: Math.min(bounds.minLng, item.lng),
    maxLng: Math.max(bounds.maxLng, item.lng),
  }), {
    minLat: coords[0].lat,
    maxLat: coords[0].lat,
    minLng: coords[0].lng,
    maxLng: coords[0].lng,
  })
}

function boundsCenter(bounds) {
  return {
    lat: (bounds.minLat + bounds.maxLat) / 2,
    lng: (bounds.minLng + bounds.maxLng) / 2,
  }
}

function mercatorY(latitude) {
  const radians = Math.max(-85.05112878, Math.min(85.05112878, latitude)) * Math.PI / 180
  return (1 - Math.log(Math.tan(radians) + (1 / Math.cos(radians))) / Math.PI) / 2
}

function mapViewportForBounds(bounds, size) {
  const [width, height] = String(size).split('x').map(Number)
  const usableWidth = Number.isFinite(width) ? width : 640
  const usableHeight = Number.isFinite(height) ? height : 400
  const lngSpan = Math.abs(bounds.maxLng - bounds.minLng) / 360
  const latSpan = Math.abs(mercatorY(bounds.maxLat) - mercatorY(bounds.minLat))
  const lngZoom = lngSpan > 0 ? Math.log2(usableWidth / (256 * lngSpan)) : 20
  const latZoom = latSpan > 0 ? Math.log2(usableHeight / (256 * latSpan)) : 20
  const exactZoom = Math.min(lngZoom, latZoom, 20)
  const closerZoom = Math.ceil(exactZoom)
  const markerPadding = 20
  const requiredWidth = Math.ceil(256 * lngSpan * (2 ** closerZoom) + markerPadding)
  const requiredHeight = Math.ceil(256 * latSpan * (2 ** closerZoom) + markerPadding)

  if (requiredWidth <= 640 && requiredHeight <= 640) {
    return {
      zoom: closerZoom,
      size: `${Math.max(usableWidth, requiredWidth)}x${Math.max(usableHeight, requiredHeight)}`,
    }
  }

  return {
    zoom: Math.max(0, Math.floor(exactZoom)),
    size: `${usableWidth}x${usableHeight}`,
  }
}

function markerMapViewport(coords, options) {
  const center = mapCenter(coords)
  if (!center) return null
  if (coords.length < 5) {
    return {
      center,
      zoom: options.zoom ?? 18,
      size: options.size ?? '640x400',
    }
  }

  const bounds = mapBounds(coords)
  const viewport = mapViewportForBounds(bounds, options.size ?? '640x400')
  return {
    center: boundsCenter(bounds),
    ...viewport,
  }
}

function clusterCenter(coords) {
  const center = mapCenter(coords)

  return {
    lat: center.lat,
    lng: center.lng,
    count: coords.length,
  }
}

function spatialClusters(coords, clusterSize) {
  const rowCount = Math.max(1, Math.round(Math.sqrt(coords.length / clusterSize)))
  const rowSize = Math.ceil(coords.length / rowCount)
  const sortedByLat = [...coords].sort((a, b) => a.lat - b.lat || a.lng - b.lng)
  const clusters = []

  for (let rowIndex = 0; rowIndex < rowCount; rowIndex += 1) {
    const row = sortedByLat
      .slice(rowIndex * rowSize, (rowIndex + 1) * rowSize)
      .sort((a, b) => a.lng - b.lng || a.lat - b.lat)

    for (let index = 0; index < row.length; index += clusterSize) {
      clusters.push(clusterCenter(row.slice(index, index + clusterSize)))
    }
  }

  return clusters
}

function mapMarkerCoords(coords, maxMarkers) {
  if (coords.length <= maxMarkers) {
    return {
      coords,
      clustered: false,
      largestCluster: 1,
    }
  }

  let clusterSize = Math.ceil(coords.length / maxMarkers)
  let clusters = []

  for (let attempt = 0; attempt < 8; attempt += 1) {
    clusters = spatialClusters(coords, clusterSize)
    if (clusters.length <= maxMarkers) break
    clusterSize += 1
  }

  return {
    coords: clusters.slice(0, maxMarkers),
    clustered: true,
    largestCluster: Math.max(...clusters.map(cluster => cluster.count)),
  }
}

function reportMapMarkerIconUrl() {
  if (typeof window === 'undefined') return null

  const localHosts = new Set(['localhost', '127.0.0.1', '[::1]'])
  if (window.location.protocol !== 'https:' || localHosts.has(window.location.hostname)) return null

  return new URL('/img/icons/markers/all-map_icon.png', window.location.origin).toString()
}

function staticMap(markers, options = {}) {
  const key = import.meta.env?.VITE_GOOGLE_MAPS_API_KEY
  if (!key) return null

  const coords = markers.map(markerCoords).filter(Boolean)
  if (!coords.length) return null
  const viewport = markerMapViewport(coords, options)
  if (!viewport) return null

  const params = new URLSearchParams({
    size: viewport.size,
    scale: '2',
    maptype: options.maptype ?? 'roadmap',
    key,
  })
  params.set('center', `${viewport.center.lat.toFixed(6)},${viewport.center.lng.toFixed(6)}`)
  params.set('zoom', String(viewport.zoom))

  if (options.markPlots === true) appendGeometryPaths(params, options.park)

  const baseUrl = 'https://maps.googleapis.com/maps/api/staticmap?'
  let markerLimit = Math.min(options.maxMarkers ?? 350, coords.length)
  let preparedMarkers
  let url
  const markerIconUrl = reportMapMarkerIconUrl()
  const markerStyle = markerIconUrl
    ? ['anchor:center', `icon:${markerIconUrl}`]
    : ['size:tiny', 'color:0x007c57']

  for (;;) {
    preparedMarkers = mapMarkerCoords(coords, markerLimit)
    params.set('markers', [
      ...markerStyle,
      ...preparedMarkers.coords
        .map(item => `${item.lat.toFixed(5)},${item.lng.toFixed(5)}`)
    ].join('|'))
    url = `${baseUrl}${params.toString()}`

    if (url.length <= staticMapMaxUrlLength || markerLimit === 1) break
    markerLimit = Math.max(1, Math.floor(markerLimit * 0.75))
  }

  return {
    url,
    markerCount: preparedMarkers.coords.length,
    originalCount: coords.length,
    clustered: preparedMarkers.clustered,
    largestCluster: preparedMarkers.largestCluster,
  }
}

export function reportMapGroups(markers, options = {}) {
  const parksById = new Map((options.parks || []).map(park => [String(park.id), park]))
  return groupMarkersByPark(markers, options.fallbackPark)
    .map(group => {
      const park = parksById.get(String(group.park.id)) || group.park
      const map = staticMap(group.markers, {
        maptype: 'roadmap',
        markPlots: options.markPlots,
        maxMarkers: 350,
        park,
        size: '640x400',
        zoom: 16,
      })

      return {
        ...group,
        map,
      }
    })
    .filter(group => group.map?.url)
}
