<script setup>
import NumberSelect from '@/Components/Custom/NumberSelect.vue'
import StateSelector from '@/Components/Custom/StateSelector.vue'
import { createDraggableMarkerWithLine, createDraggableMarker } from '@/Helpers/Admin/CreateDraggableMarker'
import { useParkStore } from '@/Stores/useParkStore'
import axios from 'axios'
import { onBeforeUnmount, onMounted, ref, watch, computed } from 'vue'
import SelectWithSearchAndAdd from '@/Components/Custom/SelectWithSearchAndAdd.vue'
import Modal from '@/Components/Default/Modal.vue'
import DictTaxonomy from '@/Components/Dictionaries/DictTaxonomy.vue'
import DictInfrastructureType from '@/Components/Dictionaries/DictInfrastructureType.vue'
import TagList from '../TagList.vue'
import DictTags from '@/Components/Dictionaries/DictTags.vue'
import DictHedgeRow from '@/Components/Dictionaries/DictHedgeRow.vue'
import DictHedgeShape from '@/Components/Dictionaries/DictHedgeShape.vue'
import DictPlots from '@/Components/Dictionaries/DictPlots.vue'
import { cacheMarkerCoords, getCoordsFromMarker } from '@/Helpers/Maps/MapHelper'
import FormError from '@/Components/Custom/FormError.vue'
import MarkerDescriptionEditor from './MarkerDescriptionEditor.vue'

const props = defineProps({ marker: Object })

const marker = ref(JSON.parse(JSON.stringify(props.marker)))
const parkStore = useParkStore()
const isAddingNew = ref(!!props.marker.isDraft)
const googleMapMarker = ref(null)
const originalPosition = ref(null)
const destroyDraggableMarker = ref(null)
const hasUserMovedDraftMarker = ref(false)

const showModal = ref({
  species: false,
  infrastructureType: false,
  tags: false,
  hedgeShape: false,
  hedgeRow: false,
  plot: false
})

onMounted(async () => {
  originalPosition.value = getCoordsFromMarker(marker.value)

  const draggableMarkerFactory = isAddingNew.value ? createDraggableMarker : createDraggableMarkerWithLine

  const result = await draggableMarkerFactory({
    map: parkStore.map,
    position: { ...originalPosition.value },
    drawLineFrom: originalPosition.value,
    onDrag: (latLng) => {
      hasUserMovedDraftMarker.value = true
      marker.value.coordinates = [latLng.lng(), latLng.lat()]
      cacheMarkerCoords(marker.value)
    }
  })
  googleMapMarker.value = result.marker
  destroyDraggableMarker.value = result.destroy
})

watch(() => marker.value.type, (newType) => {
  initializeMarkerType(marker.value, newType)
}, { immediate: true })

watch(
  () => props.marker,
  (newMarker) => {
    if (!newMarker || !isAddingNew.value) return
    if (hasUserMovedDraftMarker.value) return

    marker.value = JSON.parse(JSON.stringify(newMarker))

    if (googleMapMarker.value && Array.isArray(newMarker.coordinates)) {
      googleMapMarker.value.position = getCoordsFromMarker(newMarker)
    }
  },
  { deep: true }
)

onBeforeUnmount(() => {
  destroyDraggableMarker.value?.()
})

defineExpose({ save })

const errors = ref({})
function getByPath(obj, path) {
  return path.split('.').reduce((acc, k) => acc?.[k], obj)
}
watch(
  () => Object.keys(errors.value || {}).map(key => [key, getByPath(marker.value, key)]),
  (newEntries, oldEntries = []) => {
    for (let index = 0; index < newEntries.length; index += 1) {
      const [key, value] = newEntries[index]
      const previousValue = oldEntries[index]?.[1]

      if (value !== previousValue) {
        delete errors.value[key]
      }
    }
  }
)

async function save() {
  cleanupMarkerType(marker.value, marker.value.type)
  errors.value = {}
  try {
    if(isAddingNew.value) {
      marker.value.park_id = parkStore.selectedPark.id
      await axios.post('/api/markers', marker.value).then((response) => {
        marker.value.id = response.data.id
        marker.value.icon = response.data.icon
        marker.value.description = response.data.description
        if (marker.value.green)
          marker.value.green.green_state_changed_at = response.data.green_state_changed_at
        marker.value.isDraft = false
        cacheMarkerCoords(marker.value)
        parkStore.markers.push(marker.value)
        parkStore.selectedMarker = marker.value
        parkStore.selectedMarker.edited = true
      })
    } else {
      await axios.patch(`/api/markers/${marker.value.id}`, marker.value).then((response) => {
        marker.value.description = response.data.description
        if (marker.value.green)
          marker.value.green.green_state_changed_at = response.data.green_state_changed_at
        const keysToCopy = ['coordinates', 'description', 'type', 'plot_id', 'green', 'infrastructure']
        for (const key of keysToCopy) {
          if (key in marker.value)
            parkStore.selectedMarker[key] = marker.value[key]
        }
        cacheMarkerCoords(parkStore.selectedMarker)
        parkStore.selectedMarker.icon = response.data.icon
        parkStore.selectedMarker.edited = true
      })
    }
    return true
  } catch(e) {
    parkStore.markerStates.saveFailed = true
    errors.value = e.response.data.errors || {}
    setTimeout(() => { parkStore.markerStates.saveFailed = true }, 5000)
    return false
  }
}

const isGreen = computed(() => ['tree', 'bush', 'hedge', 'flower'].includes(marker.value.type))

function initializeMarkerType(marker, type) {
  marker.tags ||= []
  if (['tree', 'bush', 'hedge', 'flower'].includes(type)) {
    marker.green ||= {}
    marker.green.inventory_number ||= null
    marker.green.green_state ||= null
    marker.green.green_state_changed_at ||= null
    marker.green.green_state_note ||= null
    marker.green.species_id ||= null
    marker.green.subplot_id ||= null
    marker.green.subplot ||= {}
    if (type === 'tree') {
      marker.green.tree ||= {}
      marker.green.tree.inventory_tag ??= null
    }
    if (type === 'bush') marker.green.bush ||= {}
    if (type === 'hedge') marker.green.hedge ||= {}
  }
  if (type === 'infrastructure') {
    marker.infrastructure ||= {}
    marker.infrastructure.name ||= ''
    marker.infrastructure.infrastructure_type_id ||= null
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

const plantingDate = computed({
  get: () => marker.value.green?.planting_date?.slice(0, 10) ?? '',
  set: val => {
    if (!marker.value.green) marker.value.green = {}
    marker.value.green.planting_date = val || null
  }
})

const selectSpecies = (species) => {
  species.edited = true
  marker.value.green.species = species
  marker.value.green.species_id = species.id
  showModal.value.species = false
}
const selectSubplot = (data) => {
  const green = marker.value.green
  green.subplot = data.subplot
  green.subplot_id = data.subplot.id
  green.plot = data.plot
  green.plot_id = data.plot.id
  showModal.value.plot = false
}
const selectInfrastructureType = (infraType) => {
  infraType.edited = true
  marker.value.infrastructure.infrastructure_type = infraType
  marker.value.infrastructure.infrastructure_type_id = infraType.id
  showModal.value.infrastructureType = false
}
const selectTag = (tag) => {
  marker.value.tags ||= [];
  const exists = marker.value.tags.some(t => t.id === tag.id)
  if (!exists)
    marker.value.tags.push(tag)
  showModal.value.tags = false
}

const selectHedgeShape = (shape) => {
  // shape.edited = true
  marker.value.green.hedge.hedge_shape = shape
  marker.value.green.hedge.hedge_shape_id = shape.id
  showModal.value.hedgeShape = false
}
const selectHedgeRow = (row) => {
  // row.edited = true
  marker.value.green.hedge.hedge_row = row
  marker.value.green.hedge.hedge_row_id = row.id
  showModal.value.hedgeRow = false
}

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
        <FormError :errors="errors['type']" />
      </div>

      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Опис</label>
        <MarkerDescriptionEditor v-model="marker.description" />
        <FormError :errors="errors['description']" />
      </div>
    </div>

    <div v-if="isGreen" class="bg-white rounded px-4 py-4 space-y-4">
      <h3 class="text-lg font-semibold text-gray-800">Зелене насадження</h3>

      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Інвентарний номер</label>
        <input v-model="marker.green.inventory_number" class="w-full border border-gray-300 rounded px-2 py-1" />
        <FormError :errors="errors['green.inventory_number']" />
      </div>

      <div v-if="marker.type === 'tree'" class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Номер бірки</label>
        <input v-model="marker.green.tree.inventory_tag" class="w-full border border-gray-300 rounded px-2 py-1" />
        <FormError :errors="errors['green.tree.inventory_tag']" />
      </div>

      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Стан</label>
        <StateSelector v-model="marker.green.green_state" />
        <FormError :errors="errors['green.green_state']" />
      </div>

      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Дата набуття стану</label>
        <input type="date" v-model="marker.green.green_state_changed_at" class="w-full border border-gray-300 rounded px-2 py-1" />
        <FormError :errors="errors['green.green_state_changed_at']" />
      </div>

      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Коментар до стану</label>
        <textarea v-model="marker.green.green_state_note" class="w-full border border-gray-300 rounded px-2 py-1" rows="3" />
        <FormError :errors="errors['green.green_state_note']" />
      </div>

      <SelectWithSearchAndAdd
        mode="species"
        class="space-y-1"
        v-model="marker.green.species_id"
        :startingItem="marker.green.species"
        :type="marker.type"
        @show-modal="() => showModal.species = true"
      />
      <FormError :errors="errors['green.species_id']" />

      <Modal :show="showModal.species" maxWidth="2xl" @close="showModal.species = false">
        <DictTaxonomy
          :type="marker.type"
          @selectSpecies="selectSpecies"
        />
      </Modal>

      <div class="space-y-1">
        <SelectWithSearchAndAdd
          mode="plots"
          class="space-y-1"
          v-model="marker.green.plot_id"
          :startingItem="marker.green.plot"
          :parentId="parkStore.selectedPark?.id"
          @update:modelValue="() => {marker.green.subplot_id = null; marker.green.subplot = null}"
          @show-modal="() => showModal.plot = true"
        />
      </div>

      <div class="space-y-1">
        <SelectWithSearchAndAdd
          mode="subplots"
          class="space-y-1"
          v-model="marker.green.subplot_id"
          :startingItem="marker.green.subplot"
          :disabled="!marker.green?.plot_id"
          :parentId="marker.green?.plot_id"
          @show-modal="() => showModal.plot = true"
        />
        <FormError :errors="errors['green.subplot_id']" />
      </div>

      <Modal :show="showModal.plot" maxWidth="2xl" @close="showModal.plot = false">
        <DictPlots
          :parkId="parkStore.selectedPark?.id"
          :plotId="marker.green?.plot_id"
          @select="selectSubplot"
        />
      </Modal>

      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Дата посадки</label>
        <input type="date" v-model="plantingDate" class="w-full border border-gray-300 rounded px-2 py-1" />
        <FormError :errors="errors['green.planting_date']" />
      </div>
    </div>
    <div v-if="isGreen && marker.type !== 'flower'" class="bg-white rounded px-4 py-4 space-y-4">
      <h3 class="text-lg font-semibold text-gray-800">Властивості {{ subGreenTitle }}</h3>

      <div v-if="marker.type === 'tree'" class="pt-2">
        <NumberSelect v-model="marker.green.tree.height_m" :min="0" :max="50" label="Висота (м)" />
        <FormError :errors="errors['green.tree.height_m']" />
        <NumberSelect v-model="marker.green.tree.trunk_circumference_cm" :min="0" :max="250" label="Охоплення стовбура (см)" />
        <FormError :errors="errors['green.tree.trunk_circumference_cm']" />
        <NumberSelect v-model="marker.green.tree.tilt_degree" :min="0" :max="60" label="Нахил (°)" />
        <FormError :errors="errors['green.tree.tilt_degree']" />
        <NumberSelect v-model="marker.green.tree.crown_condition_percent" :min="0" :max="100" label="Стан крони (%)" />
        <FormError :errors="errors['green.tree.crown_condition_percent']" />
      </div>

      <div v-if="marker.type === 'bush'" class="pt-2">
        <NumberSelect v-model="marker.green.bush.quantity" :min="0" :max="150" label="Кількість кущів" />
        <FormError :errors="errors['green.bush.quantity']" />
      </div>

      <div v-if="marker.type === 'hedge'" class="pt-2 space-y-2">
        <NumberSelect v-model="marker.green.hedge.length_m" :min="0" :max="150" label="Довжина (м)" />
        <FormError :errors="errors['green.hedge.length_m']" />

        <div class="space-y-1">
          <SelectWithSearchAndAdd
            mode="hedgeRows"
            class="space-y-1"
            v-model="marker.green.hedge.hedge_row_id"
            :startingItem="marker.green.hedge.hedge_row"
            :type="marker.type"
            @show-modal="() => showModal.hedgeRow = true"
          />
          <FormError :errors="errors['green.hedge.hedge_row_id']" />

          <Modal :show="showModal.hedgeRow" maxWidth="2xl" @close="showModal.hedgeRow = false">
            <DictHedgeRow
              @selectHedgeRow="selectHedgeRow"
            />
          </Modal>
        </div>

        <div class="space-y-1">
          <SelectWithSearchAndAdd
            mode="hedgeShapes"
            class="space-y-1"
            v-model="marker.green.hedge.hedge_shape_id"
            :startingItem="marker.green.hedge.hedge_shape"
            :type="marker.type"
            @show-modal="() => showModal.hedgeShape = true"
          />
          <FormError :errors="errors['green.hedge.hedge_shape_id']" />

          <Modal :show="showModal.hedgeShape" maxWidth="2xl" @close="showModal.hedgeShape = false">
            <DictHedgeShape
              @selectHedgeShape="selectHedgeShape"
            />
          </Modal>
        </div>
      </div>
    </div>

    <div v-if="marker.type === 'infrastructure'" class="bg-white rounded px-4 py-4 space-y-4">
      <h3 class="text-lg font-semibold text-gray-800">Інфраструктура</h3>

      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Назва</label>
        <input v-model="marker.infrastructure.name" class="w-full border border-gray-300 rounded px-2 py-1" />
        <FormError :errors="errors['infrastructure.name']" />
      </div>

      <SelectWithSearchAndAdd
        mode="infrastructureType"
        class="space-y-1"
        v-model="marker.infrastructure.infrastructure_type_id"
        :startingItem="marker.infrastructure.infrastructure_type"
        :type="marker.type"
        @show-modal="() => showModal.infrastructureType = true"
      />
      <FormError :errors="errors['infrastructure.infrastructure_type_id']" />

      <Modal :show="showModal.infrastructureType" maxWidth="2xl" @close="showModal.infrastructureType = false">
        <DictInfrastructureType
          @selectInfrastructureType="selectInfrastructureType"
        />
      </Modal>

    </div>
    
    <div v-if="marker" class="bg-white rounded px-4 py-4 space-y-1">
      <h3 class="text-lg font-semibold text-gray-800">Теги</h3>
      <SelectWithSearchAndAdd
        mode="tags"
        class="space-y-1"
        v-model="marker.tags"
        :type="marker.type"
        @show-modal="() => showModal.tags = true"
      />
      <FormError :errors="errors.tags" />

      <Modal :show="showModal.tags" maxWidth="2xl" @close="showModal.tags = false">
        <DictTags
          @selectTag="selectTag"
          :type="marker.type"
        />
      </Modal>
      <TagList v-model="marker.tags" :edit="true"/>
    </div>
  </div>
  <div class="h-[5rem]"></div> <!-- Spacer -->
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
