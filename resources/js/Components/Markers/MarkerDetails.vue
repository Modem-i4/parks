<script setup>
import ImageSlider from '@/Components/Custom/ImageSlider.vue'
import PanelHeader from '@/Components/Custom/PanelHeader.vue'
import TagList from './TagList.vue'
import QualityStateIndicator from './QualityStateIndicator.vue'
import { useParkStore } from '@/Stores/useParkStore.js'
import { ref, watch } from 'vue'
import axios from 'axios'
import GreenDetails from './GreenDetails.vue'
import Tooltip from '../Custom/Tooltip.vue'

const parkStore = useParkStore()
const marker = ref(null)
const loading = ref(true)

const copyCompleted = ref(false)

function back() {
  parkStore.selectedMarker = null
}

watch(
  () => parkStore.selectedMarker,
  async (newVal) => {
    marker.value = newVal
    if (!newVal) return

    loading.value = true
    try {
      const { data } = await axios.get(`/api/markers/${marker.value.id}`)
      marker.value = data
    } catch (e) {
      console.error('Не вдалося довантажити маркер:', e)
    } finally {
      loading.value = false
    }
  },
  { immediate: true }
)

const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    copyCompleted.value = true
  } catch (err) {
    console.error('Не вдалося скопіювати:', err)
  }
}
</script>

<template>
  <div class="p-4 overflow-x-hidden" v-if="marker">
    <button @click="back" class="text-blue-500 mb-2">← Назад</button>

    <PanelHeader :subtitle="marker.type" :icon="marker.icon?.file_path">
      <template #right>
        <QualityStateIndicator :green="marker.green" />
      </template>
    </PanelHeader>

    <ImageSlider :modelId="marker.id" model="markers" class="my-2" />

    <div
      v-if="marker.green?.inventory_number"
      class="bg-white rounded px-4 py-3 flex items-center justify-between text-gray-700 my-2"
    >
      <span class="font-medium">Інвентарний номер: {{ marker.green.inventory_number }}</span>
      <div class="relative group">
        <button
          @click="copyToClipboard(marker.green.inventory_number)"
          @mouseenter="copyCompleted = false"
        >
          <img src="/storage/img/icons/copy-icon.svg" alt="Скопіювати" class="w-5 h-5" />
        </button>
        <Tooltip>
          {{ copyCompleted ? 'Скопійовано!' : 'Скопіювати' }}
        </Tooltip>
      </div>
    </div>

    <div v-if="marker.description" class="bg-white rounded px-4 py-6 text-gray-600">
      <h3 class="text-lg font-semibold pb-2">Опис</h3>
      <p>{{ marker.description }}</p>
    </div>
    
    <GreenDetails :details="marker.green?.details" :type="marker?.type" />

    <TagList :tags="marker.tags" :loading="loading" />
  </div>
</template>
