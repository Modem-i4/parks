<script setup>
import { ref, computed, watch } from 'vue'
import axios from 'axios'
import WorkItem from './WorkItem.vue'
import WorkAddForm from './WorkAddForm.vue'
import ArrowIcon from '../Custom/Icons/ArrowIcon.vue'

const props = defineProps({
  modelValue: Array,
  greenId: Number,
  loading: Boolean
})

const emit = defineEmits(['update:modelValue'])

const works = ref([...props.modelValue])
const showCompletedWorks = ref(false)

watch(() => props.modelValue, (val) => {
  works.value = [...val]
})

const completedWorks = computed(() =>
  works.value.filter(work => work.execution_date)
)

const activeWorks = computed(() =>
  works.value.filter(work => !work.execution_date)
)

async function createWork(data) {
  const res = await axios.post('/api/works', {
    green_id: props.greenId,
    ...data
  })
  works.value.push(res.data)
  emit('update:modelValue', [...works.value])
}

function removeWork(id) {
  works.value = works.value.filter(w => w.id !== id)
  emit('update:modelValue', [...works.value])
}

function completeWork(work) {
  showCompletedWorks.value = true
  updateWork(work)
}

function updateWork(work) {
  const index = works.value.findIndex(w => w.id === work.id)
  if (index !== -1) {
    works.value[index] = work
    emit('update:modelValue', [...works.value])
  }
}
</script>

<template>
  <div class="space-y-2 bg-white rounded px-4 py-4 mb-2 text-gray-700">
    <WorkAddForm @create="createWork" />

    <div v-if="activeWorks.length" class="space-y-2 mt-4">
      <WorkItem
        v-for="work in activeWorks"
        :key="work.id"
        :work="work"
        @update="updateWork"
        @complete="completeWork"
        @delete="removeWork"
      />
    </div>

    <div v-if="completedWorks.length">
      <div
        class="bg-gray-50 px-2 py-3 rounded cursor-pointer hover:bg-gray-100"
        @click="showCompletedWorks = !showCompletedWorks"
      >
        <div class="flex items-center space-x-2">
          <ArrowIcon :class="{ 'rotate-90': showCompletedWorks }" />
          <span class="font-semibold text-sm">Завершені роботи</span>
        </div>
      </div>
      <Transition name="accordion">
        <div class="space-y-2" v-if="showCompletedWorks">
            <WorkItem
              v-for="work in completedWorks"
              @complete="completeWork"
              :key="work.id"
              :work="work"
              :readonly="true"
            />
        </div>
      </Transition>
    </div>
  </div>
</template>


<style scoped>
@import '@/../css/assets/accordion.css';
</style>
