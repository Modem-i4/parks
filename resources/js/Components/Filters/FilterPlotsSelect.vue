<script setup>
import { computed, ref, watch } from 'vue'
import axios from 'axios'
import { GetOrCreateFilterTargetNode } from '@/Helpers/Maps/GetFilterTargetNode'
import { useParkStore } from '@/Stores/useParkStore'
import PlotsOption from '@/Components/Plots/PlotsOption.vue'

const props = defineProps({
  filters: Object,
  path: Array,
  node: Object
})

const parkStore = useParkStore()

const target = ref(null)
const entries = ref([])

const plotOptions = ref([])
const plotOptionsById = ref({})
const subplotsByPlotId = ref({})
const subplotsById = ref({})
const optionsLoaded = ref(false)

const parkId = computed(() => props.node.park_id ?? parkStore.selectedPark?.id)

function ensureTarget() {
  if (target.value) return
  target.value = GetOrCreateFilterTargetNode(props.filters, props.path)
  hydrateEntries()
}

function hydrateEntries() {
  if (!target.value?.plots) return

  if (Array.isArray(target.value.plots)) {
    const converted = {}
    target.value.plots.forEach(id => {
      if (id) converted[id] = { subplots: [] }
    })
    target.value.plots = converted
  }

  entries.value = Object.entries(target.value.plots).map(([plotId, data]) => ({
    plotId: +plotId,
    subplots: [...(data?.subplots ?? [])]
  }))
}

function setPlotOptions(plots = []) {
  const byId = {}
  const subByPlot = {}
  const subById = {}

  plotOptions.value = plots.map(p => {
    byId[p.id] = p.name

    const subs = (p.subplots ?? []).map(s => {
      subById[s.id] = s.name
      return { id: s.id, name: s.name }
    })

    subByPlot[p.id] = subs

    return { id: p.id, name: p.name }
  })

  plotOptionsById.value = byId
  subplotsByPlotId.value = subByPlot
  subplotsById.value = subById
}

async function loadPlots() {
  if (props.node.options) {
    setPlotOptions(props.node.options)
    return
  }

  if (!parkId.value) return
  return axios.get(`/api/plots?parkId=${parkId.value}`)
    .then(res => setPlotOptions(res.data ?? []))
    .catch(err => {
      console.error('Error loading plots:', err)
    })
}

async function addPlotsOption() {
  ensureTarget()
  entries.value.push({ plotId: null, subplots: [] })

  if (!optionsLoaded.value && parkId.value) {
    await loadPlots()
    optionsLoaded.value = true
  }
}

function removePlotsOption(index) {
  entries.value.splice(index, 1)
}

watch(entries, () => {
  if (!target.value) return

  const plots = {}
  entries.value.forEach(e => {
    if (!e.plotId) return
    plots[e.plotId] = { subplots: e.subplots.filter(Boolean) }
  })

  if (Object.keys(plots).length) target.value.plots = plots
  else delete target.value.plots
}, { deep: true })

watch(parkId, async (id) => {
  if (!id) return
  await loadPlots()
  optionsLoaded.value = true
}, { immediate: true })
</script>

<template>
  <div class="space-y-2 px-2">
    <div class="font-medium">{{ node.name }}</div>

    <div class="space-y-2" v-if="entries.length">
      <PlotsOption
        v-for="(entry, index) in entries"
        :key="index"
        v-model="entries[index]"
        :plotOptions="plotOptions"
        :plotOptionsById="plotOptionsById"
        :subplotsByPlotId="subplotsByPlotId"
        :subplotsById="subplotsById"
        :parkId="parkId"
        @remove="removePlotsOption(index)"
      />
    </div>

    <button
      class="text-md text-blue-600 hover:underline"
      @click="addPlotsOption"
    >
      + Додати фільтр за виділом
    </button>
  </div>
</template>
