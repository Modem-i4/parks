<template>
  <div
    class="border-2 border-dashed border-gray-300 p-4 rounded text-center cursor-pointer relative bg-white hover:bg-gray-50 transition"
    @dragover.prevent
    @drop.prevent="handleDrop"
  >
    <input
      type="file"
      ref="fileInput"
      class="hidden"
      accept="image/*"
      @change="upload"
    />

    <div v-if="loading" class="text-sm text-gray-500 animate-pulse">
      Завантаження...
    </div>

    <div v-else @click="fileInput.click()" class="text-sm text-gray-600">
      Перетягніть або натисніть для вибору зображення
      <span class="block text-xs text-gray-400 mt-1">Тип: {{ type === 'icon' ? 'іконка' : type === 'image' ? 'зображення' : type }}</span>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const emit = defineEmits(['uploaded']);

const props = defineProps({
  type: { type: String, required: true }, // 'icon' або 'image'
});

const fileInput = ref(null);
const loading = ref(false);

const upload = async (e) => {
  const file = e.target.files[0];
  if (!file) return;
  await sendFile(file);
};

const handleDrop = async (e) => {
  const file = e.dataTransfer.files[0];
  if (!file) return;
  await sendFile(file);
};

const sendFile = async (file) => {
  loading.value = true;

  const formData = new FormData();
  formData.append('file', file);
  formData.append('type', props.type);
  try {
    await axios.post('/api/media-library', formData);
    emit('uploaded');
  } catch (e) {
    console.error('Upload failed:', e);
    alert('Помилка під час завантаження зображення');
  } finally {
    loading.value = false;
  }
};
</script>
