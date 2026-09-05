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

const THUMBNAIL_MAX_EDGE = 480;
const THUMBNAIL_QUALITY = 0.72;

const createThumbnail = async (file) => {
  if (props.type !== 'image' || !['image/jpeg', 'image/png', 'image/webp', 'image/bmp'].includes(file.type)) {
    return null;
  }

  const objectUrl = URL.createObjectURL(file);

  try {
    const image = new Image();
    image.src = objectUrl;
    await image.decode();

    const scale = Math.min(1, THUMBNAIL_MAX_EDGE / Math.max(image.naturalWidth, image.naturalHeight));
    const canvas = document.createElement('canvas');
    canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
    canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));

    const context = canvas.getContext('2d');
    if (!context) return null;

    context.drawImage(image, 0, 0, canvas.width, canvas.height);

    const blob = await new Promise((resolve) => {
      canvas.toBlob(resolve, 'image/webp', THUMBNAIL_QUALITY);
    });

    if (!blob) return null;

    const basename = file.name.replace(/\.[^.]+$/, '') || 'image';
    return new File([blob], `${basename}.preview.webp`, { type: 'image/webp' });
  } finally {
    URL.revokeObjectURL(objectUrl);
  }
};

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
    const thumbnail = await createThumbnail(file).catch((error) => {
      console.warn('Thumbnail creation failed; uploading the original without it:', error);
      return null;
    });
    if (thumbnail) formData.append('thumbnail', thumbnail);

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
