<script setup>
import { computed } from 'vue'
import { getColorByGreenState } from '@/Helpers/Maps/CreateSimpleIcon'
import allIcon from '@/assets/markers/all-map_icon.svg?raw'

const stateItems = computed(() => ([
  { key: 'good',    label: 'Добрий стан', color: getColorByGreenState('good') },
  { key: 'normal',  label: 'Задовільний стан', color: getColorByGreenState('normal') },
  { key: 'bad',     label: 'Незадовільний стан', color: getColorByGreenState('bad') },
  { key: 'planned', label: 'Заплановано висадку', color: getColorByGreenState('planned') },
  { key: 'removed', label: 'Видалено', color: getColorByGreenState('removed') },
]))

const typeItems = [
  { key: 'trees',   label: 'Дерева', icon: '/img/icons/split-markers/trees-map_icon.svg' },
  { key: 'bushes',  label: 'Кущі', icon: '/img/icons/split-markers/bushes-map_icon.svg' },
  { key: 'flowers', label: 'Квітники', icon: '/img/icons/split-markers/flowers-map_icon.svg' },
  { key: 'hedges',  label: 'Живоплоти', icon: '/img/icons/split-markers/hedges-map_icon.svg' },
]

const infraItems = [
  { key: 'wc',        label: 'Туалет', icon: '/img/icons/infrastructure-types/wc.svg' },
  { key: 'parenting', label: 'Кімната матері та дитини', icon: '/img/icons/infrastructure-types/parenting.svg' },
  { key: 'slide',     label: 'Дитячий майданчик', icon: '/img/icons/infrastructure-types/slide.svg' },
  { key: 'stadium',   label: 'Спортивний майданчик', icon: '/img/icons/infrastructure-types/stadium.svg' },
  { key: 'fountain',  label: 'Фонтан', icon: '/img/icons/infrastructure-types/fountain.svg' },
  { key: 'statue',    label: 'Пам’ятник', icon: '/img/icons/infrastructure-types/statue.svg' },
]
</script>

<template>
  <div class="w-full space-y-2">
    <section class="rounded-2xl border bg-white p-4">
      <h3 class="mb-3 text-base font-semibold">Стани насаджень</h3>

      <ul class="grid grid-flow-col grid-rows-3 gap-x-6 gap-y-2">
        <li
          v-for="s in stateItems"
          :key="s.key"
          class="flex items-center gap-3"
        >
          <span
            class="shrink-0 w-[25px] h-[25px]"
            v-html="allIcon"
            :style="{ color: s.color }"
            :aria-label="s.label"
          />
          <span class="text-sm">{{ s.label }}</span>
        </li>
      </ul>
    </section>

    <section class="rounded-2xl border bg-white p-4">
      <h3 class="mb-3 text-base font-semibold">Типи насаджень</h3>
      <ul class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <li
          v-for="t in typeItems"
          :key="t.key"
          class="flex flex-col items-center gap-2 rounded-xl border p-3"
        >
          <img :src="t.icon" :alt="t.label" class="w-10 h-10" loading="lazy" />
          <span class="text-xs text-center">{{ t.label }}</span>
        </li>
      </ul>
    </section>

    <section class="rounded-2xl border bg-white p-4">
      <h3 class="mb-3 text-base font-semibold">Види інфраструктури</h3>
      <ul class="grid grid-cols-2 sm:grid-cols-3 gap-4">
        <li
          v-for="i in infraItems"
          :key="i.key"
          class="flex items-center gap-3 rounded-xl border p-3"
        >
          <img :src="i.icon" :alt="i.label" class="w-8 h-8 shrink-0" loading="lazy" />
          <span class="text-sm">{{ i.label }}</span>
        </li>
      </ul>
    </section>
  </div>
</template>
