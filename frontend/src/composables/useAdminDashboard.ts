import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'

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

  onMounted(async () => {
    const authStore = useAuthStore()
    try {
      isLoading.value = true
      const response = await fetch('/api/admin/dashboard', {
        headers: {
          'Authorization': `Bearer ${authStore.token}`
        }
      })
      const data = await response.json()
      
      if (response.ok) {
        summary.value = data.data.summary
        recentOrders.value = data.data.recent_orders
      } else {
        error.value = data.message || 'Gagal memuat dashboard'
      }
    } catch (err) {
      error.value = 'Koneksi gagal'
    } finally {
      isLoading.value = false
    }
  })

  return {
    isLoading,
    error,
    summary,
    recentOrders
  }
}