import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { usePolling } from './usePolling'

export function useAdminDashboard() {
  const isLoading = ref(true)
  const isRefreshing = ref(false)
  const error = ref<string | null>(null)
  const lastUpdated = ref<Date | null>(null)
  const summary = ref({
    revenue: 0,
    orders: 0,
    tickets: 0,
    visitors: 0,
  })
  const recentOrders = ref<any[]>([])
  const userInsights = ref({
    new_today: 0,
    new_month: 0,
    active_sessions: 0,
    total_users: 0,
  })

  async function fetchDashboard() {
    // Saat load pertama: tampilkan skeleton. Setelah itu: silent refresh.
    if (isLoading.value === false) {
      isRefreshing.value = true
    }

    try {
      const response = await api.get('/admin/dashboard')
      const data = response.data

      if (data.success) {
        summary.value = data.data.summary
        recentOrders.value = data.data.recent_orders
        if (data.data.user_insights) {
          userInsights.value = data.data.user_insights
        }
        error.value = null
        lastUpdated.value = new Date()
      } else {
        error.value = data.message || 'Gagal memuat dashboard'
      }
    } catch (err: any) {
      // Jangan hapus data lama saat polling gagal — tetap tampilkan data sebelumnya
      if (isLoading.value) {
        error.value = err.response?.data?.message || 'Koneksi gagal'
      }
    } finally {
      isLoading.value = false
      isRefreshing.value = false
    }
  }

  onMounted(() => void fetchDashboard())

  // Polling setiap 30 detik — pause otomatis saat tab tidak aktif
  const { isPolling } = usePolling(fetchDashboard, 30_000)

  return {
    isLoading,
    isRefreshing,
    isPolling,
    error,
    summary,
    recentOrders,
    userInsights,
    lastUpdated,
    refresh: fetchDashboard,
  }
}