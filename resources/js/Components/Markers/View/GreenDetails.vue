<script setup>
import { computed } from 'vue';

const props = defineProps({
  green: Object,
  type: String,
})
const showDetails = computed(() => (props.green[props.type] && ['tree', 'bush', 'hedge'].includes(props.type)) || props.green.age)
</script>

<template>
  <div v-if="showDetails" class="text-gray-600 bg-white rounded px-4 py-6">
    <h3 class="text-lg font-semibold pb-2">Деталі насадження</h3>

    <p v-if="green.age"><strong>Вік:</strong> {{ Math.floor(green.age%12) }} р. {{ Math.floor(green.age/12) }} м.</p>

    <div v-if="type === 'hedge'" class="space-y-1">
      <p><strong>Довжина:</strong> {{ green.hedge.length_m }} м</p>
      <p v-if="green.hedge.hedge_row"><strong>Тип ряду:</strong> {{ green.hedge.hedge_row }}</p>
      <p v-if="green.hedge.hedge_shape"><strong>Форма:</strong> {{ green.hedge.hedge_shape }}</p>
    </div>

    <div v-else-if="type === 'bush'" class="space-y-1">
      <p><strong>Кількість:</strong> {{ green.bush.quantity }}</p>
    </div>

    <div v-else-if="type === 'tree'" class="space-y-1">
      <p><strong>Висота:</strong> {{ green.tree.height_m }} м</p>
      <p><strong>Діаметр стовбура:</strong> {{ green.tree.trunk_diameter_cm }} см</p>
      <p><strong>Окружність стовбура:</strong> {{ green.tree.trunk_circumference_cm }} см</p>
      <p><strong>Нахил:</strong> {{ green.tree.tilt_degree }}°</p>
      <p><strong>Стан крони:</strong> {{ green.tree.crown_condition_percent }}%</p>
    </div>
  </div>
</template>
