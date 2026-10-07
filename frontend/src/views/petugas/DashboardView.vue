<script setup lang="ts">
import { usePetugasDashboard } from '@/composables/usePetugasDashboard'
import { QrCode, Users, CheckCircle, Clock, AlertTriangle, ArrowRight } from 'lucide-vue-next'
import StatCard from '@/components/ui/StatCard.vue'
import DataTable from '@/components/ui/DataTable.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'

const { isLoading, isRefreshing, isPolling, summary, recentVisits, error, lastUpdated } = usePetugasDashboard()

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric'
  })
}

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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-[#173B35] flex items-center gap-2">
          Operasional Hari Ini
          <span v-if="isRefreshing" class="inline-flex h-2 w-2 relative">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#C9965B] opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-[#C9965B]"></span>
          </span>
          <span v-else-if="isPolling" class="inline-flex h-2 w-2 relative">
            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
          </span>
        </h1>
        <div class="flex items-center gap-3 mt-1">
          <p class="text-sm font-medium text-[#66706C]">Pantau kunjungan dan validasi tiket hari ini.</p>
          <div v-if="lastUpdated" class="text-[10px] font-bold text-[#4F7465] bg-[#4F7465]/10 px-2 py-0.5 rounded-full flex items-center gap-1.5">
            <Clock class="w-3 h-3" :class="isRefreshing ? 'animate-spin' : ''" />
            {{ lastUpdated.toLocaleTimeString('id-ID', { hour: '2-digit', minute:'2-digit', second:'2-digit' }) }}
          </div>
        </div>
      </div>
      <router-link
        to="/petugas/scanner"
        class="inline-flex items-center justify-center gap-2 bg-[#173B35] text-white px-6 py-3 rounded-xl hover:bg-[#112a26] transition-all font-bold shadow-md shadow-[#173B35]/20"
      >
        <QrCode class="w-5 h-5" />
        Scan Tiket
      </router-link>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
      {{ error }}
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <StatCard
        title="Kunjungan Hari Ini"
        :value="summary.kunjungan_hari_ini"
        :is-loading="isLoading"
      >
        <template #icon>
          <Users class="w-6 h-6" />
        </template>
      </StatCard>

      <StatCard
        title="Diverifikasi"
        :value="summary.diverifikasi"
        :is-loading="isLoading"
      >
        <template #icon>
          <CheckCircle class="w-6 h-6 text-emerald-600" />
        </template>
      </StatCard>

      <StatCard
        title="Menunggu"
        :value="summary.menunggu"
        :is-loading="isLoading"
      >
        <template #icon>
          <Clock class="w-6 h-6 text-amber-500" />
        </template>
      </StatCard>

      <StatCard
        title="Tiket Bermasalah"
        :value="summary.bermasalah"
        :is-loading="isLoading"
      >
        <template #icon>
          <AlertTriangle class="w-6 h-6 text-red-500" />
        </template>
      </StatCard>
    </div>

    <!-- Main Content Area -->
    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h2 class="text-base font-bold text-[#1D2724]">Kunjungan Terbaru</h2>
        <router-link to="/petugas/visits" class="text-sm font-bold text-[#173B35] hover:underline flex items-center gap-1">
          Lihat Semua <ArrowRight class="w-4 h-4" />
        </router-link>
      </div>

      <DataTable 
        :headers="['Kode', 'Wisatawan', 'Tanggal', 'Status']"
        :is-loading="isLoading"
        :is-empty="!recentVisits || recentVisits.length === 0"
        empty-message="Belum ada kunjungan terbaru."
      >
        <tr v-for="visit in recentVisits" :key="visit.id" class="hover:bg-[#F7F5EF]/50 transition-colors">
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm font-bold text-[#1D2724]">#{{ visit.order_code }}</span>
          </td>
          <td class="px-6 py-4">
            <div class="text-sm text-[#1D2724] font-medium">{{ visit.user?.name || '-' }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm text-[#66706C]">{{ formatDate(visit.visit_date) }}</span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <StatusBadge :tone="getStatusTone(visit.status)">
              <span class="capitalize font-bold">{{ formatStatusText(visit.status) }}</span>
            </StatusBadge>
          </td>
        </tr>
      </DataTable>
    </div>
  </div>
</template>
