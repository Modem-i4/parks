<script setup>
import { ref, onMounted, watch, computed } from 'vue'
import axios from 'axios'
import TaxonItem from '@/Components/Taxonomy/TaxonItem.vue'
import TaxonAddForm from './TaxonAddForm.vue'
import LoadingLineIndicator from '@/Components/Custom/LoadingLineIndicator.vue'

const families = ref([])
const isLoading = ref(false)

const props = defineProps({
    type: {
        type: String,
        required: true
    }
})

onMounted(loadFamilies)
watch(
  () => [props.type], 
  () => {
    families.value = []
    loadFamilies()
  }
)

async function handleLoading(fn) {
  isLoading.value = true
  try {
    await fn()
  } finally {
    isLoading.value = false
  }
}

async function loadFamilies() {
  await handleLoading(async () => {
    const res = await axios.get(`/api/families-full-structure/${props.type}`)
    families.value = res.data
  })
}

// CRUD
const endpoints = {
  family: '/api/families',
  genus: '/api/genus',
  species: '/api/species',
}

async function handleCreate({ parent, data, level }) {
  await handleLoading(async () => {
    const formData = {
      family: () => ({ ...data, type: props.type }),
      genus: () => ({ ...data, family_id: parent.id }),
      species: () => ({ ...data, genus_id: parent.id })
    }[level]?.()

    await axios.post(endpoints[level], formData)
    await loadFamilies()
  })
}

async function handleUpdate({ id, data, level }) {
  await handleLoading(async () => {
    await axios.patch(`${endpoints[level]}/${id}`, data)
    await loadFamilies()
  })
}

async function handleDelete({ id, level }) {
  await handleLoading(async () => {
    await axios.delete(`${endpoints[level]}/${id}`)
    await loadFamilies()
  })
}

// search
const searchQuery = ref('')

function matches(item, query) {
  const q = query.toLowerCase()
  return (
    item.name_ukr?.toLowerCase().includes(q) ||
    item.name_lat?.toLowerCase().includes(q)
  )
}

function filterTree(families, query) {
  return families
    .map(family => {
      const genus = (family.genus || [])
        .map(g => {
          const species = (g.species || []).filter(sp => matches(sp, query))
          return matches(g, query) || species.length
            ? { ...g, species, expanded: !!species.length }
            : null
        })
        .filter(Boolean)

      return matches(family, query) || genus.length
        ? { ...family, genus, expanded: !!genus.length }
        : null
    })
    .filter(Boolean)
}


const filteredFamilies = computed(() => {
  return searchQuery.value.trim()
    ? filterTree(families.value, searchQuery.value)
    : families.value
})

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

    <TaxonAddForm
      level="root"
      nextLevel="family"
      :item="families"
      @create="handleCreate"
    />

    <TaxonItem
      v-for="family in filteredFamilies"
      :key="family.id"
      :item="family"
      level="family"
      @create="handleCreate"
      @update="handleUpdate"
      @delete="handleDelete"
    />
  </div>
</template>
