<template>
  <div>
    <div
      class="bg-gray-50 px-2 py-3 rounded cursor-pointer hover:bg-gray-100"
      @click="open = !open"
    >
      <div class="flex items-center space-x-2">
        <PlusIcon :class="{ 'rotate-45': open }" />
        <span class="font-semibold text-sm">Додати роботу</span>
      </div>
    </div>

    <Transition name="accordion">
      <div
        v-if="open"
        class="ml-4 space-y-2 mt-2"
      >
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
            rows="3"
          />
        </div>

        <div class="flex justify-end gap-3">
          <SecondaryButton size="sm" @click="reset">Скасувати</SecondaryButton>
          <PrimaryButton size="sm" @click="submit">Додати</PrimaryButton>
        </div>
      </div>
    </Transition>

    <Modal :show="showModal.recommendation" maxWidth="2xl" @close="showModal.recommendation = false">
      <DictRecommendations @select="selectRecommendation" />
    </Modal>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import PlusIcon from '@/Components/Custom/Icons/PlusIcon.vue'
import Modal from '@/Components/Default/Modal.vue'
import SelectWithSearchAndAdd from '@/Components/Custom/SelectWithSearchAndAdd.vue'
import DictRecommendations from '@/Components/Dictionaries/DictRecommendations.vue'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'

const emit = defineEmits(['create'])

const open = ref(false)
const showModal = ref({ recommendation: false })

const form = ref({
  recommendation_id: null,
  recommendation: null,
  notes: ''
})

function selectRecommendation(rec) {
  form.value.recommendation_id = rec.id
  form.value.recommendation = rec
  showModal.value.recommendation = false
}

function submit() {
  if (!form.value.recommendation_id) return

  emit('create', {
    recommendation_id: form.value.recommendation_id,
    notes: form.value.notes
  })

  reset()
}

function reset() {
  form.value = { recommendation_id: null, recommendation: null, notes: '' }
  open.value = false
}
</script>

<style scoped>
@import '@/../css/assets/accordion.css';
</style>
