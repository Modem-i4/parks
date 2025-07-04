<script setup>
import { useParkStore } from '@/Stores/useParkStore.js'
import PanelHeader from '../Custom/PanelHeader.vue';

const parkStore = useParkStore()
</script>

<template>
  <ul>
    <li
      v-for="park in parkStore.markers"
      :key="park.id"
      class="p-4 border-b hover:bg-gray-100 cursor-pointer"
      @click="parkStore.selectedMarker=park"
    >
      <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 cursor-pointer">
        <PanelHeader
          :title="park.name" :subtitle="park.address" :icon="park.icon?.file_path"
        />
        <div class="px-4 pb-4">
          <p class="text-gray-700 text-sm line-clamp-3" v-if="park.description">{{ park.description }}</p>
          <p v-else class="text-gray-400 text-sm italic">Опис відсутній</p>
        </div>
      </div>
    </li>
  </ul>
</template>
