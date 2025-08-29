<script setup>
import { computed, ref } from 'vue'

const props = defineProps({
  genus: { type: Array, default: [] },
  colors: {
    type: Array,
    default: ['#22c55e','#f59e0b','#ef4444','#06b6d4','#a855f7','#3b82f6']
  },
  size: { type: Number, default: 345 },
  thickness: { type: Number, default: 60 },
  title: { type: String, default: 'Роди дерев у місті' },
  centralIcon: { type: String, default: '/img/icons/tree-stats.svg' }
})

const radius = computed(() => (props.size - props.thickness) / 2)
const innerR = computed(() => radius.value - props.thickness / 2)
const outerR = computed(() => radius.value + props.thickness / 2)
const cx = computed(() => props.size / 2)
const cy = computed(() => props.size / 2)
const C = computed(() => 2 * Math.PI * radius.value)
const gapPx = computed(() => 2)

const donutInnerDiameter = computed(() => Math.max(props.size - 2 * props.thickness, 0))
const treeIconSize = computed(() => Math.max(Math.floor(donutInnerDiameter.value * 0.85), 0))

const total = computed(() => props.genus.reduce((s, x) => s + (x.count || 0), 0))

const slices = computed(() => {
  const main = props.genus.filter(g => !(g.name === 'інші' || g.id === 0)).slice(0, 6)
  const others = props.genus.find(g => g.name === 'інші' || g.id === 0)
  const data = others && (others.count || 0) > 0 ? [...main, others] : main
  let acc = 0
  return data.map((it, idx) => {
    const value = it.count || 0
    const pct = total.value ? value / total.value : 0
    const arcLen = pct * C.value
    const drawLen = Math.max(arcLen - gapPx.value, 0)
    const offset = (C.value - acc) % C.value

    const startT = acc / C.value
    const endT = (acc + arcLen) / C.value

    const startAngle = startT * 2 * Math.PI
    const endAngle = endT * 2 * Math.PI
    const midAngle = (startAngle + endAngle) / 2 - Math.PI / 2

    const centerX = cx.value + Math.cos(midAngle) * (radius.value + props.thickness / 2)
    const centerY = cy.value + Math.sin(midAngle) * (radius.value + props.thickness / 2)

    acc += arcLen
    const isOthers = it.name === 'інші' || it.id === 0
    return {
      id: it.id,
      name: it.name,
      count: value,
      pct,
      color: isOthers ? '#9CA3AF' : props.colors[idx % props.colors.length],
      dasharray: `${drawLen} ${C.value - drawLen}`,
      dashoffset: offset,
      centerX,
      centerY,
      startT,
      endT
    }
  })
})

const hovered = ref(null)
const tooltip = ref({ show: false, x: 0, y: 0, text: '' })
const svgWrap = ref(null)

function fmtPct(p) {
  return `${Math.round(p * 1000) / 10}%`
}

function showTooltipAtSliceCenter(s) {
  const r = svgWrap.value?.getBoundingClientRect()
  if (!r) return
  tooltip.value = {
    show: true,
    x: r.left + s.centerX,
    y: r.top + s.centerY - 16,
    text: `${s.name}: ${s.count} (${fmtPct(s.pct)})`
  }
}

function hideTooltip() {
  tooltip.value.show = false
}

function onSvgMove(e) {
  const r = svgWrap.value?.getBoundingClientRect()
  if (!r) return
  const x = e.clientX - r.left
  const y = e.clientY - r.top
  const dx = x - cx.value
  const dy = y - cy.value
  const dist = Math.hypot(dx, dy)

  if (dist < innerR.value - 4 || dist > outerR.value + 4) {
    hovered.value = null
    hideTooltip()
    return
  }

  let angleVis = Math.atan2(dy, dx)
  if (angleVis < 0) angleVis += 2 * Math.PI

  let angleUnrot = angleVis + Math.PI / 2
  if (angleUnrot >= 2 * Math.PI) angleUnrot -= 2 * Math.PI

  const t = angleUnrot / (2 * Math.PI)

  let s = null
  for (const item of slices.value) {
    if (item.startT <= t && t < item.endT) { s = item; break }
    if (item.endT > 1 && (t < (item.endT % 1))) { s = item; break }
  }
  if (!s) {
    hovered.value = null
    hideTooltip()
    return
  }

  hovered.value = s.id
  tooltip.value = {
    show: true,
    x: e.clientX + 12,
    y: e.clientY - 40,
    text: `${s.name}: ${s.count} (${fmtPct(s.pct)})`
  }
}

function onSvgLeave() {
  hovered.value = null
  hideTooltip()
}
</script>

<template>
  <section class="max-w-7xl mx-auto px-4 py-10 md:py-14 flex flex-col md:flex-row gap-x-10 gap-y-5 md:justify-center text-center md:text-start">
    <div class="space-y-2">
      <h3 class="text-2xl md:text-3xl font-extrabold uppercase tracking-wide text-[#007C57] mb-10">{{ props.title }}</h3>
      <ul class="space-y-2 max-w-72 mx-auto md:max-w-full">
        <li
          v-for="s in slices"
          :key="s.id + '-' + s.name"
          class="flex items-center gap-3 cursor-pointer"
          @mouseenter="hovered = s.id; showTooltipAtSliceCenter(s)"
          @mouseleave="hovered = null; hideTooltip()"
        >
          <span
            class="inline-flex items-center rounded-md px-2 py-1 text-sm text-white"
            :style="{ backgroundColor: s.color }"
          >
            {{ s.count }} ({{ fmtPct(s.pct) }})
          </span>
          <span class="text-gray-700 font-semibold">{{ s.name }}</span>
        </li>
      </ul>
    </div>

    <div class="relative flex items-center justify-center">
      <svg
        ref="svgWrap"
        :width="size"
        :height="size"
        :viewBox="`0 0 ${size} ${size}`"
        class="select-none"
        @mousemove="onSvgMove"
        @mouseleave="onSvgLeave"
      >
        <circle
          :cx="cx" :cy="cy" :r="radius"
          :stroke-width="thickness"
          class="fill-none"
          stroke="#e5e7eb"
        />

        <g :transform="`rotate(-90 ${cx} ${cy})`">
          <circle
            v-for="s in slices"
            :key="s.id + '-' + s.name"
            :cx="cx" :cy="cy" :r="radius"
            :stroke="s.color"
            :stroke-width="thickness"
            fill="none"
            stroke-linecap="butt"
            vector-effect="non-scaling-stroke"
            :stroke-dasharray="s.dasharray"
            :stroke-dashoffset="s.dashoffset"
            :opacity="hovered === null || hovered === s.id ? 1 : 0.35"
            class="transition-opacity duration-300 ease-in-out"
            style="cursor:pointer"
          />
        </g>
      </svg>

      <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
        <div
          class="relative bg-center bg-no-repeat bg-contain"
          :style="{
            width: treeIconSize + 'px',
            height: treeIconSize + 'px',
            backgroundImage: `url('${centralIcon}')`
          }"
        >
          <span
            class="absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-[38px] font-semibold text-gray-900 text-xl"
          >
            {{ total }}
          </span>
        </div>
      </div>

      <div
        v-if="tooltip.show"
        class="fixed z-50 bg-white border border-gray-300 text-gray-900 text-sm rounded-md px-3 py-2 shadow-lg pointer-events-none whitespace-nowrap"
        :style="{ top: tooltip.y + 'px', left: tooltip.x + 'px' }"
      >
        {{ tooltip.text }}
      </div>
    </div>
  </section>
</template>
