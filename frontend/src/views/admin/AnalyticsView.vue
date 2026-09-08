<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { TrendingUp, ShoppingCart, Ticket, ShieldCheck, Building } from 'lucide-vue-next'
import StatCard from '@/components/ui/StatCard.vue'
import DataTable from '@/components/ui/DataTable.vue'

interface AnalyticsData {
  summary: { revenue: number; orders: number; tickets: number; pending: number; expired: number }
  scans: { valid: number; invalid: number; success_rate: number }
  trend: { date: string; revenue: number; orders_count: number }[]
  top_tickets: { name: string; total_sold: number }[]
  accommodations: { total_accommodations: number; active_accommodations: number; total_bookings: number }
}

const data = ref<AnalyticsData | null>(null)
const isLoading = ref(true)
const error = ref('')
const period = ref('month')

const fetchAnalytics = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const res = await api.get(`/admin/analytics?period=${period.value}`)
    if (res.data.success) data.value = res.data.data
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal memuat analitik'
  } finally { isLoading.value = false }
}

onMounted(fetchAnalytics)

const formatCurrency = (v: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(v)
const formatDate = (d: string) => new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-[#173B35]">Analitik</h1>
        <p class="text-sm text-[#66706C] mt-1">Performa penjualan, scan, dan penginapan</p>
      </div>
      <div class="flex gap-1 bg-white rounded-lg border border-[#E8E6DE] p-1">
        <button v-for="p in ['today','week','month','year']" :key="p" @click="period=p; fetchAnalytics()" :class="['px-3 py-1.5 text-xs font-bold rounded-md', period===p ? 'bg-[#173B35] text-white' : 'text-[#66706C] hover:bg-[#F7F5EF]']">{{ p }}</button>
      </div>
    </div>

    <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">{{ error }}</div>

    <div v-if="isLoading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 animate-pulse">
      <div v-for="i in 4" :key="i" class="h-28 bg-white rounded-xl border border-[#E8E6DE]"></div>
    </div>

    <div v-else-if="data" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <StatCard title="Pendapatan" :value="formatCurrency(data.summary.revenue)"><template #icon><TrendingUp class="w-6 h-6" /></template></StatCard>
      <StatCard title="Pesanan Sukses" :value="data.summary.orders"><template #icon><ShoppingCart class="w-6 h-6" /></template></StatCard>
      <StatCard title="Tiket Terjual" :value="data.summary.tickets"><template #icon><Ticket class="w-6 h-6" /></template></StatCard>
      <StatCard title="Scan Valid" :value="`${data.scans.valid} (${data.scans.success_rate}%)`"><template #icon><ShieldCheck class="w-6 h-6" /></template></StatCard>
    </div>

    <div v-if="data" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <DataTable :headers="['Tanggal','Pendapatan','Transaksi']" :is-loading="isLoading" :is-empty="!data.trend.length" empty-message="Tidak ada data tren">
          <tr v-for="row in data.trend" :key="row.date" class="hover:bg-[#F7F5EF]/50">
            <td class="px-6 py-3 text-sm">{{ formatDate(row.date) }}</td>
            <td class="px-6 py-3 text-sm font-bold text-right">{{ formatCurrency(row.revenue) }}</td>
            <td class="px-6 py-3 text-sm text-right">{{ row.orders_count }}</td>
          </tr>
        </DataTable>
      </div>
      <div class="bg-white rounded-xl border border-[#E8E6DE] p-6">
        <h3 class="font-bold text-[#1D2724] mb-4 flex items-center gap-2"><Ticket class="w-5 h-5" /> Top Tiket</h3>
        <div v-if="!data.top_tickets.length" class="text-sm text-[#66706C]">Belum ada data</div>
        <div v-else class="space-y-3">
          <div v-for="(t, i) in data.top_tickets" :key="i" class="flex justify-between text-sm"><span class="font-medium">#{{ i+1 }} {{ t.name }}</span><span class="font-bold">{{ t.total_sold }}</span></div>
        </div>
        <div class="mt-6 pt-4 border-t border-[#E8E6DE]">
          <h4 class="font-bold text-sm flex items-center gap-2"><Building class="w-4 h-4" /> Penginapan</h4>
          <p class="text-sm text-[#66706C] mt-1">Total: {{ data.accommodations.total_accommodations }} (aktif {{ data.accommodations.active_accommodations }})</p>
          <p class="text-sm text-[#66706C]">Booking periode ini: {{ data.accommodations.total_bookings }}</p>
          <p class="text-sm text-[#66706C]">Pending: {{ data.summary.pending }} • Expired: {{ data.summary.expired }}</p>
        </div>
      </div>
    </div>
  </div>
</template>
