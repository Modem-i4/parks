<script setup>
import { ref } from 'vue'
import axios from 'axios'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import SelectWithSearchAndAdd from '@/Components/Custom/SelectWithSearchAndAdd.vue'
import Modal from '@/Components/Default/Modal.vue'
import DictRecommendations from '@/Components/Dictionaries/DictRecommendations.vue'
import DeleteForm from '../Custom/DeleteForm.vue'

const props = defineProps({
  work: Object,
  readonly: Boolean
})

const emit = defineEmits(['update', 'complete', 'delete'])

const editing = ref(false)
const confirmingDelete = ref(false)
const showModal = ref({ recommendation: false })

const form = ref({
  notes: props.work.notes ?? '',
  recommendation_id: props.work.recommendation_id,
  recommendation: props.work.recommendation ?? null
})

function startEditing() {
  form.value = {
    notes: props.work.notes ?? '',
    recommendation_id: props.work.recommendation_id,
    recommendation: props.work.recommendation ?? null
  }
  editing.value = true
}

function cancelEditing() {
  editing.value = false
}

async function saveForm() {
  const res = await axios.patch(`/api/works/${props.work.id}`, {
    notes: form.value.notes,
    recommendation_id: form.value.recommendation_id
  })

  emit('update', res.data)
  editing.value = false
}

function selectRecommendation(rec) {
  form.value.recommendation_id = rec.id
  form.value.recommendation = rec
  showModal.value.recommendation = false
}

async function complete() {
  const res = await axios.patch(`/api/works/${props.work.id}/complete`)
  emit('complete', res.data)
}

async function revert() {
  const res = await axios.patch(`/api/works/${props.work.id}/revert`)
  emit('complete', res.data)
}

async function deleteWork() {
  await axios.delete(`/api/works/${props.work.id}`)
  emit('delete', props.work.id)
}
</script>

<template>
  <div class="bg-gray-100 rounded border px-4 py-3 shadow-sm">
    <DeleteForm
      v-if="!readonly && confirmingDelete"
      :label="form.recommendation.name"
      @confirmDelete="deleteWork"
      @cancelDelete="confirmingDelete = false"
      class="mb-2"
    />
    <div class="grid grid-cols-[1fr_auto] items-start gap-2">
      <div class="text-lg font-semibold text-gray-800 space-y-1">
        <template v-if="!editing">
          {{ props.work.recommendation?.name || '—' }}
        </template>
        <template v-else>
          <SelectWithSearchAndAdd
            mode="recommendations"
            v-model="form.recommendation_id"
            :startingItem="form.recommendation"
            :showLabel="false"
            @show-modal="showModal.recommendation = true"
          />
          <Modal :show="showModal.recommendation" maxWidth="4xl" @close="showModal.recommendation = false">
            <DictRecommendations @select="selectRecommendation" />
          </Modal>
        </template>
      </div>
      <div class="flex gap-x-1 items-center">
        <template v-if="!readonly">
          <template v-if="!editing">
            <SecondaryButton size="sm" variant="danger" class="bg-gray-200" @click.stop="confirmingDelete = !confirmingDelete">
              🗑️
            </SecondaryButton>

            <SecondaryButton size="sm" variant="danger" class="bg-gray-200" @click.stop="startEditing">
              ✏️
            </SecondaryButton>

            <SecondaryButton v-if="!props.work.execution_date" size="sm" class="bg-white" @click.stop="complete">
              ✔️
            </SecondaryButton>
          </template>

          <template v-else>
            <SecondaryButton size="sm" class="bg-gray-200" @click.stop="cancelEditing">
              ❌
            </SecondaryButton>

            <SecondaryButton size="sm" class="bg-white" @click.stop="saveForm">
              ✔️
            </SecondaryButton>
          </template>
        </template>
        <template v-if="readonly">
          <SecondaryButton size="sm" class="bg-white" @click.stop="revert">
            ⬅️
          </SecondaryButton>
        </template>
      </div>
    </div>

    <div class="text-xs text-gray-500 flex justify-between italic mt-1">
      <div>Замовлено: {{ props.work.recommendation_date?.split('T')[0] }}</div>
      <div v-if="props.work.recommender">{{ props.work.recommender.name }}</div>
    </div>

    <div
      v-if="props.work.execution_date" 
      class="text-xs text-gray-500 flex justify-between italic mt-1"
    >
      <div>Виконано: {{ props.work.execution_date?.split('T')[0] }}</div>
      <div v-if="props.work.executor">{{ props.work.executor.name }}</div>
    </div>

    <div class="text-sm pt-2">
      <template v-if="editing">
        <textarea
          v-model="form.notes"
          class="w-full border rounded p-1 text-sm"
          rows="2"
        />
      </template>
      <template v-else-if="props.work.notes">
        <div class="whitespace-pre-wrap text-gray-800 text-sm">
          {{ props.work.notes }}
        </div>
      </template>
    </div>
  </div>
</template>
