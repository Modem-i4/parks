<script setup>
import { ref } from 'vue'

const emit = defineEmits(['close'])

const touchStartY = ref(0)
const panelOffsetY = ref(0)
const isDragging = ref(false)

function onTouchStart(event) {
  touchStartY.value = event.touches[0].clientY
  isDragging.value = true
}

function onTouchMove(event) {
  if (!isDragging.value) return
  const currentY = event.touches[0].clientY
  const delta = currentY - touchStartY.value
  panelOffsetY.value = Math.max(0, delta)
}

defineProps({
    show: Boolean
})

function onTouchEnd() {
  isDragging.value = false
  if (panelOffsetY.value > 80) {
    emit('close')
  }
  panelOffsetY.value = 0
}
</script>

<template>
  <div
    class="md:hidden fixed bottom-0 left-0 right-0 bg-gray-100 shadow-lg rounded-t-xl z-50"
    :class="!isDragging ? 'transition-transform duration-300 ease-in-out' : ''"
    :style="{
      transform: show ? `translateY(${isDragging ? panelOffsetY + 'px' : '0'})` : 'translateY(100%)'
    }"
  >
    <div
      class="p-2 border-b flex justify-between items-center active:cursor-grabbing touch-none"
      @touchstart.passive="onTouchStart"
      @touchmove.passive="onTouchMove"
      @touchend="onTouchEnd"
    >
      <div class="flex items-center px-3 space-x-2 text-lg font-semibold">
        <slot name="header" />
      </div>
      <button @click="emit('close')" class="text-gray-600 text-4xl">×</button>
    </div>

    <slot />
  </div>
</template>
