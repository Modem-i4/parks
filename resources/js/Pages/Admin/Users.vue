<script setup>
import UsersList from '@/Components/Users/UsersList.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    users: Array
})
</script>

<template>
    <Head title="Користувачі" />
    <div class="max-w-5xl mx-auto mt-6 bg-white rounded-xl shadow p-6">
        <UsersList :users="users" />
    </div>
</template>
