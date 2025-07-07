<template>
    <svg :class="`w-${size} h-${size} `" class="transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
    </svg>
</template>
<script setup>
defineProps({
    size: {
        type: Number,
        default: 4
    }
})
</script>