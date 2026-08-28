<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import api from '@/services/api'
import { Search, Eye, Filter, X } from 'lucide-vue-next'
import DataTable from '@/components/ui/DataTable.vue'
import StatusBadge from '@/components/ui/StatusBadge.vue'
import Pagination from '@/components/ui/Pagination.vue'

interface Order {
  id: number
  order_code: string
  customer_name: string
  visit_date: string
  total_amount: number
  status: string
  user?: { name: string; email: string }
  items?: Array<{ id: number, quantity: number, price: number, ticket_type: { name: string } }>
}

const orders = ref<Order[]>([])
const isLoading = ref(true)
const error = ref('')

const searchQuery = ref('')
const filterStatus = ref('all')
const currentPage = ref(1)
const perPage = ref(10)
const total = ref(0)
const lastPage = ref(1)

const isDetailOpen = ref(false)
const selectedOrder = ref<Order | null>(null)

const fetchOrders = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const params = new URLSearchParams()
    params.set('page', String(currentPage.value))
    params.set('per_page', String(perPage.value))
    if (searchQuery.value.trim()) params.set('search', searchQuery.value.trim())
    if (filterStatus.value !== 'all') params.set('status', filterStatus.value)
    const response = await api.get(`/admin/orders?${params.toString()}`)
    if (response.data.success) {
      if (response.data.meta) {
        orders.value = response.data.data
        total.value = response.data.meta.total
        lastPage.value = response.data.meta.last_page
        currentPage.value = response.data.meta.current_page
      } else {
        orders.value = response.data.data
        total.value = orders.value.length
        lastPage.value = 1
      }
    }
  } catch (err: any) {
    error.value = err.response?.data?.message || 'Gagal memuat data pesanan'
  } finally { isLoading.value = false }
}

onMounted(() => { fetchOrders() })
let searchTimer: ReturnType<typeof setTimeout> | null = null
watch(searchQuery, () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => { currentPage.value = 1; void fetchOrders() }, 400)
})
watch(filterStatus, () => { currentPage.value = 1; void fetchOrders() })
function handlePageChange(p: number) { if (p<1||p>lastPage.value) return; currentPage.value=p; void fetchOrders() }

const filteredOrders = computed(() => orders.value)

const getStatusTone = (status: string) => {
  switch (status.toLowerCase()) {
    case 'paid': return 'success'
    case 'failed': return 'danger'
    case 'cancelled': return 'danger'
    case 'pending': return 'info'
    default: return 'neutral'
  }
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency', currency: 'IDR', minimumFractionDigits: 0
  }).format(value)
}

const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('id-ID', {
    day: 'numeric', month: 'long', year: 'numeric'
  })
}

const openDetail = (order: Order) => {
  selectedOrder.value = order
  isDetailOpen.value = true
}

const updateStatus = async (newStatus: string) => {
  if (!selectedOrder.value) return
  try {
    const response = await api.patch(`/admin/orders/${selectedOrder.value.order_code}/status`, { status: newStatus })
    if (response.data.success) { selectedOrder.value.status = newStatus; await fetchOrders() }
  } catch (err: any) { alert(err.response?.data?.message || 'Gagal mengubah status') }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-black text-[#173B35]">Pesanan</h1>
      <p class="text-sm font-medium text-[#66706C] mt-1">Kelola seluruh pesanan tiket wisatawan.</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">
      {{ error }}
    </div>

    <!-- Main Content -->
    <DataTable
      :headers="['Kode Booking', 'Wisatawan', 'Tanggal Kunjungan', 'Total', 'Status', 'Aksi']"
      :is-loading="isLoading"
      :is-empty="filteredOrders.length === 0"
      empty-message="Tidak ada pesanan yang sesuai dengan filter."
    >
      <template #toolbar>
        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between">
          <div class="relative w-full sm:w-64">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <Search class="w-4 h-4 text-[#66706C]" />
            </div>
            <input 
              v-model="searchQuery"
              type="text" 
              placeholder="Cari kode atau nama..." 
              class="w-full pl-9 pr-3 py-2 text-sm border border-[#E8E6DE] rounded-lg bg-white focus:outline-none focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
            >
          </div>
          
          <div class="flex items-center gap-2 w-full sm:w-auto">
            <Filter class="w-4 h-4 text-[#66706C]" />
            <select 
              v-model="filterStatus"
              class="w-full sm:w-auto text-sm border border-[#E8E6DE] rounded-lg bg-white px-3 py-2 focus:outline-none focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
            >
              <option value="all">Semua Status</option>
              <option value="pending">Pending</option>
              <option value="paid">Lunas</option>
              <option value="failed">Gagal</option>
              <option value="cancelled">Dibatalkan</option>
            </select>
          </div>
        </div>
      </template>

      <tr v-for="order in filteredOrders" :key="order.id" class="hover:bg-[#F7F5EF]/50 transition-colors">
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="text-sm font-bold text-[#1D2724]">#{{ order.order_code }}</span>
        </td>
        <td class="px-6 py-4">
          <div class="text-sm text-[#1D2724] font-medium">{{ order.user?.name || order.customer_name }}</div>
          <div class="text-xs text-[#66706C]">{{ order.user?.email || '-' }}</div>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="text-sm text-[#1D2724]">{{ formatDate(order.visit_date) }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <span class="text-sm font-medium text-[#1D2724]">{{ formatCurrency(order.total_amount) }}</span>
        </td>
        <td class="px-6 py-4 whitespace-nowrap">
          <StatusBadge :tone="getStatusTone(order.status)">
            <span class="capitalize">{{ order.status }}</span>
          </StatusBadge>
        </td>
        <td class="px-6 py-4 whitespace-nowrap text-right">
          <button 
            @click="openDetail(order)"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-bold text-[#173B35] bg-[#173B35]/10 hover:bg-[#173B35]/20 rounded-lg transition-colors"
          >
            <Eye class="w-3.5 h-3.5" /> Detail
          </button>
        </td>
      </tr>
    </DataTable>
    <Pagination :current-page="currentPage" :last-page="lastPage" :total="total" :per-page="perPage" @page-change="handlePageChange" />

    <!-- Detail Drawer -->
    <div v-if="isDetailOpen && selectedOrder" class="fixed inset-0 z-50 overflow-hidden">
      <div class="absolute inset-0 bg-[#1D2724]/40 backdrop-blur-sm transition-opacity" @click="isDetailOpen = false"></div>
      <div class="fixed inset-y-0 right-0 max-w-md w-full bg-white shadow-xl transform transition-transform duration-300 ease-in-out flex flex-col">
        
        <!-- Drawer Header -->
        <div class="px-6 py-4 border-b border-[#E8E6DE] flex items-center justify-between shrink-0 bg-[#F7F5EF]">
          <div>
            <h3 class="text-base font-bold text-[#1D2724]">Detail Pesanan</h3>
            <p class="text-xs text-[#66706C] font-medium">#{{ selectedOrder.order_code }}</p>
          </div>
          <button @click="isDetailOpen = false" class="p-1.5 text-[#66706C] hover:text-[#1D2724] hover:bg-white rounded-lg transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Drawer Content -->
        <div class="flex-1 overflow-y-auto p-6 space-y-6">
          
          <div class="flex items-center justify-between p-4 bg-[#F7F5EF] rounded-xl border border-[#E8E6DE]">
            <div>
              <p class="text-xs font-bold text-[#66706C] uppercase tracking-wider mb-1">Status</p>
              <StatusBadge :tone="getStatusTone(selectedOrder.status)">
                <span class="capitalize font-bold">{{ selectedOrder.status }}</span>
              </StatusBadge>
            </div>
            <div class="text-right">
              <p class="text-xs font-bold text-[#66706C] uppercase tracking-wider mb-1">Total Biaya</p>
              <p class="text-lg font-black text-[#173B35]">{{ formatCurrency(selectedOrder.total_amount) }}</p>
            </div>
          </div>

          <div>
            <p class="text-xs font-bold text-[#1D2724] uppercase tracking-wider mb-3">Informasi Wisatawan</p>
            <div class="space-y-3">
              <div>
                <p class="text-xs text-[#66706C]">Nama Lengkap</p>
                <p class="text-sm font-medium text-[#1D2724]">{{ selectedOrder.user?.name || selectedOrder.customer_name }}</p>
              </div>
              <div v-if="selectedOrder.user">
                <p class="text-xs text-[#66706C]">Email</p>
                <p class="text-sm font-medium text-[#1D2724]">{{ selectedOrder.user.email }}</p>
              </div>
            </div>
          </div>

          <div>
            <p class="text-xs font-bold text-[#1D2724] uppercase tracking-wider mb-3">Rincian Tiket / Paket</p>
            <div class="space-y-3 border border-[#E8E6DE] rounded-xl p-4">
              <div class="flex items-center justify-between mb-2">
                <p class="text-xs text-[#66706C]">Tanggal Kunjungan</p>
                <p class="text-sm font-bold text-[#1D2724]">{{ formatDate(selectedOrder.visit_date) }}</p>
              </div>
              
              <div class="border-t border-[#E8E6DE] pt-3 mt-3">
                <div v-for="item in selectedOrder.items" :key="item.id" class="flex justify-between items-center mb-2 last:mb-0">
                  <div>
                    <p class="text-sm font-medium text-[#1D2724]">{{ item.ticket_type?.name || 'Tiket' }}</p>
                    <p class="text-xs text-[#66706C]">{{ item.quantity }}x @ {{ formatCurrency(item.price) }}</p>
                  </div>
                  <p class="text-sm font-bold text-[#1D2724]">{{ formatCurrency(item.price * item.quantity) }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Actions -->
          <div v-if="selectedOrder.status !== 'paid' && selectedOrder.status !== 'cancelled'" class="pt-4 border-t border-[#E8E6DE]">
            <p class="text-xs font-bold text-[#1D2724] uppercase tracking-wider mb-3">Tindakan Khusus</p>
            <div class="flex gap-3">
              <button 
                @click="updateStatus('paid')"
                class="flex-1 px-4 py-2 text-sm font-bold text-white bg-[#173B35] hover:bg-[#112a26] rounded-lg transition-colors"
              >
                Tandai Lunas
              </button>
              <button 
                @click="updateStatus('cancelled')"
                class="flex-1 px-4 py-2 text-sm font-bold text-red-700 bg-red-50 border border-red-200 hover:bg-red-100 rounded-lg transition-colors"
              >
                Batalkan
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>
</template>
