<template>
  <div class="p-4 space-y-5">
    <h2 class="text-lg font-semibold text-gray-800">
      ⏬ Експорт
      <template v-if="authStore.can.import"> / Імпорт</template>
    </h2>

    <div class="flex justify-center" v-if="authStore.can.import">
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
      v-else-if="authStore.can.import"
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
import { useAuthStore } from '@/Stores/useAuthStore'

const emit = defineEmits(['close', 'update'])
const panelMode = ref('export')
const loading = ref(false)
const authStore = useAuthStore()

function handleDone() {
  emit('update')
  emit('close')
}
</script>
