<template>
  <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2 mt-2">
    <MediaItem
      v-for="file in library"
      :key="file.id"
      :mediaFile="file"
      :selected="selectedIds.includes(file.id)"
      @click="() => $emit('toggle', file)"
    />
  </div>
</template>

<script setup>
import { computed } from 'vue';
import MediaItem from './MediaItem.vue';

const props = defineProps({
  library: { type: Array, required: true },
  selected: { type: Array, required: true },
  multiple: { type: Boolean, default: false }
});

defineEmits(['toggle']);

const selectedIds = computed(() => props.selected.map(item => item.media_library_id));
</script>
