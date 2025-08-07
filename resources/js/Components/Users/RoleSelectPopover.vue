<script setup>
import { onMounted, onBeforeUnmount, ref } from 'vue'
import RoleSelectList from './RoleSelectList.vue'

const props = defineProps({
  currentRole: String,
  anchor: Object
})
const emit = defineEmits(['select', 'close'])

const menuRef = ref(null)

function clickOutside(e) {
  if (!menuRef.value?.contains(e.target) && !props.anchor?.contains(e.target)) {
    emit('close')
  }
}

onMounted(() => document.addEventListener('click', clickOutside))
onBeforeUnmount(() => document.removeEventListener('click', clickOutside))
</script>

<template>
  <div
    ref="menuRef"
    class="absolute z-50 mt-1 bg-white border rounded shadow-lg p-2"
    style="left: 0"
  >
    <RoleSelectList
      :currentRole="props.currentRole"
      @select="$emit('select', $event)"
    />
  </div>
</template>
