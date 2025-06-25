<template>
  <div class="p-4">
    <button @click="$emit('back')" class="text-blue-500 mb-2">← Назад</button>
    <img :src="park.icon" alt="" class="w-16 h-16 mb-2" />
    <h2 class="text-xl font-bold">{{ park.title }}</h2>
    <p class="text-gray-600">{{ park.description }}</p>
    <p class="mt-2 text-sm">{{ park.address }}</p>
  </div>
</template>

<script setup>
defineProps({ park: Object })
</script>
