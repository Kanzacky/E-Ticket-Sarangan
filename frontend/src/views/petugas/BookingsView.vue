<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Search } from 'lucide-vue-next'
import api from '@/services/api'
import DataTable from '@/components/ui/DataTable.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'

const bookings = ref<any[]>([])
const isLoading = ref(true)
const searchQuery = ref('')
const filterStatus = ref('all')

onMounted(async () => {
  try {
    isLoading.value = true
    const response = await api.get('/petugas/bookings')
    if (response.data.success) {
      bookings.value = response.data.data
    }
  } catch (error) {
    console.error('Gagal mengambil data booking', error)
  } finally {
    isLoading.value = false
  }
})

const filteredBookings = computed(() => {
  let result = bookings.value

  if (filterStatus.value !== 'all') {
    result = result.filter(b => b.status === filterStatus.value)
  }

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    result = result.filter(b => 
      b.order_code.toLowerCase().includes(q) || 
      (b.user?.name || '').toLowerCase().includes(q)
    )
  }

  return result
})

const formatDate = (dateString: string) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'short', year: 'numeric'
  })
}

const getStatusTone = (status: string) => {
  switch (status.toLowerCase()) {
    case 'paid': return 'info'
    case 'completed': return 'success'
    case 'pending': return 'warning'
    case 'cancelled': return 'danger'
    case 'failed': return 'danger'
    default: return 'neutral'
  }
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-[#1D2724]">Daftar Booking</h1>
        <p class="text-sm font-medium text-[#66706C] mt-1">Lihat seluruh riwayat booking tiket.</p>
      </div>
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
          <option value="PENDING">Pending</option>
          <option value="PAID">Paid</option>
          <option value="COMPLETED">Selesai</option>
          <option value="CANCELLED">Batal</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-2xl border border-[#E8E6DE] overflow-hidden">
      <DataTable
        :headers="['Kode', 'Wisatawan', 'Tanggal Kunjungan', 'Status', 'Aksi']"
        :is-loading="isLoading"
        :is-empty="filteredBookings.length === 0"
        empty-message="Tidak ada data booking."
      >
        <tr v-for="booking in filteredBookings" :key="booking.id" class="hover:bg-[#F7F5EF]/50 transition-colors border-b border-[#E8E6DE] last:border-0">
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm font-bold text-[#1D2724]">#{{ booking.order_code }}</span>
          </td>
          <td class="px-6 py-4">
            <div class="text-sm text-[#1D2724] font-medium">{{ booking.user?.name || '-' }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="text-sm font-medium text-[#66706C]">{{ formatDate(booking.visit_date) }}</span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <StatusBadge :tone="getStatusTone(booking.status)">
              <span class="capitalize">{{ booking.status }}</span>
            </StatusBadge>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <!-- Reuse admin's booking detail or specific route -->
            <router-link :to="`/petugas/bookings/${booking.order_code}`" class="text-xs font-bold text-[#173B35] bg-[#173B35]/10 px-3 py-1.5 rounded-lg hover:bg-[#173B35]/20 transition-colors">
              Detail
            </router-link>
          </td>
        </tr>
      </DataTable>
    </div>
  </div>
</template>
