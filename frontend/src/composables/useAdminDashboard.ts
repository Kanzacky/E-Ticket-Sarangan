import { ref, onMounted } from 'vue'
import api from '@/services/api'

export function useAdminDashboard() {
  const isLoading = ref(true)
  const error = ref<string | null>(null)
  const summary = ref({
    revenue: 0,
    orders: 0,
    tickets: 0,
    visitors: 0
  })
  const recentOrders = ref<any[]>([])
  const userInsights = ref({
    new_today: 0,
    new_month: 0,
    active_sessions: 0,
    total_users: 0
  })

  onMounted(async () => {
    try {
      isLoading.value = true
      const response = await api.get('/admin/dashboard')
      const data = response.data
      
      if (data.success) {
        summary.value = data.data.summary
        recentOrders.value = data.data.recent_orders
        if (data.data.user_insights) {
          userInsights.value = data.data.user_insights
        }
      } else {
        error.value = data.message || 'Gagal memuat dashboard'
      }
    } catch (err: any) {
      error.value = err.response?.data?.message || 'Koneksi gagal'
    } finally {
      isLoading.value = false
    }
  })

  return {
    isLoading,
    error,
    summary,
    recentOrders,
    userInsights
  }
}