<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useCrudList } from '@/Helpers/Dictionaries/useCrudList'
import { useSearchFilter } from '@/Helpers/Dictionaries/useSearchFilter'
import LoadingLineIndicator from '@/Components/Custom/LoadingLineIndicator.vue'
import PlotNode from '../Plots/PlotNode.vue'

const props = defineProps({ parkId: Number })
const emit = defineEmits(['select'])

const {
  items: plots,
  isLoading,
  load,
  handleCreate,
  handleUpdate,
  handleDelete
} = useCrudList('/api/plots')

const { query: searchQuery, filtered: filteredPlots } = useSearchFilter(plots)

const isLoadingParks = ref(true)
const parks = ref([])

async function loadParks() {
  const res = await axios.get('/api/parks')
  parks.value = res.data
  isLoadingParks.value = false
}

function isNodeExpanded(parkId) {
  return parkId === props.parkId
}

const groupedPlots = computed(() => {
  const map = {}
  for (const plot of filteredPlots.value) {
    const id = plot.park_id
    if (!map[id]) {
      const park = parks.value.find(p => p.id == id)
      map[id] = {
        park,
        plots: []
      }
    }
    map[id].plots.push(plot)
  }
  return Object.values(map)
})

onMounted(async () => {
  await Promise.all([load(), loadParks()])
})

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
    <div v-if="!isLoadingParks" v-for="group in groupedPlots" :key="group.park.id">
      <PlotNode
        :park="group.park"
        :plots="group.plots"
        :expanded="isNodeExpanded(group.park.id)"
        @create="handleCreate"
        @update="handleUpdate"
        @delete="handleDelete"
        @select="emit('select', $event)"
      />
    </div>
  </div>
</template>
