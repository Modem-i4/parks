<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { NodeViewWrapper } from '@tiptap/vue-3'

const props = defineProps({
  node: Object,
  updateAttributes: Function,
})

const boxEl = ref(null)

let startX = 0
let startWidthPx = 0
let parentWidthPx = 0
let dragging = false

function onHandleDown(e) {
  e.preventDefault()
  e.stopPropagation()
  dragging = true

  const img = boxEl.value.querySelector('img')
  const rect = img.getBoundingClientRect()
  startWidthPx = rect.width

  const riw = boxEl.value.parentElement
  parentWidthPx = (riw.parentElement?.getBoundingClientRect().width) || rect.width
  startX = e.clientX

  window.addEventListener('pointermove', onMove, { passive: false })
  window.addEventListener('pointerup', onUp, { once: true })
}

function onMove(e) {
  if (!dragging) return
  e.preventDefault()

  const dx = e.clientX - startX
  let newW = startWidthPx + dx
  newW = Math.max(40, Math.min(newW, parentWidthPx))
  const pct = Math.round((newW / parentWidthPx) * 1000) / 10

  props.updateAttributes({ width: `${pct}%` })
}

function onUp() {
  dragging = false
  window.removeEventListener('pointermove', onMove)
}

function setAlign(where) {
  props.updateAttributes({ align: where })
}

onMounted(() => {
  const img = boxEl.value.querySelector('img')
  img?.setAttribute('data-drag-handle', '')
})
onBeforeUnmount(() => {
  window.removeEventListener('pointermove', onMove)
})
</script>

<template>
  <NodeViewWrapper
    class="riw"
    data-inline="true"
    :data-align="(node.attrs.align || 'center')"
    :style="{ width: node.attrs.width || '100%' }"
    :draggable="true"
  >
    <span ref="boxEl" class="riw-box">
      <img
        :src="node.attrs.src"
        :alt="node.attrs.alt || ''"
        :title="node.attrs.title || ''"
        style="width:100%; height:auto; display:block;"
        draggable="false"
        data-drag-handle
      />

      <span class="riw-ctrl" contenteditable="false" @mousedown.prevent>
        <button
          class="riw-btn"
          :class="{active: node.attrs.align === 'left'}"
          title="Align left"
          @click.stop.prevent="setAlign('left')"
        >Л</button>
        <button
          class="riw-btn"
          :class="{active: (node.attrs.align || 'center') === 'center'}"
          title="Align center"
          @click.stop.prevent="setAlign('center')"
        >Ц</button>
        <button
          class="riw-btn"
          :class="{active: node.attrs.align === 'right'}"
          title="Align right"
          @click.stop.prevent="setAlign('right')"
        >П</button>
      </span>

      <span class="riw-handle" @pointerdown="onHandleDown" />
    </span>
  </NodeViewWrapper>
</template>

<style >
.riw[data-inline="true"]{
  display:inline-block;
  vertical-align:baseline;
}
.riw-box{
  display:inline-block;
  position:relative;
  line-height:0;
  cursor: grab;
}
.riw img{
  max-width:100%;
  height:auto;
  margin:.5rem 0;
}
.riw-handle{
  position:absolute;
  right:-8px;
  bottom:-8px;
  width:14px; height:14px;
  border-radius:3px;
  background:#6d28d9;
  box-shadow:0 0 0 2px #fff;
  cursor:nwse-resize;
  opacity:0;
  transition:opacity .12s linear;
  touch-action:none;
}
.riw-ctrl{
  position:absolute;
  left:8px;
  top:8px;
  display:flex;
  align-items:center;
  gap:6px;
  padding:4px 6px;
  border-radius:8px;
  background:rgba(17,24,39,.72);
  backdrop-filter:saturate(120%) blur(3px);
  color:#fff;
  opacity:0;
  transition:opacity .12s linear;
  user-select:none;
}
.riw-btn{
  font:500 12px/1 system-ui;
  padding:3px 6px;
  border-radius:6px;
  border:1px solid rgba(255,255,255,.15);
  background:transparent;
  color:#fff;
  cursor:pointer;
}
.riw-btn:hover{ background:rgba(255,255,255,.1); }
.riw-btn.active{ background:#6d28d9; border-color:#6d28d9; }
.riw-box:hover .riw-handle,
.riw-box:focus-within .riw-handle,
.riw-box:hover .riw-ctrl,
.riw-box:focus-within .riw-ctrl { opacity:1; }

@media (min-width: 720px) {
  .news-content .riw[data-align="left"]  { float: left; margin: .25rem .75rem .25rem 0; }
  .news-content .riw[data-align="right"] { float: right; margin: .25rem 0 .25rem .75rem; }
  .news-content .riw[data-align="center"]{ display: block; margin: .5rem auto; }
}
@media (max-width: 720px) {
  .news-content .riw { display: block; width: 100% !important; }
  .riw-ctrl, .riw-handle { display: none; }
}
</style>
