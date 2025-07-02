export function setParkView(parkStore, mode) {
  if(!parkStore.lockMapChange) {
    if (mode === 'single' && !parkStore.isSingleParkView) {
      parkStore.showPanel = false
      parkStore.selectedPark = parkStore.selectedMarker
      parkStore.selectedMarker = null
      parkStore.isSingleParkView = true
      window.history.pushState(null, '', `/parks/${parkStore.selectedPark?.id}`)

    } else if (mode === 'parks') {
      parkStore.selectedMarker = null
      parkStore.selectedPark = null
      parkStore.showPanel = false
      parkStore.lockMapChange = true
      parkStore.isSingleParkView = false
      window.history.pushState(null, '', `/parks`)
    }
  }
}
