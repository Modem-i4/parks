<script setup>
import Tooltip from '../Custom/Tooltip.vue';

const props = defineProps({
  green: Object
})

const state = props.green?.quality_state ?? null

const stateLabel = {
  good: 'Хорший',
  normal: 'Нормальний',
  bad: 'Поганий',
}[state] ?? state
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
