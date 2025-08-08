<script setup>
import { router } from '@inertiajs/vue3'

const props = defineProps({
  news: Array
})

function openNews(id) {
  router.visit(`/news/${id}`)
}

function stripHtml(html) {
  const div = document.createElement('div')
  div.innerHTML = html
  return div.textContent || div.innerText || ''
}

function shortText(text, limit = 200) {
  return text.length > limit ? text.slice(0, limit) + '…' : text
}

const fallbackImage = '/storage/img/images/park1-1.jpg'
</script>

<template>
  <div class="max-w-6xl mx-auto py-10 px-4">
    <h1 class="text-3xl font-bold mb-8 text-center text-gray-800">📰 Новини</h1>

    <div v-if="news.length === 0" class="text-center text-gray-500">
      Новин ще немає.
    </div>

    <div v-else class="grid gap-6 sm:grid-cols-2">
      <div
        v-for="item in news"
        :key="item.id"
        class="bg-white rounded-xl shadow-sm overflow-hidden transition hover:shadow-md hover:scale-[1.01] cursor-pointer"
        @click="openNews(item.id)"
      >
        <img
          :src="item.cover?.file_path || fallbackImage"
          class="w-full h-48 object-cover"
          alt="preview"
        />

        <div class="p-4 space-y-2">
          <h2 class="text-lg font-semibold text-gray-800 leading-tight">
            {{ item.title }}
          </h2>

          <p class="text-sm text-gray-500">
            {{ new Date(item.created_at).toLocaleDateString() }}
          </p>

          <p class="text-sm text-gray-700 leading-snug line-clamp-3">
            {{ shortText(stripHtml(item.body), 200) }}
          </p>
        </div>
      </div>
    </div>
  </div>
</template>
