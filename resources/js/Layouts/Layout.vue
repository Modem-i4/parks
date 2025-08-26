<script setup>
import { ref, computed, watchEffect } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'

import Dropdown from '@/Components/Default/Dropdown.vue'
import DropdownLink from '@/Components/Default/DropdownLink.vue'
import NavLink from '@/Components/Default/NavLink.vue'
import ResponsiveNavLink from '@/Components/Default/ResponsiveNavLink.vue'

import { useAuthStore } from '@/Stores/useAuthStore'
import { useMobileNavClose } from '@/Helpers/MobileNavCloseHelper'
import Footer from '@/Components/Sections/Footer.vue'

const showingNavigationDropdown = ref(false)
const page = usePage()
const user = computed(() => page.props.auth?.user)
const showFooter = computed(() => page.props.showFooter ?? true)

const authStore = useAuthStore()
watchEffect(() => {
  authStore.setUser(user.value)
})

const guestPages = [
  { label: 'ГОЛОВНА', routeName: 'home' },
  { label: 'МАПА ПАРКІВ', routeName: 'parks' },
  { label: 'НОВИНИ', routeName: 'news' },
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

// Mobile nav close controls
const navEl = ref(null)
useMobileNavClose({ isOpenRef: showingNavigationDropdown, rootElRef: navEl })
</script>

<template>
  <div class="min-h-dvh flex flex-col bg-gray-100">
    <nav class="border-b border-gray-100 sticky top-0 z-[51] bg-white shadow" ref="navEl">
      <div class="flex mx-auto max-w-[1500px] h-[85px] px-4 sm:px-6 lg:px-8">
        <div class="flex-1 flex items-center justify-between md:grid md:grid-cols-[1fr_auto_1fr] md:gap-4">
          <!-- Logo -->
          <div class="flex items-center gap-4 md:max-w-none overflow-hidden min-w-0">
            <Link :href="route('home')" class="shrink">
              <img 
                src="/img/icons/logo-parks-matter.png" 
                class="block max-h-[65px] md:max-h-[clamp(28px,10vw,65px)] w-auto object-contain max-w-full" 
              />
            </Link>
            <a href="https://pl-ua.eu/ua/" target="_blank" class="shrink">
              <img 
                src="/img/icons/logo-interreg.png" 
                class="block max-h-[55px] md:max-h-[clamp(24px,8vw,50px)] w-auto object-contain max-w-full" 
              />
            </a>
          </div>

          <!-- Desktop Navigation -->
          <div class="hidden md:flex justify-center space-x-8 min-w-0">
            <NavLink
              v-for="item in desktopPages"
              :key="item.routeName"
              :href="route(item.routeName)"
              :active="route().current(item.routeName) || route().current(item.routeName + '.*')"
            >
              {{ item.label }}
            </NavLink>
          </div>

          <!-- User Dropdown -->
          <div v-if="user" class="hidden md:flex md:ms-6 items-center justify-end">
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
          <div class="-me-2 flex items-center md:hidden">
            <button
              @click.stop="showingNavigationDropdown = !showingNavigationDropdown"
              class="inline-flex items-center p-2 rounded-md text-gray-700 hover:bg-gray-100 focus:outline-none"
              aria-label="Toggle menu"
            >
              <span class="relative inline-flex h-6 w-8 items-center justify-center">
                <!-- top -->
                <span
                  aria-hidden="true"
                  class="absolute block h-[2px] w-6 bg-current transition-all duration-300 ease-in-out"
                  :class="showingNavigationDropdown ? 'rotate-45' : '-translate-y-[6px]'"
                />
                <!-- middle -->
                <span
                  aria-hidden="true"
                  class="absolute block h-[2px] w-6 bg-current transition-all duration-300 ease-in-out"
                  :class="showingNavigationDropdown ? 'opacity-0' : 'opacity-100'"
                />
                <!-- bottom -->
                <span
                  aria-hidden="true"
                  class="absolute block h-[2px] w-6 bg-current transition-all duration-300 ease-in-out"
                  :class="showingNavigationDropdown ? 'rotate-[-45deg]' : 'translate-y-[6px]'"
                />
              </span>
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile Navigation -->
      <div
        class="sm:hidden absolute w-full overflow-hidden bg-white max-h-0 opacity-0 [transition-property:max-height,opacity] duration-300 ease-in-out motion-reduce:transition-none"
        :class="showingNavigationDropdown ? 'max-h-[70vh] opacity-100' : ''"
      >
        <div class="space-y-1 pb-3 pt-2">
          <ResponsiveNavLink
            v-for="item in mobilePages"
            :key="item.routeName"
            :href="route(item.routeName)"
            :active="route().current(item.routeName)"
            @click="showingNavigationDropdown = false"
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
            <ResponsiveNavLink :href="route('profile.edit')" @click="showingNavigationDropdown = false">Профіль</ResponsiveNavLink>
            <ResponsiveNavLink :href="route('logout')" method="post" as="button" @click="showingNavigationDropdown = false">Вийти</ResponsiveNavLink>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <main>
      <slot />
    </main>

    <!-- Footer -->
    <Footer v-if="showFooter" class="mt-auto" />
  </div>
</template>
