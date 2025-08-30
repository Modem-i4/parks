<script setup>
import { useParkStore } from '@/Stores/useParkStore.js'
import PanelHeader from '../Custom/PanelHeader.vue';
import { ref, onMounted } from 'vue';

const parkStore = useParkStore()
const parksLoaded = ref(false)

function getParks() {
  parksLoaded.value = false
  axios.get(`/api/parks`)
    .then(response => {
      parkStore.markers = response.data;
      parksLoaded.value = true
    })
    .catch(error => {
      console.error('Error fetching markers:', error);
    });
}
onMounted(getParks)
</script>

<template>
  <ul v-if="parksLoaded">
    <li
      v-for="park in parkStore.markers"
      :key="park.id"
      class="p-4 border-b hover:bg-gray-100 cursor-pointer"
      @click="parkStore.selectedMarker=park"
    >
      <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 cursor-pointer">
        <PanelHeader
          :title="park.name" :subtitle="`${park.area} гектарів`" :icon="park.icon?.file_path" :shouldFilter="true"
          iconBg="gray-100"
        />
        <div class="px-4 pb-4">
          <p class="text-gray-700 text-sm line-clamp-3" v-if="park.description">{{ park.description }}</p>
          <p v-else class="text-gray-400 text-sm italic">Опис відсутній</p>
        </div>
      </div>
    </li>
    <a href="https://pl-ua.eu/en/" target="_blank" class="mx-4">
      <img src="/img/icons/logo-project-hor.jpg" alt="parksMatter" class="w-[calc(100%-1.5rem)] max-w-[23rem] mx-auto mt-3 py-5 bg-white rounded"/>
    </a>
  </ul>
</template>
