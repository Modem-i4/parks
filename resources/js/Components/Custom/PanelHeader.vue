<template>
  <div class="flex items-center p-4 space-x-4">
    <div
      class="flex-shrink-0 w-16 h-16 rounded-full overflow-hidden bg-white relative cursor-pointer"
      @click="$emit('onIconClick')"
    >
      <img v-if="icon" :src="icon" alt="Icon" class="w-12 h-12 object-contain mx-auto my-auto" />
      <div v-else class="text-gray-400 text-xl flex items-center justify-center h-full">{{ fallbackEmoji }}</div>
      <div
        v-if="editable"
        class="absolute bottom-0 left-0 right-0 h-4 bg-black/50 text-white text-sm flex items-center justify-center pointer-events-none"
      >+</div>
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
  editable: {
    type: Boolean,
    default: false,
  },
})
const emit = defineEmits(['onIconClick'])
</script>
