<script setup>
import { GetFilterTargetNode, GetOrCreateFilterTargetNode } from '@/Helpers/Maps/GetFilterTargetNode'

const props = defineProps({
  node: Object,
  filters: Object,
  path: Array
})

const handleCheckboxChange = (id, event) => {
  const val = event.target.checked

  const target = GetOrCreateFilterTargetNode(props.filters, props.path)
  const key = props.node.slug

  target[key] ??= []

  if (val) {
    if (!target[key].includes(id)) {
      target[key].push(id)
    }
  } else {
    target[key] = target[key].filter(o => o !== id)
    if (target[key].length === 0) {
      delete target[key]
    }
  }
}

</script>

<template>
  <div class="space-y-1 px-2">
    <div class="font-medium">{{ node.name }}</div>
    <div class="flex flex-wrap gap-2">
      <template v-if="node.type === 'multiselect'">
        <label v-for="opt in node.options" :key="opt.id" class="inline-flex items-center space-x-1">
          <input
            type="checkbox"
            :value="opt.name"
            :checked="opt.checked && (GetFilterTargetNode(filters, [...path,props.node.slug])?.includes(opt.id))"
            @change="handleCheckboxChange(opt.id, $event)"
          >
          <span>{{ opt.name }}</span>
        </label>
      </template>

      <template v-if="node.type === 'infrastructureSelect'">

        <label
          v-for="opt in node.options"
          :key="opt.id"
          class="flex items-center justify-between w-full"
        >
          <div class="flex items-center gap-2">
            <img
              v-if="opt.icon"
              :src="opt.icon"
              class="w-6 h-6 object-contain"
              alt="icon"
            >
            <span class="text-md">{{ opt.name }}</span>
          </div>
          <input
            type="checkbox"
            :value="opt.name"
            @change="handleCheckboxChange(opt.id, $event)"  
          />
        </label>
      </template>
    </div>
  </div>
</template>