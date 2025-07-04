<template>
  <div class="flex items-center p-4 space-x-4">
    <div class="flex-shrink-0 w-16 h-16 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center">
      <img v-if="icon" :src="icon" alt="Icon" class="w-12 h-12 object-contain" />
      <div v-else class="text-gray-400 text-xl">{{ fallbackEmoji }}</div>
    </div>
    <div>
      <h3 class="text-lg font-semibold text-gray-900">{{ title }}</h3>
      <p v-if="subtitle" class="text-sm text-gray-500">{{ subtitle }}</p>
    </div>
    <div class="flex-1 flex justify-end">
      <slot name="right" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: String,
  subtitle: String,
  icon: String,
  fallbackEmoji: {
    type: String,
    default: '🌳',
  },
})
</script>
