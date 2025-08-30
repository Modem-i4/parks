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

const loading = ref(true)
const copyCompleted = ref(false)
const imageSliderRef = ref(null)

const props = defineProps({
    marker: Object
})

const authStore = useAuthStore()

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
const confirmingDelete = ref(false)
const forceImageUpdate = () => {
  imageSliderRef.value?.update(props.marker?.id)
}
const emit = defineEmits(['onImageClick', 'deleteMarker'])
defineExpose({ forceImageUpdate })
</script>

<template>
    <ImageSlider :modelId="props.marker.id" :isDraft="props.marker.isDraft" model="markers" ref="imageSliderRef"
      class="my-2"
      :editable="authStore.can.upload"
      :showByDefault="!isMobile || authStore.can.upload"
      @onImageClick="() => { if(authStore.can.upload) emit('onImageClick') }"
    />

    <div
      v-if="props.marker.green?.inventory_number && authStore.can.view"
      class="bg-white rounded px-4 py-3 flex items-center justify-between text-gray-700 my-2"
    >
      <span class="font-medium">Інвентарний номер: {{ props.marker.green.inventory_number }}</span>
      <div class="relative group">
        <button
          @click="copyToClipboard(props.marker.green.inventory_number)"
          @mouseenter="copyCompleted = false"
        >
          <img src="/img/icons/copy-icon.svg" alt="Скопіювати" class="w-5 h-5" />
        </button>
        <Tooltip>
          {{ copyCompleted ? 'Скопійовано!' : 'Скопіювати' }}
        </Tooltip>
      </div>
    </div>
    <WorkHistory v-if="props.marker.green?.works && authStore.can.view" 
      v-model="props.marker.green.works" :loading="loading" :greenId="props.marker.green.id"/>

    <div v-if="props.marker.description" class="bg-white rounded px-4 py-6 text-gray-600">
      <h3 class="text-lg font-semibold pb-2">Опис</h3>
      <p>{{ props.marker.description }}</p>
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

    <TagList v-model="props.marker.tags" :loading="loading" />

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
