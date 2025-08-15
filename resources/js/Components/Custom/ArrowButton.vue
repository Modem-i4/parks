<template>
  <button
    :class="[
      'group w-full font-semibold py-2 px-4 rounded-lg shadow flex items-center justify-between transition',
      variantClasses
    ]"
    @click="$emit('click')"
  >
    <span><slot /></span>
    <span class="transform transition-transform group-hover:translate-x-1">→</span>
  </button>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'primary'
  }
})
const variantClasses = computed(() => {
  return {
    primary: 'bg-blue-500 text-white hover:bg-blue-700',
    secondary: 'bg-[#007c57] text-white border border-blue-600 hover:bg-[#006f4d]',
    secondaryBlack: 'bg-white text-black-600 border border-black-600 hover:bg-gray-100'
  }[props.variant]
})

</script>
