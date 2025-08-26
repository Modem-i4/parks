<script setup>
const props = defineProps({
  post: Object,
  coverSrc: String,
  preview: String,
  dateStr: String
})
</script>

<template>
  <article
    class="overflow-hidden cursor-pointer space-y-2 hover:bg-gray-100 hover:shadow-md p-2 transition-all hover:scale-[1.01] flex"
  >
    <img :src="coverSrc" alt="" class="w-56 md:w-[20em]" />
    <div class="p-4">
        <h3 class="text-xl font-extrabold leading-snug tracking-tight">
        {{ post.title || 'Безіменна новина' }}
        </h3>
        <p class="text-sm text-gray-500 mt-1 mb-2">
        {{ post.published_at ? dateStr : 'Чернетка' }}
        </p>
        <p class="text-sm font-semibold leading-tight text-justify text-gray-700">
        {{ preview }}
        </p>
        <div
            v-if="!post.published_at"
            class="absolute top-3 right-3 bg-black/60 text-white text-[10px] font-semibold px-2 py-1 rounded"
        >
            ЧЕРНЕТКА
        </div>
    </div>
  </article>
</template>
