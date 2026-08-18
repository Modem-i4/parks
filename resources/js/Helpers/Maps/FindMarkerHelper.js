import { ref, watch } from 'vue'
import axios from 'axios'
import { setViewToParkMarker } from '@/Helpers/Maps/SetParkView'

export function normalizeMarkerNumber(value) {
  const number = String(value ?? '').trim()
  const match = number.toLocaleUpperCase('uk-UA').match(/^([АМШ])[\s-]*0*(\d+)$/u)

  if (!match) return number

  const width = match[1] === 'Ш' ? 6 : 5
  return `${match[1]}${match[2].padStart(width, '0')}`
}

export function splitMarkerNumbers(value) {
  return value
    .replace(/([аАмМшШ])[\s-]*(\d+)/gu, '$1$2')
    .split(/[,;\s]+/)
    .map(normalizeMarkerNumber)
    .filter(Boolean)
}

export function useFindMarker(parkStore) {
  const search = ref('')
  const errorMessage = ref('')
  const loading = ref(false)

  async function findMarker() {
    const inventoryNumber = normalizeMarkerNumber(search.value)

    if (!inventoryNumber) {
      errorMessage.value = 'Введіть інвентарний номер або номер бірки'
      return null
    }

    loading.value = true
    errorMessage.value = ''

    try {
      const marker = await fetchMarker(inventoryNumber)

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

  async function findMarkers(inventoryNumbers) {
    const uniqueNumbers = [...new Set(inventoryNumbers.map(normalizeMarkerNumber).filter(Boolean))]

    if (!uniqueNumbers.length) {
      errorMessage.value = 'Введіть інвентарний номер або номер бірки'
      return []
    }

    loading.value = true
    errorMessage.value = ''

    try {
      const results = await Promise.allSettled(
        uniqueNumbers.map(async inventoryNumber => ({
          inventoryNumber,
          marker: await fetchMarker(inventoryNumber)
        }))
      )
      const markers = []
      const missingNumbers = []
      let hasLoadingError = false

      results.forEach((result, index) => {
        if (result.status === 'rejected') {
          if (result.reason?.response?.status === 404) {
            missingNumbers.push(uniqueNumbers[index])
          } else {
            hasLoadingError = true
          }
          return
        }

        if (result.value.marker) {
          markers.push(result.value.marker)
        } else {
          missingNumbers.push(result.value.inventoryNumber)
        }
      })

      if (hasLoadingError) {
        errorMessage.value = 'Помилка завантаження деяких маркерів'
      } else if (missingNumbers.length) {
        errorMessage.value = `Не знайдено: ${missingNumbers.join(', ')}`
      }

      return markers
    } finally {
      loading.value = false
    }
  }

  async function fetchMarker(inventoryNumber) {
    const response = await axios.get(
      `/api/markers/inv/${encodeURIComponent(inventoryNumber)}`
    )
    return response.data
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
    findMarkers,
    showMarker
  }
}
