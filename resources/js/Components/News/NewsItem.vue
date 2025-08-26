<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import NewsInline from './NewsInline.vue'
import NewsCard from './NewsCard.vue'

const props = defineProps({
  post: { type: Object, required: true },
  fallbackImage: { type: String, default: '/img/parks/default/park1-1.jpg' },
  variant: { type: String, default: 'card' }
})

function openNews(id) {
  router.visit(`/news/${id}`)
}

function stripHtml(html) {
  const div = document.createElement('div')
  div.innerHTML = html
  return div.textContent || div.innerText || ''
}

function shortText(text, limit = 380) {
  return text.length > limit ? text.slice(0, limit) + '…' : text
}

const coverSrc = computed(() => props.post.cover?.file_path || props.fallbackImage)
const dateStr  = computed(() =>
  props.post.published_at ? new Date(props.post.published_at).toLocaleDateString('uk-UA') : ''
)
const previewLength = computed(() => ({
  card: 260,
  inline: 380
}[props.variant] || 260))
const preview  = computed(() => shortText(stripHtml(props.post.body || ''), previewLength.value))
</script>

<template>
    <Component :is="variant === 'inline' ? NewsInline : NewsCard"
      :post
      :coverSrc
      :preview
      :dateStr
      @click="openNews(post.id)"
    />
</template>
