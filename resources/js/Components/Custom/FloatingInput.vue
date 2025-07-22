<script setup>
defineProps({
  modelValue: String,
  label: String,
  labelClasses: {
    type: String,
    default: 'default'
  },
  inputClasses: {
    type: String,
    default: 'default'
  }
})
defineEmits(['update:modelValue'])
</script>

<template>
  <div class="relative p-1 pt-4 rounded">
    <label class="block relative" :class="labelClasses">
      <input
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        type="text"
        placeholder=" "
        class="block w-full appearance-none bg-transparent border-0 border-b-2 border-gray-300 focus:border-blue-600 text-sm py-1 peer focus:outline-none focus-visible:outline-none focus:ring-0 hover:cursor-text"
        :class="inputClasses"
      />
      <span
        class="absolute left-0 -top-3.5 text-gray-600 text-xs transition-all hover:cursor-text
               peer-placeholder-shown:text-sm peer-placeholder-shown:text-gray-400 peer-placeholder-shown:top-1
               peer-focus:-top-3.5 peer-focus:text-xs peer-focus:text-gray-600 pointer-events-none"
      >
        {{ label }}
      </span>
    </label>
  </div>
</template>
