import axios from 'axios'

export function setParkView(parkStore, page, contentMode=null) {
  if (page === 'single' && !parkStore.isSingleParkView) {
    return openSingleParkView(parkStore, contentMode)
  } else if (page === 'parks') {
    parkStore.selectedMarker = null
    parkStore.selectedPark = null
    parkStore.showPanel = false
    parkStore.markers = []
    parkStore.isSingleParkView = false
  }
}

async function openSingleParkView(parkStore, contentMode) {
  const parkId = parkStore.selectedMarker?.id ?? parkStore.selectedPark?.id
  if (!parkId) return

  parkStore.showPanel = false

  const response = await axios.get(`/api/parks/${parkId}`)

  parkStore.selectedPark = response.data
  parkStore.selectedMarker = null
  parkStore.singleParkContentMode = contentMode ?? 'green'
  parkStore.markers = []
  parkStore.isSingleParkView = true
}

import { isTweening } from '@/Helpers/Maps/MapHelper'
import { watch } from 'vue'
import { getMarkerTitle } from './GetMarkerTitle'
export async function setViewToParkMarker(parkStore, marker) {
  parkStore.selectedMarker = null
  let targetMarker = marker
  let markerParkId = targetMarker.park_id ?? targetMarker.park?.id

  if (!markerParkId) {
    const response = await axios.get(`/api/markers/${targetMarker.id}`)
    targetMarker = response.data
    markerParkId = targetMarker.park_id ?? targetMarker.park?.id
  }

  const needsFullPark = String(parkStore.selectedPark?.id) !== String(markerParkId)
    || !Array.isArray(parkStore.selectedPark?.plots)

  if (needsFullPark) {
    const response = await axios.get(`/api/parks/${markerParkId}`)
    parkStore.selectedPark = response.data
  }
  if(parkStore.isSingleParkView) {
    parkStore.isSingleParkView = false
    await new Promise(resolve => setTimeout(resolve, 1000)) // time for optional particular zoom-out
  }
  parkStore.isSingleParkView = true
  while (isTweening.value || parkStore.markerStates.isLoading) {
    await new Promise(resolve => setTimeout(resolve, 100))
  }
  parkStore.selectedMarker = targetMarker
}

export function initParkRouteWatcher(parkStore) {
  watch(
    () => [
      parkStore.isSingleParkView,
      parkStore.selectedPark?.id, 
      parkStore.selectedMarker?.id,
    ],
    () => {
      updateParkRoute(parkStore) 
    },
    { immediate: true } 
  )
}

function updateParkRoute(parkStore) {
  if(!parkStore || parkStore.selectedMarker?.isDraft) return;
  const route = getRoute(parkStore)
  window.history.pushState(null, '', route)
  const appName = import.meta.env.VITE_APP_NAME
  const title = `${getPageTitle(parkStore)} – ${appName}`
  document.title = title
}

function getRoute(parkStore) {
  if (parkStore.isSingleParkView) {
    return `${getParkRoute(parkStore)}${getMarkerRoute(parkStore)}`
  }
  return getParkRoute(parkStore)
}

function getParkRoute(parkStore) {
  return `/parks/${parkStore.selectedPark?.id || parkStore.selectedMarker?.id || ''}`
}
function getMarkerRoute(parkStore) {
  return `/m/${parkStore.selectedMarker?.id ?? ''}`
}

function getPageTitle(parkStore) {
  const m = parkStore.selectedMarker
  const p = parkStore.selectedPark
  if(!m) {
    if(!p) return "Парки"
    return p.name
  }
  if(m.type === 'park') return m.name
  return getMarkerTitle(m)
}
