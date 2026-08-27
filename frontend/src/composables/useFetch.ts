import { ref, onMounted } from 'vue'

import api from '@/services/api'

export interface FetchResult<T = unknown> {
  data: T | null
  error: string | null
  isLoading: boolean
  refresh: () => Promise<void>
}

export function useFetch<T = unknown>(url: string, options?: { headers?: Record<string, string> }): FetchResult<T> {
  const data = ref<T | null>(null)
  const error = ref<string | null>(null)
  const isLoading = ref(false)

  async function refresh(): Promise<void> {
    isLoading.value = true
    error.value = null

    try {
      const response = await api.get(url, {
        headers: {
          'Content-Type': 'application/json',
          ...options?.headers,
        },
      })

      data.value = response.data as T
    } catch (err: any) {
      error.value = err.response?.data?.message || err.message || 'Gagal memuat data'
    } finally {
      isLoading.value = false
    }
  }

  onMounted(() => {
    void refresh()
  })

  return {
    data: data.value as T,
    error: error.value,
    isLoading: isLoading.value,
    refresh,
  }
}
