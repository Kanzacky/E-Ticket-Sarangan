<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import { Search, QrCode } from 'lucide-vue-next'
import api from '@/services/api'
import DataTable from '@/components/ui/DataTable.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import Pagination from '@/components/ui/Pagination.vue'

const visits = ref<any[]>([])
const isLoading = ref(true)
const searchQuery = ref('')
const filterStatus = ref('all')

const currentPage = ref(1)
const perPage = ref(10)
const total = ref(0)
const lastPage = ref(1)

const fetchVisits = async () => {
  try {
    isLoading.value = true
    const params = new URLSearchParams()
    params.set('page', String(currentPage.value))
    params.set('per_page', String(perPage.value))
    if (searchQuery.value.trim()) params.set('search', searchQuery.value.trim())
    if (filterStatus.value !== 'all') params.set('status', filterStatus.value)

    const response = await api.get(`/petugas/visits?${params.toString()}`)
    if (response.data.success) {
      if (response.data.meta) {
        visits.value = response.data.data
        total.value = response.data.meta.total
        lastPage.value = response.data.meta.last_page
        currentPage.value = response.data.meta.current_page
      } else {
        visits.value = response.data.data
        total.value = visits.value.length
        lastPage.value = 1
      }
    }
  } catch (error) {
    console.error('Gagal mengambil data kunjungan', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => fetchVisits())

const handlePageChange = (page: number) => {
  currentPage.value = page
  fetchVisits()
}

watch([searchQuery, filterStatus], () => {
  currentPage.value = 1
  fetchVisits()
})

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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-[#1D2724]">Kunjungan Hari Ini</h1>
        <p class="text-sm font-medium text-[#66706C] mt-1">Daftar wisatawan yang dijadwalkan hadir hari ini.</p>
      </div>
      <router-link
        to="/petugas/scanner"
        class="inline-flex items-center justify-center gap-2 bg-[#173B35] text-white px-4 py-2 rounded-xl hover:bg-[#112a26] transition-all font-bold shadow-md shadow-[#173B35]/20 text-sm"
      >
        <QrCode class="w-4 h-4" />
        Scan Tiket
      </router-link>
    </div>

    <!-- Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-[#E8E6DE] flex flex-col sm:flex-row gap-4">
      <div class="relative flex-1">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <Search class="w-4 h-4 text-[#66706C]" />
        </div>
        <input 
          v-model="searchQuery"
          type="text" 
          placeholder="Cari kode booking / nama..." 
          class="w-full pl-9 pr-4 py-2 bg-[#F7F5EF] border border-[#E8E6DE] rounded-xl text-sm focus:ring-2 focus:ring-[#173B35] focus:border-transparent outline-none transition-all"
        />
      </div>
      
      <div class="flex gap-2">
        <select v-model="filterStatus" class="bg-[#F7F5EF] border border-[#E8E6DE] rounded-xl px-4 py-2 text-sm font-medium text-[#1D2724] focus:outline-none focus:ring-2 focus:ring-[#173B35] transition-all">
          <option value="all">Semua Status</option>
          <option value="PAID">Menunggu</option>
          <option value="COMPLETED">Sudah Masuk</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-[#E8E6DE] overflow-hidden">
      <DataTable
        :headers="['Kode', 'Wisatawan', 'Paket', 'Status', 'Aksi']"
        :is-loading="isLoading"
        :is-empty="visits.length === 0"
        empty-message="Tidak ada data kunjungan ditemukan."
      >
        <tr v-for="visit in visits" :key="visit.id" class="hover:bg-[#F7F5EF]/50 transition-colors border-b border-[#E8E6DE] last:border-0">
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm font-bold text-[#1D2724]">#{{ visit.order_code }}</span>
          </td>
          <td class="px-6 py-4">
            <div class="text-sm text-[#1D2724] font-medium">{{ visit.user?.name || '-' }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm text-[#66706C]">-</span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <StatusBadge :tone="getStatusTone(visit.status)">
              <span class="capitalize font-bold">{{ formatStatusText(visit.status) }}</span>
            </StatusBadge>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <router-link :to="`/petugas/bookings/${visit.id}`" class="text-xs font-bold text-[#173B35] bg-[#173B35]/10 px-3 py-1.5 rounded-lg hover:bg-[#173B35]/20 transition-colors">
              Detail
            </router-link>
          </td>
        </tr>
        <template #pagination>
          <Pagination :current-page="currentPage" :last-page="lastPage" :total="total" :per-page="perPage" @page-change="handlePageChange" @update:perPage="v => { perPage = v; currentPage = 1; fetchVisits() }" />
        </template>
      </DataTable>
    </div>
  </div>
</template>
