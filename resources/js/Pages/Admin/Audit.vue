<script setup>
import { ref, onMounted, watch } from 'vue'
import axios from 'axios'
import AuditTable from '@/Components/Audit/AuditTable.vue'
import AuditDiff from '@/Components/Audit/AuditDiff.vue'
import { Head } from '@inertiajs/vue3'
import Modal from '@/Components/Default/Modal.vue'

const props = defineProps({
  options: { type: Object, required: true }
})

const filters = ref({
  user_id: null,
  model_type: '',
  model_id: '',
  action: '',
  date_from: '',
  date_to: ''
})

const loading = ref(false)
const items = ref([])
const nextUrl = ref(null)
const diffLog = ref(null)

async function load(url = null) {
  loading.value = true
  try {
    const { data } = await axios.get(url ?? '/api/audit', { params: filters.value })
    items.value = data.data
    nextUrl.value = data.next_page_url
  } finally {
    loading.value = false
  }
}

async function loadMore() {
  if (!nextUrl.value) return
  const { data } = await axios.get(nextUrl.value)
  items.value.push(...data.data)
  nextUrl.value = data.next_page_url
}

function resetFilters() {
  filters.value = { user_id: null, model_type: '', model_id: '', action: '', date_from: '', date_to: '' }
  load()
}

let t = null
watch(filters, () => {
  clearTimeout(t)
  t = setTimeout(() => load(), 250) 
}, { deep: true })

onMounted(load)
</script>

<template>
  <Head title="Журнал змін" />
  <div class="max-w-7xl mx-auto my-6 bg-white rounded-xl shadow p-6">
    <div class="space-y-4 pb-3 relative">
      <div class="flex w-full justify-between">
        <h2 class="text-xl font-semibold leading-tight text-gray-800"
        >Журнал змін</h2>
      </div>
    </div>
    <AuditTable
      v-model:filters="filters"
      class="mt-2 min-h-64"
      :items="items"
      :loading="loading"
      :next-url="nextUrl"
      :options="props.options"
      @load-more="loadMore"
      @reset-filters="resetFilters"
      @show-diff="log => diffLog = log"
    />
    <Modal :show="!!diffLog" @close="diffLog = null">
      <AuditDiff :log="diffLog"  />
    </Modal>
  </div>
</template>
