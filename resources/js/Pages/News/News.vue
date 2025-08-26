<script setup>
import { ref } from 'vue'
import axios from 'axios'
import { Head, router } from '@inertiajs/vue3'
import SearchBar from '@/Components/Custom/SearchBar.vue'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'

import { useAuthStore } from '@/Stores/useAuthStore'
import NewsInline from '@/Components/News/NewsInline.vue'
import PageCoverHead from '@/Components/Custom/PageCoverHead.vue'
import NewsItem from '@/Components/News/NewsItem.vue'

const props = defineProps({
  news: Array,
  nextPage: Number,
  perPage: Number,
  query: String
})

const list = ref([...(props.news ?? [])])
const searchQuery = ref(props.query || '')
const nextPage = ref(props.nextPage)
const fallbackImage = '/img/parks/default/park1-1.jpg'

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
  <Head title="Новини" />
  <PageCoverHead coverImg="/img/parks/news-cover.webp">
    <div class="w-full md:w-1/2">
      <h1 class="text-3xl font-bold text-white">НОВИНИ</h1>
      <div class="space-y-2 mt-5">
        <SearchBar v-model="searchQuery" @search="search" placeholder="Що вас цікавить?" />
        <div class="flex justify-center" v-if="useAuthStore().can.editNews">
          <PrimaryButton class="bg-green-800" @click="addPost">Додати новину</PrimaryButton>
        </div>
      </div>
    </div>
  </PageCoverHead>
  <div class="max-w-6xl mx-auto py-10 px-4">
    <div v-if="list.length === 0" class="text-center text-gray-500">
      Нічого не знайдено.
    </div>
    <div v-else>
      <NewsItem
        v-for="(item, index) in list"
        :key="item.id"
        :post="item"
        :fallback-image="fallbackImage"
        :class="{'opacity-0 animate-fadeIn': index > props.perPage}"
        variant="inline"
      />
    </div>

    <div v-if="nextPage" class="mt-6 text-center">
      <button
        @click="loadMore"
        class="bg-[#007C57] text-white font-bold text-lg px-10 py-4 rounded-md hover:bg-[#006347] transition"
      >
          Завантажити ще
      </button>
    </div>
  </div>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}
.animate-fadeIn {
  animation: fadeIn 0.7s ease forwards;
}
</style>