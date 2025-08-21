<template>
  <div class="absolute inset-y-0 w-full h-full" :style="{ left: `${index*100}%` }">
    <div class="relative w-full h-full overflow-hidden">
      <img
        :src="slide.image"
        alt=""
        class="absolute inset-0 w-[110%] h-[110%] object-cover will-change-transform"
        :style="{ transform: `translate3d(${imgX}%,0,0) scale(1.06)` }"
      />
      <div class="absolute inset-0 bg-black/40"></div>
      <div
        class="relative h-full max-w-7xl mx-auto px-6 md:px-24
               md:flex md:flex-col md:justify-center md:items-start"
        :style="{ transform: `translate3d(${contentX}%,0,0)` }"
      >
        <div
          class="absolute left-6 top-24 flex flex-col items-start gap-3
                 md:static md:flex-row md:items-center md:gap-4"
        >
          <img v-if="slide.icon" :src="slide.icon"
               class="w-16 h-16 md:w-32 md:h-32 filter brightness-0 invert pointer-events-none" alt="" />
          <h2 class="text-white font-extrabold leading-tight text-3xl md:text-5xl">
            <span class="block">Парк</span>
            <span class="block md:inline">{{ slide.title }}</span>
          </h2>
        </div>
        <div
          class="absolute inset-x-0 bottom-24 flex justify-center
                 md:static md:mt-6 md:justify-start"
        >
          <a :href="slide.href || '#'"
             class="rounded bg-white text-gray-900 font-semibold px-5 py-3 shadow hover:shadow-lg transition">
            ПЕРЕГЛЯНУТИ
          </a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  slide: { type: Object, required: true },
  index: { type: Number, required: true },
  current: { type: Number, required: true },
  imgX: { type: Number, required: true },
  contentX: { type: Number, required: true },
})
</script>
