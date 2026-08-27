<script setup lang="ts">
import { computed } from 'vue'
import { useAdminDashboard } from '@/composables/useAdminDashboard'
import { Banknote, ShoppingCart, Ticket, Users } from 'lucide-vue-next'
import StatCard from '@/components/ui/StatCard.vue'
import DataTable from '@/components/ui/DataTable.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'

const { isLoading, summary, recentOrders, error } = useAdminDashboard()

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric'
  })
}

// Convert summary data safely
const totalRevenue = computed(() => summary.value?.revenue || 0)
const totalOrders = computed(() => summary.value?.orders || 0)
const totalTickets = computed(() => summary.value?.tickets || 0)
const totalVisitors = computed(() => summary.value?.visitors || 0)

const getStatusTone = (status: string) => {
  switch (status.toLowerCase()) {
    case 'paid': return 'success'
    case 'failed': return 'danger'
    case 'cancelled': return 'danger'
    case 'pending': return 'info'
    default: return 'neutral'
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header Area -->
    <div>
      <h1 class="text-2xl font-black text-[#173B35]">Dashboard</h1>
      <p class="text-sm font-medium text-[#66706C] mt-1">Ringkasan aktivitas e-Ticket Sarangan hari ini.</p>
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
      >
        <template #icon>
          <Banknote class="w-6 h-6" />
        </template>
      </StatCard>

      <StatCard
        title="Total Booking"
        :value="totalOrders"
      >
        <template #icon>
          <ShoppingCart class="w-6 h-6" />
        </template>
      </StatCard>

      <StatCard
        title="Tiket Terjual"
        :value="totalTickets"
        :trend="{ value: 5, label: 'vs kemarin' }"
      >
        <template #icon>
          <Ticket class="w-6 h-6" />
        </template>
      </StatCard>

      <StatCard
        title="Wisatawan"
        :value="totalVisitors"
      >
        <template #icon>
          <Users class="w-6 h-6" />
        </template>
      </StatCard>
    </div>

    <!-- Main Content Area -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
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
                <span class="capitalize">{{ order.status }}</span>
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