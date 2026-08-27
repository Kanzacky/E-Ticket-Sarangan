<script setup lang="ts">
import {
  AlertCircle,
  Calendar,
  Clock,
  Eye,
  LoaderCircle,
  Plus,
  Search,
  Ticket,
  Users,
  X,
  ChevronLeft,
  ChevronRight,
  RefreshCw,
} from 'lucide-vue-next'
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'
import QrcodeVue from 'qrcode.vue'

import { getMyOrdersApi } from '@/services/order.service'
import type { Order, OrderStatus } from '@/types/booking.types'
import { formatCurrency, formatDateTime, formatDate } from '@/utils/formatters'
import { useAuthStore } from '@/stores/auth'

const authStore = useAuthStore()

const orders = ref<Order[]>([])
const isLoading = ref(true)
const errorMessage = ref('')
const selectedStatus = ref<string>('ALL')
const searchQuery = ref('')
const selectedOrder = ref<Order | null>(null)

// Pagination
const currentPage = ref(1)
const pageSize = ref(10)

async function fetchOrders() {
  try {
    isLoading.value = true
    errorMessage.value = ''
    const data = await getMyOrdersApi()
    orders.value = data
  } catch (error: unknown) {
    if (axios.isAxiosError(error) && error.response?.data?.message) {
      errorMessage.value = error.response.data.message as string
    } else {
      errorMessage.value = 'Gagal memuat riwayat pesanan. Silakan coba lagi.'
    }
    console.error('Failed to fetch orders:', error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => void fetchOrders())

// Stats computed
const stats = computed(() => ({
  all: orders.value.length,
  pending: orders.value.filter(o => o.status === 'PENDING').length,
  paid: orders.value.filter(o => o.status === 'PAID').length,
  cancelled: orders.value.filter(o => o.status === 'CANCELLED').length,
  expired: orders.value.filter(o => o.status === 'EXPIRED').length,
}))

const filteredOrders = computed(() => {
  currentPage.value = 1
  return orders.value.filter((order) => {
    const matchesStatus =
      selectedStatus.value === 'ALL' || order.status === selectedStatus.value

    const matchesSearch =
      searchQuery.value.trim() === '' ||
      order.order_code.toLowerCase().includes(searchQuery.value.toLowerCase().trim()) ||
      order.customer_name.toLowerCase().includes(searchQuery.value.toLowerCase().trim())

    return matchesStatus && matchesSearch
  })
})

const totalPages = computed(() => Math.ceil(filteredOrders.value.length / pageSize.value))

const paginatedOrders = computed(() => {
  const start = (currentPage.value - 1) * pageSize.value
  return filteredOrders.value.slice(start, start + pageSize.value)
})

function setPage(page: number) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

const statusTabs = [
  { key: 'ALL', label: 'Semua', count: computed(() => stats.value.all) },
  { key: 'PENDING', label: 'Menunggu Bayar', count: computed(() => stats.value.pending) },
  { key: 'PAID', label: 'Lunas', count: computed(() => stats.value.paid) },
  { key: 'CANCELLED', label: 'Dibatalkan', count: computed(() => stats.value.cancelled) },
]

function getStatusBadgeClass(status: OrderStatus) {
  switch (status) {
    case 'PENDING':  return 'bg-orange-50 text-orange-700 border-orange-200'
    case 'PAID':     return 'bg-green-50 text-green-700 border-green-200'
    case 'CANCELLED':return 'bg-red-50 text-red-700 border-red-200'
    case 'EXPIRED':  return 'bg-gray-100 text-gray-500 border-gray-200'
    default:         return 'bg-gray-50 text-gray-600 border-gray-200'
  }
}

function getStatusTabActive(key: string) {
  if (key !== selectedStatus.value) return false
  return true
}

function getStatusTabClass(key: string) {
  if (!getStatusTabActive(key)) return 'text-[#66706C] border-transparent hover:text-[#1D2724]'
  switch (key) {
    case 'PENDING':   return 'text-orange-600 border-orange-500 font-bold'
    case 'PAID':      return 'text-green-700 border-green-600 font-bold'
    case 'CANCELLED': return 'text-red-600 border-red-500 font-bold'
    default:          return 'text-[#173B35] border-[#173B35] font-bold'
  }
}

function getStatusLabel(status: OrderStatus) {
  switch (status) {
    case 'PENDING':   return 'Menunggu Bayar'
    case 'PAID':      return 'Lunas'
    case 'CANCELLED': return 'Dibatalkan'
    case 'EXPIRED':   return 'Kadaluarsa'
    default:          return status
  }
}

function openDetail(order: Order) {
  selectedOrder.value = order
}

function closeDetail() {
  selectedOrder.value = null
}

// Stat card styles
const statCards = computed(() => [
  { label: 'Total Pesanan', value: stats.value.all, color: 'text-[#173B35]', bg: 'bg-[#173B35]/8' },
  { label: 'Menunggu Bayar', value: stats.value.pending, color: 'text-orange-600', bg: 'bg-orange-50' },
  { label: 'Lunas', value: stats.value.paid, color: 'text-green-700', bg: 'bg-green-50' },
  { label: 'Dibatalkan', value: stats.value.cancelled + stats.value.expired, color: 'text-red-600', bg: 'bg-red-50' },
])
</script>

<template>
  <div class="space-y-6">

    <!-- ======================================================= -->
    <!-- WELCOME HEADER                                          -->
    <!-- ======================================================= -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-xl border border-[#173B35]/10">
      <div>
        <h1 class="text-2xl font-black text-[#1D2724] tracking-tight">
          Halo, {{ authStore.user?.name?.split(' ')[0] || 'Wisatawan' }}!
        </h1>
        <p class="text-sm text-[#66706C] mt-0.5">Kelola pesanan tiket dan riwayat kunjungan Anda di sini.</p>
      </div>
      <router-link
        to="/booking"
        class="inline-flex items-center gap-2 rounded-lg bg-[#C9965B] px-5 py-2.5 text-sm font-bold text-[#1D2724] transition hover:bg-[#b0814a] active:scale-[0.98] shrink-0"
      >
        <Plus class="h-4 w-4" />
        Pesan Tiket Baru
      </router-link>
    </div>

    <!-- ======================================================= -->
    <!-- STATS ROW                                               -->
    <!-- ======================================================= -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      <div
        v-for="stat in statCards"
        :key="stat.label"
        class="bg-white border border-[#173B35]/10 rounded-xl p-4"
      >
        <p class="text-xs text-[#66706C] font-medium mb-1">{{ stat.label }}</p>
        <p class="text-3xl font-black" :class="stat.color">{{ stat.value }}</p>
      </div>
    </div>

    <!-- ======================================================= -->
    <!-- PESANAN SECTION                                         -->
    <!-- ======================================================= -->
    <div class="bg-white border border-[#173B35]/10 rounded-xl overflow-hidden">

      <!-- Filter Bar -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-[#173B35]/10 px-4 sm:px-6">
        <!-- Status Tabs -->
        <div class="flex items-center gap-0 overflow-x-auto">
          <button
            v-for="tab in statusTabs"
            :key="tab.key"
            type="button"
            class="whitespace-nowrap px-4 py-4 text-sm border-b-2 transition-all"
            :class="getStatusTabClass(tab.key)"
            @click="selectedStatus = tab.key"
          >
            {{ tab.label }}
            <span class="ml-1.5 text-xs font-normal opacity-70">({{ tab.count.value }})</span>
          </button>
        </div>

        <!-- Search -->
        <div class="relative w-full sm:w-56 pb-3 sm:pb-0">
          <Search class="absolute left-3 top-2.5 h-4 w-4 text-[#66706C]" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari kode booking..."
            class="w-full rounded-lg border border-[#173B35]/20 bg-[#F7F5EF] pl-9 pr-3 py-2 text-sm text-[#1D2724] outline-none transition focus:border-[#4F7465] focus:ring-2 focus:ring-[#4F7465]/10 focus:bg-white"
          />
        </div>
      </div>

      <!-- ======================================================= -->
      <!-- LOADING STATE                                           -->
      <!-- ======================================================= -->
      <div v-if="isLoading" class="py-16 flex flex-col items-center gap-3 text-[#66706C]">
        <LoaderCircle class="h-8 w-8 animate-spin text-[#173B35]" />
        <p class="text-sm font-medium">Memuat pesanan...</p>
        <!-- Skeleton cards -->
        <div class="w-full px-6 space-y-4 mt-2">
          <div v-for="i in 3" :key="i" class="rounded-xl border border-[#173B35]/10 p-4 flex gap-4 animate-pulse">
            <div class="w-24 h-20 rounded-lg bg-[#F7F5EF] shrink-0"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-[#F7F5EF] rounded w-2/5"></div>
              <div class="h-3 bg-[#F7F5EF] rounded w-1/3"></div>
              <div class="h-3 bg-[#F7F5EF] rounded w-1/4"></div>
            </div>
            <div class="space-y-2 hidden sm:block">
              <div class="h-8 w-24 bg-[#F7F5EF] rounded-lg"></div>
              <div class="h-8 w-24 bg-[#F7F5EF] rounded-lg"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- ======================================================= -->
      <!-- ERROR STATE                                             -->
      <!-- ======================================================= -->
      <div v-else-if="errorMessage" class="py-16 flex flex-col items-center gap-4 px-6 text-center">
        <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center">
          <AlertCircle class="h-7 w-7 text-red-500" />
        </div>
        <div>
          <h3 class="font-bold text-[#1D2724] mb-1">Gagal Memuat Pesanan</h3>
          <p class="text-sm text-[#66706C]">Terjadi kesalahan saat memuat data. Silakan coba lagi.</p>
        </div>
        <button
          type="button"
          class="inline-flex items-center gap-2 rounded-lg border border-[#173B35]/20 px-5 py-2.5 text-sm font-bold text-[#173B35] transition hover:bg-[#F7F5EF]"
          @click="fetchOrders"
        >
          <RefreshCw class="h-4 w-4" />
          Coba Lagi
        </button>
      </div>

      <!-- ======================================================= -->
      <!-- EMPTY STATE                                             -->
      <!-- ======================================================= -->
      <div
        v-else-if="filteredOrders.length === 0"
        class="py-20 flex flex-col items-center gap-4 px-6 text-center"
      >
        <div class="w-16 h-16 rounded-full bg-[#F7F5EF] flex items-center justify-center">
          <Ticket class="h-8 w-8 text-[#66706C]/50" />
        </div>
        <div>
          <h3 class="font-bold text-[#1D2724] mb-1">Belum Ada Pesanan</h3>
          <p class="text-sm text-[#66706C]">
            {{ selectedStatus === 'ALL' && !searchQuery
              ? 'Anda belum memiliki tiket. Yuk, rencanakan liburan ke Sarangan sekarang!'
              : 'Tidak ada pesanan yang sesuai dengan filter yang dipilih.'
            }}
          </p>
        </div>
        <router-link
          v-if="selectedStatus === 'ALL' && !searchQuery"
          to="/booking"
          class="inline-flex items-center gap-2 rounded-lg bg-[#C9965B] px-5 py-2.5 text-sm font-bold text-[#1D2724] transition hover:bg-[#b0814a]"
        >
          <Plus class="h-4 w-4" />
          Pesan Tiket Sekarang
        </router-link>
        <button
          v-else
          type="button"
          class="text-sm text-[#4F7465] font-bold hover:underline"
          @click="selectedStatus = 'ALL'; searchQuery = ''"
        >
          Tampilkan semua pesanan
        </button>
      </div>

      <!-- ======================================================= -->
      <!-- ORDER CARDS                                             -->
      <!-- ======================================================= -->
      <div v-else class="divide-y divide-[#173B35]/8">
        <div
          v-for="order in paginatedOrders"
          :key="order.id"
          class="flex flex-col sm:flex-row gap-4 p-4 sm:p-5 hover:bg-[#F7F5EF]/60 transition-colors"
        >
          <!-- Thumbnail -->
          <div class="w-full sm:w-24 h-20 rounded-lg overflow-hidden bg-[#173B35]/10 shrink-0">
            <img
              src="/images/sarangan-story-2.jpg"
              alt="Telaga Sarangan"
              class="w-full h-full object-cover"
            />
          </div>

          <!-- Main Info -->
          <div class="flex-1 min-w-0">
            <!-- Ticket name & status -->
            <div class="flex items-start justify-between gap-3 mb-2">
              <div>
                <h3 class="font-bold text-[#1D2724] text-sm">
                  {{ order.items?.[0]?.ticket_type?.name || 'Tiket Wisata Sarangan' }}
                  <span v-if="order.items && order.items.length > 1" class="text-[#66706C] font-normal">
                    +{{ order.items.length - 1 }} lainnya
                  </span>
                </h3>
                <p class="text-xs text-[#66706C] font-mono mt-0.5">{{ order.order_code }}</p>
              </div>
              <span
                class="shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-bold border"
                :class="getStatusBadgeClass(order.status)"
              >
                {{ getStatusLabel(order.status) }}
              </span>
            </div>

            <!-- Details row -->
            <div class="flex flex-wrap gap-x-5 gap-y-1 text-xs text-[#66706C]">
              <span class="flex items-center gap-1.5">
                <Calendar class="h-3.5 w-3.5" />
                {{ formatDate(order.visit_date) }}
              </span>
              <span class="flex items-center gap-1.5">
                <Users class="h-3.5 w-3.5" />
                {{ order.total_quantity }} Orang
              </span>
              <span class="flex items-center gap-1.5">
                <Clock class="h-3.5 w-3.5" />
                Dipesan {{ formatDateTime(order.created_at) }}
              </span>
            </div>

            <!-- Total -->
            <p class="text-base font-black text-[#173B35] mt-2">{{ formatCurrency(order.total_amount) }}</p>
          </div>

          <!-- Actions -->
          <div class="flex sm:flex-col gap-2 sm:items-end justify-end sm:justify-start shrink-0">
            <button
              type="button"
              class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-[#173B35]/20 px-3 py-2 text-xs font-bold text-[#173B35] transition hover:bg-[#F7F5EF] whitespace-nowrap"
              @click="openDetail(order)"
            >
              <Eye class="h-3.5 w-3.5" />
              Lihat Detail
            </button>
            <button
              v-if="order.status === 'PAID'"
              type="button"
              class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-[#173B35] px-3 py-2 text-xs font-bold text-white transition hover:bg-[#122c27] whitespace-nowrap"
              @click="openDetail(order)"
            >
              <Ticket class="h-3.5 w-3.5" />
              E-Ticket
            </button>
            <a
              v-else-if="order.status === 'PENDING' && order.payment_url"
              :href="order.payment_url"
              class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-orange-500 px-3 py-2 text-xs font-bold text-white transition hover:bg-orange-600 whitespace-nowrap"
            >
              Bayar Sekarang
            </a>
          </div>
        </div>
      </div>

      <!-- ======================================================= -->
      <!-- PAGINATION                                              -->
      <!-- ======================================================= -->
      <div
        v-if="!isLoading && !errorMessage && filteredOrders.length > 0"
        class="flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-[#173B35]/10 px-5 py-3 bg-[#F7F5EF]/50"
      >
        <!-- Page info -->
        <p class="text-xs text-[#66706C]">
          Menampilkan {{ Math.min((currentPage - 1) * pageSize + 1, filteredOrders.length) }}–{{ Math.min(currentPage * pageSize, filteredOrders.length) }}
          dari {{ filteredOrders.length }} pesanan
        </p>

        <!-- Page controls -->
        <div class="flex items-center gap-1">
          <button
            type="button"
            class="w-8 h-8 flex items-center justify-center rounded-lg border border-[#173B35]/15 text-[#66706C] transition hover:bg-white hover:border-[#4F7465] disabled:opacity-40 disabled:cursor-not-allowed"
            :disabled="currentPage <= 1"
            @click="setPage(currentPage - 1)"
          >
            <ChevronLeft class="h-4 w-4" />
          </button>

          <button
            v-for="page in totalPages"
            :key="page"
            type="button"
            class="w-8 h-8 flex items-center justify-center rounded-lg text-xs font-bold transition"
            :class="page === currentPage
              ? 'bg-[#173B35] text-white'
              : 'border border-[#173B35]/15 text-[#66706C] hover:bg-white hover:border-[#4F7465]'"
            @click="setPage(page)"
          >
            {{ page }}
          </button>

          <button
            type="button"
            class="w-8 h-8 flex items-center justify-center rounded-lg border border-[#173B35]/15 text-[#66706C] transition hover:bg-white hover:border-[#4F7465] disabled:opacity-40 disabled:cursor-not-allowed"
            :disabled="currentPage >= totalPages"
            @click="setPage(currentPage + 1)"
          >
            <ChevronRight class="h-4 w-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- ======================================================= -->
    <!-- DETAIL MODAL                                            -->
    <!-- ======================================================= -->
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="selectedOrder"
        class="fixed inset-0 z-[100] flex items-center justify-center bg-[#1D2724]/70 p-4 backdrop-blur-sm"
        @click.self="closeDetail"
      >
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl max-h-[90vh] overflow-y-auto">

          <!-- Modal Header -->
          <div class="flex items-center justify-between px-6 py-5 border-b border-[#173B35]/10">
            <div>
              <p class="text-xs font-bold text-[#66706C] uppercase tracking-widest">E-Ticket Sarangan</p>
              <h3 class="font-mono text-xl font-black text-[#1D2724]">{{ selectedOrder.order_code }}</h3>
            </div>
            <button
              type="button"
              class="w-9 h-9 rounded-lg flex items-center justify-center text-[#66706C] hover:bg-[#F7F5EF] transition-colors"
              @click="closeDetail"
            >
              <X class="h-5 w-5" />
            </button>
          </div>

          <div class="p-6 space-y-5">
            <!-- Status -->
            <div
              class="flex items-center justify-between rounded-xl px-4 py-3 border"
              :class="getStatusBadgeClass(selectedOrder.status)"
            >
              <span class="text-xs font-bold uppercase tracking-wider">Status</span>
              <span class="text-sm font-black">{{ getStatusLabel(selectedOrder.status) }}</span>
            </div>

            <!-- Customer Info -->
            <div class="rounded-xl bg-[#F7F5EF] p-4 space-y-3 text-sm">
              <div class="flex justify-between gap-2 pb-2 border-b border-[#173B35]/10">
                <span class="text-[#66706C]">Nama Pemesan</span>
                <span class="font-bold text-[#1D2724] text-right">{{ selectedOrder.customer_name }}</span>
              </div>
              <div class="flex justify-between gap-2 pb-2 border-b border-[#173B35]/10">
                <span class="text-[#66706C]">Tanggal Kunjungan</span>
                <span class="font-bold text-[#173B35] text-right">{{ formatDate(selectedOrder.visit_date) }}</span>
              </div>
              <div class="flex justify-between gap-2 pb-2 border-b border-[#173B35]/10">
                <span class="text-[#66706C]">Jumlah Pengunjung</span>
                <span class="font-bold text-[#1D2724]">{{ selectedOrder.total_quantity }} orang</span>
              </div>
              <div class="flex justify-between gap-2">
                <span class="text-[#66706C]">Dipesan pada</span>
                <span class="font-medium text-[#1D2724] text-right text-xs">{{ formatDateTime(selectedOrder.created_at) }}</span>
              </div>
            </div>

            <!-- Items -->
            <div>
              <h4 class="text-xs font-bold uppercase tracking-widest text-[#66706C] mb-3">Rincian Tiket</h4>
              <div class="space-y-2">
                <div
                  v-for="item in selectedOrder.items"
                  :key="item.id"
                  class="flex justify-between items-center p-3 rounded-xl border border-[#173B35]/10 text-sm"
                >
                  <div>
                    <p class="font-bold text-[#1D2724]">{{ item.ticket_type?.name || 'Tiket' }}</p>
                    <p class="text-[#66706C] text-xs">{{ item.quantity }}× {{ formatCurrency(item.price) }}</p>
                  </div>
                  <span class="font-black text-[#173B35]">{{ formatCurrency(item.subtotal) }}</span>
                </div>
              </div>
              <!-- Total -->
              <div class="mt-3 px-4 py-3 rounded-xl bg-[#173B35] text-white flex items-center justify-between">
                <div>
                  <p class="text-xs text-white/60">Total</p>
                  <p class="text-sm font-medium">{{ selectedOrder.total_quantity }} Tiket</p>
                </div>
                <span class="font-black text-lg">{{ formatCurrency(selectedOrder.total_amount) }}</span>
              </div>
            </div>

            <!-- Action: Bayar (PENDING) -->
            <div v-if="selectedOrder.status === 'PENDING' && selectedOrder.payment_url">
              <p class="text-xs text-orange-600 font-bold text-center mb-3">⚠️ Tiket belum dibayar. Segera selesaikan pembayaran.</p>
              <a
                :href="selectedOrder.payment_url"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#C9965B] py-3 text-sm font-bold text-[#1D2724] transition hover:bg-[#b0814a]"
              >
                Bayar Sekarang
              </a>
            </div>

            <!-- QR Code (PAID) -->
            <div v-if="selectedOrder.status === 'PAID'" class="flex flex-col items-center gap-3 pt-2 border-t border-[#173B35]/10">
              <p class="text-xs font-bold text-[#66706C] uppercase tracking-widest">QR Code E-Ticket</p>
              <div class="bg-white p-3 border-2 border-[#173B35]/15 rounded-xl shadow-sm">
                <qrcode-vue :value="selectedOrder.order_code" :size="180" level="H" />
              </div>
              <p class="text-xs text-[#66706C] text-center max-w-xs">
                Tunjukkan QR Code ini kepada petugas di pintu masuk Telaga Sarangan.
              </p>
              <p v-if="selectedOrder.qr_expires_at" class="text-xs font-bold text-red-500">
                Kedaluwarsa: {{ formatDate(selectedOrder.qr_expires_at) }}
              </p>
            </div>

          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>
