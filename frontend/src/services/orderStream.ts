import { useAuthStore } from '@/stores/auth'

const apiBaseUrl = import.meta.env.VITE_API_URL || '/api'

/**
 * Buka stream SSE status sebuah order (mis. status tiket menjadi COMPLETED
 * setelah dipindai petugas). Mengembalikan fungsi untuk menutup stream.
 * Jika stream gagal dibuka, cukup abaikan — pemanggil tetap punya polling
 * sebagai fallback.
 */
export function watchOrderStream(
  orderCode: string,
  onUpdate: (status: string, order?: unknown) => void,
): () => void {
  const authStore = useAuthStore()
  const controller = new AbortController()

  ;(async () => {
    try {
      const res = await fetch(`${apiBaseUrl}/orders/${orderCode}/stream`, {
        headers: {
          Accept: 'text/event-stream',
          ...(authStore.token ? { Authorization: `Bearer ${authStore.token}` } : {}),
        },
        signal: controller.signal,
      })
      if (!res.ok || !res.body) return

      const reader = res.body.getReader()
      const decoder = new TextDecoder()
      let buffer = ''

      for (;;) {
        const { done, value } = await reader.read()
        if (done) break
        buffer += decoder.decode(value, { stream: true })
        const events = buffer.split('\n\n')
        buffer = events.pop() ?? ''
        for (const ev of events) {
          const line = ev.split('\n').find((l) => l.startsWith('data:'))
          if (!line) continue
          try {
            const payload = JSON.parse(line.slice(5).trim())
            if (payload.status) onUpdate(payload.status, payload.order)
          } catch {
            // abaikan payload rusak
          }
        }
      }
    } catch {
      // Stream gagal atau ditutup — fallback polling tetap jalan
    }
  })()

  return () => controller.abort()
}
