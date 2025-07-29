<template>
  <div class="space-y-1">
    <label class="text-sm font-medium text-gray-700">{{ label }}</label>
    <div class="relative">
      <input
        v-model="search"
        type="text"
        class="w-full border border-gray-300 rounded px-2 py-1"
        placeholder="Пошук..."
        @click="openDropdown"
        @blur="handleBlur"
      />

      <ul
        v-if="dropdownVisible"
        class="absolute z-10 w-full bg-white border border-gray-200 rounded shadow max-h-60 overflow-auto"
      >
        <li
          class="px-3 py-2 text-blue-600 hover:underline cursor-pointer border-b border-gray-100"
          v-if="props.canAddNew"
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
  modelValue: [Number, Array, null],
  mode: { type: String, required: true }, // 'species' | 'infrastructureType' | 'tags'
  startingItem: [Object, null],
  type: String, // 'trees', 'bushes', 'hedges', 'flowers'
  showLabel: { type: Boolean, default: true },
  canAddNew: { type: Boolean, default: true },
  preloadedOptions: Array,
})
const emit = defineEmits(['update:modelValue', 'show-modal'])

const dataList = ref([])
const search = ref('')
const dropdownVisible = ref(false)
const selectedName = ref('')
const preventNextOpen = ref(false)

const labelField = computed(() => {
  switch (props.mode) {
    case 'species':
    case 'genus':
    case 'families':
      return 'name_ukr'
    case 'infrastructureType': return 'name'
    case 'tags': return 'name'
    default: return 'name'
  }
})

const label = computed(() => {
  if(!props.showLabel) return
  switch (props.mode) {
    case 'species': return 'Вид'
    case 'infrastructureType': return 'Тип інфраструктури'
    case 'tags': return 'Вибір тегів'
  }
})
const labelShort = computed(() => {
  switch (props.mode) {
    case 'species': return 'вид'
    case 'genus': return 'рід'
    case 'families': return 'родина'
    case 'infrastructureType': return 'тип'
    case 'tags': return 'тег'
  }
})

watch(() => props.startingItem, (item) => {
  const name = item?.[labelField.value] ?? ''
  selectedName.value = name
  search.value = name

  if (item?.edited && props.mode !== 'tags') {
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
    case 'species': 
    case 'genus':
    case 'families':
    case 'tags':
      return `/api/${props.mode}/${props.type}`
    case 'infrastructureType': return `/api/${props.mode}`
  }
})

watch(() => props.preloadedOptions, loadData, { immediate: true })
watch(() => props.type, loadData)

async function loadData() {
  if (props.preloadedOptions?.length) {
    dataList.value = props.preloadedOptions
    return
  }
  try {
    const { data } = await axios.get(endpoint.value)
    if (props.mode === 'tags') {
      dataList.value = data.filter(item => !props.modelValue.some(tag => tag.id === item.id))
    } else {
      dataList.value = data
    }
  } catch (err) {
    console.error('Помилка завантаження даних', err)
  }
}

function select(item) {
  if(props.mode !== 'tags') {
    emit('update:modelValue', item.id)
    selectedName.value = item[labelField.value]
    search.value = item[labelField.value]
  }
  if(props.mode === 'tags') {
    const exists = props.modelValue.some(t => t.id === item.id)
    if (!exists)
      props.modelValue.push(item)
    dataList.value = dataList.value.filter(i => i.id !== item.id)
    selectedName.value = ''
    search.value = ''
  }
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
  dropdownVisible.value = false
  search.value = selectedName.value
}
</script>