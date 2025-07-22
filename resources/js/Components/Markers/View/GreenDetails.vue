<script setup>
defineProps({
  green: Object,
  type: String,
})
</script>

<template>
  <div v-if="green[type] && ['tree', 'bush', 'hedge'].includes(type)" class="text-gray-600 bg-white rounded px-4 py-6">
    <h3 class="text-lg font-semibold pb-2">Деталі насадження</h3>

    <div v-if="type === 'hedge'" class="space-y-1">
      <p><strong>Довжина:</strong> {{ green.hedge.length_m }} м</p>
      <p v-if="green.hedge.hedge_type_row"><strong>Тип ряду:</strong> {{ green.hedge.hedge_type_row }}</p>
      <p v-if="green.hedge.hedge_type_shape"><strong>Форма:</strong> {{ green.hedge.hedge_type_shape }}</p>
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
