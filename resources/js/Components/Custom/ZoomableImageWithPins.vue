<template>
  <div
    ref="containerEl"
    class="relative w-full h-full overflow-hidden rounded-2xl bg-gray-100"
    @click="resetSelection"
  >
    <div
      class="absolute top-0 left-0 will-change-transform transition-transform duration-500 ease-out"
      :style="{
        width: imgNaturalW + 'px',
        height: imgNaturalH + 'px',
        transformOrigin: '0 0',
        transform: `translate3d(${tx}px, ${ty}px, 0) scale(${scale})`,
      }"
    >
      <img
        :src="src"
        :alt="alt"
        class="absolute inset-0 block w-[inherit] h-[inherit] select-none pointer-events-none"
        draggable="false"
        @load="onImgLoad"
      />
      <button
        v-for="m in markers"
        :key="m.id"
        class="absolute -translate-x-1/2 -translate-y-full focus:outline-none"
        :style="{ left: m.x + 'px', top: m.y + 'px' }"
        @click.stop="focusMarker(m)"
        :aria-label="m.name || ('Маркер ' + m.id)"
      >
        <div
          :ref="el => setPinHost(m.id, el)"
          class="origin-bottom will-change-transform transition-transform duration-500 ease-out"
          :style="{ transform: `scale(${pinScale})` }"
        />
      </button>
    </div>
  </div>
</template>

<script setup>
import { isMobile } from '@/Helpers/isMobileHelper'
import { CreateCustomPinIcon } from '@/Helpers/Maps/CreateCustomPinIcon'
import { ref, onMounted, onBeforeUnmount, watch, nextTick, computed } from 'vue'

const props = defineProps({
  src: { type: String, required: true },
  alt: { type: String, default: 'Карта' },
  markers: { type: Array, default: () => [] },
  targetScale: { type: Number, default: 2.5 },
  maxScale: { type: Number, default: 4 },
  pinHeight: { type: Number, default: 56 },
  pinColor: { type: String, default: '#007c57' },
  mobileMarginTopPx: { type: Number, default: -100 },
  selectedMarker: { type: Object, default: null },
  pinScaleDesktop: { type: Number, default: 2.2 },
  pinScaleMobile: { type: Number, default: 3.0 },
  pinScaleMobileSelected: { type: Number, default: 1.4 }
})

const emit = defineEmits(['update:selectedMarker'])

const containerEl = ref(null)
const imgNaturalW = ref(0)
const imgNaturalH = ref(0)
const containerW = ref(0)
const containerH = ref(0)

const baseScale = ref(1)
const scale = ref(1)
const tx = ref(0)
const ty = ref(0)

const pinHosts = new Map()
let ro

const pinScale = computed(() => {
  if (isMobile.value) {
    return props.selectedMarker ? props.pinScaleMobileSelected : props.pinScaleMobile
  }
  return props.pinScaleDesktop
})

function onImgLoad(e) {
  imgNaturalW.value = e.target.naturalWidth
  imgNaturalH.value = e.target.naturalHeight
  measureAndFit()
}

function clampTranslate(txRaw, tyRaw, s) {
  const maxTx = 0
  const maxTy = 0
  const minTx = containerW.value - imgNaturalW.value * s
  const minTy = containerH.value - imgNaturalH.value * s
  return { tx: Math.min(maxTx, Math.max(minTx, txRaw)), ty: Math.min(maxTy, Math.max(minTy, tyRaw)) }
}

function clampScale(s) {
  const min = baseScale.value
  const max = baseScale.value * Math.max(1, props.maxScale)
  return Math.min(max, Math.max(min, s))
}

function fitToContain() {
  if (!imgNaturalW.value || !imgNaturalH.value || !containerW.value || !containerH.value) return
  const s = Math.min(containerW.value / imgNaturalW.value, containerH.value / imgNaturalH.value)
  baseScale.value = s
  scale.value = s
  const c = clampTranslate(
    containerW.value / 2 - (imgNaturalW.value * s) / 2,
    containerH.value / 2 - (imgNaturalH.value * s) / 2,
    s
  )
  tx.value = c.tx
  ty.value = c.ty
}

function focusMarker(m) {
  if (!imgNaturalW.value || !imgNaturalH.value) return
  
  const s = clampScale(baseScale.value * (props.targetScale || 2.5))
  const desiredTx = containerW.value / 2 - m.x * s
  const desiredTy = containerH.value / 2 - m.y * s
  const clamped = clampTranslate(desiredTx, desiredTy, s)
  scale.value = s
  tx.value = clamped.tx
  ty.value = clamped.ty - (isMobile.value ? props.mobileMarginTopPx : 0) 
  emit('update:selectedMarker', m)
}

function resetView() {
  const s = baseScale.value
  scale.value = s
  const c = clampTranslate(
    containerW.value / 2 - (imgNaturalW.value * s) / 2,
    containerH.value / 2 - (imgNaturalH.value * s) / 2,
    s
  )
  tx.value = c.tx
  ty.value = c.ty
}

function resetSelection() {
  resetView()
  emit('update:selectedMarker', null)
}

function measureAndFit() {
  if (!containerEl.value) return
  const rect = containerEl.value.getBoundingClientRect()
  containerW.value = Math.round(rect.width)
  containerH.value = Math.round(rect.height)
  fitToContain()
}

function setPinHost(id, el) {
  if (el) {
    pinHosts.set(id, el)
  } else {
    pinHosts.delete(id)
  }
}

async function renderPins() {
  await nextTick()
  for (const m of props.markers) {
    const host = pinHosts.get(m.id)
    if (!host) continue
    host.replaceChildren()
    const node = await CreateCustomPinIcon({
      glyph: m.icon?.file_path,
      color: props.pinColor,
      height: props.pinHeight,
      label: m.name
    })
    host.appendChild(node)
  }
}

watch(() => props.markers, renderPins, { deep: true })
watch([() => props.pinHeight, () => props.pinColor], renderPins)

watch(() => props.selectedMarker, m => {
  if (m && m.x != null && m.y != null) {
    focusMarker(m)
  } else {
    resetView()
  }
})

onMounted(() => {
  ro = new ResizeObserver(measureAndFit)
  ro.observe(containerEl.value)
  requestAnimationFrame(() => {
    const img = containerEl.value?.querySelector('img')
    if (img && img.complete && img.naturalWidth) {
      imgNaturalW.value = img.naturalWidth
      imgNaturalH.value = img.naturalHeight
      measureAndFit()
    }
  })
  renderPins()
  if (props.selectedMarker) focusMarker(props.selectedMarker)
})

onBeforeUnmount(() => {
  ro?.disconnect()
  pinHosts.clear()
})
</script>
