<template>
  <Teleport :to="overlaySlotExists ? overlaySelector : 'body'">
    <div class="fixed inset-0 bg-black/50 z-[100] flex justify-center items-center px-2">
      <div class="bg-white w-full max-w-4xl rounded-lg shadow-lg overflow-hidden flex flex-col h-[90vh]">
        <div class="p-4 border-b flex justify-between items-start">
          <div>
            <h2 class="text-lg font-semibold">Обрати зображення</h2>
            <p class="text-xs text-gray-500">Для: {{ shortModel(modelType) }}#{{ modelId }} ({{ type }})</p>
          </div>
          <button @click="cancel" class="text-gray-400 hover:text-gray-600">✕</button>
        </div>

        <div class="flex-1 flex flex-col gap-4 p-4 overflow-y-auto">
          <div class="min-h-0 overflow-y-auto"
            :class="type === 'icon' ? 'flex-[4]' : 'flex-[2]'"
          >
            <MediaUploader :type="type" @uploaded="fetchLibrary" />
            <MediaLibraryGrid
              :library="library"
              :selected="selected"
              :multiple="isMultiple"
              @toggle="toggleSelect"
            />
          </div>
          <div class="flex-[1] min-h-0 overflow-y-auto">
            <MediaSelectedList
              :selected="selected"
              :multiple="isMultiple"
              :type="type"
              @remove="removeSelected"
              @reorder="updateOrder"
            />
          </div>
        </div>

        <div class="p-4 border-t flex justify-between items-center">
          <button @click="cancel" class="text-sm text-gray-500 hover:text-black">Скасувати</button>
          <button @click="save" class="text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-1 rounded">Зберегти</button>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import MediaLibraryGrid from './MediaLibraryGrid.vue';
import MediaSelectedList from './MediaSelectedList.vue';
import MediaUploader from './MediaUploader.vue';

const props = defineProps({
  type: { type: String, required: true }, // 'icon' or 'image'
  modelType: { type: String, required: true },
  modelId: { type: Number, required: true },
  onClose: Function
});

const emit = defineEmits(['saved']);

const library = ref([]);
const selected = ref([]);

const isMultiple = computed(() => props.type === 'image');

const fetchLibrary = async () => {
  const res = await axios.get('/api/media-library', {
        params: { type: props.type }
    });
    library.value = res.data
};

const fetchSelected = async () => {
  const res = await axios.get('/api/media', {
    params: {
      model_type: props.modelType,
      model_id: props.modelId,
      type: props.type
    }
  });
  selected.value = res.data
};

const toggleSelect = (mediaFile) => {
  if (!isMultiple.value) {
    selected.value = [{
      media_library_id: mediaFile.id,
      file_path: mediaFile.file_path,
      model_type: props.modelType,
      model_id: props.modelId,
      order: 0
    }];
    return;
  }

  const exists = selected.value.find(item => item.media_library_id === mediaFile.id);
  if (exists) {
    selected.value = selected.value.filter(i => i.media_library_id !== mediaFile.id);
  } else {
    selected.value.push({
      media_library_id: mediaFile.id,
      file_path: mediaFile.file_path,
      model_type: props.modelType,
      model_id: props.modelId,
      order: selected.value.length
    });
  }
};

const removeSelected = (mediaFileId) => {
  selected.value = selected.value.filter(i => i.media_library_id !== mediaFileId);
};

const updateOrder = (newOrder) => {
  selected.value = newOrder.map((item, index) => ({
    ...item,
    order: index
  }));
};

const save = async () => {
  await axios.post('/api/media/sync', {
    model_type: props.modelType,
    model_id: props.modelId,
    type: props.type,
    media: selected.value.map(m => ({
      description: m.description ?? '',
      media_library_id: m.media_library_id,
      order: m.order,
    }))
  });

  emit('saved');
  cancel();
};

const cancel = () => {
  props.onClose?.();
};

const shortModel = (type) => type.split('\\').pop();

const overlaySlotExists = ref(false)
const overlaySelector = 'dialog[open] .overlay-modal-slot'
onMounted(() => {
  overlaySlotExists.value = !!document.querySelector(overlaySelector)
})

onMounted(async () => {
  await fetchSelected();
  await fetchLibrary();
});
</script>