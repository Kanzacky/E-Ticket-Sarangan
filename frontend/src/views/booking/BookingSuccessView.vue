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
  QrCode,
  ExternalLink,
} from 'lucide-vue-next'
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import QrcodeVue from 'qrcode.vue'

import axios from 'axios'

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
  } catch (error: unknown) {
    if (axios.isAxiosError(error) && error.response?.data?.message) {
      errorMessage.value = error.response.data.message as string
    } else {
      errorMessage.value = 'Gagal memuat informasi booking.'
    }
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
        <router-link
          to="/"
          class="mt-6 inline-flex items-center gap-2 rounded-xl bg-[#173B35] px-5 py-2.5 text-sm font-medium text-white hover:bg-[#1D2724]"
        >
          <Home class="h-4 w-4" /> Kembali ke Beranda
        </router-link>
      </div>

      <!-- Success Card -->
      <div v-else-if="order" class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-md">
        <!-- Top Banner -->
        <div class="bg-[#173B35] px-6 py-8 text-center text-white">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm shadow-inner">
            <CheckCircle2 class="h-10 w-10 text-white" />
          </div>
          <h1 class="mt-4 text-2xl font-extrabold tracking-tight">Booking Terkirim</h1>
          <p class="mt-1 text-sm text-[#F7F5EF]">Pesanan Anda telah tercatat dalam sistem e-Ticket Sarangan</p>
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
              <span v-if="order.status === 'PENDING'" class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700 border border-amber-200">
                MENUNGGU PEMBAYARAN
              </span>
              <span v-else-if="order.status === 'PAID'" class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 border border-emerald-200">
                LUNAS
              </span>
              <span v-else class="inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700 border border-red-200">
                {{ order.status }}
              </span>
            </div>
          </div>

          <!-- Pending Payment Actions -->
          <div v-if="order.status === 'PENDING' && order.payment_url" class="rounded-xl bg-amber-50 p-6 text-center border border-amber-200">
            <p class="text-sm text-amber-800 font-medium mb-4">Silakan selesaikan pembayaran untuk mendapatkan QR Code tiket Anda.</p>
            <a :href="order.payment_url" class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#D6AD60] px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-[#c29c55] transition w-full">
              Bayar Sekarang
              <ExternalLink class="h-4 w-4" />
            </a>
          </div>

          <!-- QR Code Section -->
          <div v-if="order.status === 'PAID'" class="rounded-2xl bg-slate-50 p-6 border border-slate-200/80 text-center">
            <h3 class="text-sm font-semibold text-slate-700 mb-4 flex items-center justify-center gap-2">
              <QrCode class="h-4 w-4" /> QR Code Tiket
            </h3>
            <div class="flex justify-center bg-white p-4 rounded-xl border border-slate-200 inline-block shadow-sm">
              <qrcode-vue :value="order.order_code" :size="200" level="H" />
            </div>
            <p class="mt-4 text-xs text-slate-500">Tunjukkan QR Code ini kepada petugas di loket masuk Sarangan.</p>
            <p v-if="order.qr_expires_at" class="mt-1 text-xs font-medium text-red-500">Berlaku s/d: {{ formatDate(order.qr_expires_at) }} 23:59</p>
          </div>

          <!-- Next Step Info -->
          <div v-if="order.status === 'PENDING'" class="rounded-xl bg-[#F7F5EF] p-4 text-xs text-[#173B35] border border-[#173B35]/20 leading-relaxed">
            💡 <strong>Informasi:</strong> Saat ini tiket berstatus <em>Menunggu Pembayaran</em>. Tiket elektronik (e-Ticket) akan diterbitkan setelah pembayaran diselesaikan.
          </div>

          <!-- Actions -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
            <router-link
              to="/my-bookings"
              class="flex items-center justify-center gap-2 rounded-xl bg-[#173B35] px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-[#1D2724] transition"
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
