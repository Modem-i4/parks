<template>
  <section class="flex flex-col md:flex-row max-w-7xl mx-auto px-4 py-10 gap-10">
    <div class="flex-1 flex justify-around">
      <div class="flex flex-col gap-6 justify-between">
        <div class="flex items-start">
          <img src="/img/icons/stats/pin.svg" alt="" class="w-10 h-10shrink-0" />
          <div class="ml-2">
            <p class="text-2xl font-extrabold">{{ Math.max(stats.parks, 3) }}</p>
            <p>парки</p>
          </div>
        </div>
        <div class="flex items-start gap-3">
          <img src="/img/icons/stats/fountain.svg" alt="" class="w-10 h-10shrink-0" />
          <div>
            <p class="text-2xl font-extrabold">{{ Math.max(stats.infrastructure, 15) }}</p>
            <p>елементів інфраструктури</p>
          </div>
        </div>
        <div class="flex items-start gap-3">
          <img src="/img/icons/stats/works.svg" alt="" class="w-10 h-10shrink-0" />
          <div>
            <p class="text-2xl font-extrabold">{{ Math.max(stats.works_done, 160) }}</p>
            <p>виконаних робіт</p>
          </div>
        </div>
      </div>
      <div class="flex flex-col gap-6 justify-between">
        <div class="flex items-start gap-3">
          <img src="/img/icons/stats/trees.svg" alt="" class="w-10 h-10shrink-0" />
          <div>
            <p class="text-2xl font-extrabold">{{ Math.max(stats.green_total, 100) }}</p>
            <p>всього насаджень</p>
          </div>
        </div>
        <div class="space-y-3">
            <div class="flex items-start gap-3">
            <span class="w-3 h-3 rounded-full bg-green-600 mt-2"></span>
            <div>
                <p class="text-2xl font-extrabold">{{ Math.max(stats.green_good, 100) }}</p>
                <p>відмінний стан</p>
            </div>
            </div>
            <div class="flex items-start gap-3">
            <span class="w-3 h-3 rounded-full bg-yellow-400 mt-2"></span>
            <div>
                <p class="text-2xl font-extrabold">{{ Math.max(stats.green_normal, 100) }}</p>
                <p>задовільний стан</p>
            </div>
            </div>
            <div class="flex items-start gap-3">
            <span class="w-3 h-3 rounded-full bg-red-600 mt-2"></span>
            <div>
                <p class="text-2xl font-extrabold">{{ Math.max(stats.green_bad, 15) }}</p>
                <p>незадовільний стан</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="flex-1 bg-gray-100 rounded-lg flex items-center justify-center">
      MAP
    </div>
  </section>
</template>

<script setup>
defineProps({
  stats: Object
})
</script>