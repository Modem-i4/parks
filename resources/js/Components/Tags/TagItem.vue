<script setup>
import EditableItemWrapper from '@/Components/Custom/EditableDictItemWrapper.vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import { ref, computed } from 'vue'
import { isMobile } from '@/Helpers/isMobileHelper'

const props = defineProps({ item: Object, level: Number })
const emit = defineEmits(['update', 'delete', 'selectTag'])

const typeUkr = {
  tree: 'для дерев',
  bush: 'для кущів',
  hedge: 'для живоплотів',
  flower: 'для квіток',
  infrastructure: 'інфраструктурний',
  all: 'спільний'
}

const isEditing = ref(false)
const confirmingDelete = ref(false)
const showErrors = ref(false)
const form = ref({ name: props.item.name, type: props.item.type })

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

function startEdit() {
  form.value.name = props.item.name
  form.value.type = props.item.type
  showErrors.value = false
  isEditing.value = true
}

function cancelEdit() {
  isEditing.value = false
  showErrors.value = false
}

function toggleDelete() {
  confirmingDelete.value = !confirmingDelete.value
}

function confirmDelete() {
  emit('delete', { id: props.item.id, level: props.level })
  confirmingDelete.value = false
}

function cancelDelete() {
  confirmingDelete.value = false
}
</script>

<template>
  <EditableItemWrapper
    :item="props.item"
    :form="form"
    :isEditing="isEditing"
    :confirmingDelete="confirmingDelete"
    :nameError="nameError"
    @saveEdit="saveEdit"
    @startEdit="startEdit"
    @cancelEdit="cancelEdit"
    @confirmDelete="confirmDelete"
    @cancelDelete="cancelDelete"
    @toggleDelete="toggleDelete"
    @select="() => emit('selectTag', props.item)"
  >
    <template #extra>
      <div class="text-xs text-gray-600 flex-[0.4]">
        <template v-if="isEditing">
          <select
            v-model="form.type"
            class="text-xs rounded border border-gray-300 px-1 py-0.5"
          >
            <option v-for="(label, type) in typeUkr" :key="type" :value="type">{{ label }}</option>
          </select>
        </template>
        <template v-else>
          {{ typeUkr[props.item.type] }}
        </template>
      </div>
    </template>
    <template #actions>
      <SecondaryButton class="bg-inherit" @click.stop="startEdit" :size="isMobile ? 'sm' : 'md'">✏️</SecondaryButton>
      <SecondaryButton size="sm" variant="danger" class="bg-inherit" @click.stop="toggleDelete">🗑️</SecondaryButton>
    </template>
  </EditableItemWrapper>
</template>