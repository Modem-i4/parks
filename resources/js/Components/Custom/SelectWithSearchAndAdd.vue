<template>
  <div class="space-y-1">
    <label class="text-sm font-medium text-gray-700">{{ label }}</label>
    <div class="relative">
      <input
        v-model="search"
        type="text"
        class="w-full border border-gray-300 rounded px-2 py-1"
        placeholder="Пошук..."
        @focus="openDropdown"
        @blur="handleBlur"
      />

      <ul
        v-if="dropdownVisible"
        class="absolute z-10 w-full bg-white border border-gray-200 rounded shadow max-h-60 overflow-auto"
      >
        <li
          class="px-3 py-2 text-blue-600 hover:underline cursor-pointer border-b border-gray-100"
          @mousedown.prevent="$emit('show-modal', props.mode)"
        >
          + Додати новий {{ labelShort }}
        </li>

        <li
          v-for="item in filteredList"
          :key="item.id"
          class="px-3 py-1 hover:bg-gray-100 cursor-pointer"
          @mousedown.prevent="select(item)"
        >
          {{ item[labelField] }}
        </li>
      </ul>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
  modelValue: [Number, null],
  mode: { type: String, required: true }, // 'species' | 'infrastructureType'
  startingItem: [Object, null],
  type: String,
})
const emit = defineEmits(['update:modelValue', 'show-modal'])

const dataList = ref([])
const search = ref('')
const dropdownVisible = ref(false)
const selectedName = ref('')
const preventNextOpen = ref(false)

const labelField = computed(() => {
  switch (props.mode) {
    case 'species': return 'name_ukr'
    case 'infrastructureType': return 'name'
    default: return 'name'
  }
})

const label = computed(() => {
  switch (props.mode) {
    case 'species': return 'Вид'
    case 'infrastructureType': return 'Тип інфраструктури'
    default: return 'Пошук'
  }
})
const labelShort = computed(() => {
  switch (props.mode) {
    case 'species': return 'вид'
    case 'infrastructureType': return 'тип'
  }
})

watch(() => props.startingItem, (item) => {
  const name = item?.[labelField.value] ?? ''
  selectedName.value = name
  search.value = name

  if (item?.edited) {
    preventNextOpen.value = true
    delete item.edited
  }
}, { immediate: true })

const filteredList = computed(() => {
  if (!search.value) return dataList.value
  const q = search.value.toLowerCase()
  return dataList.value.filter(item =>
    item[labelField.value].toLowerCase().includes(q)
  )
})

const endpoint = computed(() => {
  switch (props.mode) {
    case 'species': return `/api/species/${props.type}`
    case 'infrastructureType': return `/api/infrastructureType`
  }
})

async function loadData() {
  try {
    const { data } = await axios.get(endpoint.value)
    dataList.value = data
  } catch (err) {
    console.error('Помилка завантаження даних', err)
  }
}

function select(item) {
  emit('update:modelValue', item.id)
  selectedName.value = item[labelField.value]
  search.value = item[labelField.value]
  dropdownVisible.value = false
}

function openDropdown() {
  if (preventNextOpen.value) {
    preventNextOpen.value = false
    return
  }
  dropdownVisible.value = true
  search.value = ''
  if (!dataList.value.length) loadData()
}

function handleBlur() {
  setTimeout(() => {
    dropdownVisible.value = false
    search.value = selectedName.value
  }, 150)
}
</script>
