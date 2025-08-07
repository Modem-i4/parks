<script setup>
import { computed } from 'vue';
import Tooltip from '@/Components/Custom/Tooltip.vue';

const props = defineProps({
  green: Object
})

const state = computed(() => props.green?.green_state ?? null)

const stateLabel = computed(() => {
  return {
    good: 'Хороший',
    normal: 'Нормальний',
    bad: 'Поганий',
  }[state.value] ?? state.value
})

</script>

<template>
  <div v-if="state" class="relative group flex items-center justify-center w-4 h-4">
    <div
      class="w-4 h-4 rounded-full"
      :class="{
        'bg-green-500': state === 'good',
        'bg-yellow-500': state === 'normal',
        'bg-red-500': state === 'bad',
      }"
    />
    <Tooltip>
      Стан: {{ stateLabel }}
    </Tooltip>
  </div>
</template>
