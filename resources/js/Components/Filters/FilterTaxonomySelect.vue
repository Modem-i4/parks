<script setup>
import { ref } from 'vue'
import axios from 'axios'
import TaxonOption from './TaxonOption.vue'
import { GetOrCreateFilterTargetNode } from '@/Helpers/Maps/GetFilterTargetNode'

const props = defineProps({
  filters: Object,
  path: Array,
  node: Object
})

const target = ref(null)
const type = props.path[props.path.length - 1]
const endpointType = {
  trees: 'tree',
  bushes: 'bush',
  hedges: 'hedge',
  flowers: 'flower',
}[type] || 'tree'

const preloadedOptions = ref({
  species: [],
  genus: [],
  families: []
})

const optionsLoaded = ref(false)

async function loadOptions(model) {
  return axios.get(`/api/${model}/${endpointType}`)
    .then(response => response.data)
    .catch(error => {
      console.error(`Error loading ${model} options:`, error)
      return []
    })
}

async function loadAllOptions() {
  const types = Object.keys(preloadedOptions.value)

  const promises = types.map(async (model) => {
    const data = await loadOptions(model)
    preloadedOptions.value[model] = data
  })

  await Promise.all(promises)
}

async function addTaxonomyOption() {
  target.value = GetOrCreateFilterTargetNode(props.filters, props.path)
  target.value.taxonomy ??= []
  target.value.taxonomy.push({})
  if (!optionsLoaded.value) {
    await loadAllOptions()
    optionsLoaded.value = true
  }
}

function removeTaxonomyOption(index) {
  target.value.taxonomy.splice(index, 1)
  if (target.value.taxonomy.length === 0) {
    delete target.value.taxonomy
  }
}
</script>


<template>
  <div class="space-y-2 px-2">
    <div class="font-medium">{{ node.name }}</div>

    <div class="space-y-2" v-if="target?.taxonomy?.length">
      <TaxonOption
        v-for="(entry, index) in target.taxonomy"
        :key="index"
        :preloadedOptions="preloadedOptions"
        :type="endpointType"
        v-model="target.taxonomy[index]"
        @remove="removeTaxonomyOption(index)"
      />
    </div>

    <button
      class="text-md text-blue-600 hover:underline"
      @click="addTaxonomyOption"
    >
      + Додати фільтр класифікації
    </button>
  </div>
</template>