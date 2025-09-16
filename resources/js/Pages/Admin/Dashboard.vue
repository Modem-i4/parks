<script setup>
import { ref, computed } from 'vue'
import Accordion from '@/Components/Custom/Accordion.vue'
import { useAuthStore } from '@/Stores/useAuthStore'
import { Head } from '@inertiajs/vue3'
import { UserRole } from '@/Helpers/UserRole'
import TutorialItem from '@/Components/Sections/Dashboard/TutorialItem.vue'

const authStore = useAuthStore()
const role = authStore.currentUser.role
const q = ref('')
const query = computed(() => q.value.trim().toLowerCase())
const isMatch = (text) => {
  const t = (text || '').toLowerCase()
  const qq = query.value
  return !qq || t.includes(qq)
}

const rolesDescriptions = {
  super_admin: 'Ви можете керувати адміністраторами та користувачами.',
  admin: 'Ви можете керувати користувачами та парками.',
  news_manager: 'Ви можете переглядати, додавати та редагувати новини. Також, робити будь-які операції з маркерами та роботами.',
  work_manager: 'Ви можете переглядати, додавати та редагувати роботи, а також здійснювати імпорт.',
  editor: 'Ви можете переглядати, додавати та редагувати маркери, фото й словники.',
  worker: 'Ви можете переглядати та виконувати призначені роботи.',
  viewer: 'Ви можете переглядати всі поля насаджень.',
}

const chaptersByCategory = {
  viewer: { // role filtering
    'intro-welcome': 'Вступ', // tutorial item
    'Парки': { // tutorial category
      'parks-overview': 'Огляд парків', // another tutorial item
      'parks-modes': 'Режими перегляду',
    },
    filters: 'Фільтри',
    'Маркери': {
      'markers-overview': 'Про маркери',
      'markers-add-delete': 'Додавання та видалення маркерів',
      'markers-edit': 'Редагування маркерів',
    },
    'media-add': 'Додавання зображень',
    dictionaries: 'Словники',
    'Роботи та рекомендації': {
      'works-add-complete': 'Додавання та виконання робіт',
      'works-section': 'Розділ робіт',
      'works-bulk': 'Групове призначення робіт',
    }
  },
  works_manager: {
    'Експорт та імпорт': {
      export: 'Експорт',
      import: 'Імпорт',
    }
  },
  news_manager: {
    news: 'Новини'
  },
  admin: {
    'Адміністрування': {
      roles: 'Керування ролями',
      audit: 'Аудит',
      backups: 'Резервне копіювання',
    }
  }
}

const filteredGroups = computed(() => {
  const merged = {}
  for (const [r, groupByRole] of Object.entries(chaptersByCategory)) {
    if (!authStore.atLeast(r)) continue
    for (const [catTitle, group] of Object.entries(groupByRole)) {
      if (typeof group === 'string') {
        if (!(catTitle in merged)) merged[catTitle] = group
      } else {
        if (!(catTitle in merged) || typeof merged[catTitle] === 'string') merged[catTitle] = {}
        Object.assign(merged[catTitle], group)
      }
    }
  }
  const out = []
  for (const [catTitle, group] of Object.entries(merged)) {
    if (typeof group === 'string') {
      if (isMatch(group) || isMatch(catTitle)) {
        out.push({ kind: 'chapter', chapter: { key: catTitle, title: group } })
      }
    } else {
      const matchCategory = isMatch(catTitle)
      const entries = Object.entries(group)
      const items = matchCategory
        ? entries.map(([key, title]) => ({ key, title }))
        : entries
            .filter(([, title]) => isMatch(title))
            .map(([key, title]) => ({ key, title }))
      if (items.length) out.push({ kind: 'category', catTitle, items })
    }
  }
  return out
})
</script>

<template>
  <Head title="Інструкції" />
  <div class="py-12">
    <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
      <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <div class="p-6 text-gray-900">
          <h1>Вітаємо у адмінпанелі сайту <strong>«Парки мають значення»</strong>.</h1>
          <hr />
          <p v-if="!authStore.can.view">Щоб отримати доступ до роботи з сайтом ви маєте отримати підтвердження від одного з адміністраторів.
            До того додаткові інструменти сайту та інструкції для вас недоступні.</p>

          <div class="font-bold flex py-2">
            <div class="p-2 ps-0">Ваша роль:</div>
            <div class="p-2 rounded-sm border">{{ UserRole.label(role) }}</div>
          </div>

          <template v-if="authStore.can.view">
            <p>{{ rolesDescriptions[role] }}</p>
            <hr />
            <p>
              Маєте питання? Можете зв'язатися із розробником за email:
              <a href="mailto:vasea.tshuh@gmail.com" class="underline">vasea.tshuh@gmail.com</a>
            </p>
            <p>
              Ви також можете переглянути 
              <a href="https://youtu.be/a828bxCefXQ" class="underline" target="_blank">відео</a> 
              з демонстрацією застосування платформи <i>(дещо застаріле)</i>
            </p>
            <hr />

            <h1>Інструкції</h1>
            <div class="flex flex-col items-center gap-2 mb-4">
              <div>Введіть запит, що вас цікавить</div>
              <input v-model="q" type="search" placeholder="Пошук..." class="w-full max-w-lg rounded-md border px-3 py-2" />
            </div>

            <div v-if="filteredGroups.length === 0" class="text-sm text-gray-500">Нічого не знайдено</div>

            <div
              v-for="node in filteredGroups"
              :key="node.kind === 'category' ? node.catTitle : node.chapter.key"
              class="space-y-2"
            >
              <TutorialItem
                v-if="node.kind === 'chapter'"
                :chapter="node.chapter"
              />
              <Accordion v-else>
                <template #head>{{ node.catTitle }}</template>
                <TutorialItem v-for="c in node.items" :key="c.key" :chapter="c" />
              </Accordion>
            </div>
          </template>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
h1 { 
  @apply font-semibold text-3xl mb-4 text-center 
}
p { 
  @apply mb-4 
}
hr { 
  @apply my-4 
}
</style>
