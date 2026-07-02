<script setup>
import { computed } from 'vue'

const props = defineProps({
  align: {
    type: String,
    default: 'center'
  }
})

const alignClass = computed(() => {
  if (props.align === 'right') return 'right-0'
  if (props.align === 'left') return 'left-0'
  return 'left-1/2 -translate-x-1/2'
})
</script>

<template>
    <div
      class="absolute bottom-full mb-1 whitespace-nowrap
             bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100
             transition-opacity duration-200 pointer-events-none z-10"
      :class="alignClass"
    >
      <slot/>
    </div>
</template>
