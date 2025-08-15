<script setup>
import SearchBar from '@/Components/Custom/SearchBar.vue'
import { router } from '@inertiajs/vue3'
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  news: Array,
  nextPage: Number,
  query: String
})

const list = ref([...(props.news ?? [])])

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

const searchQuery = ref(props.query || '')
const nextPage = ref(props.nextPage)

function updateUrlQuery(q) {
  const url = new URL(window.location.href)
  if (q && q.trim() !== '') url.searchParams.set('q', q)
  else url.searchParams.delete('q')
  history.replaceState(null, '', url)
}

async function search() {
  axios.get('/api/news', { params: { q: searchQuery.value || undefined } })
    .then((res) => {
      list.value = res.data.data ?? []
      nextPage.value = res.data.nextPage || null
      updateUrlQuery(searchQuery.value)
    })
}

function loadMore() {
  if (!nextPage.value) return
  axios.get('/api/news', { params: { q: searchQuery.value || undefined, page: nextPage.value } })
    .then((res) => {
      list.value.push(...(res.data.data ?? []))
      nextPage.value = res.data.nextPage || null
    })
}
</script>

<template>
  <div class="max-w-6xl mx-auto py-10 px-4">
    <div class="space-y-2 mb-8">
      <h1 class="text-3xl font-bold text-center text-gray-800">📰 Новини</h1>
      <SearchBar v-model="searchQuery" @search="search" placeholder="Що вас цікавить?" />
    </div>

    <div v-if="list.length === 0" class="text-center text-gray-500">
      Нічого не знайдено.
    </div>

    <div v-else class="grid gap-6 sm:grid-cols-2">
      <div
        v-for="item in list"
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
            {{ item.published_at ? new Date(item.published_at).toLocaleDateString() : 'Чернетка' }}
          </p>

          <p class="text-sm text-gray-700 leading-snug line-clamp-3">
            {{ shortText(stripHtml(item.body || ''), 200) }}
          </p>
        </div>
      </div>
    </div>

    <div v-if="nextPage" class="mt-6 text-center">
      <button
        @click="loadMore"
        class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg"
      >Завантажити ще</button>
    </div>
  </div>
</template>
