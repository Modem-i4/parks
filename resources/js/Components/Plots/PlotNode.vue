<script setup>
import { computed, ref } from 'vue'
import PlotItem from './PlotItem.vue'
import ArrowIcon from '@/Components/Custom/Icons/ArrowIcon.vue'
import BasicAddForm from '../Custom/BasicAddForm.vue'

const props = defineProps({
  plots: Array,
  expanded: {
    type: Boolean,
    default: false
  },
  park: Object
})

const emit = defineEmits(['create', 'update', 'delete', 'select'])
const imgPath = computed(() => props.park?.icon?.file_path ?? '/img/icons/markers/tree-park.svg')

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
        <img :src="imgPath" alt="Іконка" class="w-5 h-5" />
        <span class="font-semibold text-sm text-gray-800">
          {{props.park?.name}}
        </span>
      </div>
      <div class="text-xs text-gray-600 flex me-4 justify-end">
        {{ plots.length }}
      </div>
    </div>

    <Transition name="accordion">
      <div v-if="expanded" class="ml-4 pl-2 border-l border-gray-300 space-y-1">
        <BasicAddForm
          label="виділ"
          :fields="['name']"
          :defaultForm="{ name: '', park_id: props.park?.id }"
          @create="emit('create', $event)"
        />
        <PlotItem
          v-for="plot in plots"
          :key="plot.id"
          :item="plot"
          @update="emit('update', $event)"
          @delete="emit('delete', $event)"
          @select="emit('select', $event)"
        />
      </div>
    </Transition>
  </div>
</template>

<style scoped>
@import '@/../css/assets/accordion.css';
</style>
