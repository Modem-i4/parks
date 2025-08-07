<script setup>
import { computed } from 'vue'
import { UserRole } from '@/Helpers/UserRole'
import { useAuthStore } from '@/Stores/useAuthStore'

const props = defineProps({
  currentRole: String
})
const emit = defineEmits(['select'])

const descriptions = {
  super_admin: 'Керує адміністраторами і користувачами.',
  admin: 'Може керувати користувачами і парками.',
  news_manager: 'Може додавати та редагувати новини.',
  work_manager: 'Може додавати та редагувати роботи.',
  editor: 'Може додавати та редагувати маркери, фото, словники.',
  worker: 'Може виконувати призначені роботи.',
  viewer: 'Має доступ до перегляду всіх полів насаджень.',
  guest: 'Не має доступу до керування.',
  dismissed: 'Користувач більше не має доступу.'
}

const roles = Object.keys(UserRole.levels)

const authStore = useAuthStore()

const availableRoles = computed(() =>
  authStore.hasRole(UserRole.SUPER_ADMIN)
    ? roles
    : roles.filter(r => r !== 'admin' && r !== 'super_admin')
)
</script>

<template>
  <div class="space-y-1 text-sm">
    <div
      v-for="role in availableRoles"
      :key="role"
      class="px-3 py-2 rounded hover:bg-gray-100 cursor-pointer"
      :class="{ 'bg-gray-100 font-semibold': role === currentRole }"
      @click="$emit('select', role)"
    >
      <div class="text-gray-800">{{ UserRole.label(role) }}</div>
      <div class="text-xs text-gray-500">{{ descriptions[role] ?? '—' }}</div>
    </div>
  </div>
</template>
