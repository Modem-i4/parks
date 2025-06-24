<template>
  <div class="flex items-center my-6">
    <div class="flex-grow border-t border-gray-300"></div>
    <span class="mx-4 text-gray-500 text-sm select-none">{{ text }}</span>
    <div class="flex-grow border-t border-gray-300"></div>
  </div>
</template>

<script setup>
defineProps({
  text: {
    type: String,
    default: 'або',
  },
})
</script>
