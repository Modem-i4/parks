<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import axios from 'axios'
import LoadingLineIndicator from '@/Components/Custom/LoadingLineIndicator.vue'
import InfrastructureTypeItem from './InfrastructureTypeItem.vue'

const infrastructure = ref([])
const isLoading = ref(false)
const endpoint = '/api/infrastructureType'

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
    infrastructure.value = res.data
  })
}

// CRUD

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

const filteredInfrastructure = computed(() => {
  const q = searchQuery.value.trim();
  return q
    ? infrastructure.value.filter(item => matches(item, q))
    : infrastructure.value;
});

</script>

<template>
  <div class="space-y-4 relative">
    <div class="sticky top-0 z-10 bg-white">
      <LoadingLineIndicator :isLoading/>
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Пошук..."
        class="w-full px-3 py-2 border rounded"
      />
    </div>
    <InfrastructureTypeItem 
      v-for="infrastructureType in filteredInfrastructure" 
      :key="infrastructureType.id"
      :item="infrastructureType"
      @create="handleCreate"
      @update="handleUpdate"
      @delete="handleDelete"
    />
  </div>
</template>
