export function useUserLocationMarker(mapRef) {
  let marker = null
  let watcherId = null

  const createArrowElement = () => {
    const el = document.createElement('div')
    el.style.width = '56px'
    el.style.height = '56px'
    el.style.position = 'relative'
    el.innerHTML = `
      <svg viewBox="0 0 24 24" width="56" height="56" fill="#4285F4" stroke="white" stroke-width="1">
        <path d="M12 4 L16 20 L12 16 L8 20 Z" />
      </svg>
    `
    return el
  }

  const createCircleElement = () => {
    const el = document.createElement('div')
    el.style.width = '25px'
    el.style.height = '25px'
    el.style.borderRadius = '50%'
    el.style.backgroundColor = '#4285F4'
    el.style.boxShadow = '0 0 0 2px white'
    el.style.position = 'relative'
    return el
  }

  const triggerPulse = (el) => {
    const pulse = document.createElement('div')
    Object.assign(pulse.style, {
      position: 'absolute',
      width: '32px',
      height: '32px',
      borderRadius: '50%',
      backgroundColor: '#4285F4',
      opacity: '0.5',
      animation: 'pulse-ring 1.5s ease-out forwards',
      zIndex: '-2',
      left: '-2px',
      top: '-2px'
    })
    el.appendChild(pulse)
    setTimeout(() => el.removeChild(pulse), 1500)
  }

  const updatePosition = ({ coords }) => {
    const map = mapRef.value
    if (!map) return

    const pos = { lat: coords.latitude, lng: coords.longitude }
    const heading = coords.heading

    if (!marker) {
      const el = heading != null && !isNaN(heading)
        ? createArrowElement()
        : createCircleElement()

      if (heading && el.tagName === 'DIV') {
        el.style.transform = `rotate(${heading}deg)`
      }

      triggerPulse(el)

      marker = new google.maps.marker.AdvancedMarkerElement({
        map,
        position: pos,
        content: el,
        zIndex: -1,
      })
    } else {
      marker.position = pos

      const hasHeading = heading != null && !isNaN(heading)
      const currentIsArrow = marker.content?.querySelector('svg')

      if (hasHeading && !currentIsArrow) {
        const el = createArrowElement()
        el.style.transform = `rotate(${heading}deg)`
        triggerPulse(el)
        marker.content = el
      } else if (!hasHeading && currentIsArrow) {
        const el = createCircleElement()
        triggerPulse(el)
        marker.content = el
      } else {
        if (hasHeading) {
          marker.content.style.transform = `rotate(${heading}deg)`
        } else {
          marker.content.style.transform = 'none'
        }
        triggerPulse(marker.content)
      }
    }
  }

  const showUserPosition = () => {
    const map = mapRef.value
    if (!navigator.geolocation || !map || !google?.maps?.marker) return

    navigator.geolocation.getCurrentPosition(updatePosition, null, {
      enableHighAccuracy: true,
    })

    if (!watcherId) {
      watcherId = navigator.geolocation.watchPosition(updatePosition, null, {
        enableHighAccuracy: true,
      })
    }
  }

  const stop = () => {
    if (watcherId !== null) {
      navigator.geolocation.clearWatch(watcherId)
      watcherId = null
    }
    if (marker) {
      marker.map = null
      marker = null
    }
  }

  return { showUserPosition, stop }
}
