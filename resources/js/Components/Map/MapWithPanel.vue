<script setup>
import { useParkStore } from '@/Stores/useParkStore.js'
import { ref, watch, onMounted } from 'vue'
import BtnWhite from '@/Components/Custom/BtnWhite.vue'
import MapView from './MapView.vue'
import { isMobile } from '@/Helpers/isMobileHelper'

const parkStore = useParkStore()

const touchStartY = ref(0)
const panelOffsetY = ref(0)
const panelRef = ref(null)
const isDragging = ref(false)

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

function back() {
  parkStore.selectedMarker = null
  parkStore.showPanel = false
  parkStore.lockMapChange = true
  parkStore.isSingleParkView = false
  window.history.pushState(null, '', `/parks`)
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
      <div
        class="md:hidden fixed bottom-0 left-0 right-0 bg-white shadow-lg rounded-t-xl z-50"
        :class="!isDragging ? 'transition-transform duration-300 ease-in-out' : ''"
        ref="panelRef"
        :style="{
          transform: parkStore.showPanel
            ? `translateY(${isDragging ? panelOffsetY + 'px' : '0'})`
            : 'translateY(100%)'
        }"
      >
        <div
          class="p-2 border-b flex justify-between items-center active:cursor-grabbing"
          style="touch-action: none"
          @touchstart="onTouchStart"
          @touchmove="onTouchMove"
          @touchend="onTouchEnd"
        >
          <span class="font-semibold">
            {{ parkStore.selectedMarker?.name || 'Список' }}
          </span>
          <button
            @click="() => { parkStore.selectedMarker = null; parkStore.showPanel = false }"
            class="text-gray-600 text-4xl"
          >×</button>
        </div>

        <div class="max-h-[60vh] overflow-y-auto" id="mobile-panel-target">
          <!-- Panel Teleport -->
        </div>
      </div>

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
          v-if="parkStore.isSingleParkView && !parkStore.showPanel"
          class="ml-auto"
          @click="back"
        >
          < ДО ПАРКІВ
        </BtnWhite>
      </div>
      
      <!-- Map panel -->
      <Teleport defer :to="isMobile ? '#mobile-panel-target' : '#sidebar-target'">
          <slot name="panelContent" />
      </Teleport>
    </div>
  </div>
</template>
