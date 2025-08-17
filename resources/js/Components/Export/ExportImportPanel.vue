<template>
  <div class="p-4 space-y-5">
    <h2 class="text-lg font-semibold text-gray-800">⏬ Експорт / Імпорт</h2>

    <div class="flex justify-center">
      <Switch
        v-model="panelMode"
        :options="[
          { value: 'export', label: '⏬ Експорт', color: 'blue' },
          { value: 'import', label: '⏫ Імпорт', color: 'green' },
        ]"
      />
    </div>

    <ExportPanel
      v-if="panelMode === 'export'"
      :loading="loading"
      @done="handleDone"
      @cancel="emit('close')"
    />

    <ImportPanel
      v-else
      :loading="loading"
      @done="handleDone"
      @cancel="emit('close')"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import Switch from '@/Components/Custom/Switch.vue'
import ExportPanel from './ExportPanel.vue'
import ImportPanel from './ImportPanel.vue'

const emit = defineEmits(['close', 'update'])
const panelMode = ref('export')
const loading = ref(false)

function handleDone() {
  emit('update')
  emit('close')
}
</script>
