<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '@/services/api'
import { TrendingUp, CreditCard, Ticket, PieChart } from 'lucide-vue-next'
import { computed } from 'vue'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  CategoryScale,
  LinearScale,
  ArcElement
} from 'chart.js'
import { Bar, Doughnut } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement)

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

const trendChartData = computed(() => {
  return {
    labels: trend.value.map(t => formatDate(t.date)),
    datasets: [
      {
        label: 'Pendapatan (Rp)',
        backgroundColor: '#173B35',
        borderRadius: 4,
        data: trend.value.map(t => t.revenue)
      }
    ]
  }
})

const trendChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    }
  }
}

const topTicketsChartData = computed(() => {
  return {
    labels: topTickets.value.map(t => t.name),
    datasets: [
      {
        backgroundColor: ['#173B35', '#D4A373', '#66706C', '#A3B18A', '#E8E6DE'],
        data: topTickets.value.map(t => t.total_sold),
        borderWidth: 0
      }
    ]
  }
})

const topTicketsChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const
    }
  }
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
      
      <!-- Trend Table/Chart (Left 2 cols) -->
      <div class="lg:col-span-2 bg-white rounded-xl border border-[#E8E6DE] shadow-sm overflow-hidden flex flex-col">
        <div class="p-6 border-b border-[#E8E6DE]">
          <h3 class="text-base font-bold text-[#1D2724] flex items-center gap-2">
            <TrendingUp class="w-5 h-5 text-[#66706C]" />
            Grafik Pendapatan Harian
          </h3>
        </div>
        <div class="p-6 flex-1 min-h-[350px] flex items-center justify-center">
          <div v-if="isLoading" class="animate-pulse flex items-end justify-center gap-2 w-full h-full pb-4">
            <div v-for="i in 7" :key="i" class="w-1/12 bg-[#E8E6DE] rounded-t" :style="{ height: `${Math.random() * 80 + 20}%` }"></div>
          </div>
          <div v-else-if="trend.length === 0" class="text-center text-sm text-[#66706C]">
            Tidak ada transaksi pada periode ini.
          </div>
          <div v-else class="w-full h-full relative">
            <Bar :data="trendChartData" :options="trendChartOptions" />
          </div>
        </div>
      </div>

      <!-- Top Tickets (Right 1 col) -->
      <div class="bg-white rounded-xl border border-[#E8E6DE] shadow-sm flex flex-col">
        <div class="p-6 border-b border-[#E8E6DE]">
          <h3 class="text-base font-bold text-[#1D2724] flex items-center gap-2">
            <PieChart class="w-5 h-5 text-[#66706C]" />
            Distribusi Tiket Terpopuler
          </h3>
        </div>
        <div class="p-6 flex-1 min-h-[350px] flex flex-col items-center justify-center">
          <div v-if="isLoading" class="animate-pulse w-48 h-48 rounded-full bg-[#E8E6DE]"></div>
          <div v-else-if="topTickets.length === 0" class="text-center text-sm text-[#66706C]">
            Belum ada penjualan tiket.
          </div>
          <div v-else class="w-full h-full relative">
            <Doughnut :data="topTicketsChartData" :options="topTicketsChartOptions" />
          </div>
        </div>
      </div>

    </div>
  </div>
</template>
