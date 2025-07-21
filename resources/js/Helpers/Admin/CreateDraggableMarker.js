export async function createDraggableMarker({
  map,
  position,
  iconUrl,
  zIndex = 999999,
  onDrag }) {
  const { AdvancedMarkerElement } = await import('@/Helpers/Maps/GoogleMapsLoader').then(m => m.default.importLibrary('marker'))

  const el = document.createElement('div')
  Object.assign(el.style, {
    position: 'absolute',
    left: '0',
    top: '0',
    width: '100px',
    height: '100px',
    backgroundImage: `url("${iconUrl}")`,
    backgroundSize: 'contain',
    backgroundRepeat: 'no-repeat',
    backgroundPosition: 'center',
    transform: 'translate(-50%, -50%)',
    cursor: 'grab',
    touchAction: 'none',
  })

  const marker = new AdvancedMarkerElement({
    map,
    position,
    content: el,
    zIndex
  })

  let isDragging = false
  let startLatLng = null
  let wasDraggable = map.get('draggable')

  el.addEventListener('pointerdown', (e) => {
    isDragging = true
    el.setPointerCapture(e.pointerId)
    el.style.cursor = 'grabbing'
    map.set('draggable', false)

    startLatLng = map.getProjection().fromLatLngToPoint(marker.position)

    const moveListener = (eMove) => {
      if (!isDragging) return

      const scale = Math.pow(2, map.getZoom())
      const deltaX = eMove.movementX / scale
      const deltaY = eMove.movementY / scale

      startLatLng.x += deltaX
      startLatLng.y += deltaY

      const latLng = map.getProjection().fromPointToLatLng(startLatLng)
      marker.position = latLng
      if (onDrag) onDrag(latLng)
    }

    const upListener = () => {
      isDragging = false
      el.style.cursor = 'grab'
      map.set('draggable', wasDraggable)
      window.removeEventListener('pointermove', moveListener)
      window.removeEventListener('pointerup', upListener)
    }

    window.addEventListener('pointermove', moveListener)
    window.addEventListener('pointerup', upListener)
  })

  return marker
}
