<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { router } from '@inertiajs/vue3'
import SearchBar from '@/Components/Custom/SearchBar.vue'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'

import { useAuthStore } from '@/Stores/useAuthStore'
import NewsCard from '@/Components/News/NewsCard.vue'

const props = defineProps({
  news: Array,
  nextPage: Number,
  query: String
})

const authStore = useAuthStore()
const list = ref([...(props.news ?? [])])
const searchQuery = ref(props.query || '')
const nextPage = ref(props.nextPage)
const fallbackImage = '/storage/img/images/park1-1.jpg'

function openNews(id) {
  router.visit(`/news/${id}`)
}

function updateUrlQuery(q) {
  const url = new URL(window.location.href)
  if (q && q.trim() !== '') url.searchParams.set('q', q)
  else url.searchParams.delete('q')
  history.replaceState(null, '', url)
}

function search() {
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

function addPost() {
  axios.post('/api/news')
    .then((res) => {
      router.visit(`news/${res.data.id}`, { data: { edit: 1 } })
    })
}
</script>

<template>
  <div class="max-w-6xl mx-auto py-10 px-4">
    <div class="space-y-2 mb-8">
      <h1 class="text-3xl font-bold text-center text-gray-800">📰 Новини</h1>
      <SearchBar v-model="searchQuery" @search="search" placeholder="Що вас цікавить?" />
      <div class="flex justify-center" v-if="useAuthStore().can.editNews">
        <PrimaryButton class="bg-green-800" @click="addPost">Додати новину</PrimaryButton>
      </div>
    </div>

    <div v-if="list.length === 0" class="text-center text-gray-500">
      Нічого не знайдено.
    </div>

    <div v-else class="grid gap-6 sm:grid-cols-2">
      <NewsCard
        v-for="item in list"
        :key="item.id"
        :post="item"
        :fallback-image="fallbackImage"
        @open="openNews"
      />
    </div>

    <div v-if="nextPage" class="mt-6 text-center">
      <button
        @click="loadMore"
        class="px-6 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg"
      >Завантажити ще</button>
    </div>
  </div>
</template>
