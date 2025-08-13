<script setup>
import { ref, watch } from 'vue'
import axios from 'axios'
import NewsEditor from '@/Components/News/NewsEditor.vue'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'

const props = defineProps({
  news: { type: Object, required: true },
})

const newsItem  = ref({ ...props.news })

const isEditing = ref(false)
const processing = ref(false)

const form = ref({
  title: newsItem .value.title ?? '',
  body:  newsItem .value.body ?? '',
})

watch(() => props.news, (n) => {
  if (!n) return
  newsItem .value = { ...n }
  form.value.title = n.title ?? ''
  form.value.body  = n.body ?? ''
})

function enableEdit() {
  isEditing.value = true
}

function cancelEdit() {
  isEditing.value = false
  form.value = {
    title: newsItem .value.title ?? '',
    body:  newsItem .value.body ?? '',
  }
}

function saveEdit() {
  processing.value = true
  axios.patch(`/api/news/${newsItem .value.id}`, form.value)
    .then((res) => {
      newsItem .value = res.data
      isEditing.value = false
    })
    .catch(err => {
      console.error('Помилка збереження', err)
    })
    .finally(() => {
      processing.value = false
    })
}
</script>

<template>
  <div class="max-w-3xl mx-auto py-10 px-4 space-y-6">
    <div class="flex justify-between items-center gap-3">
      <template v-if="isEditing">
        <input
          v-model="form.title"
          type="text"
          class="text-2xl font-bold text-gray-800 border border-gray-300 rounded px-2 py-1 w-full"
          placeholder="Заголовок"
        />
      </template>
      <template v-else>
        <h1 class="text-3xl font-bold text-gray-800">{{ newsItem .title }}</h1>
        <SecondaryButton @click="enableEdit">
          ✏️ Редагувати
        </SecondaryButton>
      </template>
    </div>

    <p class="text-sm text-gray-500">
      {{ new Date(newsItem .created_at).toLocaleDateString() }}
    </p>

    <img
      v-if="newsItem .cover"
      :src="newsItem .cover.file_path"
      class="w-full rounded shadow object-cover h-64"
      alt="Зображення"
    />

    <div class="news-content">
      <div v-if="!isEditing" class="prose max-w-none" v-html="newsItem .body" />
      <div v-else class="space-y-4">
        <NewsEditor
          v-model="form.body"
          placeholder="Напишіть новину…"
        />

        <div class="flex justify-end gap-2">
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
</template>

<style>
@import '@/../css/assets/news.css';
</style>
