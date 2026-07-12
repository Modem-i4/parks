<script setup>
import { computed } from 'vue'
import SelectWithSearchAndAdd from '@/Components/Custom/SelectWithSearchAndAdd.vue'

const props = defineProps({
  modelValue: Object,
  plotOptions: Array,
  plotOptionsById: Object,
  subplotsByPlotId: Object,
  subplotsById: Object,
  parkId: Number
})

const emit = defineEmits(['update:modelValue', 'remove'])

const value = computed({
  get: () => props.modelValue,
  set: v => emit('update:modelValue', v)
})

const plotId = computed({
  get: () => value.value.plotId,
  set: id => {
    value.value = { plotId: id, subplots: [] }
  }
})

const subplots = computed(() => value.value.subplots)

function addSubplot() {
  value.value = { ...value.value, subplots: [...subplots.value, null] }
}

function updateSubplot(index, id) {
  const next = [...subplots.value]
  next[index] = id
  value.value = { ...value.value, subplots: next }
}

function removeSubplot(index) {
  const next = [...subplots.value]
  next.splice(index, 1)
  value.value = { ...value.value, subplots: next }
}
</script>

<template>
  <div class="space-y-2">
    <div class="flex items-center gap-2">
      <SelectWithSearchAndAdd
        class="flex-1"
        mode="plots"
        v-model="plotId"
        :startingItem="plotId ? { id: plotId, name: plotOptionsById[plotId] } : null"
        :parkId="parkId"
        :preloadedOptions="plotOptions"
        :showLabel="false"
        :canAddNew="false"
      />

      <button
        @click="emit('remove')"
        class="text-red-600 hover:text-red-800 text-xl"
        title="Видалити"
      >
        ×
      </button>
    </div>

    <div class="ml-4 pl-2 border-l border-gray-300 space-y-2">
      <template v-if="plotId" >
        <div
          class="flex items-center gap-2"
          v-for="(subplotId, index) in subplots"
          :key="index"
        >
          <SelectWithSearchAndAdd
            class="flex-1"
            mode="subplots"
            :modelValue="subplotId"
            :startingItem="subplotId ? { id: subplotId, name: subplotsById[subplotId] } : null"
            :preloadedOptions="subplotsByPlotId[plotId] ?? []"
            :showLabel="false"
            :canAddNew="false"
            @update:modelValue="id => updateSubplot(index, id)"
          />

          <button
            @click="removeSubplot(index)"
            class="text-red-600 hover:text-red-800 text-xl"
            title="Видалити"
          >
            ×
          </button>
        </div>

        <button
          class="text-md text-blue-600 hover:underline"
          @click="addSubplot"
        >
          + Конкретизувати за ділянкою
        </button>
      </template>
    </div>
  </div>
</template>
