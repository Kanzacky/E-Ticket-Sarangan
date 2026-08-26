<script setup lang="ts">
import { 
  Banknote as BanknotesIcon, 
  Ticket as TicketIcon, 
  Users as UsersIcon, 
  ClipboardList as ClipboardDocumentListIcon 
} from 'lucide-vue-next'
import { ref, onMounted } from 'vue'

const isLoading = ref(true)

// Mock Data structure based on requirements (should come from API later)
const summary = ref({
  revenue: 0,
  orders: 0,
  tickets: 0,
  visitors: 0
})

const recentOrders = ref<any[]>([])

onMounted(() => {
  // Simulate API fetch delay
  setTimeout(() => {
    isLoading.value = false
    // Leave data empty to show Empty State as required by Anti-AI slop rule
    // "Jika belum ada transaksi: 'Belum ada transaksi'. Jangan menampilkan 1.234 transaksi jika data belum tersedia."
  }, 1000)
})

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)
}
</script>

<template>
  <div class="space-y-6">
    
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <!-- Revenue -->
      <div class="rounded-2xl border border-[#173B35]/10 bg-white p-5 shadow-sm transition-all hover:shadow-md">
        <div class="flex items-center gap-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#4F7465]/10 text-[#4F7465]">
            <BanknotesIcon class="h-6 w-6" />
          </div>
          <div>
            <p class="text-sm font-medium text-[#66706C]">Pendapatan Hari Ini</p>
            <div v-if="isLoading" class="mt-1 h-6 w-24 rounded bg-slate-200 animate-pulse"></div>
            <p v-else class="text-xl font-bold text-[#1D2724]">{{ formatCurrency(summary.revenue) }}</p>
          </div>
        </div>
      </div>

      <!-- Orders -->
      <div class="rounded-2xl border border-[#173B35]/10 bg-white p-5 shadow-sm transition-all hover:shadow-md">
        <div class="flex items-center gap-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#4F7465]/10 text-[#4F7465]">
            <ClipboardDocumentListIcon class="h-6 w-6" />
          </div>
          <div>
            <p class="text-sm font-medium text-[#66706C]">Pesanan Hari Ini</p>
            <div v-if="isLoading" class="mt-1 h-6 w-16 rounded bg-slate-200 animate-pulse"></div>
            <p v-else class="text-xl font-bold text-[#1D2724]">{{ summary.orders }}</p>
          </div>
        </div>
      </div>

      <!-- Tickets -->
      <div class="rounded-2xl border border-[#173B35]/10 bg-white p-5 shadow-sm transition-all hover:shadow-md">
        <div class="flex items-center gap-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#4F7465]/10 text-[#4F7465]">
            <TicketIcon class="h-6 w-6" />
          </div>
          <div>
            <p class="text-sm font-medium text-[#66706C]">Tiket Terjual</p>
            <div v-if="isLoading" class="mt-1 h-6 w-16 rounded bg-slate-200 animate-pulse"></div>
            <p v-else class="text-xl font-bold text-[#1D2724]">{{ summary.tickets }}</p>
          </div>
        </div>
      </div>

      <!-- Visitors -->
      <div class="rounded-2xl border border-[#173B35]/10 bg-white p-5 shadow-sm transition-all hover:shadow-md">
        <div class="flex items-center gap-4">
          <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#4F7465]/10 text-[#4F7465]">
            <UsersIcon class="h-6 w-6" />
          </div>
          <div>
            <p class="text-sm font-medium text-[#66706C]">Pengunjung Hari Ini</p>
            <div v-if="isLoading" class="mt-1 h-6 w-16 rounded bg-slate-200 animate-pulse"></div>
            <p v-else class="text-xl font-bold text-[#1D2724]">{{ summary.visitors }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions (Only if API exists, as per instructions) -->
    <div class="flex flex-wrap items-center gap-3">
      <router-link to="/admin/tickets" class="inline-flex items-center justify-center rounded-xl bg-white border border-[#173B35]/10 px-4 py-2 text-sm font-medium text-[#1D2724] hover:bg-[#F7F5EF] transition-colors shadow-sm">
        Kelola Tiket
      </router-link>
      <router-link to="/admin/users" class="inline-flex items-center justify-center rounded-xl bg-white border border-[#173B35]/10 px-4 py-2 text-sm font-medium text-[#1D2724] hover:bg-[#F7F5EF] transition-colors shadow-sm">
        Kelola Pengguna
      </router-link>
      <router-link to="/admin/reports" class="inline-flex items-center justify-center rounded-xl bg-white border border-[#173B35]/10 px-4 py-2 text-sm font-medium text-[#1D2724] hover:bg-[#F7F5EF] transition-colors shadow-sm">
        Lihat Laporan
      </router-link>
    </div>

    <!-- Recent Orders -->
    <div class="rounded-2xl border border-[#173B35]/10 bg-white shadow-sm overflow-hidden">
      <div class="px-6 py-5 border-b border-[#173B35]/10">
        <h3 class="text-lg font-bold text-[#1D2724]">Pesanan Terbaru</h3>
      </div>
      
      <div v-if="isLoading" class="p-6 space-y-4">
        <div v-for="i in 3" :key="i" class="h-12 bg-slate-100 rounded animate-pulse"></div>
      </div>
      
      <div v-else-if="recentOrders.length === 0" class="px-6 py-12 text-center">
        <div class="inline-flex h-12 w-12 items-center justify-center rounded-full bg-[#F7F5EF] text-[#66706C] mb-3">
          <ClipboardDocumentListIcon class="h-6 w-6" />
        </div>
        <p class="text-[#1D2724] font-medium">Belum ada transaksi</p>
        <p class="text-sm text-[#66706C] mt-1">Transaksi terbaru akan muncul di sini.</p>
      </div>
      
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm text-[#66706C]">
          <thead class="bg-[#F7F5EF] text-xs uppercase text-[#1D2724]">
            <tr>
              <th scope="col" class="px-6 py-4 font-bold">Kode Booking</th>
              <th scope="col" class="px-6 py-4 font-bold">Nama</th>
              <th scope="col" class="px-6 py-4 font-bold">Tgl. Kunjungan</th>
              <th scope="col" class="px-6 py-4 font-bold">Jumlah</th>
              <th scope="col" class="px-6 py-4 font-bold">Total</th>
              <th scope="col" class="px-6 py-4 font-bold">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-[#173B35]/10">
            <!-- Render rows when data is available -->
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
