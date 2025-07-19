<script setup>
import { ref, computed } from 'vue'
import ArrowIcon from '@/Components/Custom/Icons/ArrowIcon.vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import FloatingInput from '@/Components/Custom/FloatingInput.vue'
import Tooltip from '@/Components/Custom/Tooltip.vue'
import DeleteForm from '@/Components/Custom/DeleteForm.vue'
import { isMobile } from '@/Helpers/isMobileHelper'

const props = defineProps({
  item: Object,
  level: String,
  nextLevel: String,
  expanded: Boolean
})

const emit = defineEmits(['toggle', 'update', 'delete', 'changeGallery'])

const isEditing = ref(false)
const showErrors = ref(false)
const confirmingDelete = ref(false)

const form = ref({
  name_ukr: props.item.name_ukr,
  name_lat: props.item.name_lat
})

const startEdit = () => {
  form.value.name_ukr = props.item.name_ukr
  form.value.name_lat = props.item.name_lat
  showErrors.value = false
  isEditing.value = true
}

const cancelEdit = () => {
  isEditing.value = false
  showErrors.value = false
}

const nameUkrError = computed(() => {
  if (!showErrors.value) return ''
  if (!form.value.name_ukr.trim()) return 'Поле обовʼязкове'
  if (form.value.name_ukr.trim().length < 3) return 'Щонайменше 3 літери'
  return ''
})

const saveEdit = () => {
  showErrors.value = true
  if (nameUkrError.value) return

  emit('update', {
    id: props.item.id,
    data: form.value,
    level: props.level
  })

  isEditing.value = false
  showErrors.value = false
}

const toggleDelete = () => {
  confirmingDelete.value = !confirmingDelete.value
}

const cancelDelete = () => {
  confirmingDelete.value = false
}

const confirmDelete = () => {
  emit('delete', {
    id: props.item.id,
    level: props.level
  })
  confirmingDelete.value = false
}
</script>

<template>
  <div class="space-y-1">
    <DeleteForm
      v-if="confirmingDelete"
      :label="item.name_ukr"
      @confirmDelete="confirmDelete"
      @cancelDelete="cancelDelete"
    />

    <div
      class="flex items-center px-2 py-1 rounded cursor-pointer"
      :class="[level === 'species' ? 'bg-white border' : 'bg-gray-100 hover:bg-gray-200']"
      @click="!isEditing && level !== 'species' && emit('toggle')"
    >
      <div class="flex items-center space-x-2 flex-1 relative group">
        <ArrowIcon v-if="level !== 'species'" :class="{ 'rotate-90': expanded }" />
        <span v-if="level === 'species'">🌳</span>

        <template v-if="isEditing">
          <div class="relative flex flex-col md:flex-row md:items-start md:gap-2 w-full">
            <div class="relative w-full md:w-1/2">
              <FloatingInput v-model="form.name_ukr" label="Назва українською" inputClasses="font-semibold"
                :inputClasses="nameUkrError ? 'border-red-500' : null"
              />
              <Tooltip v-if="nameUkrError">{{ nameUkrError }}</Tooltip>
            </div>

            <div class="w-full md:w-1/2">
              <FloatingInput v-model="form.name_lat" label="Назва латиною" labelClasses="italic" inputClasses="italic" />
            </div>
          </div>
        </template>

        <template v-else>
          <span class="font-semibold text-sm">{{ item.name_ukr }}</span>
          <span class="text-gray-500 italic text-sm">({{ item.name_lat }})</span>
        </template>
      </div>

      <div class="space-x-1 flex-shrink-0 ml-2">
        <template v-if="isEditing">
          <SecondaryButton class="bg-inherit" @click.stop="saveEdit">✔️</SecondaryButton>
          <SecondaryButton class="bg-inherit" size="sm" @click.stop="cancelEdit">❌</SecondaryButton>
        </template>
        <template v-else>
          <SecondaryButton 
            class="bg-inherit" :size="isMobile ? 'sm' : 'md'"
            @click.stop="emit('changeGallery', {model_id: item.id, level})"
          >🖼️</SecondaryButton>
          <SecondaryButton class="bg-inherit" @click.stop="startEdit">✏️</SecondaryButton>
          <SecondaryButton
            size="sm"
            variant="danger"
            class="bg-inherit"
            @click.stop="toggleDelete"
          >🗑️</SecondaryButton>
        </template>
      </div>
    </div>
  </div>
</template>
