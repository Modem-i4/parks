<template>
    <div
      class="flex items-center justify-between bg-red-50 border border-red-300 text-sm text-red-700 px-3 py-2 rounded"
    >
      <span>Справді видалити "{{ label }}"?</span>
      <div class="space-x-1 flex">
        <SecondaryButton size="sm" variant="danger" @click="emit('confirmDelete')">Так</SecondaryButton>
        <SecondaryButton size="sm" @click="emit('cancelDelete')">Ні</SecondaryButton>
      </div>
    </div>
</template>

<script setup>
import SecondaryButton from '@/Components/Default/SecondaryButton.vue';

defineProps({
    label: String
})
const emit = defineEmits(['confirmDelete', 'cancelDelete'])
</script>
