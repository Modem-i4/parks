<script setup>
import { ref } from 'vue'

const props = defineProps({
  errors: {
    type: Array,
    default: () => []
  }
})

const expanded = ref(false)
</script>

<template>
  <div v-if="errors.length" class="text-sm">
    <button
      type="button"
      class="w-full text-left bg-red-50 border border-red-300 text-red-700 px-3 py-2 rounded italic hover:bg-red-100 transition-colors cursor-pointer"
      @click="expanded = !expanded"
      :aria-expanded="expanded"
    >
      Заповніть поле правильно
    </button>

    <div
      class="overflow-hidden transition-all duration-300"
      :class="expanded ? 'max-h-48 opacity-100 mt-2' : 'max-h-0 opacity-0'"
    >
      <ul class="bg-red-50 border border-red-200 text-red-800 rounded px-3 py-2 space-y-1">
        <li v-for="(err, i) in errors" :key="i">{{ err }}</li>
      </ul>
    </div>
  </div>
</template>
