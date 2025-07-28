<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import LoadingLineIndicator from '@/Components/Custom/LoadingLineIndicator.vue'
import RecommendationsAddForm from '../Recommendations/RecommendationsAddForm.vue'
import RecommendationItem from '../Recommendations/RecommendationItem.vue'

const recommendations = ref([])
const isLoading = ref(false)

const endpoint = '/api/recommendations'

onMounted(load)

async function handleLoading(fn) {
  isLoading.value = true
  try {
    await fn()
  } finally {
    isLoading.value = false
  }
}

async function load() {
  await handleLoading(async () => {
    const res = await axios.get(endpoint)
    recommendations.value = res.data
  })
}

async function handleCreate({ data }) {
  await handleLoading(async () => {
    await axios.post(endpoint, data)
    await load()
  })
}

async function handleUpdate({ id, data }) {
  await handleLoading(async () => {
    await axios.patch(`${endpoint}/${id}`, data)
    await load()
  })
}

async function handleDelete({ id }) {
  await handleLoading(async () => {
    await axios.delete(`${endpoint}/${id}`)
    await load()
  })
}

// search
const searchQuery = ref('');

function matches(item, query) {
  const q = query.toLowerCase();
  return item.name?.toLowerCase().includes(q);
}

const filteredRecommendations = computed(() => {
  const q = searchQuery.value.trim();
  return q
    ? recommendations.value.filter(item => matches(item, q))
    : recommendations.value;
});

const emit = defineEmits(['selectRecommendation'])
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
    <RecommendationsAddForm @create="handleCreate" />
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
