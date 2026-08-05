<script setup>
import { ref } from 'vue'

const isCollapsed = ref(false)
</script>

<template>
  <aside
    class="hidden md:block relative min-w-0 flex-none transition-[width] duration-300 ease-in-out"
    :style="{ width: isCollapsed ? '0' : 'max(33.333333%, 300px)' }"
  >
    <div class="h-full w-full overflow-y-auto overflow-x-clip border-r bg-gray-100">
      <slot />
    </div>

    <button
      type="button"
      class="absolute right-0 top-[7.8rem] z-40 flex h-12 w-5 translate-x-full items-center justify-center rounded-r-lg border border-l-0 border-gray-300 bg-gray-100 text-xl text-gray-600 shadow-md transition hover:bg-white focus:outline-none focus:ring-2 focus:ring-green-500"
      :aria-label="isCollapsed ? 'Розгорнути бічну панель' : 'Згорнути бічну панель'"
      :title="isCollapsed ? 'Розгорнути панель' : 'Згорнути панель'"
      @click="isCollapsed = !isCollapsed"
    >
      <span aria-hidden="true">{{ isCollapsed ? '›' : '‹' }}</span>
    </button>
  </aside>
</template>
