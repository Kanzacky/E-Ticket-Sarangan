import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { usePolling } from './usePolling'

export function usePetugasDashboard() {
  const isLoading = ref(true)
  const isRefreshing = ref(false)
  const error = ref<string | null>(null)
  const lastUpdated = ref<Date | null>(null)
  const summary = ref({
    kunjungan_hari_ini: 0,
    diverifikasi: 0,
    menunggu: 0,
    bermasalah: 0,
  })
  const recentVisits = ref<any[]>([])

  async function fetchDashboard() {
    if (isLoading.value === false) {
      isRefreshing.value = true
    }

    try {
      const response = await api.get('/petugas/dashboard')
      const data = response.data

      if (data.success) {
        summary.value = data.data.summary
        recentVisits.value = data.data.recent_visits
        error.value = null
        lastUpdated.value = new Date()
      } else {
        error.value = data.message || 'Gagal memuat dashboard petugas'
      }
    } catch (err: any) {
      if (isLoading.value) {
        error.value = err.response?.data?.message || 'Koneksi gagal'
      }
    } finally {
      isLoading.value = false
      isRefreshing.value = false
    }
  }

  onMounted(() => void fetchDashboard())

  // Polling setiap 10 detik (petugas butuh update lebih cepat)
  const { isPolling } = usePolling(fetchDashboard, 10_000)

  return {
    isLoading,
    isRefreshing,
    isPolling,
    error,
    summary,
    recentVisits,
    lastUpdated,
    refresh: fetchDashboard,
  }
}
