<script setup>
import { ref, computed } from 'vue'
import axios from 'axios'
import UserNode from './UserNode.vue'
import { UserRole } from '@/Helpers/UserRole'
import LoadingLineIndicator from '../Custom/LoadingLineIndicator.vue'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
  users: Array
})

const searchQuery = ref('')
const users = ref([...props.users])

const isLoading = ref(false)
async function updateRole({ id, role }) {
  isLoading.value = true
  axios.patch(`/api/users/${id}/role`, { role })
    .then(() => {
      const u = users.value.find(u => u.id === id)
      if (u) u.role = role
      isLoading.value = false
    })
}
const groupedUsers = computed(() => {
  const q = searchQuery.value.toLowerCase().trim()
  const map = {}

  for (const user of users.value) {
    const match =
      user.name?.toLowerCase().includes(q) ||
      user.email?.toLowerCase().includes(q) ||
      user.role.toLowerCase().includes(q)
    if (!match) continue
    if (!map[user.role]) map[user.role] = []
    map[user.role].push(user)
  }

  for (const role in map) {
    map[role].sort((a, b) => new Date(a.created_at) - new Date(b.created_at)) 
  }

  const entries = Object.entries(map)
  entries.sort(([a], [b]) => {
    if (a === UserRole.GUEST) return -1
    if (b === UserRole.GUEST) return 1
    return UserRole.level(b) - UserRole.level(a)
  })

  return entries.map(([role, users]) => ({ role, users }))
})
</script>

<template>
  <div class="space-y-4 pb-3 relative">
    <div class="flex w-full justify-between">
      <h2 class="text-xl font-semibold leading-tight text-gray-800"
      >Користувачі</h2>
      <PrimaryButton @click="router.visit('/admin/audit')">Аудит дій</PrimaryButton>
    </div>
    <div class="sticky top-0 z-10 bg-white">
      <LoadingLineIndicator :isLoading />
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Пошук..."
        class="w-full px-3 py-2 border rounded"
      />
    </div>

    <div v-if="groupedUsers.length">
      <UserNode
        v-for="group in groupedUsers"
        :key="group.role"
        :role="group.role"
        :users="group.users"
        @updateRole="updateRole"
      />
    </div>

    <div v-else class="text-center text-gray-500 py-8">
      Нічого не знайдено.
    </div>
  </div>
</template>
