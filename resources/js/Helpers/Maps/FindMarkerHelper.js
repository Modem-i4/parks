import { ref } from 'vue'
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
      parkStore.selectedMarker = marker
    } else {
      await setViewToParkMarker(parkStore, marker)
    }
  }

  return {
    search,
    errorMessage,
    loading,
    findMarker,
    showMarker
  }
}
