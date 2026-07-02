<script setup>
import { ref } from 'vue'
import { GetOrCreateFilterTargetNode } from '@/Helpers/Maps/GetFilterTargetNode'

const props = defineProps({
  node: Object,
  filters: Object,
  path: Array
})

const minValue = ref(props.node.min)
const maxValue = ref(props.node.max)

const clamp = (value) => Math.min(props.node.max, Math.max(props.node.min, value))

const handleChange = (index, rawVal) => {
  const val = Number(rawVal)
  if (Number.isNaN(val)) return

  if (index === 0) {
    if (val > maxValue.value) return
    minValue.value = clamp(val)
  } else {
    if (val < minValue.value) return
    maxValue.value = clamp(val)
  }
}

function setVal() {
  const target = GetOrCreateFilterTargetNode(props.filters, props.path)
  target[props.node.slug] = [minValue.value, maxValue.value]
}

</script>

<template>
  <div class="space-y-2 px-2">
    <div class="font-medium">{{ node.name }}</div>
    <div class="grid grid-cols-2 whitespace-nowrap">
      <div class="flex items-center justify-start gap-3">
        <label class="text-xs text-gray-500">Від</label>
        <input
          type="number"
          :min="node.min"
          :max="node.max"
          :step="node.step || 1"
          :value="minValue"
          @input="handleChange(0, $event.target.value)"
          @change="setVal"
          class="w-16 border border-gray-300 rounded px-1.5 py-1 text-base text-center sm:w-22"
        >
      </div>

      <div class="flex items-center justify-start gap-3">
        <label class="text-xs text-gray-500">До</label>
        <input
          type="number"
          :min="node.min"
          :max="node.max"
          :step="node.step || 1"
          :value="maxValue"
          @input="handleChange(1, $event.target.value)"
          @change="setVal"
          class="w-16 border border-gray-300 rounded px-1.5 py-1 text-base text-center sm:w-22"
        >
      </div>
    </div>
  </div>
</template>
