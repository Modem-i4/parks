<script setup>
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import LoadingLineIndicator from '@/Components/Custom/LoadingLineIndicator.vue'
import TagNode from '../Tags/TagNode.vue'
import TagAddForm from '../Tags/TagAddForm.vue'

const tags = ref([])
const isLoading = ref(false)
const searchQuery = ref('')

const endpoint = '/api/tags'

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
    tags.value = res.data
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

const props = defineProps({
  type: String
})

function isNodeExpanded(type) {
  return type === 'all' || type === props.type
}

const groupedTags = computed(() => {
  const q = searchQuery.value.toLowerCase().trim()
  const map = {}
  for (const tag of tags.value) {
    if (q && !tag.name.toLowerCase().includes(q)) continue
    if (!map[tag.type]) map[tag.type] = []
    map[tag.type].push(tag)
  }
  return map
})

const emit = defineEmits(['selectTag'])
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
    <TagAddForm @create="handleCreate" />
    <div v-for="(tags, type) in groupedTags" :key="type">
      <TagNode
        :type="type"
        :tags="tags"
        :expanded="isNodeExpanded(type)"
        @create="handleCreate"
        @update="handleUpdate"
        @delete="handleDelete"
        @selectTag="emit('selectTag', $event)"
      />
    </div>
  </div>
</template>
