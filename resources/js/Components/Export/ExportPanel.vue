<template>
  <div class="space-y-4">
    <div>
      <div class="text-sm text-gray-600 text-center">Експортувати маркери</div>
      <div class="flex justify-center">
        <Switch
          v-model="exportScope"
          :options="[
            { value: 'picked', label: '🎯 Обрано у властивостях', color: 'green' },
            { value: 'filtered', label: '🗄️ Відфільтровано на мапі', color: 'blue' },
          ]"
        />
      </div>

      <div class="px-2">
        <template v-if="exportScope === 'picked'">
          <div v-if="parkStore.pickedMarkers.length === 0" class="text-sm text-red-500 italic flex justify-center">
            (немає вибраних маркерів)
          </div>
          <div v-else class="max-h-[15rem] overflow-y-auto">
            <h3 class="font-semibold text-gray-800 text-center">Обрані маркери</h3>
            <PanelHeader
              v-for="marker in parkStore.pickedMarkers"
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

        <div v-if="exportScope === 'filtered' || parkStore.pickedMarkers.length > 0"
             class="flex items-center justify-center gap-3 mt-2">
          <p class="text-sm font-medium text-blue-700">
            Обрано {{ filteredCount }} маркер{{ filteredCount === 1 ? '' : filteredCount > 0 && filteredCount < 5 ? 'а' : 'ів' }}.
          </p>
          <SecondaryButton
            class="py-0.5 text-blue-600"
            v-if="parkStore.pickedMarkers.length > 3"
            @click="clearPickedMarkers"
          >
            Очистити вибір
          </SecondaryButton>
        </div>
      </div>
    </div>

    <div>
      <div class="text-sm text-gray-600">Формат</div>
      <div class="flex flex-wrap gap-2">
        <label
          v-for="opt in exportFormats"
          :key="opt.value"
          class="inline-flex items-center gap-2 px-3 py-1.5 rounded border cursor-pointer"
          :class="exportFormat === opt.value ? 'border-blue-600 ring-2 ring-blue-200' : 'border-gray-300'"
        >
          <input type="radio" class="hidden" :value="opt.value" v-model="exportFormat" />
          <span class="text-sm font-medium">{{ opt.label }}</span>
        </label>
      </div>
    </div>

    <div class="flex justify-end gap-3 pt-2">
      <SecondaryButton @click="$emit('cancel')">Скасувати</SecondaryButton>
      <PrimaryButton :disabled="exportDisabled || loading" @click="onExport">
        {{ loading ? 'Готуємо…' : 'Експортувати' }}
      </PrimaryButton>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import { useParkStore } from '@/Stores/useParkStore'
import Switch from '@/Components/Custom/Switch.vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import PanelHeader from '@/Components/Custom/PanelHeader.vue'
import { getMarkerTitle, typeUkr } from '@/Helpers/Maps/GetMarkerTitle'
import GreenStateIndicator from '@/Components/Markers/View/GreenStateIndicator.vue'

const props = defineProps({ loading: Boolean })
const emit = defineEmits(['done', 'cancel'])

const parkStore = useParkStore()

const exportScope = ref('filtered')
const exportFormat = ref('geojson')
const exportFormats = [
  { value: 'geojson', label: 'GeoJSON' },
  { value: 'shapefile', label: 'Shapefile (.zip)' },
  { value: 'csv', label: 'CSV (Excel)' },
]

const targetMarkers = computed(() => {
  return exportScope.value === 'picked'
    ? parkStore.pickedMarkers
    : parkStore.markers
})
const filteredCount = computed(() => targetMarkers.value.length)
const exportDisabled = computed(() => filteredCount.value === 0)

function removePickedMarker(marker) {
  parkStore.pickedMarkers = parkStore.pickedMarkers.filter(m => m.id !== marker.id)
}
function clearPickedMarkers() {
  parkStore.pickedMarkers = []
}

async function onExport() {
  if (exportDisabled.value) return
  try {
    const ids = targetMarkers.value.map(m => m.id)
    const res = await axios.post('/api/markers/export', { markers: ids, format: exportFormat.value }, { responseType: 'blob' })
    const ext = exportFormat.value === 'shapefile' ? 'zip' : exportFormat.value
    const fileName = `parks_export_${new Date().toISOString().slice(0,19).replace(/[:T]/g,'-')}.${ext}`

    const url = URL.createObjectURL(new Blob([res.data]))
    const a = document.createElement('a')
    a.href = url
    a.download = fileName
    document.body.appendChild(a)
    a.click()
    a.remove()
    URL.revokeObjectURL(url)

    emit('done')
  } catch (e) {
    console.error('Export error:', e)
  }
}
</script>
