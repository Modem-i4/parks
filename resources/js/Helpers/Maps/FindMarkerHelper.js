import { ref, watch } from 'vue'
import axios from 'axios'
import { setViewToParkMarker } from '@/Helpers/Maps/SetParkView'

export function useFindMarker(parkStore) {
  const search = ref('')
  const errorMessage = ref('')
  const loading = ref(false)

  async function findMarker() {
    const inventoryNumber = search.value.trim()

    if (!inventoryNumber) {
      errorMessage.value = 'Введіть інвентарний номер'
      return null
    }

    loading.value = true
    errorMessage.value = ''

    try {
      const response = await axios.get(`/api/markers/inv/${encodeURIComponent(inventoryNumber)}`)
      const marker = response.data

      if (!marker) {
        errorMessage.value = 'Маркер не знайдено'
        return null
      }

      return marker
    } catch {
      errorMessage.value = 'Помилка завантаження'
      return null
    } finally {
      loading.value = false
    }
  }

  async function showMarker(marker) {
    if (String(marker.park_id) === String(parkStore.selectedPark?.id)) {
      if (!hasMarker(marker.id)) {
        parkStore.activeMarkerPreset = 'all'
        parkStore.markerFilterRevision++
        await waitForMarkerToLoad(marker.id)
      }

      parkStore.selectedMarker = marker
    } else {
      await setViewToParkMarker(parkStore, marker)
    }
  }

  function hasMarker(markerId) {
    return parkStore.markers.some(
      marker => String(marker.id) === String(markerId)
    )
  }

  function waitForMarkerToLoad(markerId) {
    return new Promise(resolve => {
      const stop = watch(
        [
          () => hasMarker(markerId),
          () => parkStore.markerStates.isLoading
        ],
        ([isLoaded, isLoading]) => {
          if (!isLoaded && isLoading) return

          stop()
          resolve()
        },
        { flush: 'post' }
      )
    })
  }

  return {
    search,
    errorMessage,
    loading,
    findMarker,
    showMarker
  }
}
