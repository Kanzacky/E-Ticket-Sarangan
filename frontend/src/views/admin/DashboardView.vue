<script setup lang="ts">
import { computed } from 'vue'
import { useAdminDashboard } from '@/composables/useAdminDashboard'
import { Banknote, ShoppingCart, Ticket, Users, TrendingUp, PieChart, Activity } from 'lucide-vue-next'
import StatCard from '@/components/ui/StatCard.vue'
import DataTable from '@/components/ui/DataTable.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import api from '@/services/api'
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
import { onMounted, ref } from 'vue'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement)

const { isLoading, isRefreshing, isPolling, summary, recentOrders, userInsights, error, lastUpdated } = useAdminDashboard()

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short'
  })
}

const trend = ref<any[]>([])
const topTickets = ref<any[]>([])
const isChartLoading = ref(true)

const fetchTrend = async () => {
  try {
    const response = await api.get('/admin/reports/summary?period=month')
    if (response.data.success) {
      trend.value = response.data.data.trend
      topTickets.value = response.data.data.top_tickets
    }
  } catch (e) {
    console.error('Failed to load trend', e)
  } finally {
    isChartLoading.value = false
  }
}

const colorPalette = ['#173B35', '#D4A373', '#66706C', '#A3B18A', '#E8E6DE']

const trendChartData = computed(() => {
  return {
    labels: trend.value.map(t => formatDate(t.date)),
    datasets: [
      {
        label: 'Pendapatan (Rp)',
        backgroundColor: trend.value.map((_, i) => colorPalette[i % colorPalette.length]),
        borderRadius: 4,
        maxBarThickness: 32,
        data: trend.value.map(t => t.revenue)
      }
    ]
  }
})

const trendChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false }
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
    legend: { position: 'bottom' as const }
  }
}

onMounted(() => {
  fetchTrend()
})

// Convert summary data safely
const totalRevenue = computed(() => summary.value?.revenue || 0)
const totalOrders = computed(() => summary.value?.orders || 0)
const totalTickets = computed(() => summary.value?.tickets || 0)
const totalVisitors = computed(() => summary.value?.visitors || 0)

const getStatusTone = (status: string) => {
  switch (status.toLowerCase()) {
    case 'paid': return 'info'
    case 'completed': return 'success'
    case 'failed': return 'danger'
    case 'cancelled': return 'danger'
    case 'pending': return 'warning'
    default: return 'neutral'
  }
}

const formatStatusText = (status: string) => {
  switch (status.toLowerCase()) {
    case 'paid': return 'Lunas (Belum Scan)'
    case 'completed': return 'Selesai (Sudah Scan)'
    case 'failed': return 'Gagal'
    case 'cancelled': return 'Dibatalkan'
    case 'pending': return 'Menunggu Pembayaran'
    default: return status
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header Area -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h1 class="text-2xl font-black text-[#173B35] flex items-center gap-2">
          Dashboard
          <span v-if="isRefreshing" class="inline-flex h-2 w-2 relative">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#C9965B] opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#C9965B]"></span>
          </span>
          <span v-else-if="isPolling" class="inline-flex h-2 w-2 relative">
            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
          </span>
        </h1>
        <p class="text-sm font-medium text-[#66706C] mt-1">Ringkasan aktivitas e-Ticket Sarangan hari ini.</p>
      </div>
      
      <div v-if="lastUpdated" class="text-xs font-medium text-[#66706C] bg-white border border-[#E8E6DE] px-3 py-1.5 rounded-lg flex items-center gap-2">
        <Activity class="w-3.5 h-3.5" :class="isRefreshing ? 'text-[#C9965B] animate-spin' : 'text-[#4F7465]'" />
        Update terakhir: {{ lastUpdated.toLocaleTimeString('id-ID', { hour: '2-digit', minute:'2-digit', second:'2-digit' }) }}
      </div>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
      {{ error }}
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <StatCard
        title="Total Pendapatan"
        :value="formatCurrency(totalRevenue)"
        :trend="{ value: 12, label: 'vs bulan lalu' }"
        :is-loading="isLoading"
      >
        <template #icon>
          <Banknote class="w-6 h-6" />
        </template>
      </StatCard>

      <StatCard
        title="Total Booking"
        :value="totalOrders"
        :is-loading="isLoading"
      >
        <template #icon>
          <ShoppingCart class="w-6 h-6" />
        </template>
      </StatCard>

      <StatCard
        title="Tiket Terjual"
        :value="totalTickets"
        :trend="{ value: 5, label: 'vs kemarin' }"
        :is-loading="isLoading"
      >
        <template #icon>
          <Ticket class="w-6 h-6" />
        </template>
      </StatCard>

      <StatCard
        title="Wisatawan"
        :value="totalVisitors"
        :is-loading="isLoading"
      >
        <template #icon>
          <Users class="w-6 h-6" />
        </template>
      </StatCard>
    </div>

    <!-- Widgets Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
      
      <!-- Trend Chart -->
      <div class="bg-white rounded-xl border border-[#E8E6DE] shadow-sm flex flex-col">
        <div class="p-6 border-b border-[#E8E6DE]">
          <h3 class="text-base font-bold text-[#1D2724] flex items-center gap-2">
            <TrendingUp class="w-5 h-5 text-[#66706C]" />
            Pendapatan
          </h3>
        </div>
        <div class="p-6 min-h-[250px] flex items-center justify-center">
          <div v-if="isChartLoading" class="animate-pulse flex items-end justify-center gap-2 w-full h-full pb-4">
            <div v-for="i in 10" :key="i" class="w-[8%] bg-[#E8E6DE] rounded-t" :style="{ height: `${Math.random() * 80 + 20}%` }"></div>
          </div>
          <div v-else-if="trend.length === 0" class="text-center text-sm text-[#66706C]">
            Belum ada data.
          </div>
          <div v-else class="w-full h-[220px] relative">
            <Bar :data="trendChartData" :options="trendChartOptions" />
          </div>
        </div>
      </div>

      <!-- Ticket Comparison -->
      <div class="bg-white rounded-xl border border-[#E8E6DE] shadow-sm flex flex-col">
        <div class="p-6 border-b border-[#E8E6DE]">
          <h3 class="text-base font-bold text-[#1D2724] flex items-center gap-2">
            <PieChart class="w-5 h-5 text-[#66706C]" />
            Perbandingan Tiket
          </h3>
        </div>
        <div class="p-6 min-h-[250px] flex items-center justify-center">
          <div v-if="isChartLoading" class="animate-pulse w-40 h-40 rounded-full bg-[#E8E6DE]"></div>
          <div v-else-if="topTickets.length === 0" class="text-center text-sm text-[#66706C]">
            Belum ada data.
          </div>
          <div v-else class="w-full h-[220px] relative">
            <Doughnut :data="topTicketsChartData" :options="topTicketsChartOptions" />
          </div>
        </div>
      </div>

      <!-- User Insights -->
      <div class="bg-white rounded-xl border border-[#E8E6DE] shadow-sm flex flex-col">
        <div class="p-6 border-b border-[#E8E6DE]">
          <h3 class="text-base font-bold text-[#1D2724] flex items-center gap-2">
            <Activity class="w-5 h-5 text-[#66706C]" />
            Insight Pengguna
          </h3>
        </div>
        <div class="p-6 min-h-[250px] flex flex-col justify-center gap-6">
          <div v-if="isLoading" class="space-y-6">
            <div v-for="i in 3" :key="i" class="animate-pulse">
              <div class="h-4 bg-[#E8E6DE] rounded w-2/3 mb-2"></div>
              <div class="h-8 bg-[#E8E6DE] rounded w-1/3"></div>
            </div>
          </div>
          <template v-else>
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-[#66706C]">Pendaftar Baru Hari Ini</p>
                <h4 class="text-2xl font-black text-[#1D2724]">{{ userInsights?.new_today || 0 }}</h4>
              </div>
              <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center shrink-0">
                <Users class="w-6 h-6 text-emerald-600" />
              </div>
            </div>
            <div class="flex items-center justify-between border-t border-[#E8E6DE] pt-4">
              <div>
                <p class="text-sm font-medium text-[#66706C]">Pendaftar Bulan Ini</p>
                <h4 class="text-2xl font-black text-[#1D2724]">{{ userInsights?.new_month || 0 }}</h4>
              </div>
              <div class="w-10 h-10 rounded-full bg-blue-50 flex items-center justify-center shrink-0">
                <TrendingUp class="w-5 h-5 text-blue-600" />
              </div>
            </div>
            <div class="flex items-center justify-between border-t border-[#E8E6DE] pt-4">
              <div>
                <p class="text-sm font-medium text-[#66706C]">Akun Login (Aktif)</p>
                <h4 class="text-2xl font-black text-[#1D2724]">{{ userInsights?.active_sessions || 0 }}</h4>
              </div>
              <div class="flex items-center gap-2">
                <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
                </span>
                <span class="text-xs font-bold text-emerald-600">Online</span>
              </div>
            </div>
          </template>
        </div>
      </div>

    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
      
      <!-- Recent Bookings Table (Takes 2 columns) -->
      <div class="lg:col-span-2 space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-base font-bold text-[#1D2724]">Booking Terbaru</h2>
          <router-link to="/admin/bookings" class="text-sm font-bold text-[#173B35] hover:underline">Lihat Semua</router-link>
        </div>

        <DataTable 
          :headers="['Kode', 'Wisatawan', 'Total', 'Status']"
          :is-loading="isLoading"
          :is-empty="!recentOrders || recentOrders.length === 0"
          empty-message="Belum ada pesanan terbaru."
        >
          <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-[#F7F5EF]/50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="text-sm font-bold text-[#1D2724]">#{{ order.order_code }}</span>
            </td>
            <td class="px-6 py-4">
              <div class="text-sm text-[#1D2724] font-medium">{{ order.user?.name || '-' }}</div>
              <div class="text-xs text-[#66706C]">{{ order.user?.email || '-' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span class="text-sm font-medium text-[#1D2724]">{{ formatCurrency(order.total_amount) }}</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <StatusBadge :tone="getStatusTone(order.status)">
                <span class="capitalize font-bold">{{ formatStatusText(order.status) }}</span>
              </StatusBadge>
            </td>
          </tr>
        </DataTable>
      </div>

      <!-- Recent Activities / Notifications -->
      <div class="space-y-4">
        <h2 class="text-base font-bold text-[#1D2724]">Aktivitas Terbaru</h2>
        
        <div class="bg-white rounded-xl border border-[#E8E6DE] p-5 shadow-sm">
          <div v-if="isLoading" class="animate-pulse space-y-4">
            <div class="h-10 bg-[#F7F5EF] rounded-lg"></div>
            <div class="h-10 bg-[#F7F5EF] rounded-lg"></div>
            <div class="h-10 bg-[#F7F5EF] rounded-lg"></div>
          </div>
          
          <div v-else-if="!recentOrders || recentOrders.length === 0" class="text-center py-8">
            <p class="text-sm text-[#66706C] font-medium">Belum ada aktivitas.</p>
          </div>
          
          <div v-else class="space-y-5">
            <div v-for="order in recentOrders.slice(0, 4)" :key="'act-'+order.id" class="flex gap-4">
              <div class="w-8 h-8 rounded-full bg-[#173B35]/10 flex items-center justify-center shrink-0 mt-0.5">
                <ShoppingCart class="w-4 h-4 text-[#173B35]" />
              </div>
              <div>
                <p class="text-sm font-medium text-[#1D2724] leading-snug">
                  Booking baru <span class="font-bold">#{{ order.order_code }}</span> dibuat oleh {{ order.user?.name || 'Wisatawan' }}.
                </p>
                <p class="text-xs text-[#66706C] mt-1">{{ formatDate(order.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>