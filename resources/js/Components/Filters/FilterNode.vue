<template>
  <div v-if="isVisible">
    <component
      :is="getComponentName(node.type)"
      :node="node"
      :filters="filters"
      :path="path"
      :key="node.type === 'group' ? node.slug : `${node.slug}-${renderKey}`"
      :renderKey
    />
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import FilterGroup from './FilterGroup.vue'
import FilterMultiselect from './FilterMultiselect.vue'
import FilterSlider from './FilterSlider.vue'
import FilterNumeric from './FilterNumeric.vue'
import FilterButton from './FilterButton.vue'
import FilterStateSelect from './FilterStateSelect.vue'
import FilterTaxonomySelect from './FilterTaxonomySelect.vue'
import FilterDates from './FilterDates.vue'
import FilterPlotsSelect from './FilterPlotsSelect.vue'
import { GetFilterNodeValue, MatchesFilterCondition, RemoveFilterNodeValue } from '@/Helpers/Filters/FilterVisibility'

const props = defineProps({
  node: Object,
  filters: Object,
  path: Array,
  renderKey: Number
})

const componentsMap = {
  group: FilterGroup,
  multiselect: FilterMultiselect,
  stateSelect: FilterStateSelect,
  infrastructureSelect: FilterMultiselect,
  taxonomy: FilterTaxonomySelect,
  dates: FilterDates,
  slider: FilterSlider,
  numeric: FilterNumeric,
  button: FilterButton,
  plots: FilterPlotsSelect
}

const isVisible = computed(() =>
  MatchesFilterCondition(props.node.visibleWhen, props.filters)
)

const nodeValue = computed(() =>
  GetFilterNodeValue(props.filters, props.path, props.node)
)

watch([isVisible, nodeValue], ([visible]) => {
  if (!visible) {
    RemoveFilterNodeValue(props.filters, props.path, props.node)
  }
}, { deep: true, immediate: true })

function getComponentName(type) {
  return componentsMap[type] || null
}
</script>
