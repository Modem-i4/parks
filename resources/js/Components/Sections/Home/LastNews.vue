<template>
    <section class="max-w-7xl mx-auto px-4 py-10 flex flex-col gap-y-8">
        <h1 class="text-center text-2xl md:text-4xl font-bold">ОСТАННІ НОВИНИ</h1>
        
        <div class="grid gap-12 sm:grid-cols-2 lg:grid-cols-3">
            <NewsCard
                v-for="item in news"
                :key="item.id"
                :post="item"
            />
        </div>

        <div class="flex justify-center">
            <Link
                href="/news"
                class="bg-[#007C57] text-white font-bold text-lg px-10 py-4 rounded-md hover:bg-[#006347] transition"
            >
                ВСІ НОВИНИ
            </Link>
        </div>
    </section>
</template>


<script setup>
import NewsCard from '@/Components/News/NewsCard.vue';
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  news: Array
})
</script>