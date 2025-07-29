import { ref } from 'vue'
import axios from 'axios'
import { handleLoading } from './handleLoading'

export function useCrudList(endpoint) {
  const items = ref([])
  const isLoading = ref(false)

  const load = async () => {
    await handleLoading(isLoading, async () => {
      const res = await axios.get(endpoint)
      items.value = res.data
    })
  }

  const handleCreate = async ({ data }) => {
    await handleLoading(isLoading, async () => {
      await axios.post(endpoint, data)
      await load()
    })
  }

  const handleUpdate = async ({ id, data }) => {
    await handleLoading(isLoading, async () => {
      await axios.patch(`${endpoint}/${id}`, data)
      await load()
    })
  }

  const handleDelete = async ({ id }) => {
    await handleLoading(isLoading, async () => {
      await axios.delete(`${endpoint}/${id}`)
      await load()
    })
  }

  return {
    items,
    isLoading,
    load,
    handleCreate,
    handleUpdate,
    handleDelete,
  }
}
