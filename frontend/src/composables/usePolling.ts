import { ref, onMounted, onUnmounted } from 'vue'

/**
 * usePolling — jalankan fungsi fetch secara berkala (polling cerdas).
 *
 * Fitur:
 * - Stop otomatis saat tab/window tidak aktif (visibilitychange) → hemat bandwidth
 * - Resume otomatis saat user kembali ke tab
 * - Hanya poll saat tidak ada request yang sedang berjalan
 * - Cleanup otomatis saat komponen di-unmount
 *
 * @param fetchFn  Fungsi async yang dipanggil setiap interval
 * @param interval Interval dalam milidetik (default: 30.000 = 30 detik)
 */
export function usePolling(fetchFn: () => Promise<void>, interval = 30_000) {
  const isPolling = ref(false)
  let timerId: ReturnType<typeof setInterval> | null = null
  let isFetching = false

  async function poll() {
    if (isFetching) return
    isFetching = true
    try {
      await fetchFn()
    } finally {
      isFetching = false
    }
  }

  function start() {
    if (timerId) return
    isPolling.value = true
    timerId = setInterval(() => void poll(), interval)
  }

  function stop() {
    if (timerId) {
      clearInterval(timerId)
      timerId = null
    }
    isPolling.value = false
  }

  // Pause saat tab tidak terlihat, resume saat kembali
  function handleVisibility() {
    if (document.hidden) {
      stop()
    } else {
      void poll() // langsung fetch saat kembali
      start()
    }
  }

  onMounted(() => {
    document.addEventListener('visibilitychange', handleVisibility)
    start()
  })

  onUnmounted(() => {
    stop()
    document.removeEventListener('visibilitychange', handleVisibility)
  })

  return { isPolling, start, stop, poll }
}
