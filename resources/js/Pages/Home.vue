<template>
  <Head title="Головна" />
  <HeroSlider :slides="heroSlides" />
  <StrategicPlan/>
  <AboutProject />
  <MapStats :stats>
    <ParksMapThumbnail :parks />
  </MapStats>
  <GenusStats :genus="stats.genus" />
  <LastNews :news v-if="news" />
</template>

<script setup>
import { ref } from 'vue'
import HeroSlider from '@/Components/Sections/Home/HeroSlider.vue'
import AboutProject from '@/Components/Sections/Home/AboutProject.vue';
import MapStats from '@/Components/Sections/Home/MapStats.vue';
import LastNews from '@/Components/Sections/Home/LastNews.vue';
import ParksMapThumbnail from '@/Components/Sections/Home/ParksMapThumbnail.vue';
import { Head } from '@inertiajs/vue3'
import GenusStats from '@/Components/Sections/Home/GenusStats.vue';
import StrategicPlan from '@/Components/Sections/Home/StrategicPlan.vue';

const heroSlides = ref([
  {
    title: 'ім. Тараса Шевченка',
    subtitle: 'Міське озеро, алеї та атракціони',
    image: '/img/parks/shevchenko_park.webp',
    icon: '/img/icons/shevchenko_park.png',
    href: '/parks/1'
  },
  {
    title: 'воїнів-афганців',
    subtitle: 'Меморіал, алея пам’яті та спорт',
    image: '/img/parks/liberators_park.webp',
    icon: '/img/icons/liberators_park.png',
    href: '/parks/2'
  },
  {
    title: 'по вул. Молодіжна',
    subtitle: 'Тихі доріжки і вигул собак',
    image: '/img/parks/hotkevych_park.webp',
    icon: '/img/icons/hotkevych_park.png',
    href: '/parks/3'
  }
])

const props = defineProps({
  news: Array,
  stats: Object,
  parks: Array
})
</script>
