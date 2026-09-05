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
      accept="image/*,.heic,.heif"
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
import { compressImage, createThumbnail } from '@/Helpers/Media/ImageUploadProcessor';

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

  try {
    const uploadFile = await compressImage(file, props.type).catch((error) => {
      console.warn('Client image optimization failed; uploading the original for server-side processing:', error);
      return file;
    });
    const thumbnail = await createThumbnail(uploadFile, props.type).catch((error) => {
      console.warn('Thumbnail creation failed; uploading the original without it:', error);
      return null;
    });

    const formData = new FormData();
    formData.append('file', uploadFile);
    formData.append('type', props.type);
    if (thumbnail) formData.append('thumbnail', thumbnail);

    await axios.post('/api/media-library', formData);
    emit('uploaded');
  } catch (e) {
    console.error('Upload failed:', e);
    alert(e.response?.data?.message || e.message || 'Помилка під час завантаження зображення');
  } finally {
    loading.value = false;
    if (fileInput.value) fileInput.value.value = '';
  }
};
</script>
