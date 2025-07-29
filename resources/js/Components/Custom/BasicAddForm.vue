<script setup>
import { ref, computed, watch } from 'vue'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import FloatingInput from '@/Components/Custom/FloatingInput.vue'
import Tooltip from '@/Components/Custom/Tooltip.vue'
import PlusIcon from '@/Components/Custom/Icons/PlusIcon.vue'

const props = defineProps({
  label: String,
  fields: { type: Array, default: () => ['name'] },
  defaultForm: { type: Object, default: () => ({ name: '' }) },
  typeOptions: { type: Object, default: null }
})

const emit = defineEmits(['create'])

const open = ref(false)
const form = ref(structuredClone(props.defaultForm))
watch(() => props.defaultForm, () => { form.value = structuredClone(props.defaultForm) })

const showErrors = ref(false)

const errors = computed(() => {
  const e = {}
  if (!showErrors.value) return e
  for (const field of props.fields) {
    if (!form.value[field]?.trim?.()) {
      e[field] = 'Поле обовʼязкове'
    } else if (form.value[field].trim().length < 3) {
      e[field] = 'Щонайменше 3 літери'
    }
  }
  return e
})

function submit() {
  showErrors.value = true
  if (Object.keys(errors.value).length) return

  emit('create', { data: { ...form.value } })

  form.value = structuredClone(props.defaultForm)
  showErrors.value = false
  open.value = false
}
</script>

<template>
  <div>
    <div class="bg-gray-50 px-2 py-3 rounded cursor-pointer hover:bg-gray-100" @click="open = !open">
      <div class="flex items-center space-x-2">
        <PlusIcon :class="{ 'rotate-45': open }" />
        <span class="font-semibold text-sm">Додати {{ label }}</span>
      </div>
    </div>

    <Transition name="accordion">
      <div v-if="open" class="ml-4 pl-2 border-l border-gray-300 space-y-2 mt-2">
        <div class="flex flex-col gap-2 relative group">
          <template v-for="field in fields" :key="field">
            <div v-if="field === 'type' && typeOptions" class="flex flex-col gap-1">
              <label class="text-sm text-gray-600 block">Тип</label>
              <select
                v-model="form.type"
                class="w-full border border-gray-300 rounded px-2 py-1 text-sm"
              >
                <option v-for="(label, value) in typeOptions" :key="value" :value="value">
                  {{ label }}
                </option>
              </select>
            </div>
            <div v-else class="relative">
              <FloatingInput
                v-model="form[field]"
                :label="field === 'name' ? 'Назва' : field"
                :inputClasses="errors[field] ? 'border-red-500' : ''"
              />
              <Tooltip v-if="errors[field]">{{ errors[field] }}</Tooltip>
            </div>
          </template>

          <PrimaryButton @click="submit">Додати</PrimaryButton>
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
@import '@/../css/assets/accordion.css';
</style>
