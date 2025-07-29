<script setup>
import { ref, computed, watch } from 'vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import FloatingInput from '@/Components/Custom/FloatingInput.vue'
import Tooltip from '@/Components/Custom/Tooltip.vue'
import DeleteForm from '@/Components/Custom/DeleteForm.vue'

const props = defineProps({
  item: Object,
  form: Object,
  isEditing: Boolean,
  confirmingDelete: Boolean,
  nameError: String,
})

const emit = defineEmits([
  'update:form',
  'update:isEditing',
  'update:confirmingDelete',
  'saveEdit',
  'startEdit',
  'cancelEdit',
  'confirmDelete',
  'cancelDelete',
  'toggleDelete',
  'select'
])
</script>

<template>
  <div class="space-y-1">
    <DeleteForm
      v-if="confirmingDelete"
      :label="item.name"
      @confirmDelete="() => emit('confirmDelete')"
      @cancelDelete="() => emit('cancelDelete')"
    />
    <div
      class="flex items-center px-2 py-1 rounded cursor-pointer bg-gray-100 hover:bg-gray-200 w-full"
      @click="emit('select', item)"
    >
      <div class="flex items-center space-x-2 flex-1 relative group">
        <template v-if="isEditing">
          <FloatingInput
            v-model="form.name"
            label="Назва"
            class="w-full"
            :inputClasses="nameError ? 'border-red-500' : 'font-semibold'"
          />
          <Tooltip v-if="nameError">{{ nameError }}</Tooltip>
          <slot name="extra" />
        </template>
        <template v-else>
          <img v-if="item.icon?.file_path" :src="item.icon.file_path" alt="Іконка" class="w-5 h-5" />
          <span class="font-semibold text-sm">{{ item.name }}</span>
          <slot name="extra" />
        </template>
      </div>
      <div class="space-x-1 flex-shrink-0 ml-2">
        <template v-if="isEditing">
          <SecondaryButton class="bg-inherit" @click.stop="emit('saveEdit')">✔️</SecondaryButton>
          <SecondaryButton class="bg-inherit" size="sm" @click.stop="emit('cancelEdit')">❌</SecondaryButton>
        </template>
        <template v-else>
          <slot name="actions" />
        </template>
      </div>
    </div>
  </div>
</template>