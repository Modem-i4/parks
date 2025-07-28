<script setup>
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import FloatingInput from '@/Components/Custom/FloatingInput.vue'
import Tooltip from '@/Components/Custom/Tooltip.vue'
import DeleteForm from '@/Components/Custom/DeleteForm.vue'
import { ref, computed } from 'vue'

const props = defineProps({
  item: Object,
})

const emit = defineEmits(['update', 'delete', 'selectRecommendation'])

const isEditing = ref(false)
const confirmingDelete = ref(false)
const showErrors = ref(false)

const form = ref({
  name: props.item.name,
})

const nameError = computed(() => {
  if (!showErrors.value) return ''
  if (!form.value.name.trim()) return 'Поле обовʼязкове'
  if (form.value.name.trim().length < 3) return 'Щонайменше 3 літери'
  return ''
})

function saveEdit() {
  showErrors.value = true
  if (nameError.value) return
  emit('update', { id: props.item.id, data: form.value })
  isEditing.value = false
  showErrors.value = false
}

function toggleDelete() {
  confirmingDelete.value = !confirmingDelete.value
}

function confirmDelete() {
  emit('delete', { id: props.item.id })
  confirmingDelete.value = false
}

function cancelDelete() {
  confirmingDelete.value = false
}
</script>

<template>
  <div class="space-y-1">
    <DeleteForm
      v-if="confirmingDelete"
      :label="item.name"
      @confirmDelete="confirmDelete"
      @cancelDelete="cancelDelete"
    />
    <div
      class="flex items-center px-2 py-1 rounded cursor-pointer bg-gray-100 hover:bg-gray-200"
      @click="emit('selectRecommendation', item)"
    >
      <div class="flex items-center space-x-2 flex-1">
        <template v-if="isEditing">
          <FloatingInput
            v-model="form.name"
            label="Назва"
            class="w-full"
            :inputClasses="nameError ? 'border-red-500' : 'font-semibold'"
          />
          <Tooltip v-if="nameError">{{ nameError }}</Tooltip>
        </template>
        <template v-else>
          <span class="font-semibold text-sm">{{ item.name }}</span>
        </template>
      </div>
      <div class="space-x-1 flex-shrink-0 ml-2">
        <template v-if="isEditing">
          <SecondaryButton class="bg-inherit" @click.stop="saveEdit">✔️</SecondaryButton>
          <SecondaryButton class="bg-inherit" size="sm" @click.stop="isEditing = false">❌</SecondaryButton>
        </template>
        <template v-else>
          <SecondaryButton class="bg-inherit" @click.stop="isEditing = true">✏️</SecondaryButton>
          <SecondaryButton size="sm" variant="danger" class="bg-inherit" @click.stop="toggleDelete">🗑️</SecondaryButton>
        </template>
      </div>
    </div>
  </div>
</template>
