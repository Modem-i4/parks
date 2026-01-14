<template>
  <div class="p-4 space-y-4">
    <h2 class="text-lg font-semibold text-center text-gray-700">
      🔍 {{ isMobile ? 'Пошук насадження' : 'Пошук насадження за номером'}}
    </h2>

    <input
      v-model="search"
      type="text"
      placeholder="Введіть інвентарний номер"
      class="w-full px-4 py-2 border rounded-lg shadow focus:outline-none focus:ring focus:border-blue-300 text-center"
      @keyup.enter="find"
    />

    <div class="flex justify-center">
      <PrimaryButton
        class="bg-blue-600 text-white font-semibold px-6 py-2 rounded-lg hover:bg-blue-700 transition"
        @click="find"
      >
        Перейти
      </PrimaryButton>
    </div>

    <p v-if="errorMessage" class="text-sm text-red-500 text-center">{{ errorMessage }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useParkStore } from '@/Stores/useParkStore'
import axios from 'axios'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import { setViewToParkMarker } from '@/Helpers/Maps/SetParkView'

const search = ref('')
const errorMessage = ref(null)
const emit = defineEmits(['close'])

const parkStore = useParkStore()

async function find() {
  if(search.value === '') {
    errorMessage.value = 'Введіть інвентарний номер'
    return
  }
  axios.get(`/api/markers/inv/${search.value}`)
    .then((res) => {
        errorMessage.value = null
        const foundMarker = res.data
        if(!foundMarker) {
          errorMessage.value = 'Маркер не знайдено'
          return
        }
        if(foundMarker.park_id === parkStore.selectedPark?.id) {
          parkStore.selectedMarker = foundMarker
        }
        else {
          setViewToParkMarker(parkStore, foundMarker)
        }
        emit('close')
    })
    .catch(() => errorMessage.value = 'Помилка завантаження')
}
</script>
