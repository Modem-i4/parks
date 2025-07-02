<script setup>
import { GetOrCreateFilterTargetNode } from '@/Helpers/GetFilterTargetNode'
import { ref, computed } from 'vue'

const props = defineProps({
  node: Object,
  filters: Object,
  path: Array
})

const minValue = ref(props.node.min)
const maxValue = ref(props.node.max)

const handleSliderChange = (index, rawVal) => {
  let val = Number(rawVal)
  if (index === 0) {
    if (val > maxValue.value) return
    minValue.value = val
  } else {
    if (val < minValue.value) return
    maxValue.value = val
  }
}
function setVal() {
  const target = GetOrCreateFilterTargetNode(props.filters, props.path)
  target[props.node.slug] = [minValue.value, maxValue.value]
}

function adjustMax() {
  maxValue.value = Math.min(props.node.max, maxValue.value + 1)
  setVal()
}
function adjustMin() {
  minValue.value = Math.max(props.node.min, minValue.value-1)
  setVal()
}
</script>

<template>
  <div class="space-y-2 px-2">
    <div class="font-medium">{{ node.name }}</div>
    <div class="flex flex-row items-center space-x-2 max-w-full">
      <!-- Від -->
      <div class="flex flex-col items-center w-20">
        <label class="text-xs text-gray-500">Від</label>
        <input
          type="number"
          :min="node.min"
          :max="node.max"
          :value="minValue"
          @input="handleSliderChange(0, $event.target.value)"
          @change="adjustMin"
          class="w-full border border-gray-300 rounded px-1 py-0.5 text-center"
        >
      </div>

      <!-- Діапазон -->
      <div class="flex-1 w-full pt-4">
        <input
          type="range"
          :min="node.min"
          :max="node.max"
          :value="minValue"
          @input="handleSliderChange(0, $event.target.value)"
          @change="adjustMin"
          class="w-full accent-green-600"
        />

        <input
          type="range"
          :min="node.min"
          :max="node.max"
          :value="maxValue"
          @input="handleSliderChange(1, $event.target.value)"
          @change="adjustMax"
          class="w-full accent-green-600"
        />
      </div>

      <!-- До -->
      <div class="flex flex-col items-center w-20">
        <label class="text-xs text-gray-500">До</label>
        <input
          type="number"
          :min="node.min"
          :max="node.max"
          :value="maxValue"
          @input="handleSliderChange(1, $event.target.value)"
          @change="adjustMax"
          class="w-full border border-gray-300 rounded px-1 py-0.5 text-center"
        >
      </div>
    </div>
  </div>
</template>