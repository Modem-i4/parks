<template>
  <div class="space-y-2">
    <p class="text-sm font-semibold">{{ title }}</p>

    <div v-if="!selected.length" class="text-sm text-gray-500 italic">
      Нічого не обрано
    </div>

    <draggable
      v-model="ordered"
      item-key="media_library_id"
      handle=".handle"
      :disabled="!multiple"
      @end="emit('reorder', ordered)"
      class="space-y-1"
    >
      <template #item="{ element }">
        <div
          class="flex items-center gap-2 bg-gray-100 p-2 rounded relative handle select-none cursor-move"
        >
          <img
            :src="element.thumbnail_path || element.file_path"
            class="w-12 h-12 object-cover rounded"
          />
          <div class="flex-1 text-sm truncate">
            <FloatingInput v-if="props.type === 'image'" v-model="element.description" label="Публічний опис зображення" labelClasses="italic"/>
          </div>

          <div class="flex items-center gap-2">
            <button
              @click="() => emit('remove', element.media_library_id)"
              class="text-red-500 hover:text-red-700 text-sm"
              title="Видалити"
            >
              🗑️
            </button>
            <span
              v-if="multiple"
              class="cursor-move handle text-gray-400 hover:text-gray-600 text-sm"
              title="Перетягнути"
            >
              ☰
            </span>
          </div>
        </div>
      </template>
    </draggable>
  </div>
</template>

<script setup>
import draggable from 'vuedraggable';
import FloatingInput from '@/Components/Custom/FloatingInput.vue'
import { computed } from 'vue';

const props = defineProps({
  selected: { type: Array, required: true },
  multiple: { type: Boolean, default: false },
  type: { type: String, required: true }, // 'icon' or 'image'
});
const emit = defineEmits(['remove', 'reorder']);

const ordered = computed({
  get: () => props.selected,
  set: (val) => emit('reorder', val)
});

const title = computed(() => {
  if (props.type === 'icon') return 'Обрана іконка'
  return 'Обрані зображення'
});
</script>
