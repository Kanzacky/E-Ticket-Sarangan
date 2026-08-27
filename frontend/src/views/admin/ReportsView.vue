<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { TrendingUp, CreditCard, Ticket, Calendar } from 'lucide-vue-next'

interface ReportSummary {
  revenue: number
  orders: number
  tickets_sold: number
}

interface TrendItem {
  date: string
  revenue: number
  orders_count: number
}

interface TopTicket {
  name: string
  total_sold: number
}

const summary = ref<ReportSummary>({ revenue: 0, orders: 0, tickets_sold: 0 })
const trend = ref<TrendItem[]>([])
const topTickets = ref<TopTicket[]>([])
const isLoading = ref(true)
const error = ref('')
const selectedPeriod = ref('month') // today, week, month, year

const fetchReports = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const response = await api.get(`/admin/reports/summary?period=${selectedPeriod.value}`)
    if (response.data.success) {
      summary.value = response.data.data.summary
      trend.value = response.data.data.trend
      topTickets.value = response.data.data.top_tickets
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Gagal memuat data laporan'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchReports()
})

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', minimumFractionDigits: 0
  }).format(value)
}

const formatDate = (dateStr: string) => {
  return new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium'
  }).format(new Date(dateStr))
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-[#173B35]">Laporan Penjualan</h1>
        <p class="text-sm font-medium text-[#66706C] mt-1">Ringkasan performa penjualan dan pendapatan tiket.</p>
      </div>
      
      <div class="flex items-center gap-2 bg-white rounded-lg border border-[#E8E6DE] p-1">
        <button 
          @click="selectedPeriod = 'today'; fetchReports()"
          :class="['px-3 py-1.5 text-xs font-bold rounded-md transition-colors', selectedPeriod === 'today' ? 'bg-[#173B35] text-white' : 'text-[#66706C] hover:bg-[#F7F5EF]']"
        >Hari Ini</button>
        <button 
          @click="selectedPeriod = 'week'; fetchReports()"
          :class="['px-3 py-1.5 text-xs font-bold rounded-md transition-colors', selectedPeriod === 'week' ? 'bg-[#173B35] text-white' : 'text-[#66706C] hover:bg-[#F7F5EF]']"
        >Minggu Ini</button>
        <button 
          @click="selectedPeriod = 'month'; fetchReports()"
          :class="['px-3 py-1.5 text-xs font-bold rounded-md transition-colors', selectedPeriod === 'month' ? 'bg-[#173B35] text-white' : 'text-[#66706C] hover:bg-[#F7F5EF]']"
        >Bulan Ini</button>
        <button 
          @click="selectedPeriod = 'year'; fetchReports()"
          :class="['px-3 py-1.5 text-xs font-bold rounded-md transition-colors', selectedPeriod === 'year' ? 'bg-[#173B35] text-white' : 'text-[#66706C] hover:bg-[#F7F5EF]']"
        >Tahun Ini</button>
      </div>
    </div>

    <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
      {{ error }}
    </div>

    <!-- Summary Cards -->
    <div v-if="!isLoading" class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white rounded-xl border border-[#E8E6DE] p-6 shadow-sm">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-emerald-50 rounded-full flex items-center justify-center shrink-0">
            <TrendingUp class="w-6 h-6 text-emerald-600" />
          </div>
          <div>
            <p class="text-sm font-medium text-[#66706C] mb-1">Total Pendapatan</p>
            <h3 class="text-2xl font-black text-[#1D2724]">{{ formatCurrency(summary.revenue) }}</h3>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-[#E8E6DE] p-6 shadow-sm">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-blue-50 rounded-full flex items-center justify-center shrink-0">
            <CreditCard class="w-6 h-6 text-blue-600" />
          </div>
          <div>
            <p class="text-sm font-medium text-[#66706C] mb-1">Transaksi Berhasil</p>
            <h3 class="text-2xl font-black text-[#1D2724]">{{ summary.orders }}</h3>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-xl border border-[#E8E6DE] p-6 shadow-sm">
        <div class="flex items-center gap-4">
          <div class="w-12 h-12 bg-amber-50 rounded-full flex items-center justify-center shrink-0">
            <Ticket class="w-6 h-6 text-amber-600" />
          </div>
          <div>
            <p class="text-sm font-medium text-[#66706C] mb-1">Tiket Terjual</p>
            <h3 class="text-2xl font-black text-[#1D2724]">{{ summary.tickets_sold }}</h3>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6 animate-pulse">
      <div v-for="i in 3" :key="i" class="bg-white rounded-xl border border-[#E8E6DE] h-28"></div>
    </div>

    <!-- Charts & Tables Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Trend Table (Left 2 cols) -->
      <div class="lg:col-span-2 bg-white rounded-xl border border-[#E8E6DE] shadow-sm overflow-hidden flex flex-col">
        <div class="p-6 border-b border-[#E8E6DE]">
          <h3 class="text-base font-bold text-[#1D2724] flex items-center gap-2">
            <Calendar class="w-5 h-5 text-[#66706C]" />
            Tren Pendapatan Harian
          </h3>
        </div>
        <div class="flex-1 overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[#F7F5EF] border-y border-[#E8E6DE]">
                <th class="px-6 py-3 text-xs font-bold text-[#1D2724] uppercase">Tanggal</th>
                <th class="px-6 py-3 text-xs font-bold text-[#1D2724] uppercase text-right">Pendapatan</th>
                <th class="px-6 py-3 text-xs font-bold text-[#1D2724] uppercase text-right">Jml Transaksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-[#E8E6DE]">
              <tr v-if="isLoading">
                <td colspan="3" class="px-6 py-8 text-center text-sm text-[#66706C]">Memuat data...</td>
              </tr>
              <tr v-else-if="trend.length === 0">
                <td colspan="3" class="px-6 py-8 text-center text-sm text-[#66706C]">Tidak ada transaksi pada periode ini.</td>
              </tr>
              <tr v-else v-for="(item, idx) in trend" :key="idx" class="hover:bg-[#F7F5EF]/50 transition-colors">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-[#1D2724]">{{ formatDate(item.date) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-[#1D2724] text-right">{{ formatCurrency(item.revenue) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-[#66706C] text-right">{{ item.orders_count }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Top Tickets (Right 1 col) -->
      <div class="bg-white rounded-xl border border-[#E8E6DE] shadow-sm flex flex-col">
        <div class="p-6 border-b border-[#E8E6DE]">
          <h3 class="text-base font-bold text-[#1D2724] flex items-center gap-2">
            <Ticket class="w-5 h-5 text-[#66706C]" />
            Tiket Terpopuler
          </h3>
        </div>
        <div class="p-6 flex-1">
          <div v-if="isLoading" class="text-center text-sm text-[#66706C] py-4">Memuat data...</div>
          <div v-else-if="topTickets.length === 0" class="text-center text-sm text-[#66706C] py-4">Belum ada data penjualan tiket.</div>
          <div v-else class="space-y-4">
            <div v-for="(ticket, idx) in topTickets" :key="idx" class="flex items-center justify-between">
              <div class="flex items-center gap-3 overflow-hidden">
                <div class="w-8 h-8 rounded-full bg-[#F7F5EF] flex items-center justify-center shrink-0 text-xs font-bold text-[#173B35]">
                  #{{ idx + 1 }}
                </div>
                <div class="truncate text-sm font-bold text-[#1D2724]" :title="ticket.name">{{ ticket.name }}</div>
              </div>
              <div class="text-sm font-bold text-[#173B35] shrink-0">{{ ticket.total_sold }} <span class="text-xs font-normal text-[#66706C]">terjual</span></div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>
