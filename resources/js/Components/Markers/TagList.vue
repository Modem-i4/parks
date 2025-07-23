<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: Array,
  edit:  {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['update:modelValue'])

const tags = computed({
  get: () => props.modelValue || [],
  set: (val) => emit('update:modelValue', val)
})

function removeTag(tagId) {
  tags.value = tags.value.filter(tag => tag.id !== tagId)
}
</script>

<template>
  <div v-if="tags.length || loading" class="text-gray-600 bg-white rounded px-4 py-6">
    <h3 class="text-lg font-semibold pb-2" v-if="!props.edit">Теги</h3>
    <div class="flex flex-wrap gap-2 min-h-7">
      <span
        v-for="tag in tags"
        :key="tag.id"
        class="relative text-sm px-2 py-1 rounded-full font-medium cursor-default"
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
        <button
          v-if="edit"
          @click.prevent="removeTag(tag.id)"
          class="ml-1 text-xl text-gray-500 hover:text-red-600"
        >
          ×
        </button>
      </span>
    </div>
  </div>
</template>
