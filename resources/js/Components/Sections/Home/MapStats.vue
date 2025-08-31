<template>
  <section class="flex flex-col md:flex-row max-w-7xl mx-auto px-4 py-10 gap-10 items-center">
    <div class="flex-1 flex flex-col">
      <h3 class="text-3xl md:text-4xl font-extrabold uppercase tracking-wide text-[#007C57] mb-12 text-center">
        Статистика
      </h3>
      <div class="flex justify-around max-h-[400px]">
        <div class="flex flex-col gap-6 justify-between">
          <div class="space-y-3">
            <div
              v-for="(item, i) in stateStats"
              :key="i"
              class="flex items-start gap-3"
            >
              <component
                :is="item.icon ? 'img' : 'span'"
                v-bind="item.icon
                  ? { src: item.icon, alt: item.alt, class: 'w-10 h-10 shrink-0' }
                  : { class: `w-3 h-3 rounded-full ${item.color} mt-2` }"
              />
              <div>
                <p class="text-2xl font-extrabold">{{ Math.max(stats[item.key], item.min) }}</p>
                <p>{{ item.label }}</p>
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col gap-10 justify-around">
          <div class="space-y-3">
            <div
              v-for="(item, i) in typeStats"
              :key="i"
              class="flex items-start gap-3"
            >
              <img :src="item.icon" :alt="item.alt" class="w-8 h-8" />
              <div>
                <p class="text-2xl font-extrabold">{{ Math.max(stats[item.key], item.min) }}</p>
                <p>{{ item.label }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="flex-1 bg-gray-100 rounded-lg flex items-center justify-center">
      <slot />
    </div>
  </section>
</template>

<script setup>
defineProps({ stats: Object })

const stateStats = [
  { key: 'green_total', min: 197, label: 'всього насаджень', icon: '/img/icons/stats/trees.svg', alt: 'trees' },
  { key: 'green_good',  min: 118, label: 'відмінний стан',    color: 'bg-green-600' },
  { key: 'green_normal',min: 62, label: 'задовільний стан',  color: 'bg-yellow-400' },
  { key: 'green_bad',   min: 17,  label: 'незадовільний стан',color: 'bg-red-600' },
]

const typeStats = [
  { key: 'trees',   min: 91, label: 'дерев',      icon: '/img/icons/split-markers/trees-map_icon.svg',   alt: 'trees' },
  { key: 'bushes',  min: 70, label: 'кущів',      icon: '/img/icons/split-markers/bushes-map_icon.svg',  alt: 'bushes' },
  { key: 'hedges',  min: 12,  label: 'живоплотів', icon: '/img/icons/split-markers/hedges-map_icon.svg',  alt: 'hedges' },
  { key: 'flowers', min: 24,  label: 'квітників',  icon: '/img/icons/split-markers/flowers-map_icon.svg', alt: 'flowers' },
]
</script>
