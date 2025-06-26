<template>
  <ul>
    <li
      v-for="park in parks"
      :key="park.id"
      class="p-4 border-b hover:bg-gray-100 cursor-pointer"
      @click="$emit('select', park)"
    >
      <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300 cursor-pointer">
        <div class="flex items-center p-4 space-x-4">
          <div class="flex-shrink-0 w-16 h-16 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center">
            <img v-if="park.icon" :src="park.icon.file_path" alt="Icon" class="w-12 h-12 object-contain" />
            <div v-else class="text-gray-400 text-xl">🌳</div>
          </div>
          <div>
            <h3 class="text-lg font-semibold text-gray-900">{{ park.name }}</h3>
            <p class="text-sm text-gray-500">{{ park.address }}</p>
          </div>
        </div>
        <div class="px-4 pb-4">
          <p class="text-gray-700 text-sm line-clamp-3" v-if="park.description">{{ park.description }}</p>
          <p v-else class="text-gray-400 text-sm italic">Опис відсутній</p>
        </div>
      </div>
    </li>
  </ul>
</template>

<script setup>
defineProps({ parks: Array })

</script>
