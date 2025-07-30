<script setup>
import { ref, watch } from 'vue'
import { GetOrCreateFilterTargetNode } from '@/Helpers/Maps/GetFilterTargetNode'

const props = defineProps({
  node: Object,
  filters: Object,
  path: Array
})

const from = ref('')
const to = ref('')

const update = () => {
  const target = GetOrCreateFilterTargetNode(props.filters, props.path)

  if (!from.value && !to.value) {
    delete target[props.node.slug]
  } else {
    target[props.node.slug] = [
      from.value || null,
      to.value || null
    ]
  }
}

watch([from, to], update)
</script>
<template>
  <div class="space-y-2 px-2">
    <div class="font-medium">{{ node.name }}</div>
    <div class="flex flex-wrap gap-x-4 gap-y-2">
      <!-- Від -->
      <div class="flex flex-col xl:flex-row items-center w-[calc(50%-0.5rem)]">
        <label class="text-xs text-gray-500 mb-0.5 text-center px-1">Від</label>
        <input
          type="date"
          v-model="from"
          class="border border-gray-300 rounded px-2 py-1 text-sm w-full"
        >
      </div>

      <!-- До -->
      <div class="flex flex-col xl:flex-row items-center w-[calc(50%-0.5rem)]">
        <label class="text-xs text-gray-500 mb-0.5 text-center px-1">До</label>
        <input
          type="date"
          v-model="to"
          class="border border-gray-300 rounded px-2 py-1 text-sm w-full"
        >
      </div>
    </div>
  </div>
</template>
