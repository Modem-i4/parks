<script setup>
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import SelectWithSearchAndAdd from '../Custom/SelectWithSearchAndAdd.vue'

const props = defineProps({
  items: { type: Array, required: true },
  loading: { type: Boolean, default: false },
  nextUrl: { type: String, default: null },
  filters: { type: Object, required: true }, 
  options: { type: Object, required: true }
})

const emit = defineEmits(['load-more', 'reset-filters', 'show-diff', 'update:filters'])

const actionsUkr = {
  updated: 'оновлено',
  deleted: 'видалено',
  created: 'створено',
}
const actionsList = Object.entries(actionsUkr).map(([id, name]) => ({ id, name }))

function modelTypeName(type) {
  return props.options.models.find(m => m.id === type.replace('App\\Models\\',''))?.name || '—'
}
function updateFilter(key, val) {
  emit('update:filters', { ...props.filters, [key]: val })
}
</script>

<template>
  <div class="overflow-x-auto bg-white rounded">
    <table class="min-w-full text-sm">
      <thead class="bg-gray-100">
        <tr class="text-left text-black">
          <th class="p-4">Дата</th>
          <th class="p-4">Користувач</th>
          <th class="p-4">Модель</th>
          <th class="p-4 w-36">Дія</th>
          <th class="p-4">Зміни</th>
          <th class="p-4 w-1">Дії</th>
        </tr>

        <tr class="text-left bg-white border-b">
          <th class="px-3 py-2 font-medium">
            <div class="flex gap-2 flex-col md:flex-row">
              <input
                :value="props.filters.date_from"
                @input="updateFilter('date_from', $event.target.value)"
                type="date" class="md:w-28 rounded border-gray-300 text-sm py-1.5 px-1" placeholder="Від"
              />
              <input
                :value="props.filters.date_to"
                @input="updateFilter('date_to', $event.target.value)"
                type="date" class="md:w-28 rounded border-gray-300 text-sm py-1.5 px-1" placeholder="До"
              />
            </div>
          </th>
          <th class="px-3 py-2 font-medium">
            <SelectWithSearchAndAdd
              :modelValue="props.filters.user_id"
              :showLabel="false"
              :canAddNew="false"
              :preloadedOptions="props.options.users"
              placeholder="Користувач"
              :pickAllOption="true"
              @update:modelValue="id => updateFilter('user_id', id ? Number(id) : null)"
            />
          </th>

          <th class="px-3 py-2 font-medium">
            <div class="flex gap-2 items-center flex-col md:flex-row">
              <SelectWithSearchAndAdd
                class="flex-1"
                :modelValue="props.filters.model_type"
                :showLabel="false"
                :canAddNew="false"
                :preloadedOptions="props.options.models"
                placeholder="Модель"
                :pickAllOption="true"
                @update:modelValue="type => updateFilter('model_type', type ? `App\\Models\\${type}` : '')"
              />
              <input
                :value="props.filters.model_id"
                @input="updateFilter('model_id', $event.target.value)"
                type="number" min="1" placeholder="ID" class="max-w-14 py-1 rounded border-gray-300"
              />
            </div>
          </th>

          <th class="px-3 py-2 font-medium">
              <SelectWithSearchAndAdd
                class="flex-1"
                :modelValue="props.filters.action"
                :showLabel="false"
                :canAddNew="false"
                :preloadedOptions="actionsList"
                placeholder="Дія"
                :pickAllOption="true"
                @update:modelValue="action => updateFilter('action', action)"
              />
          </th>

          <th class="px-3 py-2 font-medium" colspan="2">
            <SecondaryButton
              @click="$emit('reset-filters')"
              class="px-3 py-1.5 rounded-md border text-xs bg-white hover:bg-gray-50"
            >Скинути фільтри</SecondaryButton>
          </th>
        </tr>
      </thead>

      <tbody>
        <tr v-for="log in props.items" :key="log.id" class="border-t">
          <td class="px-3 py-2 whitespace-nowrap">{{ new Date(log.created_at).toLocaleString() }}</td>
          <td class="px-3 py-2">{{ log.user?.name ?? 'Система' }}</td>
          <td class="px-3 py-2">
            <div class="text-gray-800">{{ modelTypeName(log.model_type) || '—' }}</div>
            <div class="text-gray-500">ID: {{ log.model_id ?? '—' }}</div>
          </td>
          <td class="px-3 py-2">
            <span
              class="px-3 py-1.5 rounded-full text-xs"
              :class="{
                'bg-green-100 text-green-800': log.action === 'created',
                'bg-yellow-100 text-yellow-800': log.action === 'updated',
                'bg-red-100 text-red-800': log.action === 'deleted',
                'bg-blue-100 text-blue-800': log.action === 'restored',
                'bg-gray-100 text-gray-800': log.action === 'rollback'
              }"
            >
              {{ actionsUkr[log.action] }}
            </span>
          </td>
          <td class="px-3 py-2">
            <template v-if="log.action === 'updated'">
              <div class="text-gray-700">
                {{ Object.keys(log.after ?? {}).length }} полів
              </div>
            </template>
            <template v-else>—</template>
          </td>
          <td class="px-3 py-2">
            <SecondaryButton @click="$emit('show-diff', log)">
              Деталі
            </SecondaryButton>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="p-3 flex items-center gap-3">
      <PrimaryButton
        v-if="props.nextUrl"
        @click="$emit('load-more')"
        class="mx-auto"
      >Показати ще</PrimaryButton>
      <span v-else class="text-sm text-gray-500">Кінець списку</span>
      <span v-if="props.loading" class="text-sm text-gray-500 ml-auto">Завантаження…</span>
    </div>
  </div>
</template>
<style scoped>
input[type=number]::-webkit-outer-spin-button,
input[type=number]::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
</style>