import { onMounted, ref } from 'vue'

import { getHealth, type HealthData } from '@/services/api'

export type ApiStatus = 'checking' | 'connected' | 'disconnected'

export function useApiHealth() {
  const status = ref<ApiStatus>('checking')
  const data = ref<HealthData | null>(null)
  const message = ref('')

  async function check(): Promise<void> {
    status.value = 'checking'

    try {
      const result = await getHealth()

      // Status "Terhubung" ditentukan dari HTTP 2xx (axios resolve),
      // BUKAN dari bentuk JSON response (mis. field `success`).
      status.value = 'connected'
      data.value = result?.data ?? null
      message.value = result?.message ?? ''
    } catch (error) {
      // Masuk ke sini HANYA jika request benar-benar gagal:
      // non-2xx, timeout, atau CORS/network error (mis. "Network Error").
      status.value = 'disconnected'
      message.value = error instanceof Error ? error.message : 'Unknown error'
    }
  }

  onMounted(() => {
    void check()
  })

  return {
    status,
    data,
    message,
    check,
  }
}
