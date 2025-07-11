<template>
  <div
    class="relative rounded overflow-hidden border cursor-pointer transition group"
    :class="{
      'border-blue-500 ring-2 ring-blue-400': selected,
      'border-gray-300 hover:border-blue-300': !selected
    }"
  >
    <img
      :src="mediaFile.file_path"
      class="w-full h-24 sm:h-32 object-cover transition"
      alt="media preview"
    />

    <div
      v-if="selected"
      class="absolute top-1 right-1 bg-blue-600 text-white text-xs px-1 rounded-full"
    >
      ✔
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  mediaFile: { type: Object, required: true },
  selected: { type: Boolean, default: false }
});
</script>
