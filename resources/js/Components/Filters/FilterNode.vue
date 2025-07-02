<template>
  <div>
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
import FilterGroup from './FilterGroup.vue'
import FilterMultiselect from './FilterMultiselect.vue'
import FilterSlider from './FilterSlider.vue'
import FilterButton from './FilterButton.vue'

const props = defineProps({
  node: Object,
  filters: Object,
  path: Array,
  renderKey: Number
})

const componentsMap = {
  group: FilterGroup,
  multiselect: FilterMultiselect,
  stateSelect: FilterMultiselect,
  infrastructureSelect: FilterMultiselect,
  slider: FilterSlider,
  button: FilterButton
}

function getComponentName(type) {
  return componentsMap[type] || null
}
</script>
