import { defineStore } from 'pinia'
import { ref } from 'vue'
import { UserRole } from '@/Helpers/UserRole'

export const useAuthStore = defineStore('auth', () => {
  const currentUser = ref(null)

  function setUser(user) {
    currentUser.value = user
  }

  function hasRole(role) {
    return currentUser.value?.role === role
  }

  function atLeast(role) {
    return UserRole.atLeast(currentUser.value?.role, role)
  }

  return {
    currentUser,
    setUser,
    hasRole,
    atLeast,
  }
})