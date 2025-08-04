<!-- Components/Default/Switch.vue -->
<template>
  <div class="inline-flex p-1 bg-gray-200 rounded-lg text-sm font-medium">
    <button
      v-for="option in options"
      :key="option.value"
      type="button"
      @click="$emit('update:modelValue', option.value)"
      :class="[
        'px-4 py-1 rounded-md transition',
        modelValue === option.value
          ? `bg-white shadow text-${option.color ?? 'blue'}-700`
          : 'text-gray-600 hover:text-black'
      ]"
    >
      {{ option.label }}
    </button>
  </div>
</template>

<script setup>
defineProps({
  modelValue: String,
  options: {
    type: Array,
    required: true // [{ value: 'filtered', label: 'Фільтровано', color: 'blue' }, ...]
  }
})
defineEmits(['update:modelValue'])
</script>
