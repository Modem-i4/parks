<template>
  <div class="p-4 space-y-5">
    <h2 class="text-lg font-semibold text-gray-800">
      📊 {{ panelTitle }}
    </h2>

    <div class="flex justify-center" v-if="panelOptions.length > 1">
      <Switch
        v-model="panelMode"
        :options="panelOptions"
      />
    </div>

    <ReportingPanel
      v-if="panelMode === 'reporting' && authStore.can.reporting"
      :loading="loading"
      @done="handleDone"
      @cancel="emit('close')"
    />

    <ExportPanel
      v-if="panelMode === 'export' && authStore.can.export"
      :loading="loading"
      @done="handleDone"
      @cancel="emit('close')"
    />

    <ImportPanel
      v-else-if="panelMode === 'import' && authStore.can.import"
      :loading="loading"
      @done="handleDone"
      @cancel="emit('close')"
    />
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import Switch from '@/Components/Custom/Switch.vue'
import ReportingPanel from './ReportingPanel.vue'
import ExportPanel from './ExportPanel.vue'
import ImportPanel from './ImportPanel.vue'
import { useAuthStore } from '@/Stores/useAuthStore'

const emit = defineEmits(['close', 'update'])
const loading = ref(false)
const authStore = useAuthStore()
const panelMode = ref(authStore.can.reporting ? 'reporting' : authStore.can.export ? 'export' : 'import')

const panelOptions = computed(() => [
  authStore.can.reporting ? { value: 'reporting', label: '📝 Звіти', color: 'blue' } : null,
  authStore.can.export ? { value: 'export', label: '⏬ Експорт', color: 'blue' } : null,
  authStore.can.import ? { value: 'import', label: '⏫ Імпорт', color: 'green' } : null,
].filter(Boolean))

const panelTitle = computed(() => panelOptions.value
  .map(option => option.label.replace(/^[^\s]+\s/, ''))
  .join(', '))

function handleDone() {
  emit('update')
  emit('close')
}
</script>
