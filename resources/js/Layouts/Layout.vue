<script setup>
import { ref, computed, watchEffect } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'

import ApplicationLogo from '@/Components/Default/ApplicationLogo.vue'
import Dropdown from '@/Components/Default/Dropdown.vue'
import DropdownLink from '@/Components/Default/DropdownLink.vue'
import NavLink from '@/Components/Default/NavLink.vue'
import ResponsiveNavLink from '@/Components/Default/ResponsiveNavLink.vue'

import { useAuthStore } from '@/Stores/useAuthStore'

const showingNavigationDropdown = ref(false)
const inertia = usePage()
const user = computed(() => inertia.props.auth?.user)

const authStore = useAuthStore()
watchEffect(() => {
  authStore.setUser(user.value)
})

const guestPages = [
  { label: 'Головна', routeName: 'home' },
  { label: 'Парки', routeName: 'parks' },
  { label: 'Новини', routeName: 'news' },
]

const adminPages = [
  { label: 'Інструкції', routeName: 'dashboard' },
  { label: 'Парки', routeName: 'parks' },
  { label: 'Новини', routeName: 'news' },
  { label: 'Словники', routeName: 'admin.dictionaries', role: 'editor' },
  { label: 'Користувачі', routeName: 'admin.users', role: 'admin' },
]

const navPages = computed(() => (user.value ? adminPages : guestPages))

function canViewItem(item) {
  return !item.role || authStore.atLeast(item.role)
}

const desktopPages = computed(() =>
  navPages.value.filter(p => !p.mobileOnly && canViewItem(p))
)

const mobilePages = computed(() =>
  navPages.value.filter(p => !p.desktopOnly && canViewItem(p))
)
</script>

<template>
  <div class="min-h-screen bg-gray-100">
    <nav class="border-b border-gray-100 bg-white">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 justify-between">
          <!-- Logo -->
          <div class="flex items-center shrink-0">
            <Link :href="route('home')">
              <ApplicationLogo class="block h-9 w-auto fill-current text-gray-800" />
            </Link>
          </div>

          <!-- Desktop Navigation -->
          <div class="hidden sm:flex sm:items-center space-x-8 ms-10">
            <NavLink
              v-for="item in desktopPages"
              :key="item.routeName"
              :href="route(item.routeName)"
              :active="route().current(item.routeName)"
            >
              {{ item.label }}
            </NavLink>
          </div>

          <!-- User Dropdown -->
          <div v-if="user" class="hidden sm:ms-6 sm:flex sm:items-center">
            <Dropdown align="right" width="48">
              <template #trigger>
                <span class="inline-flex rounded-md">
                  <button
                    type="button"
                    class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium text-gray-500 hover:text-gray-700"
                  >
                    {{ user.name }}
                    <svg class="-me-0.5 ms-2 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                  </button>
                </span>
              </template>
              <template #content>
                <DropdownLink :href="route('profile.edit')">Профіль</DropdownLink>
                <DropdownLink :href="route('logout')" method="post" as="button">Вийти</DropdownLink>
              </template>
            </Dropdown>
          </div>

          <!-- Mobile Hamburger -->
          <div class="-me-2 flex items-center sm:hidden">
            <button
              @click="showingNavigationDropdown = !showingNavigationDropdown"
              class="inline-flex items-center p-2 text-gray-400 hover:text-gray-500 hover:bg-gray-100 rounded-md focus:outline-none"
            >
              <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                <path v-if="!showingNavigationDropdown" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Navigation -->
      <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }" class="sm:hidden">
        <div class="space-y-1 pb-3 pt-2">
          <ResponsiveNavLink
            v-for="item in mobilePages"
            :key="item.routeName"
            :href="route(item.routeName)"
            :active="route().current(item.routeName)"
          >
            {{ item.label }}
          </ResponsiveNavLink>
        </div>

        <!-- Mobile User Info -->
        <div v-if="user" class="border-t border-gray-200 pb-1 pt-4">
          <div class="px-4">
            <div class="text-base font-medium text-gray-800">{{ user.name }}</div>
            <div class="text-sm font-medium text-gray-500">{{ user.email }}</div>
          </div>

          <div class="mt-3 space-y-1">
            <ResponsiveNavLink :href="route('profile.edit')">Профіль</ResponsiveNavLink>
            <ResponsiveNavLink :href="route('logout')" method="post" as="button">Вийти</ResponsiveNavLink>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main>
      <slot />
    </main>
  </div>
</template>
