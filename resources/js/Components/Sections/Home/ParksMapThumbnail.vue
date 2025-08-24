<script setup>
import BtnWhite from '@/Components/Custom/BtnWhite.vue'
import MobileSlidePanel from '@/Components/Custom/MobileSlidePanel.vue'
import PanelHeader from '@/Components/Custom/PanelHeader.vue'
import ZoomableImageWithPins from '@/Components/Custom/ZoomableImageWithPins.vue'
import { isMobile } from '@/Helpers/isMobileHelper'
import { coordsToPxFromBounds } from '@/Helpers/Maps/CoordsToPxConverter'
import { ref, computed, onMounted, onBeforeUnmount, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  parks: Array
})

const selectedPark = ref(null)

const imgMapBounds = { minLng: 24.680588, minLat: 48.899596, maxLng: 24.744766, maxLat: 48.940969 }
const markers = computed(() =>
  props.parks.map(m => {
    const { x, y } = coordsToPxFromBounds({ lng: m.coordinates[0], lat: m.coordinates[1] }, 1123, 1123, imgMapBounds)
    return { ...m, x, y }
  })
)

// Center offset
const panelRef = ref(null)
const panelHeight = ref(0)
let ro

function observePanel() {
  const el = panelRef.value?.$el ?? panelRef.value
  if (!el) return
  ro = new ResizeObserver(entries => {
    panelHeight.value = Math.round(entries[0].contentRect.height)
  })
  ro.observe(el)
}

onMounted(async () => {
  await nextTick()
  observePanel()
})

onBeforeUnmount(() => {
  ro?.disconnect()
})
</script>

<template>
  <div class="relative overflow-hidden w-[min(620px,100vw)] h-[min(620px,100vw)] md:w-[min(620px,50vw)]">
    <ZoomableImageWithPins
      v-model:selectedMarker="selectedPark"
      src="/img/parks/parks-map.jpg"
      alt="Карта парків"
      :pinSize="26"
      :targetScale="2.2"
      :maxScale="4"
      :markers="markers"
      :pinHeight="isMobile && !selectedPark ? 120 : 56"
      :mobileMarginTopPx="panelHeight"
    />
    <MobileSlidePanel
      :show="!!selectedPark" 
      @close="selectedPark = null"
      class="absolute bottom-0 left-0 w-full bg-white"
      ref="panelRef"
    >
      <template #header>
        <PanelHeader
          :title="selectedPark?.name"
          :subtitle="selectedPark?.description"
          :icon="selectedPark?.icon?.file_path"
          :shouldFilter="true"
          variant="sm"
          iconBg="gray-100"
        />
        <div class="absolute top-[-60px] left-0 w-full">
            <button class="block mx-auto px-6 py-2 rounded-full bg-[#007c57] text-white 
              transition-shadow duration-300 hover:shadow-[0_0_10px] hover:shadow-[#007c57]" 
                @click="router.visit(`/parks/${selectedPark ? selectedPark.id : ''}`)"
            >
                {{ selectedPark ? 'Перейти' : 'Всі парки' }}
            </button>
        </div>
      </template>
    </MobileSlidePanel>
  </div>
</template>
