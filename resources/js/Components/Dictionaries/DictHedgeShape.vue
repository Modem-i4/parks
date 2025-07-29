<script setup>
import { useCrudList } from '@/Helpers/Dictionaries/useCrudList'
import { useSearchFilter } from '@/Helpers/Dictionaries/useSearchFilter'
import HedgeShapeItem from '../Hedge/HedgeShapeItem.vue'
import LoadingLineIndicator from '@/Components/Custom/LoadingLineIndicator.vue'
import BasicAddForm from '../Custom/BasicAddForm.vue'
import { onMounted } from 'vue'

const {
  items: hedgeShapes,
  isLoading,
  load,
  handleCreate,
  handleUpdate,
  handleDelete
} = useCrudList('/api/hedgeShapes')

const {
  query: searchQuery,
  filtered: filteredHedgeShapes
} = useSearchFilter(hedgeShapes)

onMounted(load)

const emit = defineEmits(['selectHedgeShape'])
</script>

<template>
  <div class="space-y-4 pb-3 relative">
    <div class="sticky top-0 z-10 bg-white">
      <LoadingLineIndicator :isLoading />
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Пошук..."
        class="w-full px-3 py-2 border rounded"
      />
    </div>
    <BasicAddForm
      label="форму живоплоту"
      :fields="['name']"
      :defaultForm="{ name: '' }"
      @create="handleCreate"
    />
    <div v-for="shape in filteredHedgeShapes" :key="shape.id">
      <HedgeShapeItem
        :item="shape"
        @update="handleUpdate"
        @delete="handleDelete"
        @selectHedgeShape="emit('selectHedgeShape', $event)"
      />
    </div>
  </div>
</template>
