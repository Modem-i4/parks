<script setup>
import UsersList from '@/Components/Users/UsersList.vue';

defineProps({
    users: Array
})
</script>

<template>
    <div class="max-w-5xl mx-auto mt-6 bg-white rounded-xl shadow p-6">
        <UsersList :users="users" />
    </div>
</template>
