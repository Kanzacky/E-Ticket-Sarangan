<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { ArrowLeft, Ticket as TicketIcon } from 'lucide-vue-next'
import api from '@/services/api'
import StatusBadge from '@/components/ui/StatusBadge.vue'

const route = useRoute()
const orderCode = route.params.id as string

const booking = ref<any>(null)
const isLoading = ref(true)

onMounted(async () => {
  try {
    // Petugas doesn't have a specific show endpoint yet, but we can reuse Admin's if allowed,
    // or add one to PetugasController. Let's assume PetugasController has it or we can fetch from Admin's.
    // Wait, AdminOrderController@show gets by order_code.
    // Let's use /admin/orders/{order_code} since Petugas shares auth:sanctum and AdminOrderController doesn't strictly block by role.
    const response = await api.get(`/admin/orders/${orderCode}`)
    if (response.data.success) {
      booking.value = response.data.data
    }
  } catch (error) {
    console.error('Gagal mengambil detail booking', error)
  } finally {
    isLoading.value = false
  }
})

const formatDate = (dateString: string) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('id-ID', {
    weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
  })
}

const getStatusTone = (status: string) => {
  switch (status?.toLowerCase()) {
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
  <div class="space-y-6 pb-6">
    <div class="flex items-center gap-3">
      <router-link to="/petugas/bookings" class="p-2 -ml-2 rounded-xl text-[#66706C] hover:bg-white hover:text-[#173B35] transition-colors">
        <ArrowLeft class="w-5 h-5" />
      </router-link>
      <div>
        <h1 class="text-2xl font-black text-[#1D2724]">Detail Booking</h1>
        <p class="text-sm font-medium text-[#66706C] mt-1">#{{ orderCode }}</p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="bg-white rounded-2xl p-6 border border-[#E8E6DE] text-center py-12">
      <div class="animate-spin rounded-full h-10 w-10 border-4 border-[#173B35]/20 border-t-[#173B35] mx-auto mb-4"></div>
      <p class="text-[#66706C] font-medium">Memuat detail...</p>
    </div>

    <!-- Content -->
    <div v-else-if="booking" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Left Column: Info -->
      <div class="lg:col-span-2 space-y-6">
        
        <!-- Informasi Pengunjung -->
        <div class="bg-white rounded-2xl border border-[#E8E6DE] overflow-hidden">
          <div class="px-6 py-4 border-b border-[#E8E6DE] bg-[#F7F5EF]/50">
            <h2 class="text-sm font-bold text-[#1D2724] uppercase tracking-wider">Informasi Wisatawan</h2>
          </div>
          <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-xs font-bold text-[#66706C] mb-1">Nama</p>
                <p class="text-sm font-medium text-[#1D2724]">{{ booking.user?.name || booking.customer_name || '-' }}</p>
              </div>
              <div>
                <p class="text-xs font-bold text-[#66706C] mb-1">Email</p>
                <p class="text-sm font-medium text-[#1D2724]">{{ booking.user?.email || booking.customer_email || '-' }}</p>
              </div>
              <div>
                <p class="text-xs font-bold text-[#66706C] mb-1">No. Telepon</p>
                <p class="text-sm font-medium text-[#1D2724]">{{ booking.customer_phone || '-' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Detail Pesanan -->
        <div class="bg-white rounded-2xl border border-[#E8E6DE] overflow-hidden">
          <div class="px-6 py-4 border-b border-[#E8E6DE] bg-[#F7F5EF]/50">
            <h2 class="text-sm font-bold text-[#1D2724] uppercase tracking-wider">Tiket & Paket</h2>
          </div>
          <div class="p-6">
            <div v-if="booking.items && booking.items.length > 0" class="space-y-4">
              <div v-for="item in booking.items" :key="item.id" class="flex items-center justify-between py-2 border-b border-[#E8E6DE] last:border-0">
                <div class="flex flex-col">
                  <span class="text-sm font-bold text-[#1D2724]">{{ item.ticket_type?.name || 'Tiket' }}</span>
                  <span class="text-xs text-[#66706C]">Rp {{ item.unit_price.toLocaleString('id-ID') }} x {{ item.quantity }}</span>
                </div>
                <div class="flex items-center gap-1.5 bg-[#F7F5EF] px-2.5 py-1 rounded-lg">
                  <TicketIcon class="w-4 h-4 text-[#C9965B]" />
                  <span class="font-bold text-[#1D2724]">{{ item.quantity }} Orang</span>
                </div>
              </div>
            </div>
            <div v-else class="text-sm text-[#66706C] italic">Tidak ada item.</div>
          </div>
        </div>

      </div>

      <!-- Right Column: Status -->
      <div class="space-y-6">
        
        <div class="bg-white rounded-2xl border border-[#E8E6DE] p-6 text-center">
          <p class="text-xs font-bold text-[#66706C] mb-3 uppercase tracking-wider">Status Kunjungan</p>
          <StatusBadge :tone="getStatusTone(booking.status)" class="text-sm py-1.5 px-4">
            <span class="capitalize">{{ booking.status === 'COMPLETED' ? 'Sudah Masuk' : booking.status }}</span>
          </StatusBadge>
          
          <div class="mt-6 pt-6 border-t border-[#E8E6DE]">
            <p class="text-xs font-bold text-[#66706C] mb-1">Tanggal Kunjungan</p>
            <p class="text-sm font-black text-[#173B35]">{{ formatDate(booking.visit_date) }}</p>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>
