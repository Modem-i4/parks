<script setup>
import ImageSlider from '@/Components/Custom/ImageSlider.vue'
import PanelHeader from '@/Components/Custom/PanelHeader.vue'
import TagList from './TagList.vue'
import QualityStateIndicator from './QualityStateIndicator.vue'
import { computed, ref } from 'vue'
import GreenDetails from './GreenDetails.vue'
import Tooltip from '@/Components/Custom/Tooltip.vue'

const loading = ref(true)
const copyCompleted = ref(false)

const props = defineProps({
    marker: Object
})

const copyToClipboard = async (text) => {
  try {
    await navigator.clipboard.writeText(text)
    copyCompleted.value = true
  } catch (err) {
    console.error('Не вдалося скопіювати:', err)
  }
}

const fullNameLat = computed(
  () => [
    props.marker.green?.species?.name_lat,
    props.marker.green?.species?.genus?.name_lat,
    props.marker.green?.species?.genus?.family?.name_lat
  ].filter(Boolean).join(' / ')
)

</script>

<template>
    <ImageSlider :modelId="props.marker.id" :isDraft="props.marker.isDraft" model="markers" class="my-2" />

    <div
      v-if="props.marker.green?.inventory_number"
      class="bg-white rounded px-4 py-3 flex items-center justify-between text-gray-700 my-2"
    >
      <span class="font-medium">Інвентарний номер: {{ props.marker.green.inventory_number }}</span>
      <div class="relative group">
        <button
          @click="copyToClipboard(props.marker.green.inventory_number)"
          @mouseenter="copyCompleted = false"
        >
          <img src="/storage/img/icons/copy-icon.svg" alt="Скопіювати" class="w-5 h-5" />
        </button>
        <Tooltip>
          {{ copyCompleted ? 'Скопійовано!' : 'Скопіювати' }}
        </Tooltip>
      </div>
    </div>

    <div v-if="props.marker.description" class="bg-white rounded px-4 py-6 text-gray-600">
      <h3 class="text-lg font-semibold pb-2">Опис</h3>
      <p>{{ props.marker.description }}</p>
    </div>
    
    <GreenDetails :green="props.marker?.green" :type="props.marker?.type" />

    <div v-if="fullNameLat" class="bg-white rounded px-4 text-gray-600">
      <h3 class="text-lg font-semibold pb-2">Ім'я латиною</h3>
      <b>{{ fullNameLat }}</b>
    </div>

    <TagList :tags="props.marker.tags" :loading="loading" />
</template>
