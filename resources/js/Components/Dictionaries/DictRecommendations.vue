<script setup>
import { useCrudList } from '@/Helpers/Dictionaries/useCrudList'
import { useSearchFilter } from '@/Helpers/Dictionaries/useSearchFilter'
import RecommendationItem from '../Recommendations/RecommendationItem.vue'
import { onMounted } from 'vue'
import LoadingLineIndicator from '@/Components/Custom/LoadingLineIndicator.vue'
import BasicAddForm from '../Custom/BasicAddForm.vue'

const {
  items: recommendations,
  isLoading,
  load,
  handleCreate,
  handleUpdate,
  handleDelete
} = useCrudList('/api/recommendations')

const {
  query: searchQuery,
  filtered: filteredRecommendations
} = useSearchFilter(recommendations)

const emit = defineEmits(['selectRecommendation'])

onMounted(load)
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
      label="вид рекомендацій"
      :fields="['name']"
      :defaultForm="{ name: '' }"
      @create="handleCreate"
    />
    <div v-for="rec in filteredRecommendations" :key="rec.id">
      <RecommendationItem
        :item="rec"
        @update="handleUpdate"
        @delete="handleDelete"
        @selectRecommendation="emit('selectRecommendation', $event)"
      />
    </div>
  </div>
</template>
