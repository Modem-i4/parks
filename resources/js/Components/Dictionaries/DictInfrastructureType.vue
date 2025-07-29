<script setup>
import { onMounted, ref } from 'vue'
import { useCrudList } from '@/Helpers/Dictionaries/useCrudList'
import { useSearchFilter } from '@/Helpers/Dictionaries/useSearchFilter'
import MediaPickerModal from '@/Components/Media/MediaPickerModal.vue'
import InfrastructureTypeItem from '../Infrastructure/InfrastructureTypeItem.vue'
import LoadingLineIndicator from '@/Components/Custom/LoadingLineIndicator.vue'
import BasicAddForm from '../Custom/BasicAddForm.vue'

const {
  items: infrastructure,
  isLoading,
  load,
  handleCreate,
  handleUpdate,
  handleDelete
} = useCrudList('/api/infrastructureType')

onMounted(load)

const {
  query: searchQuery,
  filtered: filteredInfrastructure
} = useSearchFilter(infrastructure)

const emit = defineEmits(['selectInfrastructureType'])

// image pickers
const showPicker = ref(false)
const pickerModelId = ref(null)
const pickerType = ref(null)

function startIconChange(model_id) {
  pickerModelId.value = model_id
  pickerType.value = 'icon'
  showPicker.value = true
}

function startGalleryChange(model_id) {
  pickerModelId.value = model_id
  pickerType.value = 'image'
  showPicker.value = true
}

function closeImagePicker() {
  pickerModelId.value = null
  pickerType.value = null
  showPicker.value = false
}
</script>

<template>
  <div class="space-y-4 relative pb-3">
      <div>
        <MediaPickerModal
          v-if="showPicker"
          :type="pickerType"
          modelType="App\Models\InfrastructureType"
          :modelId="pickerModelId"
          @close="closeImagePicker"
          @saved="load"
        />
      </div>
    <div class="sticky top-0 z-10 bg-white">
      <LoadingLineIndicator :isLoading/>
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Пошук..."
        class="w-full px-3 py-2 border rounded"
      />
    </div>
    <BasicAddForm
      label="тип інфраструктури"
      :fields="['name']"
      :defaultForm="{ name: '' }"
      @create="handleCreate"
    />
    <InfrastructureTypeItem 
      v-for="infrastructureType in filteredInfrastructure" 
      :key="infrastructureType.id"
      :item="infrastructureType"
      @create="handleCreate"
      @update="handleUpdate"
      @delete="handleDelete"
      @changeIcon="startIconChange"
      @changeGallery="startGalleryChange"
      @selectInfrastructureType="emit('selectInfrastructureType', $event)"
    />
  </div>
</template>
