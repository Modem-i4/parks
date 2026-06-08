<script setup>
import { isMobile } from '@/Helpers/isMobileHelper'
import { computed, ref, watch } from 'vue'

const props = defineProps({
  tabs: {
    type: Array,
    default: () => []
  },
  modelValue: String
})

const emit = defineEmits(['update:modelValue'])

const activeTab = ref(props.modelValue || props.tabs[0]?.key || '')
const activeIndex = computed(() => {
  const index = props.tabs.findIndex(tab => tab.key === activeTab.value)
  return index === -1 ? 0 : index
})

function selectTab(key) {
  activeTab.value = key
  emit('update:modelValue', key)
}

watch(
  () => props.modelValue,
  val => {
    if (val) activeTab.value = val
  }
)

watch(
  () => props.tabs,
  tabs => {
    if (!activeTab.value && tabs.length) selectTab(tabs[0].key)
  },
  { immediate: true }
)
</script>

<template>
  <div>
    <div class="flex border-b border-gray-200" :class="{'bg-white': !isMobile}">
      <button
        v-for="tab in tabs"
        :key="tab.key"
        type="button"
        class="flex-1 px-4 py-3 text-sm font-semibold transition-colors"
        :class="activeTab === tab.key
          ? 'border-b-2 border-green-700 text-green-800'
          : 'text-gray-500 hover:text-gray-800'"
        @click="selectTab(tab.key)"
      >
        {{ tab.label }}
      </button>
    </div>

    <div class="overflow-hidden">
      <div
        class="tab-track"
        :style="{ transform: `translateX(-${activeIndex * 100}%)` }"
      >
        <div
          v-for="tab in tabs"
          :key="tab.key"
          class="tab-panel"
        >
          <slot :name="tab.key">
            <slot />
          </slot>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.tab-track {
  display: flex;
  transition: transform 260ms ease;
  width: 100%;
}

.tab-panel {
  flex: 0 0 100%;
  width: 100%;
}
</style>
