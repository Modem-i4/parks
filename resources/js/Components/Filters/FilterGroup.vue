<script setup>
import { ref, computed, watch } from 'vue'
import FilterNode from './FilterNode.vue'
import { GetFilterTargetNode } from '@/Helpers/Maps/GetFilterTargetNode'

const props = defineProps({
  node: Object,
  filters: Object,
  path: Array,
  renderKey: Number 
})

const isOpen = ref(props.node.open || false)
const isChecked = ref(props.node.checked || false)

const showCheckbox = computed(() => props.node.slug !== 'general')

const currentPath = computed(() => [...props.path, props.node.slug])

watch(isOpen, (val) => {
  if(val) isChecked.value = true
})

watch(() => props.filters, 
  ()=> {
    let target = GetFilterTargetNode(props.filters, currentPath.value)
    isChecked.value = !!target
  },
  { deep: true }
)

watch(isChecked, (val) => {
  const target = GetFilterTargetNode(props.filters, props.path)
  if(!target) return

  if (val) {
    target[props.node.slug] = {}
  } else {
    delete target[props.node.slug]
    isOpen.value = false
  }
})

const toggle = () => {
  isOpen.value = !isOpen.value
}

// Animation
function enter(el) {
  el.style.height = '0'
  el.style.transition = 'height 300ms ease'
  const height = el.scrollHeight
  requestAnimationFrame(() => {
    el.style.height = height + 'px'
  })
}

function afterEnter(el) {
  el.style.height = 'auto'
  el.style.transition = ''
}

function leave(el) {
  el.style.height = el.scrollHeight + 'px'
  el.style.transition = 'height 300ms ease'
  requestAnimationFrame(() => {
    el.style.height = '0'
  })
}

function afterLeave(el) {
  el.style.height = ''
  el.style.transition = ''
}

// TODO: DYNAMIC UPDATE
</script>


<template>
  <div class="space-y-2">
    <div class="flex items-center justify-between bg-gray-100 px-2 py-1 rounded cursor-pointer hover:bg-gray-200" @click="toggle">
      <div class="flex items-center space-x-2">
        <svg :class="{'transform rotate-90': isOpen}" class="w-4 h-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <span class="font-semibold">{{ node.name }}</span>
      </div>
      <input
        v-if="showCheckbox"
        type="checkbox"
        v-model="isChecked"
        @click.stop
        class="accent-green-600"
      >
    </div>
    
    <transition
      @enter="enter"
      @after-enter="afterEnter"
      @leave="leave"
      @after-leave="afterLeave"
    >
        <div
            v-show="isOpen && (isChecked || !showCheckbox)"
            class="pl-4 border-l-2 border-gray-300 ml-2 mt-1 space-y-2 overflow-hidden"
            ref="content"
        >
          <FilterNode
              v-for="child in node.children"
              :key="child.type === 'group' ? child.slug : `${child.slug}-${props.renderKey}`"
              :node="child"
              :filters="filters"
              :path="currentPath"
              :renderKey
          />
        </div>
    </transition>
  </div>
</template>