<script setup>
import MapView from '@/Components/Map/MapView.vue';
import ParkDetails from '@/Components/Map/ParkDetails.vue';
import ParkList from '@/Components/Map/ParkList.vue';
import ResolveLayout from '@/Helpers/ResolveLayout.js';
import { ref, onMounted } from 'vue'

defineOptions({
  layout: ResolveLayout,
});

/* ------------- */

defineProps({
  parks: Array
})
const selectedPark = ref(null)

function selectPark(park) {
  selectedPark.value = park
  console.log('Selected park:', park)
}

</script>

<template>
  <div class="flex h-screen">
    <div class="w-1/3 border-r overflow-y-auto">
      <ParkList
        v-if="!selectedPark"
        :parks="parks"
        @select="selectPark"
      />
      <ParkDetails
        v-else
        :park="selectedPark"
        @back="selectedPark = null"
      />
    </div>
    <div class="w-2/3">
      <MapView
        :parks="parks"
        :selectedParkId="selectedPark?.id"
        @select="selectPark"
      />
    </div>
  </div>
</template>
