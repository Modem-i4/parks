<template>
  <div class="relative w-full max-w-xl min-h-[250px] bg-gray-200 rounded-xl">
    <template v-if="images.length">
      <Swiper
        :modules="[Pagination, Navigation]"
        :pagination="{ clickable: true, el: '.custom-pagination' }"
        :navigation="false"
        @swiper="onSwiper"
        @slideChange="onSlideChange"
        class="rounded-xl overflow-hidden"
      >
        <SwiperSlide v-for="img in images" :key="img.id">
          <img
            :src="img.file_path"
            :alt="img.description || 'Park Image'"
            class="w-full h-64 object-cover"
          />
        </SwiperSlide>
      </Swiper>

      <!-- Custom arrows -->
      <button
        @click="swiper?.slidePrev()"
        class="absolute top-1/2 left-0 z-10 transform -translate-y-1/2 bg-white/70 px-3 py-1 rounded-r-lg shadow hover:bg-white transition"
      >
        ‹
      </button>
      <button
        @click="swiper?.slideNext()"
        class="absolute top-1/2 right-0 z-10 transform -translate-y-1/2 bg-white/70 px-3 py-1 rounded-l-lg shadow hover:bg-white transition"
      >
        ›
      </button>

      <!-- Custom pagination -->
      <div class="custom-pagination absolute bottom-2 w-full flex justify-center gap-2 z-10">
        <span
          v-for="(img, index) in images"
          :key="img.id"
          @click="swiper?.slideTo(index)"
          class="w-3 h-3 rounded-full cursor-pointer"
          :class="{
            'bg-blue-600': index === currentIndex,
            'bg-white/70': index !== currentIndex
          }"
        ></span>
      </div>
    </template>
    <div v-else class="text-center text-gray-500">Зображення відсутні</div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Pagination, Navigation } from 'swiper/modules'

import 'swiper/css'
import 'swiper/css/pagination'
import 'swiper/css/navigation'

const props = defineProps({
  selectedParkId: {
    type: Number,
    required: true,
  },
})

const images = ref([])
const currentIndex = ref(0)
const swiper = ref(null)

watch(
  () => props.selectedParkId,
  async (newId) => {
    if (!newId) return
    images.value = []
    try {
      const { data } = await axios.get(`/api/parks/${newId}/media`)
      images.value = data.media || []
      currentIndex.value = 0
    } catch (e) {
      console.error('Помилка при завантаженні зображень:', e)
    }
  },
  { immediate: true }
)

function onSwiper(swiperInstance) {
  swiper.value = swiperInstance
}

function onSlideChange(swiperInstance) {
  currentIndex.value = swiperInstance.activeIndex
}
</script>
