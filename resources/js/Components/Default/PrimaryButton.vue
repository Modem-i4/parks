<script setup>
const props = defineProps({
    customClasses: String
});
</script>
<template>
    <button
        class="inline-flex items-center rounded-md border border-transparent bg-gray-800 text-xs px-4 py-2 font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700 focus:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 active:bg-gray-900"
        :class="`${customClasses}`"
    >
        <slot />
    </button>
</template>
