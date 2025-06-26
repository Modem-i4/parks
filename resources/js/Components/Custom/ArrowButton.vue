<template>
  <button
    :class="[
      'group w-full font-semibold py-2 px-4 rounded-lg shadow flex items-center justify-between transition',
      primary ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-white text-blue-600 border border-blue-600 hover:bg-blue-50'
    ]"
    @click="$emit('click')"
  >
    <span><slot /></span>
    <span class="transform transition-transform group-hover:translate-x-1">→</span>
  </button>
</template>

<script setup>
defineProps({
  primary: {
    type: Boolean,
    default: true
  }
})
</script>
