<script setup>
import { ref, computed } from 'vue'
import SelectWithSearchAndAdd from '@/Components/Custom/SelectWithSearchAndAdd.vue'

const props = defineProps({
  modelValue: Object,
  preloadedOptions: Object,
  type: String, // 'trees', 'bushes', 'hedges', 'flowers'
})
const emit = defineEmits(['update:modelValue', 'remove'])

const modalVisible = ref(false)

const selectedType = ref(Object.keys(props.modelValue || {})[0] || 'species')
const selectedId = ref(Object.values(props.modelValue || {})[0] || null)

const availableTypes = [
  { key: 'families', label: 'Родина' },
  { key: 'genus', label: 'Рід' },
  { key: 'species', label: 'Вид' }
]

const value = computed({
  get: () => props.modelValue,
  set: val => emit('update:modelValue', val)
})

function updateTaxon(id) {
  value.value = { [selectedType.value]: id }
  modalVisible.value = false
}

function onTypeChange(event) {
  selectedType.value = event.target.value
  value.value = { [selectedType.value]: null }
}
</script>

<template>
  <div class="flex items-center gap-2">
    <select class="border rounded px-2 py-1 text-sm"
        :class="{ 'no-arrow': selectedType === 'families' }"
        v-model="selectedType" 
        @change="onTypeChange" 
    >
      <option
        v-for="type in availableTypes"
        :key="type.key"
        :value="type.key"
      >
        {{ type.label }}
      </option>
    </select>

    <SelectWithSearchAndAdd
      :key="selectedType"
      :mode="selectedType"
      class="flex-1"
      :modelValue="selectedId"
      :type="props.type"
      :showLabel="false"
      :canAddNew="false"
      :preloadedOptions="props.preloadedOptions[selectedType]"
      @update:modelValue="id => updateTaxon(id)"
    />

    <button
      @click="emit('remove')"
      class="text-red-600 hover:text-red-800 text-xl"
      title="Видалити"
    >
      ×
    </button>
  </div>
</template>
<style scoped>
select.no-arrow {
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  background-image: none;
  background-repeat: no-repeat;
  background-position: right 0.5rem center;
}

</style>