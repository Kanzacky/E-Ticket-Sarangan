<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import api from '@/services/api'
import DataTable from '@/components/ui/DataTable.vue'
import Pagination from '@/components/ui/Pagination.vue'

interface AuditLog {
  id: number
  user_id: number | null
  action: string
  model_type: string | null
  model_id: number | null
  old_values: any
  new_values: any
  ip_address: string | null
  user_agent: string | null
  created_at: string
  user: { id: number; name: string; email: string; role: string } | null
}

const logs = ref<AuditLog[]>([])
const isLoading = ref(true)
const error = ref('')
const currentPage = ref(1)
const perPage = ref(10)
const total = ref(0)
const lastPage = ref(1)
const searchQuery = ref('')

const fetchLogs = async () => {
  isLoading.value = true
  try {
    const params = new URLSearchParams()
    params.set('page', String(currentPage.value))
    params.set('per_page', String(perPage.value))
    if (searchQuery.value.trim()) params.set('search', searchQuery.value.trim())
    const res = await api.get('/admin/audit-logs?'+params.toString())
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
    error.value = e.response?.data?.message || 'Gagal memuat audit logs'
  } finally { isLoading.value = false }
}

onMounted(fetchLogs)

let searchTimer: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { currentPage.value = 1; void fetchLogs() }, 400)
})
function handlePageChange(p: number) { if (p<1||p>lastPage.value) return; currentPage.value=p; void fetchLogs() }


const formatDate = (s: string) => new Date(s).toLocaleString('id-ID')
</script>

<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-black text-[#173B35]">Audit Logs</h1>
      <p class="text-sm text-[#66706C] mt-1">Riwayat aktivitas admin & sistem (100 terakhir)</p>
    </div>

    <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">{{ error }}</div>

    <DataTable :headers="['Waktu','User','Action','Model','IP']" :is-loading="isLoading" :is-empty="logs.length===0" empty-message="Belum ada audit log">
      <template #toolbar>
        <div class="relative w-full sm:w-72">
          <input v-model="searchQuery" type="text" placeholder="Cari..." class="w-full pl-9 pr-3 py-2 text-sm border border-[#E8E6DE] rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-[#173B35]" />
        </div>
      </template>
      <tr v-for="log in logs" :key="log.id" class="hover:bg-[#F7F5EF]/50">
        <td class="px-6 py-3 text-xs whitespace-nowrap">{{ formatDate(log.created_at) }}</td>
        <td class="px-6 py-3 text-sm"><div class="font-medium">{{ log.user?.name || '-' }}</div><div class="text-xs text-[#66706C]">{{ log.user?.email || '' }}</div></td>
        <td class="px-6 py-3 text-xs font-bold">{{ log.action }}</td>
        <td class="px-6 py-3 text-xs">{{ log.model_type || '-' }} <span v-if="log.model_id">#{{ log.model_id }}</span></td>
        <td class="px-6 py-3 text-xs">{{ log.ip_address || '-' }}</td>
      </tr>
    </DataTable>
    <Pagination :current-page="currentPage" :last-page="lastPage" :total="total" :per-page="perPage" @page-change="handlePageChange" />
  </div>
</template>
