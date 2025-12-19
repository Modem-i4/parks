<script setup>
import { ref, computed, watch } from 'vue'
import ArrowIcon from '@/Components/Custom/Icons/ArrowIcon.vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import BasicAddForm from '../Custom/BasicAddForm.vue'
import DeleteForm from '@/Components/Custom/DeleteForm.vue'
import FloatingInput from '@/Components/Custom/FloatingInput.vue'
import Tooltip from '@/Components/Custom/Tooltip.vue'
import { isMobile } from '@/Helpers/isMobileHelper'

defineOptions({ name: 'PlotNode' })

const props = defineProps({
  item: Object,
  level: String,
  expanded: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['create', 'update', 'delete', 'select'])

const expanded = ref(!!props.expanded || !!props.item?.expanded)
watch(() => props.expanded, v => { expanded.value = !!v })
watch(() => props.item?.expanded, v => {
  if (typeof v !== 'undefined') expanded.value = !!v
})

const isPark = computed(() => props.level === 'park')
const isPlot = computed(() => props.level === 'plot')
const isSubplot = computed(() => props.level === 'subplot')

const children = computed(() =>
  isPark.value ? (props.item?.plots || []) : isPlot.value ? (props.item?.subplots || []) : []
)

const childLevel = computed(() =>
  isPark.value ? 'plot': isPlot.value ? 'subplot': null
)

const addDefaultForm = computed(() => {
  if (childLevel.value === 'plot') return { name: '', park_id: props.item?.id }
  if (childLevel.value === 'subplot') return { name: '', plot_id: props.item?.id }
  return { name: '' }
})

const isEditing = ref(false)
const confirmingDelete = ref(false)
const showErrors = ref(false)

const form = ref({ name: props.item?.name ?? '' })
watch(() => props.item?.name, v => {
  if (!isEditing.value) form.value.name = v ?? ''
})

const nameError = computed(() => {
  if (!showErrors.value) return ''
  if (!form.value.name?.trim()) return 'Поле обовʼязкове'
  if (form.value.name.trim().length < 3) return 'Щонайменше 3 літери'
  return ''
})

function toggle() {
  if (isSubplot.value) return
  if (isEditing.value || confirmingDelete.value) return
  expanded.value = !expanded.value
}

function startEdit() {
  form.value.name = props.item?.name ?? ''
  showErrors.value = false
  isEditing.value = true
}

function cancelEdit() {
  isEditing.value = false
  showErrors.value = false
}

function saveEdit() {
  showErrors.value = true
  if (nameError.value) return

  emit('update', {
    level: props.level,
    id: props.item.id,
    data: { name: form.value.name }
  })

  isEditing.value = false
  showErrors.value = false
}

function toggleDelete() {
  confirmingDelete.value = !confirmingDelete.value
}

function cancelDelete() {
  confirmingDelete.value = false
}

function confirmDelete() {
  emit('delete', { level: props.level, id: props.item.id })
  confirmingDelete.value = false
}

function onRowClick() {
  if (isEditing.value || confirmingDelete.value) return
  if (isSubplot.value) emit('select', props.item)
  else toggle()
}

function onCreateChild(data) {
  emit('create', { level: childLevel.value, data })
}
</script>

<template>
  <div class="space-y-1">
    <DeleteForm
      v-if="confirmingDelete && (isPlot || isSubplot)"
      :label="item.name"
      @confirmDelete="confirmDelete"
      @cancelDelete="cancelDelete"
    />

    <div
      class="flex items-center px-2 rounded cursor-pointer"
      :class="[
        isPark ? 'py-3' : '',
        expanded ? 'bg-gray-100' : 'bg-white border hover:bg-gray-200'
      ]"
      @click="onRowClick"
    >
      <div class="flex items-center space-x-2 flex-1 relative group">
        <ArrowIcon
          v-if="!isSubplot"
          :class="{ 'rotate-90': expanded }"
          class="transition-transform duration-200 w-4 h-4 text-gray-500"
        />

        <img v-if="isPark" 
          :src="props.item?.icon?.file_path ?? '/img/icons/markers/tree-park.svg'" 
          alt="Іконка" :class="isPark ? 'w-10' : 'w-5'" />

        <template v-if="isEditing">
          <div class="relative w-full" @click.stop>
            <FloatingInput
              v-model="form.name"
              label="Назва"
              :inputClasses="nameError ? 'border-red-500' : ''"
            />
            <Tooltip v-if="nameError">{{ nameError }}</Tooltip>
          </div>
        </template>

        <template v-else>
          <span class="font-semibold text-sm text-gray-800">
            {{ item.name }}
          </span>
        </template>
      </div>

      <div v-if="!isSubplot" class="text-xs text-gray-600 flex me-4 justify-end">
        {{ children.length }}
      </div>

      <div v-if="isPlot || isSubplot" class="space-x-1 flex-shrink-0 ml-2" @click.stop>
        <template v-if="isEditing">
          <SecondaryButton class="bg-inherit" @click="saveEdit">✔️</SecondaryButton>
          <SecondaryButton class="bg-inherit" :size="isMobile ? 'sm' : 'md'" @click="cancelEdit">❌</SecondaryButton>
        </template>
        <template v-else>
          <SecondaryButton class="bg-inherit" :size="isMobile ? 'sm' : 'md'" @click="startEdit">✏️</SecondaryButton>
          <SecondaryButton size="sm" variant="danger" class="bg-inherit" @click="toggleDelete">🗑️</SecondaryButton>
        </template>
      </div>
    </div>

    <Transition name="accordion">
      <div v-if="expanded && !isSubplot" class="ml-4 pl-2 border-l border-gray-300 space-y-1">
        <BasicAddForm
          :label="childLevel === 'plot' ? 'виділ' : childLevel === 'subplot' ? 'ділянку' : ''"
          :fields="['name']"
          :defaultForm="addDefaultForm"
          @create="onCreateChild"
        />

        <PlotNode
          v-for="child in children"
          :key="child.id"
          :item="child"
          :level="childLevel"
          :expanded="!!child.expanded"
          @create="emit('create', $event)"
          @update="emit('update', $event)"
          @delete="emit('delete', $event)"
          @select="isPlot ? emit('select', { plot: props.item, subplot: $event }) : emit('select',$event)"
        />
      </div>
    </Transition>
  </div>
</template>

<style scoped>
@import '@/../css/assets/accordion.css';
</style>
