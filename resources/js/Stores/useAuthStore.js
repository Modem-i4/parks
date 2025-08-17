import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
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

  const can = computed(() => ({
    view: atLeast('viewer'),
    edit: atLeast('editor'),
    addMarkers: atLeast('editor'),
    upload: atLeast('editor'),
    deleteMarkers: atLeast('editor'),
    completeWork: atLeast('worker'),
    assignWork: atLeast('work_manager'),

    editDictionaries: atLeast('editor'),
    export: atLeast('viewer'),
    import: atLeast('work_manager'),
    editNews: atLeast('news_manager'),
  }))

  return {
    currentUser,
    setUser,
    hasRole,
    atLeast,
    can,
  }
})