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
      return 'bg-amber-50 text-amber-700 border-amber-200'
    case 'PAID':
      return 'bg-emerald-50 text-emerald-700 border-emerald-200'
    case 'CANCELLED':
      return 'bg-red-50 text-red-700 border-red-200'
    case 'EXPIRED':
      return 'bg-slate-100 text-slate-600 border-slate-200'
    default:
      return 'bg-slate-50 text-slate-700 border-slate-200'
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
  <div class="min-h-screen bg-slate-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-5xl">
      <!-- Header -->
      <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <router-link
            to="/"
            class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-slate-200 text-slate-600 shadow-sm transition hover:bg-slate-100"
          >
            <ChevronLeft class="h-5 w-5" />
          </router-link>
          <div>
            <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">Riwayat Booking</h1>
            <p class="text-sm text-slate-500">Daftar pesanan dan e-Ticket Sarangan Anda</p>
          </div>
        </div>

        <router-link
          to="/booking"
          class="inline-flex items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700"
        >
          <Plus class="h-4 w-4" /> Pesan Tiket Baru
        </router-link>
      </div>

      <!-- Filters & Search -->
      <div class="mb-6 flex flex-col sm:flex-row items-center justify-between gap-4 rounded-2xl bg-white p-4 border border-slate-200 shadow-sm">
        <!-- Status Tabs -->
        <div class="flex flex-wrap items-center gap-1.5 w-full sm:w-auto">
          <button
            type="button"
            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
            :class="selectedStatus === 'ALL' ? 'bg-sky-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            @click="selectedStatus = 'ALL'"
          >
            Semua ({{ orders.length }})
          </button>
          <button
            type="button"
            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
            :class="selectedStatus === 'PENDING' ? 'bg-amber-500 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            @click="selectedStatus = 'PENDING'"
          >
            Menunggu Bayar
          </button>
          <button
            type="button"
            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
            :class="selectedStatus === 'PAID' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            @click="selectedStatus = 'PAID'"
          >
            Lunas
          </button>
          <button
            type="button"
            class="rounded-lg px-3 py-1.5 text-xs font-semibold transition"
            :class="selectedStatus === 'CANCELLED' ? 'bg-red-600 text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
            @click="selectedStatus = 'CANCELLED'"
          >
            Dibatalkan
          </button>
        </div>

        <!-- Search Box -->
        <div class="relative w-full sm:w-64">
          <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari kode booking..."
            class="w-full rounded-xl border border-slate-200 bg-slate-50 pl-9 pr-3 py-2 text-xs text-slate-900 outline-none transition focus:border-sky-500 focus:bg-white"
          />
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="py-16 text-center text-slate-400 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <LoaderCircle class="mx-auto h-8 w-8 animate-spin text-sky-600" />
        <p class="mt-3 text-sm font-medium">Memuat riwayat pemesanan...</p>
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
        class="py-16 text-center bg-white rounded-2xl border border-slate-200 shadow-sm px-4"
      >
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-sky-50 text-sky-600">
          <Ticket class="h-7 w-7" />
        </div>
        <h3 class="mt-4 text-base font-bold text-slate-800">Belum Ada Riwayat Pemesanan</h3>
        <p class="mt-1 text-xs text-slate-400 max-w-sm mx-auto">
          Anda belum melakukan pemesanan tiket wisata Sarangan. Pesan tiket sekarang dengan mudah dan cepat.
        </p>
        <router-link
          to="/booking"
          class="mt-5 inline-flex items-center gap-2 rounded-xl bg-sky-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-sky-700"
        >
          <Plus class="h-3.5 w-3.5" /> Pesan Tiket
        </router-link>
      </div>

      <!-- Orders List -->
      <div v-else class="space-y-4">
        <div
          v-for="order in filteredOrders"
          :key="order.id"
          class="group rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-sky-300 hover:shadow-md cursor-pointer"
          @click="openDetail(order)"
        >
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-3">
            <div class="flex items-center gap-3">
              <span class="font-mono text-sm font-extrabold text-slate-900 group-hover:text-sky-600 transition">
                {{ order.order_code }}
              </span>
              <span
                class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold border"
                :class="getStatusBadgeClass(order.status)"
              >
                {{ getStatusLabel(order.status) }}
              </span>
            </div>
            <span class="text-xs text-slate-400 flex items-center gap-1">
              <Clock class="h-3.5 w-3.5" /> Dipesan: {{ formatDateTime(order.created_at) }}
            </span>
          </div>

          <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
            <div>
              <span class="text-xs text-slate-400 block mb-0.5">Tanggal Kunjungan</span>
              <span class="font-medium text-slate-800 flex items-center gap-1.5">
                <Calendar class="h-4 w-4 text-sky-600" />
                {{ formatDate(order.visit_date) }}
              </span>
            </div>

            <div>
              <span class="text-xs text-slate-400 block mb-0.5">Jumlah Tiket</span>
              <span class="font-medium text-slate-800 flex items-center gap-1.5">
                <Users class="h-4 w-4 text-sky-600" />
                {{ order.total_quantity }} Orang
              </span>
            </div>

            <div>
              <span class="text-xs text-slate-400 block mb-0.5">Total Pembayaran</span>
              <span class="font-extrabold text-sky-700 text-base">
                {{ formatCurrency(order.total_amount) }}
              </span>
            </div>
          </div>

          <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-500">Pemesan: <strong>{{ order.customer_name }}</strong></span>
            <button
              type="button"
              class="inline-flex items-center gap-1 text-xs font-semibold text-sky-600 group-hover:text-sky-700"
            >
              <Eye class="h-3.5 w-3.5" /> Rincian Tiket &rarr;
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Detail Modal -->
    <div
      v-if="selectedOrder"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-opacity"
    >
      <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl sm:p-8 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <div>
            <span class="text-xs text-slate-400 font-semibold uppercase">Detail Pemesanan</span>
            <h3 class="font-mono text-lg font-bold text-slate-900">{{ selectedOrder.order_code }}</h3>
          </div>
          <button
            type="button"
            class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700"
            @click="closeDetail"
          >
            <X class="h-5 w-5" />
          </button>
        </div>

        <div class="mt-4 space-y-4">
          <!-- Status Banner -->
          <div class="flex items-center justify-between rounded-xl p-3 border" :class="getStatusBadgeClass(selectedOrder.status)">
            <span class="text-xs font-medium">Status Pembayaran:</span>
            <span class="text-xs font-bold uppercase">{{ getStatusLabel(selectedOrder.status) }}</span>
          </div>

          <!-- Customer Info -->
          <div class="rounded-xl bg-slate-50 p-4 text-xs space-y-2 border border-slate-200/80 overflow-hidden">
            <div class="flex flex-wrap justify-between gap-1">
              <span class="text-slate-500">Nama Pemesan:</span>
              <span class="font-semibold text-slate-900">{{ selectedOrder.customer_name }}</span>
            </div>
            <div class="flex flex-wrap justify-between gap-1">
              <span class="text-slate-500">Email:</span>
              <span class="font-medium text-slate-900 break-all">{{ selectedOrder.customer_email }}</span>
            </div>
            <div class="flex flex-wrap justify-between gap-1">
              <span class="text-slate-500">No. WhatsApp:</span>
              <span class="font-medium text-slate-900">{{ selectedOrder.customer_phone }}</span>
            </div>
            <div class="flex flex-wrap justify-between gap-1">
              <span class="text-slate-500">Tanggal Kunjungan:</span>
              <span class="font-semibold text-sky-700">{{ formatDate(selectedOrder.visit_date) }}</span>
            </div>
            <div class="flex flex-wrap justify-between gap-1">
              <span class="text-slate-500">Waktu Order:</span>
              <span class="font-medium text-slate-900">{{ formatDateTime(selectedOrder.created_at) }}</span>
            </div>
          </div>

          <!-- Items Breakdown -->
          <div class="rounded-xl border border-slate-200 p-4">
            <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Rincian Tiket</h4>
            <div class="divide-y divide-slate-100 text-xs">
              <div
                v-for="item in selectedOrder.items"
                :key="item.id"
                class="flex justify-between py-2"
              >
                <div>
                  <span class="font-semibold text-slate-800">{{ item.ticket_type?.name || 'Tiket' }}</span>
                  <span class="text-slate-400 block">{{ item.quantity }} x {{ formatCurrency(item.price) }}</span>
                </div>
                <span class="font-bold text-slate-900 self-center">{{ formatCurrency(item.subtotal) }}</span>
              </div>
            </div>

            <div class="mt-3 border-t border-slate-200 pt-3 flex justify-between items-baseline">
              <span class="font-bold text-slate-900 text-sm">Total Pembayaran ({{ selectedOrder.total_quantity }} Tiket)</span>
              <span class="font-extrabold text-sky-600 text-base">{{ formatCurrency(selectedOrder.total_amount) }}</span>
            </div>
          </div>
        </div>

        <div class="mt-6 flex justify-end">
          <button
            type="button"
            class="w-full sm:w-auto rounded-xl bg-slate-800 px-5 py-2.5 text-xs font-semibold text-white hover:bg-slate-900"
            @click="closeDetail"
          >
            Tutup
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
