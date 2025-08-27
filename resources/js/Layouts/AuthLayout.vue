<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-[80vh] flex-col items-center bg-gray-100 sm:justify-center pt-6 md:pt-0"
    >
        <div>
            <Link href="/">
                <img 
                    src="/img/icons/logo-project-ver.jpg" 
                    class="w-64 rounded"
                />
            </Link>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
