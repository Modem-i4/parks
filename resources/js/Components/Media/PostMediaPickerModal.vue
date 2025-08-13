<template>
  <Teleport :to="overlaySlotExists ? overlaySelector : 'body'">
    <div class="fixed inset-0 bg-black/50 z-[100] flex justify-center items-center px-2">
      <div class="bg-white w-full max-w-4xl rounded-lg shadow-lg overflow-hidden flex flex-col h-[90vh]">
        <div class="p-4 border-b flex justify-between items-start">
          <h2 class="text-lg font-semibold">Обрати зображення</h2>
          <button @click="cancel" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="flex-1 flex flex-col gap-4 p-4 overflow-y-auto">
          <div class="min-h-0 overflow-y-auto flex-[1]">
            <MediaUploader :type="type" @uploaded="fetchLibrary" />
            <MediaLibraryGrid
              :library="library"
              :selected="selectedArr"
              :multiple="false"
              @toggle="toggleSelect"
            />
          </div>

          <div class="flex items-center justify-between border rounded-lg p-3" v-if="selected">
            <div class="flex items-center gap-3">
              <img :src="selected.file_path" alt="" class="w-16 h-16 object-cover rounded" />
              <div class="text-sm text-gray-700 truncate max-w-[45ch]">
                {{ selected.file_path }}
              </div>
            </div>
            <button class="text-sm text-red-600 hover:text-red-700" @click="clearSelected">Очистити</button>
          </div>
        </div>

        <div class="p-4 border-t flex justify-between items-center">
          <button @click="cancel" class="text-sm text-gray-500 hover:text-black">Скасувати</button>
          <button
            @click="save"
            class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded disabled:opacity-50"
            :disabled="!selected"
          >
            Зберегти
          </button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import MediaLibraryGrid from './MediaLibraryGrid.vue'
import MediaUploader from './MediaUploader.vue'

const props = defineProps({
  type: { type: String, default: 'image' },
  onClose: Function,
})

const emit = defineEmits(['saved'])

const library = ref([]) 
const selected = ref(null) 
const selectedArr = computed(() => selected.value ? [{ media_library_id: selected.value.id }] : [])

const fetchLibrary = async () => {
  const res = await axios.get('/api/media-library', { params: { type: props.type } })
  library.value = res.data ?? []
}

function toggleSelect(mediaFile) {
  if (selected.value && selected.value.id === mediaFile.id) {
    selected.value = null
  } else {
    selected.value = { id: mediaFile.id, file_path: mediaFile.file_path }
  }
}
function clearSelected() {
  selected.value = null
}

function save() {
  if (!selected.value) return
  emit('saved', { file_path: selected.value.file_path })
  cancel()
}
function cancel() {
  props.onClose?.()
}

const overlaySlotExists = ref(false)
const overlaySelector = 'dialog[open] .overlay-modal-slot'
onMounted(() => {
  overlaySlotExists.value = !!document.querySelector(overlaySelector)
  fetchLibrary()
})
</script>
