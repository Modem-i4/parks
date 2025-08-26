<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import NewsEditor from '@/Components/News/NewsEditor.vue'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import MediaPickerModal from '@/Components/Media/MediaPickerModal.vue'
import { useAuthStore } from '@/Stores/useAuthStore'
import { Head, Link, router } from '@inertiajs/vue3'
import PageCoverHead from '@/Components/Custom/PageCoverHead.vue'
import NewsItem from '@/Components/News/NewsItem.vue'

const props = defineProps({
  news: { type: Object, required: true },
  lastNews: { type: Object },
})

const authStore = useAuthStore()

const newsItem  = ref({ ...props.news })

const isEditingDefault = new URLSearchParams(location.search).get('edit') === '1' && authStore.can.editNews

const isEditing = ref(isEditingDefault)
const confirmingDelete = ref(false)
const processing = ref(false)

const showModal = ref({ cover: false })

const form = ref({
  title: newsItem.value.title ?? '',
  body:  newsItem.value.body ?? '',
})

watch(() => props.news, (n) => {
  if (!n) return
  newsItem.value = { ...n }
  form.value.title = n.title ?? ''
  form.value.body  = n.body ?? ''
})

function setPublished(published) {
  form.value.published_at = published 
    ? new Date().toISOString()
    : null
  saveEdit()
}

function deletePost() {
  axios.delete(`/api/news/${newsItem.value.id}`)
    .then(() => {
      router.visit('/news')
    })
}

function cancelEdit() {
  isEditing.value = false
  form.value = {
    title: newsItem.value.title ?? '',
    body:  newsItem.value.body ?? '',
    published_at: newsItem.value.published_at,
  }
}

function saveEdit() {
  processing.value = true
  axios.patch(`/api/news/${newsItem.value.id}`, form.value)
    .then((res) => {
      newsItem.value = res.data
      isEditing.value = false
    })
    .catch(err => {
      console.error('Помилка збереження', err)
    })
    .finally(() => {
      processing.value = false
    })
}

function imagePicked(imgs) {
  const img = imgs[0]
  newsItem.value.cover = img || null
  showModal.value.cover = false
}
</script>

<template>
  <Head :title="newsItem.title" />
  <PageCoverHead :coverImg="newsItem?.cover?.file_path">
    <template #default>
      <template v-if="isEditing">
        <textarea
          v-model="form.title"
          :class="[
            'text-2xl font-bold px-2 py-1 h-full w-full rounded outline-none',
            newsItem?.cover
              ? 'text-white bg-black/30 backdrop-blur'
              : 'text-gray-800'
          ]"
          placeholder="Заголовок"
        />
      </template>
      <template v-else>
        <h1
          :class="[
            'text-3xl font-bold',
            newsItem?.cover ? 'text-white drop-shadow' : 'text-gray-800'
          ]"
        >
          {{ newsItem.title }}
        </h1>
      </template>
    </template>

    <template #badge>
      <div v-if="newsItem?.cover && !newsItem.published_at"
        class="absolute bottom-2 right-2 bg-black/40 text-white text-xs font-semibold px-4 py-2 rounded backdrop-blur-sm shadow-lg"
      >ЧЕРНЕТКА</div>
    </template>
  </PageCoverHead>
  <div class="max-w-7xl mx-auto py-10 px-4 space-y-6">
    <div v-if="!newsItem?.cover && !newsItem.published_at" 
      class="inline-block bg-black/40 text-white text-xs font-semibold px-3 py-1 rounded backdrop-blur-sm shadow-lg ms-4"
    >ЧЕРНЕТКА</div>
    <div v-if="!isEditing && authStore.can.editNews && !confirmingDelete" class="flex justify-around flex-wrap gap-y-2">
      <SecondaryButton @click="showModal.cover = true">
        🖼️ Змінити обкладинку
      </SecondaryButton>
      <SecondaryButton @click="isEditing = true">
        ✏️ Редагувати
      </SecondaryButton>
      <template v-if="!newsItem.published_at">
        <PrimaryButton @click="setPublished(true)">
          📰 Опублікувати
        </PrimaryButton>
        <SecondaryButton @click="confirmingDelete = !confirmingDelete">
          🗑️ Видалити
        </SecondaryButton>
      </template>
      <template v-if="newsItem.published_at">
        <SecondaryButton @click="setPublished(false)">
          ❌ Приховати
        </SecondaryButton>
      </template>
    </div>
    <div v-if="confirmingDelete && authStore.can.editNews" class="bg-red-50 rounded p-4 mx-5 md:mx-20">
      <div class="text-lg text-center font-semibold my-2">Дійсно видалити новину?</div>
      <div class="flex justify-around flex-wrap gap-y-2">
        <SecondaryButton @click="confirmingDelete = false" class="bg-white">
          ❌ Скасувати
        </SecondaryButton>
        <SecondaryButton @click="deletePost">
          🗑️ Видалити
        </SecondaryButton>
      </div>
    </div>

    <p class="text-sm text-gray-500" v-if="newsItem?.published_at">
      {{ new Date(newsItem.published_at).toLocaleDateString() }}
    </p>

    <div class="news-content">
      <div v-if="!isEditing" class="prose max-w-none" v-html="newsItem.body" />
      <div v-else class="space-y-4">
        <NewsEditor
          v-model="form.body"
          placeholder="Напишіть новину…"
        />
        <div class="fixed bottom-0 left-0 right-0 bg-gray-100 border-t shadow-md z-[51]">
          <div class="max-w-3xl mx-auto flex justify-end gap-2 p-4">
            <SecondaryButton
              @click="cancelEdit"
              class="text-sm text-gray-600 hover:underline"
              :disabled="processing"
            >
              Скасувати
            </SecondaryButton>
            <PrimaryButton
              @click="saveEdit"
              class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 disabled:opacity-50"
              :disabled="processing"
            >
              {{ processing ? 'Збереження…' : '💾 Зберегти' }}
            </PrimaryButton>
          </div>
        </div>
      </div>
    </div>
  </div>
  <MediaPickerModal
    v-if="showModal.cover"
    type="image"
    modelType="App\Models\News"
    :modelId="newsItem.id"
    :multipleSelect="false"
    @close="showModal.cover = false"
    @saved="imagePicked"
  />
  <template v-if="lastNews">
    <div class="max-w-7xl mx-auto py-10 px-4 space-y-6"> 
      <hr class="h-[2px] bg-black mt-4"/>
      <h2 class="text-2xl md:text-4xl font-bold">НОВИНИ</h2>
      <div class="grid gap-12 sm:grid-cols-2 lg:grid-cols-3">
        <NewsItem
            v-for="item in lastNews"
            :key="item.id"
            :post="item"
        />
        </div>
    </div>
  </template>
  <div class="flex justify-center mb-10">
      <Link
        href="/news"
        class="bg-[#007C57] text-white font-bold text-lg px-10 py-4 rounded-md hover:bg-[#006347] transition"
      >
        ВСІ НОВИНИ
      </Link>
  </div>
</template>

<style>
@import '@/../css/assets/news.css';
</style>
