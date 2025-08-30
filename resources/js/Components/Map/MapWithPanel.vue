<script setup>
import { useParkStore } from '@/Stores/useParkStore.js'
import { ref, watch, toRef } from 'vue'
import BtnWhite from '@/Components/Custom/BtnWhite.vue'
import MapView from './MapView.vue'
import MobileSlidePanel from '@/Components/Custom/MobileSlidePanel.vue'
import { isMobile } from '@/Helpers/isMobileHelper'
import { setParkView } from '@/Helpers/Maps/SetParkView'
import { useUserLocationMarker } from '@/Helpers/Maps/ShowGeolocationHelper'
import MapUpperMessage from '@/Components/Map/MapUpperMessage.vue';
import { useAddMarkerHelper } from '@/Helpers/Admin/AddMarkerHelper'
import FindMarker from '@/Components/Custom/FindMarker.vue'
import { useAuthStore } from '@/Stores/useAuthStore'
import MapLegendInfo from './MapLegendInfo.vue'
import ParentFitModal from '../Custom/ParentFitModal.vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'
import MapLegendPane from './MapLegendPane.vue'

const parkStore = useParkStore()
const { showUserPosition } = useUserLocationMarker(toRef(parkStore, 'map'))
const { addMarker, addMarkerFinished } = useAddMarkerHelper(parkStore)
const showModal = ref({ findMarker: false, legend: false })

const authStore = useAuthStore()
const addingMarker = ref(false)
watch(addingMarker, (newVal) => {
  parkStore.selectedMarkerLocked = newVal
  parkStore.markerStates.saveFailed = false
  if (!newVal) addMarkerFinished()
})
watch(() => parkStore.selectedMarker, (newVal) => {
  if(newVal === null) {
    if(addingMarker.value) {
      addingMarker.value = false
    } else {
      parkStore.selectedMarkerLocked = false // reset lock when marker is deselected
    }
  }
})
</script>

<template>
  <div class="flex h-[calc(100dvh-86px)]">
    <!-- Desktop sidebar -->
    <div class="hidden md:block w-1/3 min-w-[300px] border-r overflow-y-auto overflow-x-clip relative bg-gray-100" id="sidebar-target"> <!-- Має обмежуватись висотою екрана, а не flex-батьком -->
      <!-- Panel Teleport -->
    </div>

    <!-- Main map -->
    <div class="w-full md:w-2/3 relative touch-none focus:ring-0 focus:outline-none h-full flex-1">
      <MapUpperMessage />
      <MapView />
      <MapLegendPane 
          class="absolute left-1/2 -translate-x-1/2 bg-white/30 backdrop-blur-md px-5"
          :class="isMobile 
          ? 'bottom-6 rounded-3xl py-2'
          : 'bottom-0 rounded-t-3xl pt-3 pb-2'"
        />

      <!-- Mobile Slide-up Panel -->
      <MobileSlidePanel
        :show="parkStore.showPanel"
        @close="() => { parkStore.selectedMarker = null; parkStore.showPanel = false }"
        class="md:hidden fixed bottom-0 left-0 right-0"
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
            <template v-if="!parkStore.isSingleParkView && parkStore.selectedMarker">
              <button class="text-blue-500" 
                @click="parkStore.selectedMarker = null"
              >←  Всі парки</button>
            </template>
            <div v-else>{{ parkStore.selectedPark?.name || parkStore.selectedMarker?.name || 'Список' }}</div>
        </template>

        <div class="max-h-[55dvh] overflow-y-auto" id="mobile-panel-target">
          <!-- Panel Teleport -->
        </div>
      </MobileSlidePanel>

      <!-- Toggle buttons -->
      <div class="fixed bottom-[4.2rem] md:bottom-4 right-4 z-50">
        <BtnWhite
          v-if="!parkStore.isSingleParkView && !isMobile && parkStore.selectedMarker"
          class="ml-auto"
          @click="setParkView(parkStore, 'single')"
        >
          ДО ПАРКУ
        </BtnWhite>
        <BtnWhite
          v-if="!parkStore.showPanel"
          class="ml-auto md:hidden"
          @click="parkStore.showPanel = true"
        >
          МЕНЮ
        </BtnWhite>
        <BtnWhite
          v-if="parkStore.isSingleParkView && (!isMobile || !parkStore.showPanel)"
          class="ml-auto"
          @click="setParkView(parkStore, 'parks')"
        >
          < ДО ПАРКІВ
        </BtnWhite>
      </div>
      
      <div class="absolute bottom-[4.2rem] md:bottom-4 left-4">
        <BtnWhite class="bg-white border px-3 py-1 rounded shadow" 
          @click="showModal.legend = true"
        >
          🌳 Легенда
        </BtnWhite>
        <template v-if="parkStore.isSingleParkView">
          <BtnWhite class="bg-white border px-3 py-1 rounded shadow" 
            v-if="!parkStore.selectedMarkerLocked && authStore.can.addMarkers"
            @click="() => { addMarker(); addingMarker = true }"
          >
            ➕ Додати маркер
          </BtnWhite>
          <BtnWhite class="bg-white border px-3 py-1 rounded shadow" 
            v-if="addingMarker"
            @click="() => { addingMarker = false; }"
          >
            ❌ Скасувати
          </BtnWhite>
        </template>
        <BtnWhite class="bg-white border px-3 py-1 rounded shadow" 
          v-if="!parkStore.selectedMarkerLocked"
          @click="showModal.findMarker = true"
        >
          🔍 Знайти маркер
        </BtnWhite>
        <BtnWhite class=" bg-white border px-3 py-1 rounded shadow" @click="showUserPosition">
          📍 Моя позиція
        </BtnWhite>
      </div>
      <ParentFitModal 
        :show="showModal.legend" @close="showModal.legend = false"
        contentClasses="max-w-[40rem]"
      >
        <template #header>
          <h2 class="text-lg font-semibold text-center text-gray-700 mt-2">🗺️ Умовні позначення мапи</h2>
        </template>
        <MapLegendInfo  />
        <template #footer>
          <SecondaryButton @click="showModal.legend = false">Закрити</SecondaryButton>
        </template>
      </ParentFitModal>
      <ParentFitModal :show="showModal.findMarker" 
        contentClasses="bg-white/70 py-1.5"
        @close="showModal.findMarker = false"
      >
        <FindMarker @close="showModal.findMarker = false"/>
      </ParentFitModal>

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