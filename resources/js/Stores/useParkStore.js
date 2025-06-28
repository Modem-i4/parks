import { defineStore } from 'pinia'
import { ref, shallowRef, watch } from 'vue'

export const useParkStore = defineStore('park', () => {
  const isSingleParkView = ref(false)
  const selectedMarker = ref(null)
  const markers = ref([])
  const showPanel = ref(false)

  const map = shallowRef(null)
  const mapElement = ref(null)
  const defaultCenter = { lat: 48.918, lng: 24.7137 }

  // Actions
  function setIsSingleParkView(value) {
    isSingleParkView.value = value
  }

  function setSelectedMarker(marker) {
    selectedMarker.value = marker
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

  watch(
    () => selectedMarker.value,
    (selectedMarker) => selectedMarker && (showPanel.value = true)
  )

  return {
    // State
    isSingleParkView,
    selectedMarker,
    markers,
    showPanel,
    map,
    mapElement,
    defaultCenter,

    // Actions
    setIsSingleParkView,
    setSelectedMarker,
    setMarkers,
    setMap,
    setMapElement,
    setShowPanel
  }
})
