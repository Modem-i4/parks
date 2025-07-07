export function setParkView(parkStore, page, contentMode=null) {
  if (page === 'single' && !parkStore.isSingleParkView) {
    parkStore.showPanel = false
    parkStore.selectedPark = parkStore.selectedMarker
    parkStore.selectedMarker = null
    parkStore.singleParkContentMode = contentMode ?? 'infrastructure'
    parkStore.isSingleParkView = true
    window.history.pushState(null, '', `/parks/${parkStore.selectedPark?.id}`)

  } else if (page === 'parks') {
    parkStore.selectedMarker = null
    parkStore.selectedPark = null
    parkStore.showPanel = false
    parkStore.isSingleParkView = false
    window.history.pushState(null, '', `/parks`)
  }
}
