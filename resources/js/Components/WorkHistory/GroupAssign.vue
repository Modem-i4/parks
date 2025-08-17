<template>
  <div class="p-4 space-y-4">
    <h2 class="text-lg font-semibold text-gray-800">👷 Групове призначення робіт</h2>

    <div class="space-y-2">
      <div class="text-sm italic text-gray-600">Призначити роботи для насаджень, які:</div>
        <div class="flex justify-center">
            <Switch
            v-model="assignMode"
            :options="[
                { value: 'picked', label: '👷 Обрано у властивостях', color: 'green' },
                { value: 'filtered', label: '🗄️ Відфільтровано на мапі', color: 'blue' },
            ]"
            />
        </div>
        <!-- Assign modes -->
        <div class="px-2">
            <template v-if="assignMode === 'picked' ">
                <div class="text-sm text-red-500 italic flex justify-center"
                    v-if="parkStore.pickedMarkers.length === 0"
                >(немає вибраних маркерів)
                </div>
                <div v-else class="max-h-[15rem] overflow-y-auto overflow-x-clip">
                    <h3 class="font-semibold text-gray-800 text-center">Обрані маркери:</h3>

                    <PanelHeader
                        v-for="marker in parkStore.pickedMarkers"
                        :title="`${getMarkerTitle(marker)} (${marker.green?.inventory_number})`"
                        :subtitle="typeUkr[marker.type]"
                        :icon="marker.icon?.file_path"
                        variant="sm"
                        class="bg-gray-100 border p-0 rounded-full px-5 my-1"
                    >
                        <template #right>
                            <GreenStateIndicator :green="marker.green" />
                            <button class="ml-2 text-gray-400 hover:text-red-600 text-lg font-bold leading-none"
                                title="Видалити"
                                @click="removePickedMarker(marker)"
                            >×</button>
                        </template>
                    </PanelHeader>
                </div>
            </template>
            <div class="flex justify-center" v-if="assignMode === 'filtered' || parkStore.pickedMarkers.length > 0">
                <p class="text-sm font-medium">
                <span class="text-blue-700">
                    Обрано {{ filteredCount }} маркер{{ filteredCount === 1 ? '' : filteredCount > 0 && filteredCount < 5 ? 'а' : 'ів' }}.
                </span>
                <span :class="overLimit ? 'text-red-700 font-semibold' : 'text-gray-500'"
                >Ліміт: 200 маркерів.</span>
                </p>
            </div>
            <div class="flex justify-center">
              <SecondaryButton
                class="py-0.5 text-blue-600"
                v-if="parkStore.pickedMarkers.length > 3"
                @click="clearPickedMarkers"
              >Очистити вибір</SecondaryButton>
            </div>
        </div>
    </div>

    <SelectWithSearchAndAdd
      mode="recommendations"
      class="space-y-1"
      v-model="form.recommendation_id"
      :startingItem="form.recommendation"
      @show-modal="showModal.recommendation = true"
    />

    <div>
      <label class="text-sm text-gray-600 block">Опис</label>
      <textarea
        v-model="form.notes"
        class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
        rows="7"
      />
    </div>
    <div v-if="errorMessage" class="text-red-600 text-sm text-center mt-2">
    {{ errorMessage }}
    </div>

    <div class="flex justify-end gap-3">
      <SecondaryButton @click="cancel">Скасувати</SecondaryButton>
      <PrimaryButton
        @click="submit"
      >
        {{ loading ? 'Додаємо...' : 'Додати всім' }}
      </PrimaryButton>
    </div>

    <Modal
      :show="showModal.recommendation"
      maxWidth="2xl"
      @close="showModal.recommendation = false"
    >
      <DictRecommendations @selectRecommendation="selectRecommendation" />
    </Modal>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'

import { useParkStore } from '@/Stores/useParkStore'
import SelectWithSearchAndAdd from '@/Components/Custom/SelectWithSearchAndAdd.vue'
import DictRecommendations from '@/Components/Dictionaries/DictRecommendations.vue'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import Modal from '@/Components/Default/Modal.vue'
import Switch from '@/Components/Custom/Switch.vue'
import PanelHeader from '../Custom/PanelHeader.vue'
import { getMarkerTitle, typeUkr } from '@/Helpers/Maps/GetMarkerTitle';
import GreenStateIndicator from '../Markers/View/GreenStateIndicator.vue'

const emit = defineEmits(['close', 'update'])

const parkStore = useParkStore()
const showModal = ref({ recommendation: false })
const loading = ref(false)
const errorMessage = ref('')

const props = defineProps({
  mode: {
    type: String,
    default: 'picked'
  }
})
const assignMode = ref(props.mode)

const form = ref({
  recommendation_id: null,
  recommendation: null,
  notes: ''
})

const targetMarkers = computed(() => {
  const source =
    assignMode.value === 'picked'
      ? parkStore.pickedMarkers
      : parkStore.markers

  return source.filter((m) => m.green)
})

const filteredCount = computed(() => targetMarkers.value.length)
const overLimit = computed(() => filteredCount.value > 200)

function selectRecommendation(rec) {
  form.value.recommendation_id = rec.id
  form.value.recommendation = rec
  showModal.value.recommendation = false
}

function removePickedMarker(marker) {
  parkStore.pickedMarkers = parkStore.pickedMarkers.filter(m => m.id !== marker.id)
}

function clearPickedMarkers() {
  parkStore.pickedMarkers = []
}

function cancel() {
  emit('close')
}

async function submit() {
  errorMessage.value = ''

  if (!form.value.recommendation_id) errorMessage.value = 'Оберіть рекомендацію'
  if (overLimit.value) errorMessage.value = 'Забагато маркерів (макс. 200)'
  if (filteredCount.value === 0) errorMessage.value = 'Оберіть маркери'

  if(errorMessage.value) return

  const targetGreenIds = targetMarkers.value.map(m => m.green.id)
  loading.value = true

  axios.post('/api/works/bulk', {
    markers: targetGreenIds,
    recommendation_id: form.value.recommendation_id,
    notes: form.value.notes
  })
  .then(() => {
    emit('update')
    emit('close')
  })
  .catch((error) => {
    console.error('Помилка при додаванні робіт:', error)
    errorMessage.value = 'Сталася помилка при додаванні робіт.'
  })
  .finally(() => {
    loading.value = false
  })
}
</script>
