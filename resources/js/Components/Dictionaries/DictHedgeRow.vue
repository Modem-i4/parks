<script setup>
import { useCrudList } from '@/Helpers/Dictionaries/useCrudList'
import { useSearchFilter } from '@/Helpers/Dictionaries/useSearchFilter'
import HedgeRowItem from '../Hedge/HedgeRowItem.vue'
import LoadingLineIndicator from '@/Components/Custom/LoadingLineIndicator.vue'
import BasicAddForm from '../Custom/BasicAddForm.vue'
import { onMounted } from 'vue'

const {
  items: hedgeRows,
  isLoading,
  load,
  handleCreate,
  handleUpdate,
  handleDelete
} = useCrudList('/api/hedgeRows')

const {
  query: searchQuery,
  filtered: filteredHedgeRows
} = useSearchFilter(hedgeRows)

onMounted(load)

const emit = defineEmits(['selectHedgeRow'])
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
      label="тип ряду живоплоту"
      :fields="['name']"
      :defaultForm="{ name: '' }"
      @create="handleCreate"
    />
    <div v-for="row in filteredHedgeRows" :key="row.id">
      <HedgeRowItem
        :item="row"
        @update="handleUpdate"
        @delete="handleDelete"
        @selectHedgeRow="emit('selectHedgeRow', $event)"
      />
    </div>
  </div>
</template>
