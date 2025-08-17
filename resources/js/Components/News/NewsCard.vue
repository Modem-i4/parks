<script setup>
import { computed } from 'vue'

const props = defineProps({
  post: { type: Object, required: true },
  fallbackImage: { type: String, default: '/storage/img/images/park1-1.jpg' }
})

const emit = defineEmits(['open'])

function stripHtml(html) {
  const div = document.createElement('div')
  div.innerHTML = html
  return div.textContent || div.innerText || ''
}

function shortText(text, limit = 200) {
  return text.length > limit ? text.slice(0, limit) + '…' : text
}

const coverSrc = computed(() => props.post.cover?.file_path || props.fallbackImage)
const dateStr = computed(() => props.post.published_at ? new Date(props.post.published_at).toLocaleDateString('uk-UA') : '')
const preview = computed(() => shortText(stripHtml(props.post.body || ''), 200))
</script>

<template>
  <article
    class="bg-white rounded-xl shadow-sm overflow-hidden transition hover:shadow-md hover:scale-[1.01] cursor-pointer relative"
    @click="emit('open', post.id)"
  >
    <img :src="coverSrc" class="w-full h-48 object-cover" alt="preview" />

    <div class="p-4 space-y-2">
      <h2 class="text-lg font-semibold text-gray-800 leading-tight">
        {{ post.title || 'Безіменна новина' }}
      </h2>

      <p class="text-sm text-gray-500" v-if="post.published_at">
        {{ dateStr }}
      </p>

      <div v-else class="absolute bottom-4 right-4 bg-black/40 text-white text-xs font-semibold px-3 py-1 rounded backdrop-blur-sm shadow-lg">
        ЧЕРНЕТКА
      </div>

      <p class="text-sm text-gray-700 leading-snug line-clamp-3">
        {{ preview }}
      </p>
    </div>
  </article>
</template>
