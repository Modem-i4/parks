<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: Number,
  min: { type: Number, default: 0 },
  max: { type: Number, default: 100 },
  label: { type: String, default: '' }
})

const emit = defineEmits(['update:modelValue'])

const value = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', Number(val))
})
</script>

<template>
  <div class="p-2">
    <div class="font-sm mt-auto">{{ label }}</div>

    <div class="flex flex-row items-center space-x-4">
      <input
        type="number"
        :min="min"
        :max="max"
        v-model.number="value"
        class="w-24 border border-gray-300 rounded px-1 py-0.5 text-center"
      />

      <input
        type="range"
        :min="min"
        :max="max"
        v-model.number="value"
        class="w-full accent-green-600"
      />
    </div>
  </div>
</template>
