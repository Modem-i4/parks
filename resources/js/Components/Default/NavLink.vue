<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  href: {
    type: String,
    required: true,
  },
  active: {
    type: Boolean,
  },
})

const classes = computed(() =>
  [
    'relative inline-flex items-center px-1 pt-1 text-lg font-bold leading-5 transition duration-300 ease-in-out',
    props.active
      ? 'text-[#007C57] after:w-full'
      : 'hover:text-[#007C57] after:w-0 hover:after:w-full',
    'after:absolute after:left-0 after:-bottom-3 after:h-[5px] after:bg-[#007C57] after:transition-all after:duration-300',
  ]
)
</script>

<template>
  <Link :href="href" :class="classes">
    <slot />
  </Link>
</template>
