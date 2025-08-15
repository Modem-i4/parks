<template>
  <transition name="fade">
    <div
      v-show="show"
      class="text-center text-white bg-black bg-opacity-60 
        py-1 px-4 rounded shadow-md absolute top-2 left-1/2 transform -translate-x-1/2 z-[1000] w-[75%] md:w-auto">
      {{ message }}
    </div>
  </transition>
</template>

<script setup>
import { useParkStore } from '@/Stores/useParkStore';
import { ref, watch } from 'vue';

const parkStore = useParkStore()
const message = ref("")
const show = ref(false)

watch(
  () => parkStore.markerStates,
  (conds) => {
    const newMsg = resolveMessage(conds)
    show.value = !(newMsg === '')
    if(show.value) message.value = newMsg
  },
  { deep: true }
)

function resolveMessage(conds) {
  switch (true) {
    case conds.areLoaded:
      setTimeout(() => parkStore.markerStates.areLoaded = false, 2000)
      return "Маркери завантажено!"
    case conds.isLoading:
      return "Завантажуємо маркери..."
    case conds.areLimited:
      return "Наблизьте або конкретизуйте фільтри для перегляду всіх маркерів"
    default:
      return ""
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
