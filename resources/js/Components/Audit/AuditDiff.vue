<script setup>
import { pretty } from '@/Helpers/Admin/PrettifyJson'
import { computed } from 'vue'

const props = defineProps({
  log: { type: Object, default: null }
})

const fields = computed(() => {
  if (!props.log) return []
  const before = props.log.before || {}
  const after  = props.log.after  || {}
  const keys = new Set([...Object.keys(before), ...Object.keys(after)])
  return Array.from(keys)
    .filter(k => k !== 'updated_at' && k !== 'created_at' && k !== 'deleted_at')
    .sort()
})

function shortType(t) {
  return t ? t.replace(/^App\\Models\\/, '') : '—'
}
</script>

<template>
  <div class="w-full sm:max-w-3xl">
    <div class="px-4 py-3 border-b flex items-center justify-between bg-gray-100">
      <div class="font-semibold">
        Зміни: {{ shortType(log?.model_type) }} <span v-if="log">#{{ log.model_id }}</span>
        <span v-if="log" class="ml-2 text-xs text-gray-500">
          {{ new Date(log.created_at).toLocaleString() }}
        </span>
      </div>
    </div>

    <div class="p-4">
      <div v-if="!log" class="text-sm text-gray-500">Запис не вибрано.</div>

      <div v-else>
        <div v-if="!fields.length" class="text-sm text-gray-500">
          Немає змінених полів.
        </div>

        <table v-else class="w-full text-sm">
          <thead>
            <tr class="text-gray-500">
              <th class="text-left font-medium p-1">Поле</th>
              <th class="text-left font-medium p-1">Було</th>
              <th class="text-left font-medium p-1">Стало</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="k in fields" :key="k" class="border-t align-top">
              <td class="p-1 font-medium text-gray-800">{{ k }}</td>
              <td class="p-1 text-gray-600">
                <pre class="whitespace-pre-wrap break-all font-mono text-[13px] leading-snug">{{ pretty((log.before || {})[k]) }}</pre>
              </td>
              <td class="p-1 text-gray-900">
                <pre class="whitespace-pre-wrap break-all font-mono text-[13px] leading-snug">{{ pretty((log.after  || {})[k]) }}</pre>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
