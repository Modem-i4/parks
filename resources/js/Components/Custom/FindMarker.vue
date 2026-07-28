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
import { useParkStore } from '@/Stores/useParkStore'
import PrimaryButton from '@/Components/Default/PrimaryButton.vue'
import { isMobile } from '@/Helpers/isMobileHelper'
import { useFindMarker } from '@/Helpers/Maps/FindMarkerHelper'

const emit = defineEmits(['close'])

const parkStore = useParkStore()
const { search, errorMessage, findMarker, showMarker } = useFindMarker(parkStore)

async function find() {
  const marker = await findMarker()
  if (!marker) return

  await showMarker(marker)
  emit('close')
}
</script>
