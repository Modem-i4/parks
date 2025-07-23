<template>
  <div class="space-y-1">
    <DeleteForm
      v-if="confirmingDelete"
      :label="item.name"
      @confirmDelete="confirmDelete"
      @cancelDelete="cancelDelete"
    />
    <div
      class="flex items-center px-2 py-1 rounded cursor-pointer bg-white hover:bg-gray-100 border"
      @click="emit('selectTag', item)"
    >
      <div class="flex items-center space-x-2 flex-1 relative group">
        <template v-if="isEditing">
          <div class="relative flex flex-col md:flex-row md:items-start md:gap-2 w-full">
            <div class="relative w-full md:w-1/2">
              <FloatingInput
                v-model="form.name"
                label="Назва типу"
                :inputClasses="nameError ? 'border-red-500' : 'font-semibold'"
              />
              <Tooltip v-if="nameError">{{ nameError }}</Tooltip>
            </div>
          </div>
        </template>
        <template v-else>
          <span class="font-semibold text-sm">{{ item.name }}</span>
        </template>
      </div>
      <div class="text-xs text-gray-600 flex flex-[0.4]">
        <template v-if="isEditing">
          <select
            v-model="form.type"
            class="text-xs rounded border border-gray-300 px-1 py-0.5"
          >
            <option
              v-for="(label, type) in typeUkr"
              :key="type"
              :value="type"
            >
              {{ label }}
            </option>
          </select>
        </template>
        <template v-else>
          {{ typeUkr[item.type] }}
        </template>
      </div>
      <div class="space-x-1 flex-shrink-0 ml-2">
        <template v-if="isEditing">
          <SecondaryButton class="bg-inherit" @click.stop="saveEdit">✔️</SecondaryButton>
          <SecondaryButton class="bg-inherit" size="sm" @click.stop="cancelEdit">❌</SecondaryButton>
        </template>
        <template v-else>
          <SecondaryButton class="bg-inherit" @click.stop="startEdit" :size="isMobile ? 'sm' : 'md'">✏️</SecondaryButton>
          <SecondaryButton size="sm" variant="danger" class="bg-inherit" @click.stop="toggleDelete">🗑️</SecondaryButton>
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import SecondaryButton from '@/Components/Default/SecondaryButton.vue';
import FloatingInput from '@/Components/Custom/FloatingInput.vue';
import Tooltip from '@/Components/Custom/Tooltip.vue';
import DeleteForm from '@/Components/Custom/DeleteForm.vue';
import { ref, computed } from 'vue';
import { isMobile } from '@/Helpers/isMobileHelper';

const props = defineProps({
  item: Object,
  level: Number
})

const emit = defineEmits(['update', 'delete', 'changeIcon', 'changeGallery', 'selectTag'])

const isEditing = ref(false)
const showErrors = ref(false)
const confirmingDelete = ref(false)

const typeUkr = {
  'tree': 'для дерев',
  'bush': 'для кущів',
  'hedge': 'для живоплотів',
  'flower': 'для квіток',
  'infrastructure': 'інфраструктурний',
  'all': 'спільний',
}

const form = ref({
  name: props.item.name,
  type: props.item.type
})

const startEdit = () => {
  form.value.name = props.item.name
  form.value.type = props.item.type
  showErrors.value = false
  isEditing.value = true
}

const cancelEdit = () => {
  isEditing.value = false
  showErrors.value = false
}

const nameError = computed(() => {
  if (!showErrors.value) return ''
  if (!form.value.name.trim()) return 'Поле обовʼязкове'
  if (form.value.name.trim().length < 3) return 'Щонайменше 3 літери'
  return ''
})

const saveEdit = () => {
  showErrors.value = true
  if (nameError.value) return

  emit('update', {
    id: props.item.id,
    data: form.value
  })

  isEditing.value = false
  showErrors.value = false
}

const confirmDelete = () => {
  emit('delete', {
    id: props.item.id,
    level: props.level
  })
  confirmingDelete.value = false
}

const toggleDelete = () => {
  confirmingDelete.value = !confirmingDelete.value
}

const cancelDelete = () => {
  confirmingDelete.value = false
}
</script>
