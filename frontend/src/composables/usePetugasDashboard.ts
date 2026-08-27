import { ref, onMounted } from 'vue'
import api from '@/services/api'

export function usePetugasDashboard() {
  const isLoading = ref(true)
  const error = ref<string | null>(null)
  const summary = ref({
    kunjungan_hari_ini: 0,
    diverifikasi: 0,
    menunggu: 0,
    bermasalah: 0
  })
  const recentVisits = ref<any[]>([])

  onMounted(async () => {
    try {
      isLoading.value = true
      const response = await api.get('/petugas/dashboard')
      const data = response.data
      
      if (data.success) {
        summary.value = data.data.summary
        recentVisits.value = data.data.recent_visits
      } else {
        error.value = data.message || 'Gagal memuat dashboard petugas'
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
    recentVisits
  }
}
