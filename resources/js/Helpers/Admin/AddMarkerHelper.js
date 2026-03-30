import { useUserLocationMarker } from '@/Helpers/Maps/ShowGeolocationHelper'
import { toRef } from 'vue'

let newMarker

export function useAddMarkerHelper(parkStore) {
  const { getUserPosition, showKnownPosition, getLastKnownPosition } = useUserLocationMarker(
    toRef(parkStore, 'map'),
    toRef(parkStore, 'mapCustomMessage')
  )

  function addMarker() {
    const map = parkStore.map
    if (!map) return

    const screenCenter = map.getCenter()?.toJSON()
    if (!screenCenter) return

    newMarker = {
      id: Date.now(),
      name: 'Новий маркер',
      coordinates: [screenCenter.lng, screenCenter.lat],
      type: 'custom',
      green: null,
      infrastructure: null,
      media: [],
      isDraft: true,
    }

    parkStore.selectedMarker = newMarker
    const cachedGeo = getLastKnownPosition()
    if (cachedGeo) {
      applyDraftPositionIfStillActive(newMarker.id, cachedGeo.position, cachedGeo.heading)
    }
    void moveDraftMarkerToUserPosition(newMarker)
  }

  async function moveDraftMarkerToUserPosition(markerDraft) {
    const position = await getUserPosition({
      enableHighAccuracy: true,
      timeout: 2000,
      maximumAge: 15000,
    })

    if (!position) return

    applyDraftPositionIfStillActive(markerDraft.id, position)
  }

  function applyDraftPositionIfStillActive(markerId, position, heading = null) {
    const map = parkStore.map
    const activeMarker = parkStore.selectedMarker
    if (!map || !activeMarker?.isDraft || activeMarker.id !== markerId) return

    const bounds = map.getRestriction()?.latLngBounds
    if (bounds && !bounds.contains(position)) return

    parkStore.selectedMarker = {
      ...activeMarker,
      coordinates: [position.lng, position.lat],
    }

    showKnownPosition(position, heading)
  }

  function addMarkerFinished() {
    parkStore.selectedMarker = null
  }
  return { addMarker, addMarkerFinished }
}
