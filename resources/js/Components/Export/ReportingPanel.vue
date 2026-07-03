<template>
  <div class="space-y-4">
    <div>
      <div class="text-sm text-gray-600 text-center">Створити звіт</div>
      <div class="flex justify-center">
        <Switch
          v-model="reportingScope"
          :options="[
            { value: 'picked', label: '🎯 Обрано у властивостях', color: 'green' },
            { value: 'filtered', label: '🗄️ Відфільтровано на мапі', color: 'blue' },
          ]"
        />
      </div>

      <div class="px-2">
        <template v-if="reportingScope === 'picked'">
          <div v-if="pickedObjectMarkers.length === 0" class="text-sm text-red-500 italic flex justify-center">
            (немає вибраних маркерів)
          </div>
          <div v-else class="max-h-[15rem] overflow-y-auto">
            <h3 class="font-semibold text-gray-800 text-center">Обрані маркери</h3>
            <PanelHeader
              v-for="marker in pickedObjectMarkers"
              :key="marker.id"
              :title="`${getMarkerTitle(marker)} (${marker.green?.inventory_number ?? '—'})`"
              :subtitle="typeUkr[marker.type]"
              :icon="marker.icon?.file_path"
              variant="sm"
              class="bg-gray-100 border p-0 rounded-full px-5 my-1"
            >
              <template #right>
                <GreenStateIndicator :green="marker.green" />
                <button
                  class="ml-2 text-gray-400 hover:text-red-600 text-lg font-bold leading-none"
                  @click="removePickedMarker(marker)"
                >×</button>
              </template>
            </PanelHeader>
          </div>
        </template>

        <div v-if="reportingScope === 'filtered' || pickedObjectMarkers.length > 0"
             class="flex items-center justify-center gap-3 mt-2">
          <p class="text-sm font-medium text-blue-700">
            Обрано {{ filteredCount }} маркер{{ filteredCount === 1 ? '' : filteredCount > 0 && filteredCount < 5 ? 'а' : 'ів' }}.
          </p>
          <SecondaryButton
            class="py-0.5 text-blue-600"
            v-if="pickedObjectMarkers.length > 3"
            @click="clearPickedMarkers"
          >
            Очистити вибір
          </SecondaryButton>
        </div>
      </div>
    </div>

    <div class="rounded border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-600">
      Буде сформовано друкований звіт по поточній вибірці маркерів.
    </div>

    <div
      v-if="pendingReport"
      class="flex items-center justify-between gap-3 rounded border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800"
    >
      <span class="font-medium">Звіт готовий.</span>
      <PrimaryButton class="py-0.5" @click="openPreparedReport">
        Відкрити звіт
      </PrimaryButton>
    </div>

    <p v-if="errorMessage" class="text-sm text-red-600 text-center">
      {{ errorMessage }}
    </p>

    <div class="flex justify-end gap-3 pt-2">
      <SecondaryButton @click="$emit('cancel')">Скасувати</SecondaryButton>
      <PrimaryButton :disabled="reportDisabled || loading || isPreparing" @click="onCreateReport">
        {{ loading || isPreparing ? 'Готуємо…' : 'Створити звіт' }}
      </PrimaryButton>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onUnmounted } from 'vue'
import axios from 'axios'
import { useParkStore } from '@/Stores/useParkStore'
import Switch from '@/Components/Custom/Switch.vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import PanelHeader from '@/Components/Custom/PanelHeader.vue'
import { getMarkerTitle, typeUkr } from '@/Helpers/Maps/GetMarkerTitle'
import GreenStateIndicator from '@/Components/Markers/View/GreenStateIndicator.vue'
import { printReport } from '@/Helpers/Print/PrintReport'

const props = defineProps({ loading: Boolean })
const emit = defineEmits(['done', 'cancel'])

const parkStore = useParkStore()

const reportingScope = ref('filtered')
const isPreparing = ref(false)
const errorMessage = ref('')
const pendingReport = ref(null)

const pickedObjectMarkers = computed(() => parkStore.pickedMarkers.filter(isObjectMarker))
const filteredMarkerCount = computed(() => {
  if (parkStore.isSingleParkView) return parkStore.markers.filter(isObjectMarker).length

  return Object.values(parkStore.markerCountsByPark || {})
    .reduce((total, count) => total + Number(count || 0), 0)
})
const filteredCount = computed(() => reportingScope.value === 'picked'
  ? pickedObjectMarkers.value.length
  : filteredMarkerCount.value)
const reportDisabled = computed(() => filteredCount.value === 0)

function isObjectMarker(marker) {
  return marker?.type && marker.type !== 'park'
}

function cloneFilters(value) {
  return JSON.parse(JSON.stringify(value || {}))
}

function filteredPayload() {
  if (parkStore.isSingleParkView) {
    return { markers: parkStore.markers.filter(isObjectMarker).map(marker => marker.id) }
  }

  return {
    filters: cloneFilters(parkStore.savedMarkerFilters || { green: {}, infrastructure: {} }),
  }
}

function reportPayload() {
  return reportingScope.value === 'picked'
    ? { markers: pickedObjectMarkers.value.map(marker => marker.id) }
    : filteredPayload()
}

function removePickedMarker(marker) {
  parkStore.pickedMarkers = parkStore.pickedMarkers.filter(m => m.id !== marker.id)
}
function clearPickedMarkers() {
  parkStore.pickedMarkers = []
}

function clearPendingReport() {
  pendingReport.value?.revoke?.()
  pendingReport.value = null
}

function openPreparedReport() {
  if (!pendingReport.value) return

  errorMessage.value = ''

  if (!pendingReport.value.open()) {
    errorMessage.value = 'Не вдалося відкрити вікно звіту. Дозвольте спливаючі вікна для цього сайту і натисніть «Відкрити звіт» ще раз.'
    return
  }

  pendingReport.value = null
  emit('done')
}

async function onCreateReport() {
  if (reportDisabled.value) return
  isPreparing.value = true
  errorMessage.value = ''
  clearPendingReport()

  try {
    const [{ data }, { data: parks }] = await Promise.all([
      axios.post('/api/markers/report-data', reportPayload()),
      axios.get('/api/parks'),
    ])

    const report = await printReport(data.markers, {
      title: 'Звіт по насадженнях',
      fallbackPark: parkStore.selectedPark,
      parks,
    })

    if (report?.opened) {
      emit('done')
      return
    }

    if (report?.open) {
      pendingReport.value = report
      return
    }

    errorMessage.value = 'Не вдалося сформувати звіт.'
  } catch (e) {
    console.error('Report error:', e)
    errorMessage.value = e?.message || 'Не вдалося сформувати звіт.'
  } finally {
    isPreparing.value = false
  }
}

onUnmounted(clearPendingReport)
</script>
