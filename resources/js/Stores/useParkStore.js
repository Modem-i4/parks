import { defineStore } from 'pinia'
import { ref, shallowRef, watch } from 'vue'

export const useParkStore = defineStore('park', () => {
  const isSingleParkView = ref(false)
  const selectedMarker = ref(null)
  const selectedPark = ref(null)
  const markers = ref([])
  const showPanel = ref(false)
  const lockMapChange = ref(false)
  const defaultCenter = { 
    lng: parseFloat(import.meta.env.VITE_DEFAULT_LNG),
    lat: parseFloat(import.meta.env.VITE_DEFAULT_LAT)
  }

  const map = shallowRef(null)
  const mapElement = ref(null)

  function $reset() {
    isSingleParkView.value = false
    selectedMarker.value = null
    selectedPark.value = null
    markers.value = []
    showPanel.value = false
    lockMapChange.value = false
    map.value = null
    mapElement.value = null
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
    $reset,
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
