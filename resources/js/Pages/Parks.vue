<script setup>
import MapView from '@/Components/Map/MapView.vue'
import ParkDetails from '@/Components/Map/ParkDetails.vue'
import ParkList from '@/Components/Map/ParkList.vue'
import ResolveLayout from '@/Helpers/ResolveLayout.js'
import { ref } from 'vue'

defineOptions({ layout: ResolveLayout })
defineProps({ parks: Array })

const selectedPark = ref(null)
const showPanel = ref(false)

const touchStartY = ref(0)
const panelOffsetY = ref(0)
const isDragging = ref(false)

function selectPark(park) {
  selectedPark.value = park
  showPanel.value = true
}

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
    showPanel.value = false
    selectedPark.value = null
  }
  panelOffsetY.value = 0
}
</script>

<template>
  <div class="flex h-[calc(100vh-65px)]">
    <!-- Desktop sidebar -->
    <div class="hidden md:block w-1/3 border-r overflow-y-auto">
      <ParkList
        v-if="!selectedPark"
        :parks="parks"
        @select="selectPark"
      />
      <ParkDetails
        v-else
        :park="selectedPark"
        :selectedParkId="selectedPark?.id"
        @back="selectedPark = null"
      />
    </div>

    <!-- Main map -->
    <div class="w-full md:w-2/3 relative touch-none">
      <MapView
        :parks="parks"
        :selectedParkId="selectedPark?.id"
        :showPanel="showPanel"
        @select="selectPark"
      />

      <!-- Mobile Slide-up Panel -->
      <div
        class="md:hidden fixed bottom-0 left-0 right-0 bg-white shadow-lg rounded-t-xl z-50"
        :class="!isDragging ? 'transition-transform duration-300' : ''"
        :style="{
          transform: showPanel
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
            {{ selectedPark?.name || 'Список парків' }}
          </span>
          <button
            @click="() => { showPanel = false; selectedPark = null }"
            class="text-gray-600 text-4xl"
          >×</button>
        </div>

        <div class="max-h-[60vh] overflow-y-auto">
          <ParkDetails
            v-if="selectedPark"
            :park="selectedPark"
            :selectedParkId="selectedPark?.id"
            @back="selectedPark = null"
          />
          <ParkList
            v-else
            :parks="parks"
            @select="selectPark"
          />
        </div>
      </div>

      <!-- Toggle button -->
      <button
        v-if="!showPanel"
        class="md:hidden fixed bottom-4 right-4 z-50 bg-white/80 backdrop-blur p-5 rounded-full shadow-lg hover:bg-white transition"
        @click="showPanel = true"
      >
        МЕНЮ
      </button>
    </div>
  </div>
</template>
