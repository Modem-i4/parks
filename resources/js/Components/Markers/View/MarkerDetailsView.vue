<script setup>
import ImageSlider from '@/Components/Custom/ImageSlider.vue'
import TagList from '../TagList.vue'
import { computed, ref } from 'vue'
import GreenDetails from './GreenDetails.vue'
import Tooltip from '@/Components/Custom/Tooltip.vue'
import WorkHistory from '@/Components/WorkHistory/WorkHistory.vue'
import GreenStateIndicator from './GreenStateIndicator.vue'
import DeleteForm from '@/Components/Custom/DeleteForm.vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import { useAuthStore } from '@/Stores/useAuthStore'
import { isMobile } from '@/Helpers/isMobileHelper'
import { copyToClipboard, copyCompleted } from '@/Helpers/CopyToClipboard'
import { getCoordsFromMarker } from '@/Helpers/Maps/MapHelper'
import { printMarkerCard } from '@/Helpers/Print/PrintMarkerCard.js'

const imageSliderRef = ref(null)

const props = defineProps({
    marker: Object,
    loading: Boolean
})

const authStore = useAuthStore()

const shortCoordinates = computed(() => {
  const coords = getCoordsFromMarker(props.marker)
  return `
${coords.lat.toFixed(5)}, 
${coords.lng.toFixed(5)}
`
})

const fullNameLat = computed(
  () => [
    props.marker.green?.species?.name_lat,
    props.marker.green?.species?.genus?.name_lat,
    props.marker.green?.species?.genus?.family?.name_lat
  ].filter(Boolean).join(' / ')
)
const confirmingDelete = ref(false)
const forceImageUpdate = () => {
  imageSliderRef.value?.update(props.marker?.id)
}

function printMarker() {
  printMarkerCard(props.marker)
}

function handleImageClick(index) {
  if (authStore.can.upload) {
    emit('onImageClick', index)
    return
  }

  imageSliderRef.value?.openPreview(index)
}
const emit = defineEmits(['onImageClick', 'deleteMarker'])
defineExpose({ forceImageUpdate })
</script>

<template>
    <ImageSlider :modelId="props.marker.id" :isDraft="props.marker.isDraft" model="markers" ref="imageSliderRef"
      class="my-2"
      :editable="authStore.can.upload"
      :showByDefault="!isMobile || authStore.can.upload"
      @onImageClick="handleImageClick"
    />

    <div
      v-if="props.marker.green?.inventory_number && authStore.can.view"
      class="bg-white rounded px-4 py-3 flex items-center justify-between gap-3 text-gray-700 my-2"
    >
      <span class="font-medium min-w-0">Інвентарний номер: {{ props.marker.green.inventory_number }}
        <span class="text-xs italic" v-if="props.marker.green.inventory_number_old">(було: {{ props.marker.green.inventory_number_old }})</span>
      </span>
      <div class="flex shrink-0 items-center">
        <div class="relative group">
          <button
            type="button"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md transition-colors hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-500"
            aria-label="Скопіювати інвентарний номер"
            @click="copyToClipboard(props.marker.green.inventory_number)"
            @mouseenter="copyCompleted = false"
          >
            <img src="/img/icons/copy-icon.svg" alt="" class="w-5 h-5" />
          </button>
          <Tooltip>
            {{ copyCompleted ? 'Скопійовано!' : 'Скопіювати' }}
          </Tooltip>
        </div>
        <div class="relative group">
          <button
            type="button"
            class="inline-flex h-8 w-8 items-center justify-center rounded-md transition-colors hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-500"
            aria-label="Надрукувати картку"
            @click="printMarker"
          >
            <img src="/img/icons/print-icon.svg" alt="" class="w-5 h-5" />
          </button>
          <Tooltip align="right">Надрукувати картку</Tooltip>
        </div>
      </div>
    </div>
    <WorkHistory v-if="props.marker.green?.works && authStore.can.view" 
      v-model="props.marker.green.works" :loading="props.loading" :greenId="props.marker.green.id"/>

    <div v-if="props.marker.description"
      class="bg-white rounded px-4 text-gray-600 py-6 mb-2"
    >
      <h3 class="text-lg font-semibold pb-2">Опис</h3>
      <div class="marker-description" v-html="props.marker.description" />
    </div>
    <div v-if="props.marker.infrastructure?.infrastructure_type" class="bg-white rounded px-4 text-gray-600">
      <p v-if="props.marker.infrastructure?.infrastructure_type?.name"><b>Тип:</b> {{ props.marker.infrastructure?.infrastructure_type?.name }}</p>
    </div>

    <div class="bg-white rounded px-4 py-6 text-gray-600" 
      v-if="props.marker?.green?.green_state_note && authStore.can.view"
    >
      <div class="flex items-center">
        <h3 class="text-lg font-semibold pb-2 me-1">Коментар до стану</h3>"<GreenStateIndicator :green="props.marker.green"/>"
      </div>
      <p>{{ props.marker.green.green_state_note }}</p>
    </div>  
    <GreenDetails :green="props.marker?.green" :type="props.marker?.type" v-if="props.marker?.green" />

    <div v-if="fullNameLat" class="bg-white rounded px-4 text-gray-600">
      <h3 class="text-lg font-semibold pb-2">Ім'я латиною</h3>
      <b>{{ fullNameLat }}</b>
    </div>

    <TagList v-model="props.marker.tags" :loading="props.loading" />
    <div class="bg-white rounded px-4 text-gray-600 h-6 w-full" 
      v-if="!props.marker.tags?.lenght" 
    /> <!-- Spacer -->

<div
          class="bg-white rounded flex items-center justify-between text-gray-600 my-2 p-4"
        >
          <div>
            <strong class="font-bold">Координати:</strong> {{ shortCoordinates }} 
          </div>
          <div class="relative group">
            <button
              type="button"
              class="inline-flex h-8 w-8 items-center justify-center rounded-md transition-colors hover:bg-gray-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-500"
              aria-label="Скопіювати координати"
              @click="copyToClipboard(shortCoordinates)"
              @mouseenter="copyCompleted = false"
            >
              <img src="/img/icons/copy-icon.svg" alt="" class="w-5 h-5" />
            </button>
            <Tooltip align="right">
              {{ copyCompleted ? 'Скопійовано!' : 'Скопіювати' }}
            </Tooltip>
        </div>
        </div>

    <div class="bg-white rounded p-4 mt-2 text-gray-600" v-if="authStore.can.deleteMarkers">
      <DeleteForm
        v-if="confirmingDelete"
        :label="props.marker.green?.inventory_number || props.marker.infrastructure?.name || 'маркер'"
        @confirmDelete="() => emit('deleteMarker')"
        @cancelDelete="confirmingDelete = false"
      />
      <SecondaryButton @click="confirmingDelete = !confirmingDelete" class="w-full">Видалити маркер</SecondaryButton>
    </div>
</template>

<style scoped>
.marker-description :deep(p) { margin: .35rem 0; }
.marker-description :deep(a) { color: #2563eb; text-decoration: underline; }
</style>
