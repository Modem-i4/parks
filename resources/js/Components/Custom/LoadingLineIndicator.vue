<script setup>
defineProps({
    isLoading: Boolean
})
</script>

<template>
<div class="overflow-hidden transition-opacity duration-300" :class="{ 'opacity-100': isLoading, 'opacity-0': !isLoading }">
    <div class="absolute h-1 inset-0 bg-gradient-to-r from-green-400 via-blue-500 to-purple-500 animate-pulse" />
</div>
</template>