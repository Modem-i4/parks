export function coordsToPxFromBounds(point, imgW, imgH, bounds) {
  const u = (point.lng - bounds.minLng) / (bounds.maxLng - bounds.minLng)
  const v = (bounds.maxLat - point.lat) / (bounds.maxLat - bounds.minLat)

  return {
    x: u * imgW,
    y: v * imgH,
  }
}