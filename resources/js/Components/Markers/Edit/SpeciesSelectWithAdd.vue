<template>
  <div class="space-y-1">
    <label class="text-sm font-medium text-gray-700">Вид</label>
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
          @mousedown.prevent="$emit('open-species-modal')"
        >
          + Додати новий вид
        </li>

        <li
          v-for="sp in filteredList"
          :key="sp.id"
          class="px-3 py-1 hover:bg-gray-100 cursor-pointer"
          @mousedown.prevent="select(sp)"
        >
          {{ sp.name_ukr }}
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
  type: { type: String, required: true },
  startingSpecies: [Object, null],
})
const emit = defineEmits(['update:modelValue', 'open-species-modal'])

const speciesList = ref([])
const search = ref('')
const dropdownVisible = ref(false)
const selectedName = ref('')
const preventNextOpen = ref(false)

watch(() => props.startingSpecies, (sp) => {
    const name = sp?.name_ukr ?? ''
    selectedName.value = name
    search.value = name

    if(sp.edited) {
        preventNextOpen.value = true
        delete sp.edited
    }
}, { immediate: true })


const filteredList = computed(() => {
  if (!search.value) return speciesList.value
  const q = search.value.toLowerCase()
  return speciesList.value.filter(sp =>
    sp.name_ukr.toLowerCase().includes(q)
  )
})

async function loadSpecies() {
  try {
    const { data } = await axios.get(`/api/species/${props.type}`)
    speciesList.value = data
  } catch (err) {
    console.error('Помилка завантаження видів', err)
  }
}

function select(sp) {
  emit('update:modelValue', sp.id)
  selectedName.value = sp.name_ukr
  search.value = sp.name_ukr
  dropdownVisible.value = false
}

function openDropdown() {
    if (preventNextOpen.value) {
        preventNextOpen.value = false
        return
    }
    dropdownVisible.value = true
    search.value = ''
    if (!speciesList.value.length) loadSpecies()
}

function handleBlur() {
  setTimeout(() => {
    dropdownVisible.value = false
    search.value = selectedName.value
  }, 150)
}
</script>
