<script setup>
import { useSlots } from 'vue'

const props = defineProps({
  show: { type: Boolean, default: false },
  contentClasses: String
})

const emit = defineEmits(['close'])
const slots = useSlots()

function close() {
  emit('close')
}
</script>

<template>
  <Transition
    enter-active-class="duration-200 ease-out"
    enter-from-class="opacity-0"
    enter-to-class="opacity-100"
    leave-active-class="duration-150 ease-in"
    leave-from-class="opacity-100"
    leave-to-class="opacity-0"
  >
    <div v-if="show" class="absolute inset-0 z-50 flex items-center justify-center">
      <div class="absolute inset-0 bg-black/50" @click="close"></div>

      <Transition
        enter-active-class="duration-200 ease-out"
        enter-from-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        enter-to-class="opacity-100 translate-y-0 sm:scale-100"
        leave-active-class="duration-150 ease-in"
        leave-from-class="opacity-100 translate-y-0 sm:scale-100"
        leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
      >
        <div
          class="relative max-w-xl w-full mx-4 bg-white rounded-2xl shadow-lg flex flex-col max-h-[calc(100vh-8rem)]"
          :class="props.contentClasses"
        >
          <div v-if="slots.header" class="flex-shrink-0 p-3 relative">
            <slot name="header" />
            <button
              class="absolute top-6 right-6 text-gray-500 hover:text-gray-700"
              @click="close"
              aria-label="Закрити"
            >
              ✕
            </button>
          </div>

          <div class="flex-1 overflow-y-auto min-h-0 p-2 md:px-6">
            <slot />
          </div>

          <div v-if="slots.footer" class="flex-shrink-0 p-3 flex justify-center">
            <slot name="footer" />
          </div>
        </div>
      </Transition>
    </div>
  </Transition>
</template>
