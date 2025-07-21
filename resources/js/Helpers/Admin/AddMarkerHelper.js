import { useUserLocationMarker } from '@/Helpers/Maps/ShowGeolocationHelper'
import { toRef } from 'vue'
import { createDraggableMarker } from '@/Helpers/Admin/CreateDraggableMarker'
import { distanceInMeters } from '../Maps/MapHelper'

let newMarker
let googleMapMarker = null

export function useAddMarkerHelper(parkStore) {
  const { getUserPosition, showUserPosition, triggerPulse } = useUserLocationMarker(toRef(parkStore, 'map'))

  async function addMarker() {
    const map = parkStore.map
    showUserPosition()
    let position = await getUserPosition()
    const screenCenter = map.getCenter().toJSON()
    if (!map) return 
    if(!position || distanceInMeters(position, screenCenter) > 500)
      position = screenCenter

    triggerPulse()

    newMarker = {
      id: Date.now(),
      name: 'Новий маркер',
      coordinates: [position.lng, position.lat],
      type: 'custom',
      green: null,
      infrastructure: null,
      media: [],
      isDraft: true,
    }

    parkStore.selectedMarker = newMarker
    parkStore.showPanel = true

    googleMapMarker = await createDraggableMarker({
      map,
      position,
      iconUrl: '/storage/img/icons/new-marker.svg',
      onDrag: (latLng) => {
        newMarker.coordinates = [latLng.lng(), latLng.lat()]
      }
    })
    googleMapMarker.addListener('click', () => parkStore.selectedMarker = newMarker)
  }

  return { addMarker }
}
