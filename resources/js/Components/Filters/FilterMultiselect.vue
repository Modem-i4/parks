<script setup>
import { computed } from 'vue'
import { GetFilterTargetNode, GetOrCreateFilterTargetNode } from '@/Helpers/Maps/GetFilterTargetNode'

const props = defineProps({
  node: Object,
  filters: Object,
  path: Array
})

const isBig = computed(() => props.node.size === 'big')

const selectedValues = computed(() => GetFilterTargetNode(props.filters, [...props.path, props.node.slug]))

function isSelected(id) {
  const selected = selectedValues.value
  if (Array.isArray(selected)) return selected.some(value => String(value) === String(id))
  return props.node.slug === 'park' && !!selected && (
    Object.hasOwn(selected, id) || (selected.parks ?? []).some(value => String(value) === String(id))
  )
}

const handleCheckboxChange = (id, event) => {
  const val = event.target.checked

  const target = GetOrCreateFilterTargetNode(props.filters, props.path)
  const key = props.node.slug

  // Shared links may contain detailed park selections even for a guest's
  // simple park selector. Keep nested plot/subplot restrictions intact.
  if (key === 'park' && target[key] && !Array.isArray(target[key])) {
    if (val) target[key][id] ??= {}
    else {
      delete target[key][id]
      if (Array.isArray(target[key].parks)) {
        target[key].parks = target[key].parks.filter(value => String(value) !== String(id))
        if (!target[key].parks.length) delete target[key].parks
      }
      if (!Object.keys(target[key]).length) delete target[key]
    }
    return
  }

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
  <div class="space-y-1 px-2" v-if="node.options?.length">
    <div class="font-medium">{{ node.name }}</div>
    <div class="flex flex-wrap gap-2">
      <template v-if="node.type === 'multiselect'">
        <label
          v-for="opt in node.options"
          :key="opt.id"
          class="inline-flex w-max min-w-[calc(50%-0.25rem)] max-w-full shrink-0 items-center space-x-1"
        >
          <input
            type="checkbox"
            class="shrink-0"
            :value="opt.name"
            :checked="isSelected(opt.id)"
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
              class="object-contain"
              :class="isBig ? 'w-8 h-8' : 'w-6 h-6'"
              alt="icon"
            >
            <span class="text-md" :class="{'font-semibold' : isBig}">{{ opt.name }}</span>
          </div>
          <input
            type="checkbox"
            :value="opt.name"
            :checked="isSelected(opt.id)"
            @change="handleCheckboxChange(opt.id, $event)"  
          />
        </label>
      </template>
    </div>
  </div>
</template>
