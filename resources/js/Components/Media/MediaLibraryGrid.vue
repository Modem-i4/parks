<template>
  <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2 mt-2">
    <MediaItem
      v-for="file in visibleLibrary"
      :key="file.id"
      :mediaFile="file"
      :selected="selectedIds.includes(file.id)"
      @click="() => $emit('toggle', file)"
    />

    <div ref="sentinel" class="col-span-full h-1"></div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import MediaItem from './MediaItem.vue'

const props = defineProps({
  library: { type: Array, required: true },
  selected: { type: Array, required: true },
  multiple: { type: Boolean, default: false }
})

defineEmits(['toggle'])

const selectedIds = computed(() => props.selected.map(i => i.media_library_id))

const BATCH = 60
const visibleCount = ref(BATCH)
const visibleLibrary = computed(() => props.library.slice(0, visibleCount.value))

const sentinel = ref(null)
let io = null

function loadMore() {
  if (visibleCount.value < props.library.length) {
    visibleCount.value = Math.min(visibleCount.value + BATCH, props.library.length)
  }
}

watch(
  () => props.library.length,
  (len) => {
    visibleCount.value = Math.min(visibleCount.value, len || 0) || Math.min(BATCH, len || 0)
  },
  { immediate: true }
)

onMounted(() => {
  io = new IntersectionObserver(
    (entries) => {
      if (entries[0]?.isIntersecting) loadMore()
    },
    { root: null, rootMargin: '600px 0px' }
  )
  if (sentinel.value) io.observe(sentinel.value)
})

onBeforeUnmount(() => {
  if (io && sentinel.value) io.unobserve(sentinel.value)
  io?.disconnect()
})
</script>
