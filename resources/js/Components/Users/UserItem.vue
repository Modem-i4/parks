<script setup>
import { computed, ref } from 'vue'
import Modal from '@/Components/Default/Modal.vue'
import RoleSelectPopover from './RoleSelectPopover.vue'
import RoleSelectList from './RoleSelectList.vue'
import { isMobile } from '@/Helpers/isMobileHelper'
import { UserRole } from '@/Helpers/UserRole'

import { useAuthStore } from '@/Stores/useAuthStore'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import SecondaryButton from '@/Components/Default/SecondaryButton.vue'

const props = defineProps({ user: Object })
const emit = defineEmits(['updateRole'])

const showRoleMenu = ref(false)
const anchorEl = ref(null)

const { currentUser } = useAuthStore()

const editable = computed(() => {
  const cur = UserRole.level(currentUser.role)
  const tar = UserRole.level(props.user.role)
  // admins can't edit admins
  if (currentUser.role === UserRole.ADMIN && props.user.role === UserRole.ADMIN) 
    return false
  return cur > tar // super_admin > admin
    || (cur === tar && currentUser.id < props.user.id) // OR super_admin == super_admin, but registered earlier (by id)
})


function openRoleMenu(event) {
  anchorEl.value = event.currentTarget
  showRoleMenu.value = true
}

function selectRole(role) {
  if (role !== props.user.role) {
    emit('updateRole', { id: props.user.id, role })
  }
  showRoleMenu.value = false
}

const date = computed(() => new Date(props.user.created_at).toLocaleDateString('uk-UA', {
  day: '2-digit',
  month: '2-digit',
  year: 'numeric'
})) 
</script>

<template>
  <div class="grid grid-cols-[2fr_1fr_auto] items-center px-3 py-2 hover:bg-gray-50 text-sm border-b last:border-none">
    <div class="min-w-0">
      <div class="font-semibold truncate">{{ user.name }}</div>
      <div class="text-xs text-gray-500 truncate">{{ user.email }}</div>
      <div class="text-xs text-gray-400" v-if="isMobile">{{ date }}</div>
    </div>

    <div class="text-xs whitespace-nowrap relative">
      <template v-if="user.role === UserRole.GUEST">
        <div class="flex items-center gap-2">
          <component :is="isMobile ? SecondaryButton : PrimaryButton" @click="openRoleMenu">
            {{ isMobile ? '✔️' : 'Прийняти' }}
          </component>
          <SecondaryButton @click="selectRole(UserRole.DISMISSED)">
            {{ isMobile ? '❌' : 'Відхилити' }}
          </SecondaryButton>
        </div>
      </template>

      <template v-else>
        <div
          class="font-mono cursor-default border rounded p-2 max-w-32"
          :class="{ 'cursor-pointer hover:underline': editable }"
          @click="(e) => { if (editable) openRoleMenu(e) }"
          ref="anchorEl"
        >
          {{ UserRole.label(user.role) }}
          <span v-if="!editable">🔒</span>
        </div>
      </template>

      <RoleSelectPopover
        v-if="showRoleMenu && !isMobile"
        :currentRole="user.role"
        :userName="user.name"
        :anchor="anchorEl"
        @select="selectRole"
        @close="showRoleMenu = false"
      />

      <Modal
        :show="showRoleMenu && isMobile"
        max-width="sm"
        @close="showRoleMenu = false"
        content-classes="p-4 space-y-2"
      >
        <div class="text-lg font-semibold text-center text-gray-800">
          Оберіть роль
        </div>
        <div class="italic text-sm text-center text-gray-800">
          {{ user.name }}
        </div>
        <RoleSelectList :currentRole="user.role" @select="selectRole" />
      </Modal>
    </div>

    <div class="text-xs text-gray-400 whitespace-nowrap text-right" v-if="!isMobile">
      {{ date }}
    </div>
  </div>
</template>
