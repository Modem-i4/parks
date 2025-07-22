<script setup>
import { useParkStore } from '@/Stores/useParkStore.js'
import { computed, ref, watch } from 'vue'
import axios from 'axios'
import BtnWhite from '@/Components/Custom/BtnWhite.vue'
import { usePage } from '@inertiajs/vue3';
import { UserRole } from '@/Helpers/UserRole.js';
import MarkerDetailsView from './View/MarkerDetailsView.vue'
import MarkerDetailsEdit from './Edit/MarkerDetailsEdit.vue';
import PanelHeader from '../Custom/PanelHeader.vue';
import QualityStateIndicator from './View/QualityStateIndicator.vue';

const page = usePage()
const role = page.props.auth.user?.role

const parkStore = useParkStore()
const marker = ref(null)
const loading = ref(true)
const editing = ref(false)
const canEdit = UserRole.atLeast(role, page.role)
const editRef = ref(null)

function back() {
  parkStore.selectedMarker = null
}
// watch(
//   () => editing
// )

watch(
  () => parkStore.selectedMarker,
  async (newVal) => {
    marker.value = newVal
    if (!newVal || newVal.isDraft) return
    loading.value = true
    try {
      const { data } = await axios.get(`/api/markers/${marker.value.id}`)
      marker.value = data
    } catch (e) {
      console.error('Не вдалося довантажити маркер:', e)
    } finally {
      loading.value = false
    }
  },
  { immediate: true, deep: true }
)

const title = computed(
  () => marker.value.green?.species?.name_ukr || marker.value.infrastructure?.infrastructure_type?.name || marker.value.type
)

const description = computed(() => {
  if(marker.value.green)
    return `${marker.value.green?.species?.genus?.name_ukr} / ${marker.value.green?.species?.genus?.family?.name_ukr}`
  if(marker.value.infrastructure) 
    return 'Інфраструктура'
  return ''
})
</script>

<template>
  <div class="p-4 overflow-x-hidden" v-if="marker">
    <button @click="back" class="text-blue-500 mb-2">← Назад</button>

    <PanelHeader
      :title="title"
      :subtitle="description" 
      :icon="marker.icon?.file_path">
      <template #right>
        <QualityStateIndicator :green="marker.green" />
      </template>
    </PanelHeader>

    <template v-if="!editing">
      <MarkerDetailsView :marker="marker" />
      <div class="absolute right-[4.5rem]">
        <BtnWhite v-if="canEdit" class="fixed bottom-2"
          @click="editing = true"
        >✏️</BtnWhite>
      </div>
    </template>
    <template v-if="editing">
      <MarkerDetailsEdit :marker="marker" ref="editRef" />
      <div class="absolute right-[4.5rem]">
        <div class="fixed bottom-2">
          <BtnWhite
            @click="editing = false"
            class="p-[0.7rem] ms-auto"
          >❌</BtnWhite>
          <BtnWhite
            @click="() => { editRef.save(); editing = false }"
          >✔️</BtnWhite>
        </div>
      </div>
    </template>
  </div>
</template>
