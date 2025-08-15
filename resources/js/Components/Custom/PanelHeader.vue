<template>
  <div class="flex items-center p-4 space-x-4">
    <div
      class="flex-shrink-0 rounded-full overflow-hidden relative cursor-pointer flex" :class="[variants[variant].iconWrapperClasses, `bg-${iconBg}`]"
      @click="$emit('onIconClick')"
    >
      <img v-if="icon" :src="icon" alt="Icon" class="object-contain mx-auto my-auto" :class="variants[variant].iconClasses" />
      <div v-else class="text-xl flex items-center justify-center h-full w-full">{{ fallbackEmoji }}</div>
      <div
        v-if="editable"
        class="absolute bottom-0 left-0 right-0 h-4 bg-black/50 text-white text-sm flex items-center justify-center pointer-events-none"
      >+</div>
    </div>

    <div>
      <h3 class="text-gray-900" :class="variants[variant].titleTextClasses">{{ title }}</h3>
      <p v-if="subtitle" class="text-sm text-gray-500">{{ subtitle }}</p>
    </div>

    <div class="flex-1 flex justify-end">
      <slot name="right" />
    </div>
  </div>
</template>


<script setup>
defineProps({
  title: String,
  subtitle: String,
  icon: String,
  fallbackEmoji: {
    type: String,
    default: '🌳',
  },
  editable: {
    type: Boolean,
    default: false,
  },
  variant: {
    type: String,
    default: 'md' // | 'sm'
  },
  iconBg: {
    type: String,
    default: 'white'
  }
})

const variants = {
  md: {
    titleTextClasses: 'text-lg font-semibold',
    iconWrapperClasses: 'w-16 h-16',
    iconClasses:'w-12 h-12'
  }, 
  sm: {

  }
}

const emit = defineEmits(['onIconClick'])
</script>
