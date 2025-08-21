<script setup>
const props = defineProps({
  coverImg: { type: String, default: '' },
})
</script>

<template>
  <div
    class="relative w-full rounded overflow-hidden transition-[height] duration-200"
    :class="props.coverImg ? 'h-64 md:h-80 shadow' : 'h-auto'"
    :style="props.coverImg
      ? {
          backgroundImage: `url(${props.coverImg})`,
          backgroundSize: 'cover',
          backgroundPosition: 'center'
        }
      : {}"
  >
    <div v-if="props.coverImg" class="absolute inset-0 bg-black/40" />
    <div class="relative max-w-7xl mx-auto h-full">
      <div class="relative p-4 flex items-center justify-center h-full">
        <slot />
      </div>
      <slot name="badge" />
    </div>
  </div>
</template>
