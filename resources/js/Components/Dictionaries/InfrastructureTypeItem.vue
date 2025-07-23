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
            @click="emit('selectInfrastructureType', item)"
        >
            <div class="flex items-center space-x-2 flex-1 relative group">
                <img :src="item.icon?.file_path" alt="Іконка" class="w-5 h-5" />
                <template v-if="isEditing">
                    <div class="relative flex flex-col md:flex-row md:items-start md:gap-2 w-full">
                        <div class="relative w-full md:w-1/2">
                            <FloatingInput v-model="form.name" label="Назва типу" inputClasses="font-semibold"
                                :inputClasses="nameError ? 'border-red-500' : null"
                            />
                            <Tooltip v-if="nameError">{{ nameError }}</Tooltip>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <span class="font-semibold text-sm">{{ item.name }}</span>
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
                      @click.stop="emit('changeGallery', item.id)"
                    >🖼️</SecondaryButton>
                    <SecondaryButton 
                      class="bg-inherit" :size="isMobile ? 'sm' : 'md'"
                      @click.stop="emit('changeIcon', item.id)"
                    >🎨</SecondaryButton>
                    <SecondaryButton
                      class="bg-inherit" @click.stop="startEdit" :size="isMobile ? 'sm' : 'md'"
                    >✏️</SecondaryButton>
                    <SecondaryButton
                      size="sm" variant="danger" class="bg-inherit"
                      @click.stop="toggleDelete"
                    >🗑️</SecondaryButton>
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
})

const emit = defineEmits(['update', 'delete', 'changeIcon', 'changeGallery', 'selectInfrastructureType'])

const isEditing = ref(false)
const showErrors = ref(false)
const confirmingDelete = ref(false)

const form = ref({
  name: props.item.name
})

const startEdit = () => {
  form.value.name = props.item.name
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
    data: form.value,
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