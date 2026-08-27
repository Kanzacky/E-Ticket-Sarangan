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
  <main class="flex min-h-screen items-center justify-center bg-[var(--color-background)] px-4 py-12">
    <div class="w-full max-w-lg">
      <!-- Loading State -->
      <div v-if="isLoading" class="rounded-[12px] border border-[var(--color-border)] bg-white p-12 text-center">
        <LoaderCircle class="mx-auto h-10 w-10 animate-spin text-[var(--color-primary)]" />
        <p class="mt-4 text-sm text-[var(--color-text-secondary)] font-medium">Memuat data booking...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="errorMessage" class="rounded-[12px] border border-red-200 bg-white p-8 text-center">
        <p class="text-sm font-semibold text-red-600">{{ errorMessage }}</p>
        <router-link
          to="/"
          class="mt-6 inline-flex items-center gap-2 rounded-[10px] bg-[var(--color-primary)] px-5 py-2.5 text-sm font-medium text-white hover:bg-[#122c27]"
        >
          <Home class="h-4 w-4" /> Kembali ke Beranda
        </router-link>
      </div>

      <!-- Success Card -->
      <div v-else-if="order" class="overflow-hidden rounded-[12px] border border-[var(--color-border)] bg-white">
        <!-- Top Banner -->
        <div class="bg-[var(--color-primary)] px-6 py-8 text-center text-white">
          <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-[12px] bg-white/20 backdrop-blur-sm shadow-inner">
            <CheckCircle2 class="h-10 w-10 text-white" />
          </div>
          <h1 class="mt-4 text-2xl font-extrabold tracking-tight">Booking Terkirim</h1>
          <p class="mt-1 text-sm text-[var(--color-background)]">Pesanan Anda telah tercatat dalam sistem e-Ticket Sarangan</p>
        </div>

        <div class="p-6 sm:p-8 space-y-6">
          <!-- Order Code Highlight -->
          <div class="rounded-[12px] bg-[var(--color-background)] p-4 border border-[var(--color-border)] text-center">
            <span class="text-xs font-semibold uppercase tracking-wider text-[var(--color-text-secondary)]">Kode Booking</span>
            <div class="mt-1 flex items-center justify-center gap-2">
              <span class="font-mono text-xl font-extrabold text-[var(--color-text-primary)]">{{ order.order_code }}</span>
              <button
                type="button"
                class="rounded-lg p-1.5 text-[var(--color-text-secondary)] hover:bg-[var(--color-border)] hover:text-[var(--color-text-primary)] transition"
                title="Salin Kode"
                @click="copyOrderCode"
              >
                <Copy class="h-4 w-4" />
              </button>
            </div>
            <span v-if="copied" class="text-xs font-medium text-emerald-600">✓ Kode berhasil disalin</span>
          </div>

          <!-- Order Summary Details -->
          <div class="divide-y divide-[var(--color-border)] text-sm">
            <div class="flex justify-between py-3">
              <span class="flex items-center gap-2 text-[var(--color-text-secondary)]">
                <Calendar class="h-4 w-4 text-[var(--color-text-secondary)]" /> Tanggal Kunjungan
              </span>
              <span class="font-semibold text-[var(--color-text-primary)]">{{ formatDate(order.visit_date) }}</span>
            </div>

            <div class="flex justify-between py-3">
              <span class="flex items-center gap-2 text-[var(--color-text-secondary)]">
                <Ticket class="h-4 w-4 text-[var(--color-text-secondary)]" /> Jumlah Tiket
              </span>
              <span class="font-semibold text-[var(--color-text-primary)]">{{ order.total_quantity }} Orang</span>
            </div>

            <div class="flex justify-between py-3">
              <span class="flex items-center gap-2 text-[var(--color-text-secondary)]">
                <CreditCard class="h-4 w-4 text-[var(--color-text-secondary)]" /> Total Pembayaran
              </span>
              <span class="font-bold text-lg text-[var(--color-primary)]">{{ formatCurrency(order.total_amount) }}</span>
            </div>

            <div class="flex justify-between items-center py-3">
              <span class="text-[var(--color-text-secondary)]">Status Pesanan</span>
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
          <div v-if="order.status === 'PENDING' && order.payment_url" class="rounded-[12px] bg-[var(--color-accent)]/10 p-6 text-center border border-[var(--color-accent)]/20">
            <p class="text-sm text-[#8c673d] font-medium mb-4">Silakan selesaikan pembayaran untuk mendapatkan QR Code tiket Anda.</p>
            <a :href="order.payment_url" class="inline-flex items-center justify-center gap-2 rounded-[10px] bg-[var(--color-accent)] px-6 py-3 text-sm font-bold text-white shadow-sm hover:bg-[#b0804b] transition w-full">
              Bayar Sekarang
              <ExternalLink class="h-4 w-4" />
            </a>
          </div>

          <!-- QR Code Section -->
          <div v-if="order.status === 'PAID'" class="rounded-[12px] bg-[var(--color-background)] p-6 border border-[var(--color-border)] text-center">
            <h3 class="text-sm font-semibold text-[var(--color-text-primary)] mb-4 flex items-center justify-center gap-2">
              <QrCode class="h-4 w-4" /> QR Code Tiket
            </h3>
            <div class="flex justify-center bg-white p-4 rounded-[12px] border border-[var(--color-border)] inline-block">
              <qrcode-vue :value="order.order_code" :size="200" level="H" />
            </div>
            <p class="mt-4 text-xs text-[var(--color-text-secondary)]">Tunjukkan QR Code ini kepada petugas di loket masuk Sarangan.</p>
            <p v-if="order.qr_expires_at" class="mt-1 text-xs font-medium text-red-500">Berlaku s/d: {{ formatDate(order.qr_expires_at) }} 23:59</p>
          </div>

          <!-- Next Step Info -->
          <div v-if="order.status === 'PENDING'" class="rounded-[12px] bg-[var(--color-background)] p-4 text-xs text-[var(--color-primary)] border border-[var(--color-primary)]/20 leading-relaxed">
            💡 <strong>Informasi:</strong> Saat ini tiket berstatus <em>Menunggu Pembayaran</em>. Tiket elektronik (e-Ticket) akan diterbitkan setelah pembayaran diselesaikan.
          </div>

          <!-- Actions -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
            <router-link
              to="/my-tickets"
              class="flex items-center justify-center gap-2 rounded-[10px] bg-[var(--color-primary)] px-4 py-3 text-sm font-semibold text-white hover:bg-[#122c27] transition"
            >
              <FileText class="h-4 w-4" />
              Lihat Detail Booking
            </router-link>

            <router-link
              to="/"
              class="flex items-center justify-center gap-2 rounded-[10px] border border-[var(--color-border)] bg-white px-4 py-3 text-sm font-semibold text-[var(--color-text-primary)] hover:bg-[var(--color-background)] transition"
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
