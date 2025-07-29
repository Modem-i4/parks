<script setup>
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import DictTaxonomy from '@/Components/Dictionaries/DictTaxonomy.vue'
import DictInfrastructureType from '@/Components/Dictionaries/DictInfrastructureType.vue'
import { isMobile } from '@/Helpers/isMobileHelper'
import ArrowIcon from '@/Components/Custom/Icons/ArrowIcon.vue'
import DictTags from '@/Components/Dictionaries/DictTags.vue'
import DictRecommendations from '@/Components/Dictionaries/DictRecommendations.vue'
import DictHedgeShape from '@/Components/Dictionaries/DictHedgeShape.vue'
import DictHedgeRow from '@/Components/Dictionaries/DictHedgeRow.vue'

defineOptions({
  layout: AdminLayout,
})

const dictionaryItems = [
  {
    type: 'group',
    label: 'Класифікація насаджень',
    component: DictTaxonomy,
    children: [
      { slug: 'Trees', name: 'Дерева', props: { type: 'tree' } },
      { slug: 'Shrubs', name: 'Кущі', props: { type: 'bush' } },
      { slug: 'Flowers', name: 'Квіти', props: { type: 'flower' } },
      { slug: 'Hedges', name: 'Живоплоти', props: { type: 'hedge' } }
    ]
  },
  { type: 'item', slug: 'Infrastructure', name: 'Типи інфраструктури', component: DictInfrastructureType },
  { type: 'item', slug: 'ObjectState', name: 'Види рекомендацій', component: DictRecommendations },
  { type: 'item', slug: 'Tags', name: 'Теги', component: DictTags },
  {
    type: 'group',
    label: 'Живоплоти',
    children: [
      { slug: 'HedgeShape', name: 'Форма живоплоту', component: DictHedgeShape },
      { slug: 'HedgeRow', name: 'Тип ряду живоплоту', component: DictHedgeRow }
    ]
  }
]

// map dicts
const dictionaries = {}
dictionaryItems.forEach(entry => {
  if (entry.type === 'item') {
    dictionaries[entry.slug] = entry
  } else if (entry.type === 'group') {
    entry.children.forEach(child => {
      dictionaries[child.slug] = {
        ...child,
        component: child.component ?? entry.component ?? null,
        props: child.props || {}
      }
    })
  }
})

const selectedDictionarySlug = ref(null)
const showSidebar = ref(true)

function selectDictionary(slug) {
  selectedDictionarySlug.value = slug
  if (window.innerWidth < 768) {
    showSidebar.value = false
  }
}

function openSidebar() {
  showSidebar.value = true
}
</script>


<template>
  <div class="flex h-[calc(100vh-65px)] relative overflow-hidden">
    <!-- Sidebar -->
    <div
      class="w-full md:w-1/3 border-r bg-gray-50 p-4 overflow-y-auto transition-all duration-300"
      :class="{
        'absolute z-40 h-full': true,
        'left-0': showSidebar,
        '-left-full': !showSidebar,
        'md:relative md:left-0 md:block': true
      }"
    >
      <h1 class="text-xl font-bold leading-tight text-gray-800 m-3">Словники</h1>
      <ul class="space-y-2">
        <template v-for="(entry, index) in dictionaryItems" :key="index">
          <!-- Single dict -->
          <li
            v-if="entry.type === 'item'"
            class="cursor-pointer px-3 py-2 rounded hover:bg-gray-200"
            :class="{ 'bg-white shadow font-semibold': selectedDictionarySlug === entry.slug }"
            @click="selectDictionary(entry.slug)"
          >
            {{ entry.name }}
          </li>

          <!-- Dict group -->
          <li v-else>
            <div class="text-xs uppercase text-gray-500 px-3 pt-4 pb-1 tracking-wider">
              {{ entry.label }}
            </div>
            <ul class="pl-2 border-l border-gray-200 ml-2 space-y-1">
              <li
                v-for="child in entry.children"
                :key="child.slug"
                class="cursor-pointer px-3 py-2 rounded hover:bg-gray-100"
                :class="{ 'bg-white shadow font-semibold': selectedDictionarySlug === child.slug }"
                @click="selectDictionary(child.slug)"
              >
                {{ child.name }}
              </li>
            </ul>
          </li>
        </template>
      </ul>
    </div>

    <!-- Edit panel -->
    <div class="w-full md:w-2/3 p-2 md:p-4 overflow-y-scroll">
      <template v-if="selectedDictionarySlug !== null">
        <h1 class="text-xl font-bold leading-tight text-gray-800 m-3 mb-5 flex items-center" @click="openSidebar">
          <ArrowIcon v-if="isMobile" class="transform rotate-180" :size="8"/>
          Словник «{{ dictionaries[selectedDictionarySlug].name }}»
        </h1>
        <div class="border p-2 md:p-4 rounded bg-white shadow-sm">
          <component
            :is="dictionaries[selectedDictionarySlug].component"
            v-bind="dictionaries[selectedDictionarySlug].props || {}"
          />
        </div>
      </template>
      <template v-else>
        <div class="text-center text-gray-500 italic mt-20">
          Виберіть словник ліворуч для редагування
        </div>
      </template>
    </div>

    <!-- Mobile btn -->
    <div class="fixed bottom-4 right-4 z-20 md:hidden" v-if="!showSidebar">
      <button
        class="bg-white border px-4 py-2 rounded shadow"
        @click="openSidebar"
      >
        📚 Меню словників
      </button>
    </div>
  </div>
</template>
