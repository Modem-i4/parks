<template>
  <div class="space-y-4">
    <div>
      <div class="text-sm text-gray-600 text-center">Імпортувати маркери</div>
      <input
        type="file"
        accept=".geojson,.json,.csv,.zip,.shp"
        @change="onFileChange"
        class="block w-full text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
      />
      <p v-if="importFileName" class="text-xs text-gray-600">
        Обрано: <span class="font-medium">{{ importFileName }}</span>
      </p>
      <p v-if="importError" class="text-xs text-red-600">{{ importError }}</p>
    </div>

    <div v-if="importReady" class="rounded border p-3 bg-gray-50 space-y-2">
      <template v-if="previewLoading">
        <div class="flex items-center gap-2">
          <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4A4 4 0 008 12H4z"/>
          </svg>
          <span class="text-sm text-gray-700">Підрахунок...</span>
        </div>
        <div class="h-2 bg-gray-200 rounded overflow-hidden">
          <div class="h-full w-1/2 animate-pulse bg-gray-300"></div>
        </div>
      </template>

      <template v-else-if="previewDone">
        <div class="text-sm font-semibold">Результат підрахунку</div>
        <div class="text-sm">Буде додано: <span class="font-semibold">{{ previewStats.will_create }}</span></div>
        <div class="text-sm">Буде оновлено: <span class="font-semibold">{{ previewStats.will_update }}</span></div>

        <div class="pt-2 space-y-1">
          <div class="text-xs text-gray-600">Режим імпорту</div>
          <div class="flex justify-center">
            <Switch
              v-model="mode"
              :options="[
                { value: 'create', label: 'Тільки додати', color: 'blue' },
                { value: 'create_update', label: 'Додати і оновити', color: 'green' },
                { value: 'update', label: 'Тільки оновити', color: 'red' },
              ]"
            />
          </div>
        </div>

        <div v-if="previewErrors.length" class="text-xs text-red-600">
          <div class="font-semibold">Помилки / попередження:</div>
          <ul class="list-disc ml-5">
            <li v-for="(err, i) in previewErrors" :key="i">{{ formatPreviewError(err) }}</li>
          </ul>
        </div>
      </template>
    </div>

    <div class="flex justify-end gap-3 pt-2">
      <SecondaryButton @click="$emit('cancel')">Скасувати</SecondaryButton>
      <PrimaryButton
        :disabled="!importReady || loading || previewLoading || !previewDone"
        @click="onImportClick"
      >
        {{ loading ? 'Імпортуємо...' : 'Імпортувати' }}
      </PrimaryButton>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import Switch from '../Custom/Switch.vue'

const props = defineProps({ loading: Boolean })
const emit  = defineEmits(['done','cancel'])

const importFile = ref(null)
const importFileName = ref('')
const importReady = ref(false)
const importError = ref('')

const previewLoading = ref(false)
const previewDone = ref(false)
const previewResult = ref(null)
const previewStats = ref({ will_create: 0, will_update: 0 })
const mode = ref('create_update')

const previewErrors = computed(() =>
  Array.isArray(previewResult.value?.errors) ? previewResult.value.errors : []
)

const previewController = ref(null)

function onFileChange(e) {
  importError.value = ''
  previewDone.value = false
  previewResult.value = null
  previewStats.value = { will_create: 0, will_update: 0 }
  mode.value = 'create_update'

  const file = e.target.files?.[0]
  if (!file) {
    importFile.value = null
    importFileName.value = ''
    importReady.value = false
    return
  }
  importFile.value = file
  importFileName.value = file.name
  importReady.value = true

  runPreview()
}

function runPreview() {
  if (!importReady.value || !importFile.value) return

  if (previewController.value) {
    try { previewController.value.abort() } catch {}
  }
  previewController.value = new AbortController()

  previewLoading.value = true
  const fd = new FormData()
  fd.append('file', importFile.value)

  axios.post('/api/markers/import/preview', fd, { signal: previewController.value.signal })
    .then(({ data }) => {
      const res = data?.results ?? data
      if (res?.ok) {
        previewResult.value = res
        previewStats.value  = {
          will_create: Number(res?.will_create ?? res?.created ?? 0),
          will_update: Number(res?.will_update ?? res?.updated ?? 0),
        }
        previewDone.value = true
      } else {
        importError.value = data?.error || 'Помилка підрахунку'
      }
    })
    .catch((e) => {
      if (e?.name !== 'CanceledError' && e?.name !== 'AbortError') {
        console.error('Preview error:', e)
        importError.value = 'Помилка підрахунку. Перевірте файл.'
      }
    })
    .finally(() => {
      previewLoading.value = false
    })
}

function onImportClick() {
  if (!importReady.value || !importFile.value || !previewDone.value) return
  importError.value = ''

  const fd = new FormData()
  fd.append('file', importFile.value)
  fd.append('mode', mode.value)

  axios.post('/api/markers/import', fd)
    .then(({ data }) => {
      const res = data?.results ?? data
      if (res?.ok) {
        emit('done')
      } else {
        importError.value = data?.error || 'Помилка імпорту'
      }
    })
    .catch((e) => {
      console.error('Import error:', e)
      importError.value = 'Помилка імпорту. Перевірте формат файлу.'
    })
}
</script>
