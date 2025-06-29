import { defineStore } from 'pinia'
import { ref, shallowRef, watch } from 'vue'

export const useParkStore = defineStore('park', () => {
  const isSingleParkView = ref(false)
  const selectedMarker = ref(null)
  const selectedPark = ref(null)
  const markers = ref([])
  const showPanel = ref(false)

  const map = shallowRef(null)
  const mapElement = ref(null)
  const lockMapChange = ref(false)
  const defaultCenter = { 
    lat: parseFloat(import.meta.env.VITE_DEFAULT_LAT), 
    lng: parseFloat(import.meta.env.VITE_DEFAULT_LNG) 
  }

  // Actions
  function setIsSingleParkView(value) {
    isSingleParkView.value = value
  }

  function setSelectedMarker(marker) {
    selectedMarker.value = marker
  }

  function setSelectedPark(marker) {
    selectedPark.value = marker
  }

  function setMarkers(markerList) {
    markers.value = markerList
  }

  function setMap(mapInstance) {
    map.value = mapInstance
  }

  function setMapElement(element) {
    mapElement.value = element
  }

  function setShowPanel(value) {
    showPanel.value = value
  }

  function setLockMapChange(value) {
    lockMapChange.value = value
  }

  watch(
    () => selectedMarker.value,
    (selectedMarker) => selectedMarker && (showPanel.value = true)
  )

  return {
    // State
    isSingleParkView,
    selectedMarker,
    selectedPark,
    markers,
    showPanel,
    map,
    mapElement,
    defaultCenter,
    lockMapChange,

    // Actions
    setIsSingleParkView,
    setSelectedMarker,
    setSelectedPark,
    setMarkers,
    setMap,
    setMapElement,
    setShowPanel,
    setLockMapChange,
  }
})
