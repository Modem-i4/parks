<template>
  <div
    class="relative rounded overflow-hidden border cursor-pointer transition group"
    :class="{
      'border-blue-500 ring-2 ring-blue-400': selected,
      'border-gray-300 hover:border-blue-300': !selected
    }"
  >
    <img
      :src="mediaFile.thumbnail_path || mediaFile.file_path"
      class="w-full h-24 sm:h-32 object-cover transition"
      loading="lazy" decoding="async"
      alt="media preview"
    />

    <div
      v-if="selected"
      class="absolute top-1 right-1 bg-blue-600 text-white text-xs px-1 rounded-full"
    >
      ✔
    </div>

    <button
      v-if="mediaFile.type === 'image'"
      type="button"
      class="absolute bottom-1 right-1 z-10 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-red-600 shadow transition hover:bg-red-600 hover:text-white disabled:cursor-wait disabled:opacity-60"
      :disabled="deleting"
      title="Видалити зображення"
      aria-label="Видалити зображення"
      @click.stop="$emit('delete', mediaFile)"
    >
      <svg viewBox="0 0 24 24" aria-hidden="true" class="h-4 w-4 fill-none stroke-current" stroke-width="2">
        <path d="M4 7h16M9 7V4h6v3m3 0-1 13H7L6 7m4 4v5m4-5v5" />
      </svg>
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  mediaFile: { type: Object, required: true },
  selected: { type: Boolean, default: false },
  deleting: { type: Boolean, default: false },
});

defineEmits(['delete']);
</script>
