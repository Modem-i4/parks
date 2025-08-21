<template>
  <HeroSlider :slides="heroSlides" />
  <AboutProject />
  <MapStats />
  <LastNews :news v-if="news" />
</template>

<script setup>
import { ref } from 'vue'
import HeroSlider from '@/Components/Sections/Home/HeroSlider.vue'
import AboutProject from '@/Components/Sections/Home/AboutProject.vue';
import MapStats from '@/Components/Sections/Home/MapStats.vue';
import LastNews from '@/Components/Sections/Home/LastNews.vue';

const heroSlides = ref([
  {
    title: 'імені Тараса Шевченка',
    subtitle: 'Осінні алеї та події щотижня',
    image: '/img/parks/shevchenko_park.webp',
    icon: '/img/icons/shevchenko_park.png',
    href: route('parks', { id: 1 })
  },
  {
    title: 'воїнів-інтернаціоналістів',
    subtitle: 'Велодоріжки та маршрути',
    image: '/img/parks/liberators_park.webp',
    icon: '/img/icons/liberators_park.png',
    href: route('parks', { id: 2 })
  },
  {
    title: 'на вул. Молодіжна',
    subtitle: 'Майданчики для дітей',
    image: '/img/parks/hotkevych_park.webp',
    icon: '/img/icons/hotkevych_park.png',
    href: route('parks', { id: 3 })
  }
])

const props = defineProps({
  news: Array
})
</script>
