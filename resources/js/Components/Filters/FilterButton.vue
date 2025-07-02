<script setup>
const props = defineProps({
  node: Object,
  filters: Object,
  path: Array
})
</script>

<template>
  <div class="px-2">
    <button class="text-blue-600 hover:underline">{{ node.name }}</button>
  </div>
</template>