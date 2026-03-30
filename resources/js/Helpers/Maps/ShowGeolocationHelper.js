import { isTweening, tweenCameraTo } from "./MapHelper"

let marker = null
let watcherId = null
let el = null
let lastKnownPosition = null
let lastKnownHeading = null
let lastKnownAt = 0

export function useUserLocationMarker(mapRef, customMsgRef) {
  const setCustomMessage = (message) => {
    if (customMsgRef) customMsgRef.value = message
  }

  const rememberPosition = (position, heading = null) => {
    lastKnownPosition = position
    lastKnownHeading = heading
    lastKnownAt = Date.now()
  }

  const moveMarkerToPosition = (pos, heading = null) => {
    const map = mapRef.value
    if (!map) return false

    const hasHeading = heading != null && !isNaN(heading)

    if (!marker) {
      createSharedElement()
      if (hasHeading) {
        setAsArrow(heading)
      } else {
        setAsCircle()
      }
      triggerPulse()

      marker = new google.maps.marker.AdvancedMarkerElement({
        map,
        position: pos,
        content: el,
        zIndex: -1,
      })
    } else {
      marker.position = pos

      const isCurrentlyArrow = !!el.querySelector('svg')

      if (hasHeading && !isCurrentlyArrow) {
        setAsArrow(heading)
      } else if (!hasHeading && isCurrentlyArrow) {
        setAsCircle()
      } else if (hasHeading && isCurrentlyArrow) {
        el.style.rotate = `${heading}deg`
      }

      if (!hasHeading) el.style.rotate = '0deg'
      triggerPulse()
    }

    const bounds = map.getRestriction()?.latLngBounds
    if (bounds && !bounds.contains(pos)) {
      setCustomMessage('Ваша позиція не в межах мапи')
      return false
    }

    tweenCameraTo(map, pos)
    return true
  }

  const createSharedElement = () => {
    el = document.createElement('div')
    Object.assign(el.style, {
      position: 'absolute',
      display: 'flex',
      alignItems: 'center',
      justifyContent: 'center',
      transform: 'translate(-50%, -50%)',
      pointerEvents: 'none',
    })
    return el
  }

  const setAsArrow = (heading) => {
    Object.assign(el.style, {
      width: '56px',
      height: '56px',
      backgroundColor: '',
      borderRadius: '',
      boxShadow: '',
    })
    el.innerHTML = `
      <svg viewBox="0 0 24 24" width="56" height="56" fill="#4285F4" stroke="white" stroke-width="1">
        <path d="M12 4 L16 20 L12 16 L8 20 Z" />
      </svg>
    `
    el.style.rotate = !isNaN(heading) ? `${heading}deg` : '0deg'
  }

  const setAsCircle = () => {
    Object.assign(el.style, {
      width: '25px',
      height: '25px',
      backgroundColor: '#4285F4',
      borderRadius: '50%',
      boxShadow: '0 0 0 2px white',
      rotate: '0deg',
    })
    el.innerHTML = ''
  }

  const triggerPulse = () => {
    if (!el) return
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
      top: '-2px',
    })
    el.appendChild(pulse)
    setTimeout(() => pulse.remove(), 1500)
  }

  const handleGeoError = (err) => {
    if (!err) return
    if (err.code === 1) setCustomMessage('Доступ до геолокації заборонений')
    else if (err.code === 2) setCustomMessage('Неможливо визначити позицію')
    else if (err.code === 3) setCustomMessage('Не вдалось вчасно визначити геопозицію')
    else setCustomMessage('Помилка геолокації')
  }

  const updatePosition = ({ coords }) => {
    const pos = { lat: coords.latitude, lng: coords.longitude }
    rememberPosition(pos, coords.heading)
    moveMarkerToPosition(pos, coords.heading)
  }

  const showUserPosition = () => {
    const map = mapRef.value
    if (!navigator.geolocation || !map || !google?.maps?.marker || isTweening.value) return

    navigator.geolocation.getCurrentPosition(updatePosition, handleGeoError, {
      enableHighAccuracy: true,
      timeout: 5000,
      maximumAge: 10000,
    })
  }

  const getUserPosition = (options = {}) => {
    const {
      enableHighAccuracy = true,
      timeout = 5000,
      maximumAge = 0,
    } = options

    return new Promise((resolve) => {
      if (!navigator.geolocation) {
        setCustomMessage('Геолокація не підтримується')
        resolve(null)
        return
      }

      navigator.geolocation.getCurrentPosition(
        ({ coords }) => {
          const position = { lat: coords.latitude, lng: coords.longitude }
          rememberPosition(position, coords.heading)
          resolve(position)
        },
        (err) => { handleGeoError(err); resolve(null) }, 
        { enableHighAccuracy, timeout, maximumAge }
      )
    })
  }

  const showKnownPosition = (position, heading = null) => {
    if (!position || typeof position.lat !== 'number' || typeof position.lng !== 'number') return false
    rememberPosition(position, heading)
    return moveMarkerToPosition(position, heading)
  }

  const getLastKnownPosition = (maxAge = 15000) => {
    if (!lastKnownPosition) return null
    if (Date.now() - lastKnownAt > maxAge) return null
    return {
      position: lastKnownPosition,
      heading: lastKnownHeading
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
    el = null
  }

  return {
    showUserPosition,
    showKnownPosition,
    getUserPosition,
    getLastKnownPosition,
    triggerPulse,
    stop,
  }
}
