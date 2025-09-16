<script setup>
import { ref } from 'vue'
import ArrowIcon from '@/Components/Custom/Icons/ArrowIcon.vue'

const props = defineProps({
  defaultOpen: { type: Boolean, default: false }
})

const isOpen = ref(props.defaultOpen)
</script>

<template>
    <div class="bg-gray-50 px-2 py-3 rounded cursor-pointer hover:bg-gray-100" @click="isOpen = !isOpen">
      <div class="flex items-center space-x-2 font-bold">
        <ArrowIcon :class="{ 'rotate-90': isOpen }" class="transition-transform mx-2" />
        <slot name="head" />
      </div>
    </div>

    <Transition name="accordion">
      <div v-if="isOpen" class="space-y-2 ms-5 ml-4 pl-2 border-l-2 py-4">
        <slot />
      </div>
    </Transition>
</template>

<style scoped>
@import '@/../css/assets/accordion.css';
</style>
