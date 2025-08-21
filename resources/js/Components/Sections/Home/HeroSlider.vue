<template>
  <section class="relative w-full overflow-hidden">
    <div
      class="relative h-[60vh] md:h-[80vh] select-none"
      @pointerdown="onDragStart"
      @pointermove="onDragMove"
      @pointerup="onDragEnd"
      @pointerleave="onDragEnd"
    >
      <div
        class="absolute inset-0 will-change-transform transition-transform duration-0"
        :style="{ transform: `translate3d(${trackX}%,0,0)` }"
      >
        <HeroSlide
          v-for="(s, i) in slidesToUse"
          :key="i"
          :slide="s"
          :index="i"
          :current="current"
          :img-x="imgX(i)"
          :content-x="contentX(i)"
        />
      </div>

      <button
        class="absolute left-4 bottom-24 z-10 flex items-center justify-center w-14 h-14 rounded-full hover:bg-white/20 transition
               md:top-1/2 md:-translate-y-1/2 md:bottom-auto"
        @click="prev"
      >
        <img :src="chevronLeft" alt="" />
      </button>

      <button
        class="absolute right-4 bottom-24 z-10 flex items-center justify-center w-14 h-14 rounded-full hover:bg-white/20 transition
               md:top-1/2 md:-translate-y-1/2 md:bottom-auto"
        @click="next"
      >
        <img :src="chevronLeft" alt="" class="rotate-[180deg]" />
      </button>

      <div class="absolute bottom-8 md:bottom-16 left-0 right-0 flex items-center justify-center gap-5 z-10">
        <button
          v-for="(s,i) in slidesToUse"
          :key="`dot-${i}`"
          class="w-[17px] h-[17px] rounded-full border-2 border-white transition"
          :class="i === displayIndex ? 'bg-white' : 'bg-transparent'"
          @click="goTo(i, true)"
        />
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import HeroSlide from './HeroSlide.vue'
import chevronLeft from '@/assets/parks/chevron-left.svg'

const props = defineProps({
  slides: { type: Array, default: () => [] },
  autoplay: { type: Boolean, default: true },
  delay: { type: Number, default: 5000 },
  speed: { type: Number, default: 700 }
})

const slidesToUse = computed(() => props.slides.length ? props.slides : [])

const current = ref(0)
const animId = ref(null)
const autoId = ref(null)
const from = ref(0)
const to = ref(0)
const startTs = ref(0)
const preferTo = ref(null)
const isAnimating = ref(false)

const displayIndex = computed(() => preferTo.value ?? Math.round(current.value))

function easeOutCubic(t) { return 1 - Math.pow(1 - t, 3) }

function animate(ts) {
  if (!startTs.value) {
    startTs.value = ts
    isAnimating.value = true
  }
  const t = Math.min(1, (ts - startTs.value) / props.speed)
  const v = from.value + (to.value - from.value) * easeOutCubic(t)
  current.value = v
  if (t < 1) {
    animId.value = requestAnimationFrame(animate)
  } else {
    startTs.value = 0
    from.value = to.value
    preferTo.value = null
    isAnimating.value = false
  }
}

function goTo(i, user = false) {
  cancelAnimationFrame(animId.value)
  if (isAnimating.value) {
    from.value = Math.round(current.value)
    current.value = from.value
  } else {
    from.value = current.value
  }
  to.value = i
  startTs.value = 0
  preferTo.value = user ? i : null
  animId.value = requestAnimationFrame(animate)
  restartAutoplay()
}

function next() {
  const n = slidesToUse.value.length
  goTo((Math.round(current.value) + 1) % n, false)
}

function prev() {
  const n = slidesToUse.value.length
  goTo((Math.round(current.value) - 1 + n) % n, false)
}

function restartAutoplay() {
  if (!props.autoplay) return
  clearInterval(autoId.value)
  autoId.value = setInterval(next, props.delay)
}

onMounted(() => {
  if (props.autoplay) restartAutoplay()
})

onBeforeUnmount(() => {
  cancelAnimationFrame(animId.value)
  clearInterval(autoId.value)
})

const trackX = computed(() => -(current.value * 100))

function offsetOf(i) { return i - current.value }
function imgX(i) { return offsetOf(i) * -30 }
function contentX(i) { return offsetOf(i) * 50 }

const dragging = ref(false)
const startX = ref(0)
const dragDelta = ref(0)

function onDragStart(e) {
  dragging.value = true
  startX.value = e.clientX
  dragDelta.value = 0
  cancelAnimationFrame(animId.value)
  isAnimating.value = false
  from.value = current.value
  startTs.value = 0
  clearInterval(autoId.value)
}

function onDragMove(e) {
  if (!dragging.value) return
  dragDelta.value = (e.clientX - startX.value) / window.innerWidth
  let tentative = from.value - dragDelta.value
  const lastIndex = slidesToUse.value.length - 1
  if (tentative < 0) tentative = 0
  if (tentative > lastIndex) tentative = lastIndex
  current.value = tentative
}

function onDragEnd() {
  if (!dragging.value) return
  dragging.value = false
  const threshold = 0.2
  const n = slidesToUse.value.length
  let target = Math.round(current.value)
  if (dragDelta.value > threshold) {
    target = Math.max(Math.round(from.value) - 1, 0)
  } else if (dragDelta.value < -threshold) {
    target = Math.min(Math.round(from.value) + 1, n - 1)
  }
  goTo(target, true)
}
</script>
