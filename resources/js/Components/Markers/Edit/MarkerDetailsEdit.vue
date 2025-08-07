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

const props = defineProps({ marker: Object })

const marker = ref(JSON.parse(JSON.stringify(props.marker)))
const parkStore = useParkStore()
const isAddingNew = ref(!!props.marker.isDraft)
const googleMapMarker = ref(null)
const originalPosition = ref(null)
const destroyDraggableMarker = ref(null)

const showModal = ref({
  species: false,
  infrastructureType: false,
  tags: false,
  hedgeShape: false,
  hedgeRow: false,
  plot: false
})

onMounted(async () => {
  originalPosition.value = {
    lng: marker.value.coordinates[0],
    lat: marker.value.coordinates[1]
  }

  const draggableMarkerFactory = isAddingNew.value ? createDraggableMarker : createDraggableMarkerWithLine

  const result = await draggableMarkerFactory({
    map: parkStore.map,
    position: { ...originalPosition.value },
    drawLineFrom: originalPosition.value,
    onDrag: (latLng) => {
      marker.value.coordinates = [latLng.lng(), latLng.lat()]
    }
  })
  googleMapMarker.value = result.marker
  destroyDraggableMarker.value = result.destroy
})

watch(() => marker.value.type, (newType) => {
  initializeMarkerType(marker.value, newType)
}, { immediate: true })

onBeforeUnmount(() => {
  destroyDraggableMarker.value?.()
})

defineExpose({ save })

function save() {
  cleanupMarkerType(marker.value, marker.value.type)

  if(isAddingNew.value) {
    marker.value.park_id = parkStore.selectedPark.id
    return axios.post('/api/markers', marker.value).then((response) => {
      marker.value.id = response.data.id
      marker.value.isDraft = false
      parkStore.markers.push(marker.value)
      parkStore.selectedMarker = marker.value
      parkStore.selectedMarker.edited = true
    })
  } else {
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
}

const isGreen = computed(() => ['tree', 'bush', 'hedge', 'flower'].includes(marker.value.type))

function initializeMarkerType(marker, type) {
  marker.tags ||= []
  if (['tree', 'bush', 'hedge', 'flower'].includes(type)) {
    marker.green ||= {}
    marker.green.inventory_number ||= null
    marker.green.green_state ||= null
    marker.green.green_state_note ||= null
    marker.green.species_id ||= null
    if (type === 'tree') marker.green.tree ||= {}
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

const plantingMonth = computed({
  get: () => marker.value.green?.planting_date?.slice(0, 7) ?? '',
  set: val => {
    if (!marker.value.green) marker.value.green = {}
    marker.value.green.planting_date = val + '-01'
  }
})

const selectSpecies = (species) => {
  species.edited = true
  marker.value.green.species = species
  marker.value.green.species_id = species.id
  showModal.value.species = false
}
const selectPlot = (plot) => {
  // species.edited = true
  marker.value.green.plot = plot
  marker.value.green.plot_id = plot.id
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
        <StateSelector v-model="marker.green.green_state" />
      </div>

      <div class="space-y-1">
        <label class="text-sm font-medium text-gray-700">Коментар до стану</label>
        <textarea v-model="marker.green.green_state_note" class="w-full border border-gray-300 rounded px-2 py-1" rows="3" />
      </div>

      <SelectWithSearchAndAdd
        mode="species"
        class="space-y-1"
        v-model="marker.green.species_id"
        :startingItem="marker.green.species"
        :type="marker.type"
        @show-modal="() => showModal.species = true"
      />

      <Modal :show="showModal.species" maxWidth="2xl" @close="showModal.species = false">
        <DictTaxonomy
          :type="marker.type"
          @selectSpecies="selectSpecies"
        />
      </Modal>

      <SelectWithSearchAndAdd
        mode="plots"
        class="space-y-1"
        v-model="marker.green.plot_id"
        :startingItem="marker.green.plot"
        :parkId="marker.park_id"
        @show-modal="() => showModal.plot = true"
      />

      <Modal :show="showModal.plot" maxWidth="2xl" @close="showModal.plot = false">
        <DictPlots
          :parkId="marker.park_id"
          @select="selectPlot"
        />
      </Modal>

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
          <SelectWithSearchAndAdd
            mode="hedgeRows"
            class="space-y-1"
            v-model="marker.green.hedge.hedge_row_id"
            :startingItem="marker.green.hedge.hedge_row"
            :type="marker.type"
            @show-modal="() => showModal.hedgeRow = true"
          />

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
      </div>

      <SelectWithSearchAndAdd
        mode="infrastructureType"
        class="space-y-1"
        v-model="marker.infrastructure.infrastructure_type_id"
        :startingItem="marker.infrastructure.infrastructure_type"
        :type="marker.type"
        @show-modal="() => showModal.infrastructureType = true"
      />

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
