<script setup>
defineProps({
  details: Object,
  type: String,
})
</script>

<template>
  <div v-if="details" class="text-gray-600 bg-white rounded px-4 py-6">
    <h3 class="text-lg font-semibold pb-2">Деталі насадження</h3>

    <div v-if="type === 'hedge'" class="space-y-1">
      <p><strong>Довжина:</strong> {{ details.length_m }} м</p>
      <p v-if="details.hedge_type_row"><strong>Тип ряду:</strong> {{ details.hedge_type_row }}</p>
      <p v-if="details.hedge_type_shape"><strong>Форма:</strong> {{ details.hedge_type_shape }}</p>
    </div>

    <div v-else-if="type === 'bush'" class="space-y-1">
      <p><strong>Кількість:</strong> {{ details.quantity }}</p>
    </div>

    <div v-else-if="type === 'tree'" class="space-y-1">
      <p><strong>Висота:</strong> {{ details.height_m }} м</p>
      <p><strong>Діаметр стовбура:</strong> {{ details.trunk_diameter_cm }} см</p>
      <p><strong>Окружність стовбура:</strong> {{ details.trunk_circumference_cm }} см</p>
      <p><strong>Нахил:</strong> {{ details.tilt_degree }}°</p>
      <p><strong>Стан крони:</strong> {{ details.crown_condition_percent }}%</p>
    </div>
  </div>
</template>
