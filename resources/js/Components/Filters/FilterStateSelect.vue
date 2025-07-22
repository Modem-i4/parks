<script setup>
import { GetFilterTargetNode, GetOrCreateFilterTargetNode } from '@/Helpers/Maps/GetFilterTargetNode'
import StateSelector from '@/Components/Custom/StateSelector.vue' 
import { computed } from 'vue'

const props = defineProps({
  node: Object,   
  filters: Object,  
  path: Array    
})

const selected = computed(() => {
  const target = GetFilterTargetNode(props.filters, props.path)
  if (!target || typeof target !== 'object') return []
  return Array.isArray(target[props.node.slug]) ? target[props.node.slug] : []
})

function updateSelectedStates(newValues) {
  const target = GetOrCreateFilterTargetNode(props.filters, props.path)
  const key = props.node.slug

  if (Array.isArray(newValues) && newValues.length > 0) {
    target[key] = newValues
  } else {
    delete target[key]
  }
}
</script>

<template>
  <div class="space-y-1 px-2">
    <div class="font-medium">{{ node.name }}</div>

    <StateSelector
      :multiple="true"
      :model-value="selected"
      @update:modelValue="updateSelectedStates"
    />
  </div>
</template>
