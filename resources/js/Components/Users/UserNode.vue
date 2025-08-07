<script setup>
import { ref } from 'vue'
import ArrowIcon from '@/Components/Custom/Icons/ArrowIcon.vue'
import UserItem from './UserItem.vue'
import { UserRole } from '@/Helpers/UserRole'

const props = defineProps({
  role: String,
  users: Array
})

const emit = defineEmits(['updateRole'])

const expanded = ref(true)
</script>

<template>
  <div class="space-y-1">
    <div
      class="flex items-center px-2 py-3 rounded cursor-pointer"
      :class="[expanded ? 'bg-gray-100' : 'bg-white border hover:bg-gray-200']"
      @click="expanded = !expanded"
    >
      <ArrowIcon
        :class="{ 'rotate-90': expanded }"
        class="transition-transform duration-200 w-4 h-4 text-gray-500 mr-2"
      />
      <span class="font-semibold text-sm text-gray-800 flex-1">
        {{ props.role === UserRole.GUEST ? 'Очікують на схвалення' : UserRole.label(props.role) }}
      </span>
      <span class="text-xs text-gray-600 me-2">
        {{ users.length }}
      </span>
    </div>

    <Transition name="accordion">
      <div v-if="expanded" class="md:ml-4 md:pl-2 md:border-l border-gray-300 space-y-1">
        <UserItem
          v-for="user in users"
          :key="user.id"
          :user="user"
          @updateRole="emit('updateRole', $event)"
        />
      </div>
    </Transition>
  </div>
</template>

<style scoped>
@import '@/../css/assets/accordion.css';
</style>
