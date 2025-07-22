<template>
  <div class="flex w-full overflow-hidden rounded-lg border">
    <button
      v-for="option in options"
      :key="option.value"
      :class="[
        'flex-1 py-2 text-sm font-semibold transition-colors',
        isSelected(option.value) ? option.activeClass : option.baseClass,
        'focus:outline-none'
      ]"
      @click="toggleOption(option.value)"
    >
      {{ option.label }}
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  modelValue: [String, Array],
  multiple: Boolean
})
const emit = defineEmits(['update:modelValue'])

const isSelected = (val) => {
  if (props.multiple) return Array.isArray(props.modelValue) && props.modelValue.includes(val)
  return props.modelValue === val
}

function toggleOption(val) {
  if (props.multiple) {
    const current = Array.isArray(props.modelValue) ? [...props.modelValue] : []
    const index = current.indexOf(val)
    if (index >= 0) {
      current.splice(index, 1)
    } else {
      current.push(val)
    }
    emit('update:modelValue', current)
  } else {
    emit('update:modelValue', val)
  }
}

const options = [
  {
    value: 'bad',
    label: 'Поганий',
    baseClass: 'bg-red-100 text-red-700',
    activeClass: 'bg-red-500 text-white',
  },
  {
    value: 'normal',
    label: 'Задовільний',
    baseClass: 'bg-yellow-100 text-yellow-800',
    activeClass: 'bg-yellow-400 text-black',
  },
  {
    value: 'good',
    label: 'Добрий',
    baseClass: 'bg-green-100 text-green-700',
    activeClass: 'bg-green-500 text-white',
  }
]
</script>
