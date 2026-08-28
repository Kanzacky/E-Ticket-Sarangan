<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { Calendar, Ticket, Users, CreditCard, QrCode, ArrowLeft, Copy, ExternalLink, Clock, AlertCircle, LoaderCircle } from 'lucide-vue-next'
import QrcodeVue from 'qrcode.vue'
import { getOrderByCodeApi } from '@/services/order.service'
import type { Order } from '@/types/booking.types'
import { formatCurrency, formatDate, formatDateTime } from '@/utils/formatters'

const route = useRoute()
const router = useRouter()
const order = ref<Order | null>(null)
const isLoading = ref(true)
const error = ref('')
const copied = ref(false)

const orderCode = route.params.id as string

onMounted(async () => {
  if (!orderCode) {
    error.value = 'Kode booking tidak ditemukan'
    isLoading.value = false
    return
  }
  try {
    order.value = await getOrderByCodeApi(orderCode)
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Pesanan tidak ditemukan atau bukan milik Anda'
  } finally {
    isLoading.value = false
  }
})

function copyCode() {
  if (order.value) {
    navigator.clipboard.writeText(order.value.order_code)
    copied.value = true
    setTimeout(() => (copied.value = false), 2000)
  }
}

function getStatusClass(status: string) {
  switch (status) {
    case 'PENDING': return 'bg-orange-50 text-orange-700 border-orange-200'
    case 'PAID': return 'bg-emerald-50 text-emerald-700 border-emerald-200'
    case 'COMPLETED': return 'bg-green-50 text-green-700 border-green-200'
    case 'CANCELLED': return 'bg-red-50 text-red-700 border-red-200'
    case 'EXPIRED': return 'bg-gray-100 text-gray-500 border-gray-200'
    default: return 'bg-gray-50 text-gray-600'
  }
}
</script>

<template>
  <div class="space-y-6">
    <button @click="router.back()" class="inline-flex items-center gap-1.5 text-sm font-bold text-[#66706C] hover:text-[#173B35]">
      <ArrowLeft class="w-4 h-4" /> Kembali
    </button>

    <div v-if="isLoading" class="py-16 flex flex-col items-center gap-3">
      <LoaderCircle class="w-8 h-8 animate-spin text-[#173B35]" />
      <p class="text-sm text-[#66706C]">Memuat detail tiket...</p>
    </div>

    <div v-else-if="error" class="p-8 bg-red-50 border border-red-200 rounded-xl text-center">
      <AlertCircle class="w-10 h-10 text-red-500 mx-auto mb-2" />
      <p class="text-sm font-bold text-red-700">{{ error }}</p>
      <router-link to="/my-tickets" class="mt-4 inline-flex px-4 py-2 bg-[#173B35] text-white rounded-lg text-sm font-bold">Kembali ke Pesanan</router-link>
    </div>

    <div v-else-if="order" class="max-w-2xl mx-auto space-y-6">
      <!-- Header Code -->
      <div class="bg-white rounded-xl border border-[#E8E6DE] p-6 text-center">
        <p class="text-xs font-bold text-[#66706C] uppercase tracking-widest">Kode Booking</p>
        <div class="flex items-center justify-center gap-2 mt-1">
          <span class="font-mono text-2xl font-black text-[#1D2724]">{{ order.order_code }}</span>
          <button @click="copyCode" class="p-2 hover:bg-[#F7F5EF] rounded-lg"><Copy class="w-4 h-4" /></button>
        </div>
        <span v-if="copied" class="text-xs text-emerald-600">Tersalin!</span>
        <div class="mt-3 inline-flex px-3 py-1 rounded-full text-xs font-bold border" :class="getStatusClass(order.status)">{{ order.status }}</div>
      </div>

      <!-- Summary -->
      <div class="bg-white rounded-xl border border-[#E8E6DE] p-6 space-y-3">
        <div class="flex justify-between text-sm"><span class="text-[#66706C] flex items-center gap-2"><Calendar class="w-4 h-4" /> Tanggal Kunjungan</span><span class="font-bold">{{ formatDate(order.visit_date) }}</span></div>
        <div class="flex justify-between text-sm"><span class="text-[#66706C] flex items-center gap-2"><Ticket class="w-4 h-4" /> Tiket</span><span class="font-bold">{{ order.total_quantity }} x</span></div>
        <div class="flex justify-between text-sm"><span class="text-[#66706C] flex items-center gap-2"><Users class="w-4 h-4" /> Pemesan</span><span class="font-bold">{{ order.customer_name }}</span></div>
        <div class="flex justify-between text-sm"><span class="text-[#66706C] flex items-center gap-2"><Clock class="w-4 h-4" /> Dipesan</span><span class="text-xs">{{ formatDateTime(order.created_at) }}</span></div>
        <div class="flex justify-between text-sm border-t pt-3"><span class="text-[#66706C] flex items-center gap-2"><CreditCard class="w-4 h-4" /> Total</span><span class="font-black text-[#173B35] text-lg">{{ formatCurrency(order.total_amount) }}</span></div>
      </div>

      <!-- Items -->
      <div v-if="order.items" class="bg-white rounded-xl border border-[#E8E6DE] p-6">
        <h3 class="font-bold mb-3">Rincian Tiket</h3>
        <div v-for="item in order.items" :key="item.id" class="flex justify-between py-2 border-b last:border-0 text-sm">
          <div>
            <p class="font-bold">{{ item.ticket_type?.name || 'Tiket' }}</p>
            <p class="text-xs text-[#66706C]">{{ item.quantity }} × {{ formatCurrency(item.price) }}</p>
          </div>
          <span class="font-bold">{{ formatCurrency(item.subtotal) }}</span>
        </div>
      </div>

      <!-- Payment -->
      <div v-if="order.status === 'PENDING' && order.payment_url" class="bg-orange-50 border border-orange-200 rounded-xl p-6 text-center">
        <p class="text-sm font-bold text-orange-700 mb-3">Menunggu Pembayaran</p>
        <a :href="order.payment_url" class="inline-flex items-center gap-2 bg-[#C9965B] text-white px-6 py-3 rounded-xl font-bold hover:bg-[#b0814a]">Bayar Sekarang <ExternalLink class="w-4 h-4" /></a>
        <p class="text-xs text-[#66706C] mt-2">Invoice Xendit • 24 jam</p>
      </div>

      <!-- QR -->
      <div v-if="order.status === 'PAID'" class="bg-white rounded-xl border border-[#E8E6DE] p-6 text-center">
        <h3 class="font-bold flex items-center justify-center gap-2 mb-4"><QrCode class="w-5 h-5" /> QR E-Ticket</h3>
        <div class="inline-block bg-white p-4 border-2 border-[#173B35]/10 rounded-xl">
          <qrcode-vue :value="order.order_code" :size="200" level="H" />
        </div>
        <p class="text-xs text-[#66706C] mt-3">Tunjukkan kepada petugas. Berlaku s/d {{ formatDate(order.qr_expires_at || order.visit_date) }} 23:59</p>
      </div>

      <div v-if="order.status === 'COMPLETED'" class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 text-center text-sm text-emerald-700 font-bold">Tiket sudah digunakan — terima kasih telah berkunjung!</div>
      <div v-if="order.status === 'EXPIRED' || order.status === 'CANCELLED'" class="bg-red-50 border border-red-200 rounded-xl p-4 text-center text-sm text-red-700">Pesanan {{ order.status === 'EXPIRED' ? 'kadaluarsa' : 'dibatalkan' }}.</div>
    </div>
  </div>
</template>
