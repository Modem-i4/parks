<script setup>
import { ref, computed, watch } from 'vue'
import TaxonAddForm from '@/Components/Taxonomy/TaxonAddForm.vue'
import TaxonUpdateDeleteForm from '@/Components/Taxonomy/TaxonUpdateDeleteForm.vue'
import TaxonItem from '@/Components/Taxonomy/TaxonItem.vue'

const props = defineProps({
  item: Object,
  level: String // 'family' | 'genus' | 'species'
})

const expanded = ref(props.item.expanded || false)
watch(() => props.item.expanded, val => {expanded.value = val})
const toggle = () => (expanded.value = !expanded.value)
const emit = defineEmits(['create', 'update', 'delete', 'changeGallery'])

const children = computed(() =>
  props.level === 'family' ? props.item.genus :
  props.level === 'genus' ? props.item.species : []
)

const nextLevel = computed(() =>
  props.level === 'family' ? 'genus' :
  props.level === 'genus' ? 'species' : null
)
</script>

<template>
  <div class="space-y-2">
    <TaxonUpdateDeleteForm
      :level
      :nextLevel
      :item
      :expanded="expanded"
      @toggle="toggle"
      @update="$emit('update', $event)"
      @delete="$emit('delete', $event)"
      @changeGallery="$emit('changeGallery', $event)"
    />

    <Transition name="accordion">
      <div
        v-if="expanded && level !== 'species'"
        class="ml-4 pl-2 border-l border-gray-300 space-y-2"
      >
        <TaxonAddForm
          v-if="level !== 'species'"
          :level
          :nextLevel
          :item
          @create="$emit('create', $event)"
        />

        <div v-if="children.length" class="space-y-1">
          <TaxonItem
            v-for="child in children"
            :key="child.id"
            :item="child"
            :level="nextLevel"
            @create="$emit('create', $event)"
            @update="$emit('update', $event)"
            @delete="$emit('delete', $event)"
            @changeGallery="$emit('changeGallery', $event)"
          />
        </div>
      </div>
    </Transition>
  </div>
</template>

<style scoped>
@import '@/../css/assets/accordion.css';
</style>
