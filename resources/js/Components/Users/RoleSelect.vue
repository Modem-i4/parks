<script setup>
import { ref, computed } from 'vue'
import Modal from '@/Components/Default/Modal.vue'
import RoleSelectPopover from './RoleSelectPopover.vue'
import { isMobile } from '@/Helpers/isMobileHelper'

const props = defineProps({
  currentRole: String,
  anchor: Object
})
const emit = defineEmits(['select', 'close'])

const showModal = ref(false)

function selectAndClose(role) {
  emit('select', role)
  emit('close')
}
</script>

<template>
    {{ showModal }} {{ isMobile }}
  <RoleSelectPopover
    v-if="!isMobile"
    :current-role="props.currentRole"
    :anchor="props.anchor"
    @select="selectAndClose"
    @close="emit('close')"
  />

  <Modal
    max-width="sm"
    @close="emit('close')"
  >
    <div>DIV</div>
  </Modal>
</template>
