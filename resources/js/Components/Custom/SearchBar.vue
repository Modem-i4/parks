<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Що вас цікавить?' },
  autofocus: { type: Boolean, default: false },
})

const emit = defineEmits(['update:modelValue', 'search'])

const q = ref(props.modelValue)
const inputEl = ref(null)

function onInput(e) {
  q.value = e.target.value
  emit('update:modelValue', q.value)
}

function onSubmit() {
  emit('search', q.value)
}

onMounted(() => {
  if (props.autofocus) inputEl.value?.focus()
})
</script>

<template>
    <form role="search" @submit.prevent="onSubmit" class="w-full">
        <div
            class="flex w-full rounded-md border border-gray-300 bg-white overflow-hidden focus-within:ring-2 focus-within:ring-emerald-500"
        >
            <input
                id="search-input"
                ref="inputEl"
                type="search"
                :value="q"
                @input="onInput"
                :placeholder="placeholder"
                class="flex-1 h-12 md:h-14 px-4 text-gray-800 placeholder-gray-400 outline-none border-none"
            />

            <button
                type="submit"
                class="h-12 md:h-14 w-14 md:w-16 shrink-0 bg-emerald-700 hover:bg-emerald-800 text-white grid place-items-center"
                aria-label="Шукати"
                title="Шукати"
            >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7"/>
                <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            </button>
        </div>
    </form>
</template>

<style scoped>
input[type="search"]::-webkit-search-cancel-button {
  -webkit-appearance: none;
  appearance: none;
  display: none;
}
</style>