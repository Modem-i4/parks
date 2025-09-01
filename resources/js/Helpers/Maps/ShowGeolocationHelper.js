import { isTweening, tweenCameraTo } from "./MapHelper"

let marker = null
let watcherId = null
let el = null

export function useUserLocationMarker(mapRef, customMsgRef) {
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
    if (err.code === 1) customMsgRef.value = 'Доступ до геолокації заборонений'
    else if (err.code === 2) customMsgRef.value = 'Неможливо визначити позицію'
    else if (err.code === 3) customMsgRef.value = 'Не вдалось вчасно визначити геопозицію'
    else customMsgRef.value = 'Помилка геолокації'
  }

  const updatePosition = ({ coords }) => {
    const map = mapRef.value
    if (!map) return

    const pos = { lat: coords.latitude, lng: coords.longitude }
    const heading = coords.heading
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
    const bounds = map.getRestriction().latLngBounds
    if(!bounds) return
    if(bounds.contains(pos)) tweenCameraTo(map, pos)
    else customMsgRef.value = 'Ваша позиція не в межах мапи'
  }

  const showUserPosition = () => {
    const map = mapRef.value
    if (!navigator.geolocation || !map || !google?.maps?.marker || isTweening.value) return

    navigator.geolocation.getCurrentPosition(updatePosition, handleGeoError, {
      enableHighAccuracy: true,
    })

    if (!watcherId) {
      watcherId = navigator.geolocation.watchPosition(updatePosition, handleGeoError, {
        enableHighAccuracy: true,
      })
    }
  }

  const getUserPosition = () => {
    return new Promise((resolve, reject) => {
      if (!navigator.geolocation) {
        customMsgRef.value = 'Геолокація не підтримується'
        reject(new Error('Geolocation не підтримується'))
        return
      }

      navigator.geolocation.getCurrentPosition(
        ({ coords }) => resolve({ lat: coords.latitude, lng: coords.longitude }),
        (err) => { handleGeoError(err); resolve(null) }, 
        { enableHighAccuracy: true }
      )
    })
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
    getUserPosition,
    triggerPulse,
    stop,
  }
}
