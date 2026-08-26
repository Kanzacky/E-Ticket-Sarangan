<script setup lang="ts">
import {
  AlertCircle,
  Calendar,
  ChevronLeft,
  Clock,
  Eye,
  LoaderCircle,
  Plus,
  Search,
  Ticket,
  Users,
  X,
  MapPin
} from 'lucide-vue-next'
import { computed, onMounted, ref } from 'vue'
import axios from 'axios'

import { getMyOrdersApi } from '@/services/order.service'
import type { Order, OrderStatus } from '@/types/booking.types'
import { formatCurrency, formatDateTime, formatDate } from '@/utils/formatters'

const orders = ref<Order[]>([])
const isLoading = ref(true)
const errorMessage = ref('')
const selectedStatus = ref<string>('ALL')
const searchQuery = ref('')
const selectedOrder = ref<Order | null>(null)

onMounted(async () => {
  try {
    isLoading.value = true
    const data = await getMyOrdersApi()
    orders.value = data
  } catch (error: unknown) {
    if (axios.isAxiosError(error) && error.response?.data?.message) {
      errorMessage.value = error.response.data.message as string
    } else {
      errorMessage.value = 'Gagal memuat riwayat booking.'
    }
  } finally {
    isLoading.value = false
  }
})

const filteredOrders = computed(() => {
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

function getStatusBadgeClass(status: OrderStatus) {
  switch (status) {
    case 'PENDING':
      return 'bg-orange-50 text-orange-700 border-orange-200'
    case 'PAID':
      return 'bg-green-50 text-green-700 border-green-200'
    case 'CANCELLED':
      return 'bg-red-50 text-red-700 border-red-200'
    case 'EXPIRED':
      return 'bg-gray-100 text-gray-600 border-gray-200'
    default:
      return 'bg-gray-50 text-gray-700 border-gray-200'
  }
}

function getStatusLabel(status: OrderStatus) {
  switch (status) {
    case 'PENDING':
      return 'Menunggu Pembayaran'
    case 'PAID':
      return 'Lunas'
    case 'CANCELLED':
      return 'Dibatalkan'
    case 'EXPIRED':
      return 'Kadaluarsa'
    default:
      return status
  }
}

function openDetail(order: Order) {
  selectedOrder.value = order
}

function closeDetail() {
  selectedOrder.value = null
}
</script>

<template>
  <div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <router-link
          to="/wisatawan/dashboard"
          class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-[#173B35]/10 text-[#173B35] shadow-sm transition hover:bg-[#F7F5EF]"
        >
          <ChevronLeft class="h-5 w-5" />
        </router-link>
        <div>
          <h1 class="text-2xl font-bold text-[#1D2724] tracking-tight">E-Ticket Saya</h1>
          <p class="text-sm text-[#66706C]">Daftar pesanan dan riwayat kunjungan ke Sarangan</p>
        </div>
      </div>

      <router-link
        to="/wisatawan/booking"
        class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#173B35] px-5 py-2.5 text-sm font-bold text-white shadow-sm transition-all hover:bg-[#1D2724] active:scale-95"
      >
        <Plus class="h-4 w-4" /> Pesan Tiket Baru
      </router-link>
    </div>

    <!-- Filters & Search -->
    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 rounded-2xl bg-white p-4 border border-[#173B35]/10 shadow-sm">
      <!-- Status Tabs -->
      <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
        <button
          type="button"
          class="rounded-xl px-4 py-2 text-xs font-bold transition-all"
          :class="selectedStatus === 'ALL' ? 'bg-[#4F7465] text-white shadow-md' : 'bg-[#F7F5EF] text-[#66706C] hover:bg-[#e8e6df]'"
          @click="selectedStatus = 'ALL'"
        >
          Semua ({{ orders.length }})
        </button>
        <button
          type="button"
          class="rounded-xl px-4 py-2 text-xs font-bold transition-all"
          :class="selectedStatus === 'PENDING' ? 'bg-orange-500 text-white shadow-md' : 'bg-[#F7F5EF] text-[#66706C] hover:bg-[#e8e6df]'"
          @click="selectedStatus = 'PENDING'"
        >
          Menunggu Bayar
        </button>
        <button
          type="button"
          class="rounded-xl px-4 py-2 text-xs font-bold transition-all"
          :class="selectedStatus === 'PAID' ? 'bg-[#173B35] text-white shadow-md' : 'bg-[#F7F5EF] text-[#66706C] hover:bg-[#e8e6df]'"
          @click="selectedStatus = 'PAID'"
        >
          Lunas
        </button>
        <button
          type="button"
          class="rounded-xl px-4 py-2 text-xs font-bold transition-all"
          :class="selectedStatus === 'CANCELLED' ? 'bg-red-600 text-white shadow-md' : 'bg-[#F7F5EF] text-[#66706C] hover:bg-[#e8e6df]'"
          @click="selectedStatus = 'CANCELLED'"
        >
          Dibatalkan
        </button>
      </div>

      <!-- Search Box -->
      <div class="relative w-full sm:w-64">
        <Search class="absolute left-3 top-2.5 h-4 w-4 text-[#66706C]" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari kode booking..."
          class="w-full rounded-xl border border-[#173B35]/20 bg-white pl-10 pr-4 py-2 text-sm text-[#1D2724] outline-none transition focus:border-[#4F7465] focus:ring-2 focus:ring-[#4F7465]/10"
        />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="py-16 text-center text-[#66706C] bg-white rounded-2xl border border-[#173B35]/10 shadow-sm">
      <LoaderCircle class="mx-auto h-8 w-8 animate-spin text-[#173B35]" />
      <p class="mt-3 text-sm font-medium">Memuat tiket...</p>
    </div>

    <!-- Error State -->
    <div
      v-else-if="errorMessage"
      class="flex items-center gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700 shadow-sm"
    >
      <AlertCircle class="h-5 w-5 flex-shrink-0" />
      <span class="text-sm font-medium">{{ errorMessage }}</span>
    </div>

    <!-- Empty State -->
    <div
      v-else-if="filteredOrders.length === 0"
      class="py-20 text-center bg-white rounded-2xl border border-[#173B35]/10 shadow-sm px-6"
    >
      <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-[#F7F5EF] text-[#66706C]">
        <Ticket class="h-10 w-10" />
      </div>
      <h3 class="mt-6 text-xl font-bold text-[#1D2724]">Belum Ada Pesanan</h3>
      <p class="mt-2 text-sm text-[#66706C] max-w-md mx-auto">
        Anda belum memiliki tiket yang sesuai. Yuk, rencanakan liburan ke Sarangan sekarang!
      </p>
      <router-link
        to="/wisatawan/booking"
        class="mt-8 inline-flex items-center gap-2 rounded-xl bg-[#C9965B] px-6 py-3 text-sm font-bold text-[#173B35] shadow-sm hover:bg-[#b0814a] transition-colors"
      >
        <Plus class="h-4 w-4" /> Pesan Tiket Sekarang
      </router-link>
    </div>

    <!-- Orders List -->
    <div v-else class="space-y-4">
      <div
        v-for="order in filteredOrders"
        :key="order.id"
        class="group rounded-2xl border-2 border-[#173B35]/10 bg-white p-6 shadow-sm transition-all hover:border-[#4F7465] hover:shadow-md cursor-pointer relative overflow-hidden"
        @click="openDetail(order)"
      >
        <!-- Status Label Top Right for PAID -->
        <div v-if="order.status === 'PAID'" class="absolute top-0 right-0 bg-[#4F7465] text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl uppercase tracking-wider">
          Valid
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-[#173B35]/10 pb-4">
          <div class="flex items-center gap-3">
            <span class="font-mono text-lg font-black text-[#1D2724] group-hover:text-[#4F7465] transition-colors">
              {{ order.order_code }}
            </span>
            <span
              class="inline-flex items-center rounded-lg px-2.5 py-1 text-xs font-bold border uppercase tracking-wider"
              :class="getStatusBadgeClass(order.status)"
            >
              {{ getStatusLabel(order.status) }}
            </span>
          </div>
          <span class="text-xs font-medium text-[#66706C] flex items-center gap-1.5">
            <Clock class="h-4 w-4" /> Dipesan: {{ formatDateTime(order.created_at) }}
          </span>
        </div>

        <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-6 text-sm">
          <div>
            <span class="text-xs font-bold text-[#66706C] uppercase tracking-wider block mb-1.5">Tanggal Kunjungan</span>
            <span class="font-bold text-[#1D2724] flex items-center gap-2 text-base">
              <Calendar class="h-5 w-5 text-[#4F7465]" />
              {{ formatDate(order.visit_date) }}
            </span>
          </div>

          <div>
            <span class="text-xs font-bold text-[#66706C] uppercase tracking-wider block mb-1.5">Jumlah Tiket</span>
            <span class="font-bold text-[#1D2724] flex items-center gap-2 text-base">
              <Users class="h-5 w-5 text-[#4F7465]" />
              {{ order.total_quantity }} Orang
            </span>
          </div>

          <div>
            <span class="text-xs font-bold text-[#66706C] uppercase tracking-wider block mb-1.5">Total Pembayaran</span>
            <span class="font-black text-[#173B35] text-lg">
              {{ formatCurrency(order.total_amount) }}
            </span>
          </div>
        </div>

        <div class="mt-5 pt-4 border-t border-[#173B35]/10 flex items-center justify-between bg-[#F7F5EF]/50 -mx-6 -mb-6 px-6 py-4 rounded-b-2xl">
          <span class="text-sm font-medium text-[#66706C]">Atas nama: <strong class="text-[#1D2724]">{{ order.customer_name }}</strong></span>
          <button
            type="button"
            class="inline-flex items-center gap-2 text-sm font-bold text-[#173B35] group-hover:text-[#4F7465] transition-colors"
          >
            <Eye class="h-4 w-4" /> Lihat E-Ticket &rarr;
          </button>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div
      v-if="selectedOrder"
      class="fixed inset-0 z-[100] flex items-center justify-center bg-[#1D2724]/60 p-4 backdrop-blur-sm transition-opacity"
      @click.self="closeDetail"
    >
      <div class="w-full max-w-lg rounded-3xl bg-white p-8 shadow-2xl max-h-[90vh] overflow-y-auto relative">
        <button
          type="button"
          class="absolute top-6 right-6 rounded-full p-2 text-[#66706C] hover:bg-[#F7F5EF] hover:text-[#173B35] transition-colors"
          @click="closeDetail"
        >
          <X class="h-6 w-6" />
        </button>

        <div class="text-center pb-6 border-b border-[#173B35]/10">
          <div class="flex justify-center mb-4">
            <div class="bg-[#173B35] p-3 rounded-2xl">
              <MapPin class="h-8 w-8 text-white" />
            </div>
          </div>
          <span class="text-xs font-bold text-[#66706C] uppercase tracking-widest">E-Ticket Sarangan</span>
          <h3 class="font-mono text-2xl font-black text-[#1D2724] mt-2">{{ selectedOrder.order_code }}</h3>
        </div>

        <div class="mt-6 space-y-6">
          <!-- Status Banner -->
          <div class="flex items-center justify-between rounded-xl p-4 border-2" :class="getStatusBadgeClass(selectedOrder.status)">
            <span class="text-sm font-bold">Status Pembayaran:</span>
            <span class="text-sm font-black uppercase tracking-wider">{{ getStatusLabel(selectedOrder.status) }}</span>
          </div>

          <!-- Customer Info -->
          <div class="rounded-2xl bg-[#F7F5EF] p-5 text-sm space-y-3">
            <div class="flex flex-wrap justify-between gap-2 border-b border-[#173B35]/10 pb-2">
              <span class="text-[#66706C] font-medium">Pemesan:</span>
              <span class="font-bold text-[#1D2724]">{{ selectedOrder.customer_name }}</span>
            </div>
            <div class="flex flex-wrap justify-between gap-2 border-b border-[#173B35]/10 pb-2">
              <span class="text-[#66706C] font-medium">Tanggal Kunjungan:</span>
              <span class="font-bold text-[#173B35]">{{ formatDate(selectedOrder.visit_date) }}</span>
            </div>
            <div class="flex flex-wrap justify-between gap-2">
              <span class="text-[#66706C] font-medium">Dipesan pada:</span>
              <span class="font-medium text-[#1D2724]">{{ formatDateTime(selectedOrder.created_at) }}</span>
            </div>
          </div>

          <!-- Items Breakdown -->
          <div>
            <h4 class="text-xs font-bold uppercase tracking-widest text-[#66706C] mb-4">Rincian Tiket</h4>
            <div class="space-y-3 text-sm">
              <div
                v-for="item in selectedOrder.items"
                :key="item.id"
                class="flex justify-between items-center p-4 rounded-xl border border-[#173B35]/10"
              >
                <div>
                  <span class="font-bold text-[#1D2724] block mb-1">{{ item.ticket_type?.name || 'Tiket' }}</span>
                  <span class="text-[#66706C] text-xs font-medium">{{ item.quantity }}x &times; {{ formatCurrency(item.price) }}</span>
                </div>
                <span class="font-black text-[#173B35]">{{ formatCurrency(item.subtotal) }}</span>
              </div>
            </div>

            <div class="mt-4 p-4 rounded-xl bg-[#173B35] text-white flex justify-between items-center shadow-lg">
              <div>
                <span class="text-xs text-white/70 block mb-1">Total Pembayaran</span>
                <span class="font-medium text-sm">{{ selectedOrder.total_quantity }} Tiket</span>
              </div>
              <span class="font-black text-xl">{{ formatCurrency(selectedOrder.total_amount) }}</span>
            </div>
          </div>
          
          <!-- QR Placeholder for PAID -->
          <div v-if="selectedOrder.status === 'PAID'" class="pt-6 border-t border-[#173B35]/10 flex flex-col items-center justify-center text-center">
            <div class="w-40 h-40 bg-white border-4 border-[#173B35] rounded-xl flex items-center justify-center mb-3">
              <QrCode class="w-16 h-16 text-[#173B35]" />
            </div>
            <p class="text-xs font-bold text-[#66706C] max-w-xs">Tunjukkan QR Code ini kepada petugas di pintu masuk telaga Sarangan.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
