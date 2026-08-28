<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import DataTable from '@/components/ui/DataTable.vue'
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

const fetch = async () => {
  isLoading.value = true
  try {
    const res = await api.get('/admin/checkins')
    if (res.data.success) logs.value = res.data.data
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal memuat monitoring check-in'
  } finally { isLoading.value = false }
}

onMounted(fetch)
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
      <tr v-for="l in logs" :key="l.id" class="hover:bg-[#F7F5EF]/50">
        <td class="px-6 py-3 text-xs whitespace-nowrap">{{ formatDate(l.created_at) }}</td>
        <td class="px-6 py-3 text-sm font-bold">{{ l.order_code }}</td>
        <td class="px-6 py-3 text-sm">{{ l.order?.customer_name || '-' }} <span class="text-xs text-[#66706C]">({{ l.order?.items?.join(', ') || '-' }})</span></td>
        <td class="px-6 py-3"><StatusBadge :tone="l.is_valid ? 'success' : 'danger'">{{ l.is_valid ? 'Valid' : 'Tolak' }}</StatusBadge></td>
        <td class="px-6 py-3 text-xs max-w-xs truncate">{{ l.reason || '-' }}</td>
      </tr>
    </DataTable>
  </div>
</template>
