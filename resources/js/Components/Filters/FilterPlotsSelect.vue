<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import SelectWithSearchAndAdd from '@/Components/Custom/SelectWithSearchAndAdd.vue'
import { GetOrCreateFilterTargetNode } from '@/Helpers/Maps/GetFilterTargetNode'
import { useParkStore } from '@/Stores/useParkStore'

import Modal from '@/Components/Default/Modal.vue'
import DictPlots from '@/Components/Dictionaries/DictPlots.vue'

const props = defineProps({
  filters: Object,
  path: Array,
  node: Object
})

const target = ref(null)
const plotOptions = ref([])
const plotOptionsById = ref({})
const optionsLoaded = ref(false)

const showModal = ref({plots: false})

const parkStore = useParkStore()

async function loadPlots() {
  if (!parkStore.selectedPark) return
  axios.get(`/api/plots?parkId=${parkStore.selectedPark.id}`)
    .then(res => {
      plotOptions.value = res.data
      res.data.forEach((r) => {
        plotOptionsById.value[r.id] = r.name
      })
    })
    .catch(err => {
      console.error('Error loading plots:', err)
    })
}

async function addPlotsOption() {
  target.value = GetOrCreateFilterTargetNode(props.filters, props.path)
  target.value.plots ??= []
  target.value.plots.push(null)

  if (!optionsLoaded.value && parkStore.selectedPark) {
    await loadPlots()
    optionsLoaded.value = true
  }
}

function removePlotsOption(index) {
  target.value.plots.splice(index, 1)
  if (target.value.plots.length === 0) {
    delete target.value.plots
  }
}

// Modal
const activePlotIndex = ref(null)
function openPlotModal(index) {
  activePlotIndex.value = index
  showModal.value.plots = true
}
function selectPlot(plot) {
  if (activePlotIndex.value !== null) {
    target.value.plots[activePlotIndex.value] = plot.id
    plotOptionsById.value[plot.id] = plot.name
    showModal.value.plots = false
    activePlotIndex.value = null
  }
}

watch(() => parkStore.selectedPark, async (newVal) => {
  if (newVal) {
    plotOptions.value = await loadPlots()
    optionsLoaded.value = true
  }
})
</script>

<template>
  <div class="space-y-2 px-2">
    <div class="font-medium">{{ node.name }}</div>

    <div
      class="flex items-center gap-2"
      v-for="(plotId, index) in target?.plots"
      :key="index"
    >
      <SelectWithSearchAndAdd
        class="flex-1"
        mode="plots"
        v-model="target.plots[index]"
        :startingItem="{id: plotId, name: plotOptionsById[plotId]}"
        :parkId="parkStore.selectedPark?.id"
        :preloadedOptions="plotOptions"
        :showLabel="false"
        @show-modal="() => openPlotModal(index)"
      />

      <button
        @click="removePlotsOption(index)"
        class="text-red-600 hover:text-red-800 text-xl"
        title="Видалити"
      >×</button>
    </div>


    <button
      class="text-md text-blue-600 hover:underline"
      @click="addPlotsOption"
    >+ Додати фільтр за виділом</button>
    <Modal :show="showModal.plots" maxWidth="4xl" @close="showModal.recommendation = false">
      <DictPlots @select="selectPlot" :parkId="parkStore.selectedPark.id" />
    </Modal>
  </div>
</template>
