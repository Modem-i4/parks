<template>
  <section class="max-w-7xl mx-auto px-4 transition-all duration-300 ease-in-out">
    <div class="border-2 border-[#007C57] rounded-md p-4 md:px-6 flex flex-col md:flex-row md:items-center md:justify-between gap-5">
      <div class="space-y-1">
        <h2 class="text-2xl md:text-3xl font-extrabold uppercase tracking-wide text-[#007C57]">
          {{ title }}
        </h2>
        <p class="text-base md:text-lg leading-6 text-gray-700">
          {{ subtitle }}
        </p>
      </div>

      <div class="flex items-center gap-3 md:shrink-0 justify-center">
        <a :href target="_blank" class="inline-flex min-h-14 items-center justify-center bg-[#007C57] text-white font-bold text-lg px-8 sm:px-10 py-3 rounded-md hover:bg-[#006347] transition"
        >
          Переглянути
        </a>
        <a :href download
          class="inline-flex w-14 h-14 items-center justify-center rounded-md border-2 border-[#007C57] text-[#007C57] hover:bg-[#007C57] hover:text-white transition"
          aria-label="Скачати стратегічний план" title="Скачати" >
          <span class="w-6 h-6" v-html="downloadIcon" />
        </a>
      </div>
    </div>
  </section>
</template>

<script setup>
import downloadIcon from '@/assets/misc/download_icon.svg?raw'
const props = defineProps({
  href: { type: String, default: '#' },
  title: { type: String, default: 'Файл' },
  subtitle: { type: String, default: 'для скачування' },
})
</script>