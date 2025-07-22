<script setup>
const props = defineProps({
  tags: Array,
  loading: Boolean
})
</script>

<template>
  <div v-if="tags?.length || loading" class="text-gray-600 bg-white rounded px-4 py-6">
    <h3 class="text-lg font-semibold pb-2">Теги</h3>
    <div class="flex flex-wrap gap-2 min-h-7">
      <span
        v-for="tag in tags"
        :key="tag.id"
        class="text-sm px-2 py-1 rounded-full font-medium"
        :class="[tag.custom ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700',
                 {
                   'border border-green-400': tag.type === 'tree',
                   'border border-yellow-400': tag.type === 'bush',
                   'border border-red-400': tag.type === 'hedge',
                   'border border-pink-400': tag.type === 'flower',
                   'border border-indigo-400': tag.type === 'infrastructure',
                   'border border-gray-300': tag.type === 'all',
                 }
                ]"
      >
        {{ tag.name }}
      </span>
    </div>
  </div>
</template>
