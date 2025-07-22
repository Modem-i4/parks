<script setup>
import BtnWhite from '@/Components/Custom/BtnWhite.vue'
import FloatingInput from '@/Components/Custom/FloatingInput.vue'
import NumberSelect from '@/Components/Custom/NumberSelect.vue'
import StateSelector from '@/Components/Custom/StateSelector.vue'
import { createDraggableMarkerWithLine } from '@/Helpers/Admin/CreateDraggableMarker'
import { useParkStore } from '@/Stores/useParkStore'
import axios from 'axios'
import { onBeforeUnmount, onMounted, ref, watch, computed } from 'vue'

const props = defineProps({ marker: Object })

const marker = ref(JSON.parse(JSON.stringify(props.marker)))
const parkStore = useParkStore()
const googleMapMarker = ref(null)
const originalPosition = ref(null)
const destroyLine = ref(null)

const tagOptions = ref([])
const speciesOptions = ref([])
const infraTypeOptions = ref([])

onMounted(async () => {
  originalPosition.value = {
    lng: marker.value.coordinates[0],
    lat: marker.value.coordinates[1]
  }

  const result = await createDraggableMarkerWithLine({
    map: parkStore.map,
    position: { ...originalPosition.value },
    drawLineFrom: originalPosition.value,
    onDrag: (latLng) => {
      marker.value.coordinates = [latLng.lng(), latLng.lat()]
    }
  })
  googleMapMarker.value = result.marker
  destroyLine.value = result.destroy

  loadSelectOptions()
})

async function loadSelectOptions() {
  const [tags, species, infra] = await Promise.all([
    axios.get('/api/tags'),
    axios.get('/api/species'),
    axios.get('/api/infrastructureType'),
  ])
  tagOptions.value = tags.data
  speciesOptions.value = species.data
  infraTypeOptions.value = infra.data
}

watch(() => marker.value.type, (newType) => {
  initializeMarkerType(marker.value, newType)
})

onBeforeUnmount(() => {
  destroyLine.value?.()
})

defineExpose({ save })

function save() {
  cleanupMarkerType(marker.value, marker.value.type)

  axios.patch(`/api/markers/${marker.value.id}`, marker.value).then(() => {
    const keysToCopy = ['coordinates', 'description', 'type', 'plot_id', 'green', 'infrastructure']
    for (const key of keysToCopy) {
      if (key in marker.value)
        parkStore.selectedMarker[key] = marker.value[key]
    }
    parkStore.selectedMarker.cachedCoords = null
    parkStore.selectedMarker.edited = true
  })
}

const isGreen = computed(() => ['tree', 'bush', 'hedge', 'flower'].includes(marker.value.type))

function initializeMarkerType(marker, type) {
  if (['tree', 'bush', 'hedge', 'flower'].includes(type)) {
    marker.green ||= {}
    if (type === 'tree') marker.green.tree ||= {}
    if (type === 'bush') marker.green.bush ||= {}
    if (type === 'hedge') marker.green.hedge ||= {}
  }
  if (type === 'infrastructure') {
    marker.infrastructure ||= {}
  }
}

function cleanupMarkerType(marker, type) {
  if (type !== 'infrastructure') delete marker.infrastructure
  if (!['tree', 'bush', 'hedge', 'flower'].includes(type)) delete marker.green

  if (marker.green) {
    if (type !== 'tree') delete marker.green.tree
    if (type !== 'bush') delete marker.green.bush
    if (type !== 'hedge') delete marker.green.hedge
  }
}

const subGreenTitle = computed(() => ({
  tree: 'дерева',
  bush: 'куща',
  hedge: 'живоплота',
  flower: 'квітів',
}[marker.value.type] || ''))

const plantingMonth = computed({
  get: () => marker.value.green?.planting_date?.slice(0, 7) ?? '',
  set: val => {
    if (!marker.value.green) marker.value.green = {}
    marker.value.green.planting_date = val + '-01'
  }
})

</script>
<template>
  <div class="py-4 flex flex-col gap-6 max-w-xl">
    <div class="bg-white rounded px-4 py-4 space-y-4">
      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Тип</label>
        <select v-model="marker.type" class="w-full border border-gray-300 rounded px-2 py-1">
          <option value="infrastructure">Інфраструктура</option>
          <option value="tree">Дерево</option>
          <option value="bush">Кущ</option>
          <option value="hedge">Живопліт</option>
          <option value="flower">Квіти</option>
        </select>
      </div>

      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Опис</label>
        <textarea v-model="marker.description" class="w-full border border-gray-300 rounded px-2 py-1" rows="3" />
      </div>
    </div>

    <div v-if="isGreen" class="bg-white rounded px-4 py-4 space-y-4">
      <h3 class="text-lg font-semibold text-gray-800">Зелене насадження</h3>

      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Інвентарний номер</label>
        <input v-model="marker.green.inventory_number" class="w-full border border-gray-300 rounded px-2 py-1" />
      </div>

      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Стан</label>
        <StateSelector v-model="marker.green.quality_state" />
      </div>

      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Коментар до стану</label>
        <input v-model="marker.green.quality_state_note" class="w-full border border-gray-300 rounded px-2 py-1" />
      </div>

      <div class="space-y-1"> <!-- TODO -->
        <label class="text-sm font-medium text-gray-700">Вид</label>
        <select v-model="marker.green.species_id" class="w-full border border-gray-300 rounded px-2 py-1">
          <option :value="null">-- Оберіть --</option>
          <option v-for="sp in speciesOptions" :key="sp.id" :value="sp.id">{{ sp.name_ukr }}</option>
        </select>
      </div>

      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Приблизна дата посадки</label>
        <input type="month" v-model="plantingMonth" class="w-full border border-gray-300 rounded px-2 py-1" />
      </div>
    </div>
    <div v-if="isGreen && marker.type !== 'flower'" class="bg-white rounded px-4 py-4 space-y-4">
      <h3 class="text-lg font-semibold text-gray-800">Властивості {{ subGreenTitle }}</h3>

      <div v-if="marker.type === 'tree'" class="pt-2">
        <NumberSelect v-model="marker.green.tree.height_m" :min="0" :max="50" label="Висота (м)" />
        <NumberSelect v-model="marker.green.tree.trunk_diameter_cm" :min="0" :max="150" label="Діаметр стовбура (см)" />
        <NumberSelect v-model="marker.green.tree.trunk_circumference_cm" :min="0" :max="300" label="Охоплення стовбура (см)" />
        <NumberSelect v-model="marker.green.tree.tilt_degree" :min="0" :max="90" label="Нахил (°)" />
        <NumberSelect v-model="marker.green.tree.crown_condition_percent" :min="0" :max="100" label="Стан крони (%)" />
      </div>

      <div v-if="marker.type === 'bush'" class="pt-2">
        <NumberSelect v-model="marker.green.bush.quantity" :min="0" :max="500" label="Кількість кущів" />
      </div>

      <div v-if="marker.type === 'hedge'" class="pt-2 space-y-2">
        <NumberSelect v-model="marker.green.hedge.length_m" :min="0" :max="500" label="Довжина (м)" />

        <div class="space-y-1">
          <label class="text-sm font-medium text-gray-700">Тип ряду</label>
          <input v-model="marker.green.hedge.hedge_type_row" class="w-full border border-gray-300 rounded px-2 py-1" />
        </div>

        <div class="space-y-1">
          <label class="text-sm font-medium text-gray-700">Форма</label>
          <input v-model="marker.green.hedge.hedge_type_shape" class="w-full border border-gray-300 rounded px-2 py-1" />
        </div>
      </div>
    </div>

    <div v-if="marker.type === 'infrastructure'" class="bg-white rounded px-4 py-4 space-y-4">
      <h3 class="text-lg font-semibold text-gray-800">Інфраструктура</h3>

      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Назва</label>
        <input v-model="marker.infrastructure.name" class="w-full border border-gray-300 rounded px-2 py-1" />
      </div>

      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Тип</label>
        <select v-model="marker.infrastructure.infrastructure_type" class="w-full border border-gray-300 rounded px-2 py-1">
          <option v-for="type in infraTypeOptions" :key="type.id" :value="type.name">{{ type.name }}</option>
        </select>
      </div>
    </div>
  </div>

   <!-- Tags TODO -->
  <div class="h-[100px]"></div> <!-- Spacer -->
</template>

<style scoped>
select,
textarea,
input[type="date"] {
  background-color: #fff;
  color: #333;
  font-size: 14px;
}
</style>
