<template>
    <PdfFileSection 
        title="Стратегічний план" 
        subtitle="управління міськими зеленими насадженнями в Івано-Франківську та Ряшеві"
        href="/files/strategic-plan.pdf" 
        :class="isScrolled ? 'pt-10' : 'pt-3'"
    />
</template>

<script setup>
import PdfFileSection from '@/Components/Custom/PdfFileSection.vue';
import { onMounted, onUnmounted, ref } from 'vue'

const isScrolled = ref(false)

const updateScrollState = () => isScrolled.value = document.body.scrollTop > 0

onMounted(() => {
  updateScrollState()
  document.body.addEventListener('scroll', updateScrollState, { passive: true })
})
onUnmounted(() => document.body.removeEventListener('scroll', updateScrollState))
</script>