<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import NewsEditor from '@/Components/News/NewsEditor.vue'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import MediaPickerModal from '@/Components/Media/MediaPickerModal.vue'

const props = defineProps({
  news: { type: Object, required: true },
})

const newsItem  = ref({ ...props.news })

const isEditing = ref(false)
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
  <div class="max-w-3xl mx-auto py-10 px-4 space-y-6">
    <div
      class="relative w-full rounded overflow-hidden transition-[height] duration-200"
      :class="newsItem?.cover ? 'h-64 md:h-80 shadow' : 'h-auto'"
      :style="newsItem?.cover
        ? {
            backgroundImage: `url(${newsItem.cover.file_path})`,
            backgroundSize: 'cover',
            backgroundPosition: 'center'
          }
        : {}"
    >
      <template v-if="newsItem?.cover">
        <div v-if="newsItem?.cover" class="absolute inset-0 bg-black/40" />
        <div v-if="!newsItem.published_at"
          class="absolute bottom-2 right-2 bg-black/40 text-white text-xs font-semibold px-3 py-1 rounded backdrop-blur-sm shadow-lg"
        >ЧЕРНЕТКА</div>
      </template>
      <div class="relative p-4 flex items-center justify-center h-full">
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
      </div>
      <div v-if="!newsItem?.cover && !newsItem.published_at" 
        class="inline-block bg-black/40 text-white text-xs font-semibold px-3 py-1 rounded backdrop-blur-sm shadow-lg ms-4"
        >ЧЕРНЕТКА</div>
    </div>
    <div v-if="!isEditing" class="flex justify-around flex-wrap gap-y-2">
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
      </template>
      <template v-if="newsItem.published_at">
        <SecondaryButton @click="setPublished(false)">
          ❌ Приховати
        </SecondaryButton>
      </template>
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
        <div class="fixed bottom-0 left-0 right-0 bg-gray-100 border-t shadow-md">
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
</template>

<style>
@import '@/../css/assets/news.css';
</style>
