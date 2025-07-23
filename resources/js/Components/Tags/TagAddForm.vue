<script setup>
import { ref, computed } from 'vue'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import FloatingInput from '@/Components/Custom/FloatingInput.vue'
import Tooltip from '@/Components/Custom/Tooltip.vue'
import PlusIcon from '@/Components/Custom/Icons/PlusIcon.vue'

const emit = defineEmits(['create'])

const open = ref(false)

const form = ref({
  name: '',
  type: 'all'
})

const typeUkr = {
  'tree': 'для дерев',
  'bush': 'для кущів',
  'hedge': 'для живоплотів',
  'flower': 'для квіток',
  'infrastructure': 'інфраструктурний',
  'all': 'спільний',
}

const showErrors = ref(false)

const nameError = computed(() => {
  if (!showErrors.value) return ''
  if (!form.value.name.trim()) return 'Поле обовʼязкове'
  if (form.value.name.trim().length < 3) return 'Щонайменше 3 літери'
  return ''
})

function submit() {
  showErrors.value = true
  if (nameError.value) return

  emit('create', { data: { ...form.value } })

  form.value = { name: '', type: 'all' }
  showErrors.value = false
  open.value = false
}
</script>

<template>
  <div>
    <div class="bg-gray-50 px-2 py-3 rounded cursor-pointer hover:bg-gray-100" @click="open = !open">
      <div class="flex items-center space-x-2">
        <PlusIcon :class="{ 'rotate-45': open }" />
        <span class="font-semibold text-sm">Додати тег</span>
      </div>
    </div>

    <Transition name="accordion">
      <div v-if="open" class="ml-4 pl-2 border-l border-gray-300 space-y-2 mt-2">
        <div class="flex flex-col gap-2 relative group">
          <div class="relative">
            <FloatingInput
              v-model="form.name"
              label="Назва"
              :inputClasses="nameError ? 'border-red-500' : ''"
            />
            <Tooltip v-if="nameError">{{ nameError }}</Tooltip>
          </div>

          <div>
            <label class="text-sm text-gray-600 block mb-1">Тип</label>
            <select
              v-model="form.type"
              class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
            >
              <option
                v-for="(label, type) in typeUkr"
                :key="type"
                :value="type"
              >
                {{ label }}
              </option>
            </select>
          </div>

          <PrimaryButton @click="submit">Додати</PrimaryButton>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
@import '@/../css/assets/accordion.css';
</style>
