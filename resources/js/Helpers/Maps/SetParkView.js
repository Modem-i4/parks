export function setParkView(parkStore, page, contentMode=null) {
  if (page === 'single' && !parkStore.isSingleParkView) {
    parkStore.showPanel = false
    parkStore.selectedPark = parkStore.selectedMarker
    parkStore.selectedMarker = null
    parkStore.singleParkContentMode = contentMode ?? 'infrastructure'
    parkStore.markers = []
    parkStore.isSingleParkView = true
    window.history.pushState(null, '', `/parks/${parkStore.selectedPark?.id}`)

  } else if (page === 'parks') {
    parkStore.selectedMarker = null
    parkStore.selectedPark = null
    parkStore.showPanel = false
    parkStore.markers = []
    parkStore.isSingleParkView = false
    window.history.pushState(null, '', `/parks`)
  }
}

import { isTweening } from '@/Helpers/Maps/MapHelper'
export async function setViewToParkMarker(parkStore, marker) {
  parkStore.selectedMarker = null
  parkStore.selectedPark = marker.park
  if(parkStore.isSingleParkView) {
    parkStore.isSingleParkView = false
    await new Promise(resolve => setTimeout(resolve, 1000)) // time for optional particular zoom-out
  }
  parkStore.isSingleParkView = true
  while (isTweening.value || parkStore.markerStates.isLoading) {
    await new Promise(resolve => setTimeout(resolve, 100))
  }
  parkStore.selectedMarker = marker
  window.history.pushState(null, '', `/parks/${parkStore.selectedPark?.id}`)
}