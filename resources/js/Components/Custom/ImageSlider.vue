<template>
  <div class="relative w-full max-w-xl mx-auto min-h-[250px] bg-gray-200 rounded-xl h-64"
    @click="emit('onImageClick')"
  >
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
            :alt="img.description || 'Image'"
            class="w-full h-64 object-cover"
          />
        </SwiperSlide>
      </Swiper>

      <!-- Custom arrows -->
      <button
        @click.stop="swiper?.slidePrev()"
        class="absolute top-1/2 left-0 z-10 transform -translate-y-1/2 bg-white/70 px-3 py-1 rounded-r-lg shadow hover:bg-white transition"
        v-show="images.length > 1"
      >
        ‹
      </button>
      <button
        @click.stop="swiper?.slideNext()"
        class="absolute top-1/2 right-0 z-10 transform -translate-y-1/2 bg-white/70 px-3 py-1 rounded-l-lg shadow hover:bg-white transition"
        v-show="images.length > 1"
      >
        ›
      </button>

      <!-- Custom pagination -->
      <div class="custom-pagination absolute bottom-2 w-full flex justify-center gap-2 z-10">
        <span
          v-for="(img, index) in images"
          :key="img.id"
          @click.stop="swiper?.slideTo(index)"
          class="w-3 h-3 rounded-full cursor-pointer"
          :class="{
            'bg-blue-600': index === currentIndex,
            'bg-white/70': index !== currentIndex
          }"
        ></span>
      </div>
    </template>
    <div v-else class="text-center text-gray-500 absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
      Зображення відсутні
      <img src="/img/icons/camera-create-icon.svg" alt="No images" class="w-12 h-12 mx-auto mt-2" v-if="editable" />
    </div>
    <!-- Overlay -->
    <div
      v-if="editable"
      class="absolute top-0 left-0 right-0 text-white text-sm text-center z-10 pointer-events-none"
    >
      <div class="bg-black/50 py-1 px-2 rounded-t-xl">Натисніть для додавання зображень</div>
      <div v-if="sourceName" class="bg-black/30 py-0.5 px-2 w-[50%] mx-auto rounded-b-xl text-xs"
        ><i>Джерело: {{ sourceName }}</i></div>
    </div>

  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import axios from 'axios'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Pagination, Navigation } from 'swiper/modules'

import 'swiper/css'
import 'swiper/css/pagination'
import 'swiper/css/navigation'

const props = defineProps({
  model: {
    type: String,
    required: true
  },
  modelId: {
    type: [Number, String, null],
    required: true
  },
  isDraft: Boolean,
  editable: {
    type: Boolean,
    default: false
  }
})

const images = ref([])
const currentIndex = ref(0)
const swiper = ref(null)

const source = ref(null)

const emit = defineEmits(['onImageClick'])

defineExpose({ update })

watch(
  () => props.modelId,
  update,
  { immediate: true }
)
async function update(newId) {
  if (!newId || props.isDraft) return
  images.value = []
  try {
    const { data } = await axios.get(`/api/${props.model}/${newId}/media`)
    images.value = data.media || []
    currentIndex.value = 0
    source.value = data.source || null
  } catch (e) {
    console.error('Помилка при завантаженні зображень:', e)
  }
}
function onSwiper(swiperInstance) {
  swiper.value = swiperInstance
}

function onSlideChange(swiperInstance) {
  currentIndex.value = swiperInstance.activeIndex
}

const sourceNameUkr = {
  'marker': 'Цей маркер',
  'infrastructure_type': 'Тип інфраструктури',
  'species': 'Вид',
  'genus': 'Рід',
  'family': 'Родина',
}

const sourceName = computed(() => {
  if (!props.editable || images.value.length === 0) return null
  return sourceNameUkr[source.value] || null
})
</script>
