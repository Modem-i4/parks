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
                    src="/img/icons/logo-parks-matter.png" 
                    class="w-48"
                />
            </Link>
            <a href="https://pl-ua.eu/ua/" target="_blank">
                <img 
                    src="/img/icons/logo-interreg.png" 
                    class="w-48 mt-2"
                />
            </a>
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg"
        >
            <slot />
        </div>
    </div>
</template>
