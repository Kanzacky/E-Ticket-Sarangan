<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '@/services/api'
import { Search, CreditCard } from 'lucide-vue-next'
import DataTable from '@/components/ui/DataTable.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'

interface Payment {
  id: number
  transaction_id: string
  customer_name: string
  payment_method: string
  amount: number
  status: string
  paid_at: string | null
  created_at: string
}

const payments = ref<Payment[]>([])
const isLoading = ref(true)
const error = ref('')
const searchQuery = ref('')
const filterStatus = ref('all')
const isUpdating = ref(false)

const fetchPayments = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const response = await api.get('/admin/payments')
    if (response.data.success) {
      payments.value = response.data.data
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Gagal memuat data pembayaran'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchPayments()
})

const filteredPayments = computed(() => {
  return payments.value.filter(p => {
    const matchesSearch = 
      p.transaction_id.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      p.customer_name.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    const matchesStatus = filterStatus.value === 'all' || p.status === filterStatus.value
    
    return matchesSearch && matchesStatus
  })
})

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', minimumFractionDigits: 0
  }).format(value)
}

const formatDate = (dateStr: string | null) => {
  if (!dateStr) return '-'
  return new Intl.DateTimeFormat('id-ID', {
    dateStyle: 'medium', timeStyle: 'short'
  }).format(new Date(dateStr))
}

const updateStatus = async (id: number, newStatus: string) => {
  if (!confirm(`Apakah Anda yakin ingin mengubah status menjadi ${newStatus}?`)) return
  
  isUpdating.value = true
  try {
    const response = await api.patch(`/admin/payments/${id}/status`, { status: newStatus })
    if (response.data.success) {
      const index = payments.value.findIndex(p => p.id === id)
      if (index !== -1) {
        const paymentToUpdate = payments.value[index]
        if (paymentToUpdate) {
            paymentToUpdate.status = response.data.data.status
            paymentToUpdate.paid_at = response.data.data.paid_at
        }
      }
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal mengubah status')
  } finally {
    isUpdating.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-black text-[#173B35]">Pembayaran</h1>
      <p class="text-sm font-medium text-[#66706C] mt-1">Kelola dan pantau transaksi pembayaran masuk.</p>
    </div>

    <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
      {{ error }}
    </div>

    <!-- Data Table -->
    <DataTable
      :headers="['ID Transaksi', 'Pelanggan', 'Metode', 'Nominal', 'Status', 'Waktu Bayar', 'Aksi']"
      :is-loading="isLoading"
      :is-empty="filteredPayments.length === 0"
      empty-message="Belum ada transaksi pembayaran."
    >
      <template #toolbar>
        <div class="flex flex-col sm:flex-row gap-3 w-full">
          <div class="relative flex-1 sm:max-w-xs">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Search class="w-4 h-4 text-[#66706C]" />
            </div>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Cari ID TRX atau Nama..." 
              class="w-full pl-9 pr-3 py-2 text-sm border border-[#E8E6DE] rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
            >
          </div>
          
          <select 
            v-model="filterStatus"
            class="text-sm px-3 py-2 border border-[#E8E6DE] rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
          >
            <option value="all">Semua Status</option>
            <option value="PAID">Lunas (PAID)</option>
            <option value="PENDING">Menunggu (PENDING)</option>
            <option value="FAILED">Gagal (FAILED)</option>
            <option value="CANCELLED">Batal (CANCELLED)</option>
          </select>
        </div>
      </template>

      <tr v-for="payment in filteredPayments" :key="payment.id" class="hover:bg-[#F7F5EF]/50 transition-colors">
        <td class="px-6 py-4 whitespace-nowrap">
          <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-[#173B35]/5 flex items-center justify-center shrink-0">
              <CreditCard class="w-4 h-4 text-[#173B35]" />
            </div>
            <span class="text-sm font-bold text-[#1D2724]">{{ payment.transaction_id }}</span>
          </div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="text-sm text-[#1D2724]">{{ payment.customer_name }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="text-xs font-medium px-2 py-1 bg-[#F7F5EF] text-[#66706C] rounded-md">{{ payment.payment_method }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="text-sm font-bold text-[#1D2724]">{{ formatCurrency(payment.amount) }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <StatusBadge :tone="payment.status === 'PAID' ? 'success' : (payment.status === 'PENDING' ? 'info' : 'danger')">
            <span class="font-semibold">{{ payment.status }}</span>
          </StatusBadge>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="text-sm text-[#66706C]">{{ formatDate(payment.paid_at || payment.created_at) }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right">
          <select 
            :value="payment.status"
            @change="updateStatus(payment.id, ($event.target as HTMLSelectElement).value)"
            :disabled="isUpdating"
            class="text-xs px-2 py-1.5 border border-[#E8E6DE] rounded bg-white focus:outline-none focus:ring-1 focus:ring-[#173B35]"
          >
            <option value="PENDING">Set PENDING</option>
            <option value="PAID">Set PAID</option>
            <option value="FAILED">Set FAILED</option>
            <option value="CANCELLED">Set CANCELLED</option>
          </select>
        </td>
      </tr>
    </DataTable>
  </div>
</template>
