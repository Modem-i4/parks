<script setup>
import { ref } from 'vue'
import TagItem from './TagItem.vue'
import ArrowIcon from '@/Components/Custom/Icons/ArrowIcon.vue'

const props = defineProps({
  type: String,
  tags: Array,
  expanded: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['create', 'update', 'delete', 'selectTag'])

const typeUkr = {
  tree: 'Дерева',
  bush: 'Кущі',
  hedge: 'Живоплоти',
  flower: 'Квітки',
  infrastructure: 'Інфраструктура',
  all: 'Спільні теги'
}

const expanded = ref(props.expanded)
</script>
<template>
  <div class="space-y-1">
    <div
      class="flex items-center px-2 py-3 rounded cursor-pointer"
      :class="[expanded ? 'bg-gray-100' : 'bg-white border hover:bg-gray-200']"
      @click="expanded = !expanded"
    >
      <div class="flex items-center space-x-2 flex-1 relative group">
        <ArrowIcon
          :class="{ 'rotate-90': expanded }"
          class="transition-transform duration-200 w-4 h-4 text-gray-500"
        />
        <span class="font-semibold text-sm text-gray-800">
          {{ typeUkr[type] || type }}
        </span>
      </div>

      <div class="text-xs text-gray-600 flex me-4 justify-end">
        {{ tags.length }}
      </div>
    </div>

    <Transition name="accordion">
      <div v-if="expanded" class="ml-4 pl-2 border-l border-gray-300 space-y-1">
        <TagItem
          v-for="item in tags"
          :key="item.id"
          :item="item"
          @update="emit('update', $event)"
          @delete="emit('delete', $event)"
          @selectTag="emit('selectTag', $event)"
        />
      </div>
    </Transition>
  </div>
</template>


<style scoped>
@import '@/../css/assets/accordion.css';
</style>
