<script setup>
import { useParkStore } from '@/Stores/useParkStore.js'
import { ref, watch, onMounted, toRef } from 'vue'
import BtnWhite from '@/Components/Custom/BtnWhite.vue'
import MapView from './MapView.vue'
import MobileSlidePanel from '@/Components/Custom/MobileSlidePanel.vue'
import { isMobile } from '@/Helpers/isMobileHelper'
import { setParkView } from '@/Helpers/Maps/SetParkView'
import { useUserLocationMarker } from '@/Helpers/Maps/ShowGeolocationHelper'

const parkStore = useParkStore()

const touchStartY = ref(0)
const panelOffsetY = ref(0)
const panelRef = ref(null)
const isDragging = ref(false)

const { showUserPosition } = useUserLocationMarker(toRef(parkStore, 'map'))

function onTouchStart(event) {
  touchStartY.value = event.touches[0].clientY
  isDragging.value = true
}

function onTouchMove(event) {
  if (!isDragging.value) return
  const currentY = event.touches[0].clientY
  const delta = currentY - touchStartY.value
  panelOffsetY.value = Math.max(0, delta)
}

function onTouchEnd() {
  isDragging.value = false
  if (panelOffsetY.value > 80) {
    parkStore.selectedMarker = null
    parkStore.showPanel = false
  }
  panelOffsetY.value = 0
}
</script>

<template>
  <div class="flex h-[calc(100vh-65px)]">
    <!-- Desktop sidebar -->
    <div class="hidden md:block w-1/3 border-r overflow-y-auto" id="sidebar-target">
          <!-- Panel Teleport -->
    </div>

    <!-- Main map -->
    <div class="w-full md:w-2/3 relative touch-none">
      <MapView />

      <!-- Mobile Slide-up Panel -->
      <MobileSlidePanel
        :show="parkStore.showPanel"
        @close="() => { parkStore.selectedMarker = null; parkStore.showPanel = false }"
      >
        <template #header>
          <div class="flex-shrink-0 w-8 h-8 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center">
            <img
              v-if="parkStore.selectedPark?.icon?.file_path"
              :src="parkStore.selectedPark.icon?.file_path"
              alt="Icon"
              class="w-8 h-8 object-contain"
            />
            <div v-else class="text-gray-400 text-xl">🌳</div>
          </div>
          <div>{{ parkStore.selectedPark?.name || parkStore.selectedMarker?.name || 'Список' }}</div>
        </template>

        <div class="max-h-[60vh] overflow-y-auto" id="mobile-panel-target">
          <!-- Panel Teleport -->
        </div>
      </MobileSlidePanel>

      <!-- Toggle buttons -->
      <div class="fixed bottom-4 right-4 z-50">
        <BtnWhite
          v-if="!parkStore.showPanel"
          class="ml-auto md:hidden"
          @click="parkStore.showPanel = true"
        >
          {{ parkStore.isSingleParkView ? 'ФІЛЬТРИ' : 'МЕНЮ' }}
        </BtnWhite>
        <BtnWhite
          v-if="parkStore.isSingleParkView && (!isMobile || !parkStore.showPanel)"
          class="ml-auto"
          @click="setParkView(parkStore, 'parks')"
        >
          < ДО ПАРКІВ
        </BtnWhite>
      </div>
      
      <div>
        <BtnWhite class="fixed bottom-4 left-4 bg-white border px-3 py-1 rounded shadow" @click="showUserPosition">
          📍 Моя позиція
        </BtnWhite>
      </div>

      <!-- Map panel -->
      <Teleport defer :to="isMobile ? '#mobile-panel-target' : '#sidebar-target'">
          <slot name="panelContent" />
      </Teleport>
    </div>
  </div>
</template>

<!-- Map position centering effect -->
<style>
@keyframes pulse-ring {
  0% {
    transform: scale(0.33);
    opacity: 0.6;
  }
  80% {
    transform: scale(6);
    opacity: 0;
  }
  100% {
    opacity: 0;
  }
}
</style>