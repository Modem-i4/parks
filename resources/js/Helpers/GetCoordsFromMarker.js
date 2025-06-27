function getCoordsFromMarker(marker) {
  if (marker.geo_json) {
    try {
      const properties = marker.geo_json.properties
      if (properties?.center) {
        return properties.center
      }
    } catch (e) {
      console.warn(`Не вдалося центрувати маркер "${marker.name}":`, e)
    }
  }

  if (marker.coordinates) {
    return marker.coordinates
  }
  return [0, 0]
}
export default getCoordsFromMarker