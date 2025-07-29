<script setup>
import { useCrudList } from '@/Helpers/Dictionaries/useCrudList'
import { useSearchFilter } from '@/Helpers/Dictionaries/useSearchFilter'
import TagNode from '../Tags/TagNode.vue'
import LoadingLineIndicator from '@/Components/Custom/LoadingLineIndicator.vue'
import { computed, onMounted } from 'vue'
import BasicAddForm from '../Custom/BasicAddForm.vue'

const {
  items: tags,
  isLoading,
  load,
  handleCreate,
  handleUpdate,
  handleDelete
} = useCrudList('/api/tags')

const {
  query: searchQuery,
  filtered: filteredTags
} = useSearchFilter(tags)

const props = defineProps({ type: String })
const emit = defineEmits(['selectTag'])

function isNodeExpanded(type) {
  return type === 'all' || type === props.type
}

const groupedTags = computed(() => {
  const map = {}
  for (const tag of filteredTags.value) {
    if (!map[tag.type]) map[tag.type] = []
    map[tag.type].push(tag)
  }
  return map
})

onMounted(load)
</script>

<template>
  <div class="space-y-4 pb-3 relative">
    <div class="sticky top-0 z-10 bg-white">
      <LoadingLineIndicator :isLoading />
      <input
        v-model="searchQuery"
        type="text"
        placeholder="Пошук..."
        class="w-full px-3 py-2 border rounded"
      />
    </div>
    <BasicAddForm
      label="тег"
      :fields="['name', 'type']"
      :defaultForm="{ name: '', type: 'all' }"
      :typeOptions="{
        'tree': 'для дерев',
        'bush': 'для кущів',
        'hedge': 'для живоплотів',
        'flower': 'для квіток',
        'infrastructure': 'інфраструктурний',
        'all': 'спільний'
      }"
      @create="handleCreate"
    />
    <div v-for="(tags, type) in groupedTags" :key="type">
      <TagNode
        :type="type"
        :tags="tags"
        :expanded="isNodeExpanded(type)"
        @create="handleCreate"
        @update="handleUpdate"
        @delete="handleDelete"
        @selectTag="emit('selectTag', $event)"
      />
    </div>
  </div>
</template>
