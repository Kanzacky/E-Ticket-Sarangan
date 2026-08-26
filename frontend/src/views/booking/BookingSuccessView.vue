<script setup lang="ts">
import {
  Calendar,
  CheckCircle2,
  Copy,
  CreditCard,
  FileText,
  Home,
  LoaderCircle,
  Ticket,
} from 'lucide-vue-next'
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'

import { getOrderByCodeApi } from '@/services/order.service'
import type { Order } from '@/types/booking.types'
import { formatCurrency, formatDate } from '@/utils/formatters'

const route = useRoute()
const orderCode = route.params.orderCode as string

const order = ref<Order | null>(null)
const isLoading = ref(true)
const errorMessage = ref('')
const copied = ref(false)

onMounted(async () => {
  if (!orderCode) {
    errorMessage.value = 'Kode booking tidak ditemukan.'
    isLoading.value = false
    return
  }

  try {
    isLoading.value = true
    const data = await getOrderByCodeApi(orderCode)
    order.value = data
  } catch (error: any) {
    errorMessage.value = error.response?.data?.message || 'Gagal memuat informasi booking.'
  } finally {
    isLoading.value = false
  }
})

function copyOrderCode() {
  if (orderCode) {
    navigator.clipboard.writeText(orderCode)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  }
}
</script>

<template>
  <main class="flex min-h-screen items-center justify-center bg-slate-50 px-4 py-12">
    <div class="w-full max-w-lg">
      <!-- Loading State -->
      <div v-if="isLoading" class="rounded-3xl border border-slate-200 bg-white p-12 text-center shadow-sm">
        <LoaderCircle class="mx-auto h-10 w-10 animate-spin text-sky-600" />
        <p class="mt-4 text-sm text-slate-500 font-medium">Memuat data booking...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="rounded-3xl border border-red-200 bg-white p-8 text-center shadow-sm">
        <p class="text-sm font-semibold text-red-600">{{ errorMessage }}</p>
        <router-link
          to="/"
          class="mt-6 inline-flex items-center gap-2 rounded-xl bg-slate-800 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-900"
        >
          <Home class="h-4 w-4" /> Kembali ke Beranda
        </router-link>
      </div>

      <!-- Success Card -->
      <div v-else-if="order" class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-md">
        <!-- Top Banner -->
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 px-6 py-8 text-center text-white">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm shadow-inner">
            <CheckCircle2 class="h-10 w-10 text-white" />
          </div>
          <h1 class="mt-4 text-2xl font-extrabold tracking-tight">Booking Berhasil Dibuat</h1>
          <p class="mt-1 text-sm text-emerald-100">Pesanan Anda telah tercatat dalam sistem e-Ticket Sarangan</p>
        </div>

        <div class="p-6 sm:p-8 space-y-6">
          <!-- Order Code Highlight -->
          <div class="rounded-2xl bg-slate-50 p-4 border border-slate-200/80 text-center">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-400">Kode Booking</span>
            <div class="mt-1 flex items-center justify-center gap-2">
              <span class="font-mono text-xl font-extrabold text-slate-900">{{ order.order_code }}</span>
              <button
                type="button"
                class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-200 hover:text-slate-700 transition"
                title="Salin Kode"
                @click="copyOrderCode"
              >
                <Copy class="h-4 w-4" />
              </button>
            </div>
            <span v-if="copied" class="text-xs font-medium text-emerald-600">✓ Kode berhasil disalin</span>
          </div>

          <!-- Order Summary Details -->
          <div class="divide-y divide-slate-100 text-sm">
            <div class="flex justify-between py-3">
              <span class="flex items-center gap-2 text-slate-500">
                <Calendar class="h-4 w-4 text-slate-400" /> Tanggal Kunjungan
              </span>
              <span class="font-semibold text-slate-900">{{ formatDate(order.visit_date) }}</span>
            </div>

            <div class="flex justify-between py-3">
              <span class="flex items-center gap-2 text-slate-500">
                <Ticket class="h-4 w-4 text-slate-400" /> Jumlah Tiket
              </span>
              <span class="font-semibold text-slate-900">{{ order.total_quantity }} Orang</span>
            </div>

            <div class="flex justify-between py-3">
              <span class="flex items-center gap-2 text-slate-500">
                <CreditCard class="h-4 w-4 text-slate-400" /> Total Pembayaran
              </span>
              <span class="font-bold text-lg text-sky-600">{{ formatCurrency(order.total_amount) }}</span>
            </div>

            <div class="flex justify-between items-center py-3">
              <span class="text-slate-500">Status Pesanan</span>
              <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 border border-amber-200">
                MENUNGGU PEMBAYARAN
              </span>
            </div>
          </div>

          <!-- Next Step Info -->
          <div class="rounded-xl bg-sky-50/70 p-4 text-xs text-sky-800 border border-sky-100 leading-relaxed">
            💡 <strong>Informasi:</strong> Saat ini tiket berstatus <em>Menunggu Pembayaran</em>. Tiket elektronik (e-Ticket) akan diterbitkan setelah pembayaran diselesaikan pada tahap berikutnya.
          </div>

          <!-- Actions -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
            <router-link
              to="/my-bookings"
              class="flex items-center justify-center gap-2 rounded-xl bg-sky-600 px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-sky-700 transition"
            >
              <FileText class="h-4 w-4" />
              Lihat Detail Booking
            </router-link>

            <router-link
              to="/"
              class="flex items-center justify-center gap-2 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition"
            >
              <Home class="h-4 w-4" />
              Kembali ke Beranda
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>
