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
      status.value = 'connected'
      data.value = result.data
      message.value = result.message
    } catch (error) {
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
