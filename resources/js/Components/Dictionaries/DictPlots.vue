<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import { useCrudList } from '@/Helpers/Dictionaries/useCrudList'
import LoadingLineIndicator from '@/Components/Custom/LoadingLineIndicator.vue'
import PlotNode from '../Plots/PlotNode.vue'

const props = defineProps({ 
  parkId: Number,
  plotId: Number
})
const emit = defineEmits(['select'])

const parks = ref([])
const isLoadingParks = ref(true)

const searchQuery = ref('')

const plotsCrud = useCrudList('/api/plots')
const subplotsCrud = useCrudList('/api/subplots')

async function loadParks() {
  const res = await axios.get('/api/parks')
  parks.value = res.data
  isLoadingParks.value = false
}

function isNodeExpanded(parkId) {
  return parkId === props.parkId
}

const isLoading = computed(() =>
  !!plotsCrud.isLoading.value || !!subplotsCrud.isLoading.value
)

const plotsByParkId = computed(() => {
  const map = {}
  for (const plot of plotsCrud.items.value || []) {
    const id = plot.park_id
    if (!map[id]) map[id] = []
    if(plot.id === props.plotId) plot.expanded = true
    map[id].push(plot)
  }
  return map
})

const subplotsByPlotId = computed(() => {
  const map = {}
  for (const sp of subplotsCrud.items.value || []) {
    const id = sp.plot_id
    if (!map[id]) map[id] = []
    map[id].push(sp)
  }
  return map
})

function matches(item, query) {
  const q = query.toLowerCase()
  return (item?.name ?? '').toLowerCase().includes(q)
}

function buildTree() {
  return (parks.value || []).map(park => {
    const plots = (plotsByParkId.value[park.id] || []).map(plot => ({
      ...plot,
      subplots: subplotsByPlotId.value[plot.id] || []
    }))

    return { ...park, plots }
  })
}

function filterTree(tree, query) {
  const q = query.trim().toLowerCase()

  return tree
    .map(park => {
      const parkMatch = matches(park, q)
      if (parkMatch) return { ...park, expanded: true }

      const plots = (park.plots || [])
        .map(plot => {
          const plotMatch = matches(plot, q)
          if (plotMatch) return { ...plot, expanded: true }

          const subplots = (plot.subplots || []).filter(sp => matches(sp, q))
          if (!subplots.length) return null

          return { ...plot, subplots, expanded: true }
        })
        .filter(Boolean)

      if (!plots.length) return null

      return { ...park, plots, expanded: true }
    })
    .filter(Boolean)
}

const tree = computed(() => buildTree())

const filteredTree = computed(() => {
  const q = searchQuery.value.trim()
  return q ? filterTree(tree.value, q) : tree.value
})

async function handleCreateNode({ level, data }) {
  if (level === 'plot') return plotsCrud.handleCreate(data)
  if (level === 'subplot') return subplotsCrud.handleCreate(data)
}

async function handleUpdateNode({ level, id, data }) {
  if (level === 'plot') return plotsCrud.handleUpdate({ id, data })
  if (level === 'subplot') return subplotsCrud.handleUpdate({ id, data })
}

async function handleDeleteNode({ level, id }) {
  if (level === 'plot') return plotsCrud.handleDelete({ id })
  if (level === 'subplot') return subplotsCrud.handleDelete({ id })
}

onMounted(async () => {
  await Promise.all([loadParks(), plotsCrud.load(), subplotsCrud.load()])
})
</script>

<template>
  <div class="space-y-4 pb-3 relative">
    <div class="sticky top-0 z-10 bg-white">
      <LoadingLineIndicator :isLoading="isLoading" />
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Пошук..."
        class="w-full px-3 py-2 border rounded"
      />
    </div>

    <div
      v-if="!isLoadingParks"
      v-for="park in filteredTree"
      :key="park.id"
    >
      <PlotNode
        :item="park"
        level="park"
        :expanded="isNodeExpanded(park.id)"
        @create="handleCreateNode"
        @update="handleUpdateNode"
        @delete="handleDeleteNode"
        @select="emit('select', $event)"
      />
    </div>
  </div>
</template>
