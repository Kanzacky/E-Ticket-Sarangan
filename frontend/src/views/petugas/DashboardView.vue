<script setup lang="ts">
import { usePetugasDashboard } from '@/composables/usePetugasDashboard'
import { QrCode, Users, CheckCircle, Clock, AlertTriangle, ArrowRight } from 'lucide-vue-next'
import StatCard from '@/components/ui/StatCard.vue'
import DataTable from '@/components/ui/DataTable.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'

const { isLoading, summary, recentVisits, error } = usePetugasDashboard()

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric'
  })
}

const getStatusTone = (status: string) => {
  switch (status.toLowerCase()) {
    case 'completed': return 'success'
    case 'paid': return 'info'
    case 'failed': return 'danger'
    case 'cancelled': return 'danger'
    case 'pending': return 'warning'
    default: return 'neutral'
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header Area -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-[#173B35]">Operasional Hari Ini</h1>
        <p class="text-sm font-medium text-[#66706C] mt-1">Pantau kunjungan dan validasi tiket hari ini.</p>
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
      >
        <template #icon>
          <Users class="w-6 h-6" />
        </template>
      </StatCard>

      <StatCard
        title="Diverifikasi"
        :value="summary.diverifikasi"
      >
        <template #icon>
          <CheckCircle class="w-6 h-6 text-emerald-600" />
        </template>
      </StatCard>

      <StatCard
        title="Menunggu"
        :value="summary.menunggu"
      >
        <template #icon>
          <Clock class="w-6 h-6 text-amber-500" />
        </template>
      </StatCard>

      <StatCard
        title="Tiket Bermasalah"
        :value="summary.bermasalah"
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
              <span class="capitalize">{{ visit.status === 'COMPLETED' ? 'Sudah Masuk' : visit.status }}</span>
            </StatusBadge>
          </td>
        </tr>
      </DataTable>
    </div>
  </div>
</template>
