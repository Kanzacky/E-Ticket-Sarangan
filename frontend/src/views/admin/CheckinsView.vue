<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import api from '@/services/api'
import DataTable from '@/components/ui/DataTable.vue'
import Pagination from '@/components/ui/Pagination.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'

interface Checkin {
  id: number
  order_code: string
  is_valid: boolean
  reason: string | null
  created_at: string
  order: { order_code: string; customer_name: string; visit_date: string; total_quantity: number; status: string; items: string[] } | null
}

const logs = ref<Checkin[]>([])
const isLoading = ref(true)
const error = ref('')
const currentPage = ref(1)
const perPage = ref(10)
const total = ref(0)
const lastPage = ref(1)
const searchQuery = ref('')

const fetch = async () => {
  isLoading.value = true
  try {
    const params = new URLSearchParams()
    params.set('page', String(currentPage.value))
    params.set('per_page', String(perPage.value))
    if (searchQuery.value.trim()) params.set('search', searchQuery.value.trim())
    const res = await api.get('/admin/checkins?'+params.toString())
    if (res.data.success) if (res.data.meta) {
        logs.value = res.data.data
        total.value = res.data.meta.total
        lastPage.value = res.data.meta.last_page
        currentPage.value = res.data.meta.current_page
      } else {
        logs.value = res.data.data
        total.value = logs.value.length
        lastPage.value = 1
      }
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal memuat monitoring check-in'
  } finally { isLoading.value = false }
}

onMounted(fetch)

let searchTimer: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { currentPage.value = 1; void fetch() }, 400)
})
function handlePageChange(p: number) { if (p<1||p>lastPage.value) return; currentPage.value=p; void fetch() }

const formatDate = (s: string) => new Date(s).toLocaleString('id-ID')
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-black text-[#173B35]">Monitoring Check-in</h1>
      <p class="text-sm text-[#66706C] mt-1">Riwayat scan QR petugas (100 terakhir) — valid & ditolak</p>
    </div>
    <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">{{ error }}</div>
    <DataTable :headers="['Waktu','Order','Pengunjung','Valid','Alasan']" :is-loading="isLoading" :is-empty="logs.length===0" empty-message="Belum ada scan">
      <template #toolbar>
        <div class="relative w-full sm:w-72">
          <input v-model="searchQuery" type="text" placeholder="Cari..." class="w-full pl-9 pr-3 py-2 text-sm border border-[#E8E6DE] rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-[#173B35]" />
        </div>
      </template>
      <tr v-for="l in logs" :key="l.id" class="hover:bg-[#F7F5EF]/50">
        <td class="px-6 py-3 text-xs whitespace-nowrap">{{ formatDate(l.created_at) }}</td>
        <td class="px-6 py-3 text-sm font-bold">{{ l.order_code }}</td>
        <td class="px-6 py-3 text-sm">{{ l.order?.customer_name || '-' }} <span class="text-xs text-[#66706C]">({{ l.order?.items?.join(', ') || '-' }})</span></td>
        <td class="px-6 py-3"><StatusBadge :tone="l.is_valid ? 'success' : 'danger'">{{ l.is_valid ? 'Valid' : 'Tolak' }}</StatusBadge></td>
        <td class="px-6 py-3 text-xs max-w-xs truncate">{{ l.reason || '-' }}</td>
      </tr>
    </DataTable>
    <Pagination :current-page="currentPage" :last-page="lastPage" :total="total" :per-page="perPage" @page-change="handlePageChange" />
  </div>
</template>
