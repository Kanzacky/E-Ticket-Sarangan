<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { Calendar, MapPin, Users, CreditCard, Search, Building, ExternalLink, LoaderCircle, AlertCircle } from 'lucide-vue-next'
import { getMyAccommodationBookingsApi } from '@/services/accommodation.service'
import type { AccommodationBooking } from '@/services/accommodation.service'
import { formatCurrency } from '@/utils/formatters'

const bookings = ref<AccommodationBooking[]>([])
const isLoading = ref(true)
const error = ref('')
const searchQuery = ref('')

const fetch = async () => {
  isLoading.value = true
  error.value = ''
  try {
    bookings.value = await getMyAccommodationBookingsApi()
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal memuat riwayat penginapan'
  } finally { isLoading.value = false }
}

onMounted(fetch)

const filtered = computed(() => {
  if (!searchQuery.value.trim()) return bookings.value
  const q = searchQuery.value.toLowerCase()
  return bookings.value.filter(b => b.booking_code.toLowerCase().includes(q) || (b.accommodation?.name || '').toLowerCase().includes(q))
})

function statusTone(s: string) {
  switch (s) {
    case 'confirmed': return 'bg-emerald-50 text-emerald-700 border-emerald-200'
    case 'pending': return 'bg-orange-50 text-orange-700 border-orange-200'
    case 'cancelled': return 'bg-red-50 text-red-700 border-red-200'
    case 'completed': return 'bg-blue-50 text-blue-700 border-blue-200'
    default: return 'bg-gray-50 text-gray-600'
  }
}

function formatDate(d: string) {
  return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>

<template>
  <div class="space-y-6">
    <div class="flex gap-2 border-b border-[#E8E6DE] -mb-6">
      <router-link to="/my-tickets" class="px-4 py-2 text-sm font-bold border-b-2 border-transparent text-[#66706C] hover:text-[#1D2724]">Tiket Saya</router-link>
      <router-link to="/my-accommodations" class="px-4 py-2 text-sm font-bold border-b-2 border-[#173B35] text-[#173B35]">Penginapan Saya</router-link>
    </div>
    <div>
      <h1 class="text-2xl font-black text-[#173B35]">Riwayat Penginapan</h1>
      <p class="text-sm text-[#66706C] mt-1">Daftar booking penginapan Anda</p>
    </div>

    <div class="relative w-full sm:w-72">
      <Search class="w-4 h-4 absolute left-3 top-2.5 text-[#66706C]" />
      <input v-model="searchQuery" placeholder="Cari kode / penginapan..." class="w-full pl-9 pr-3 py-2 text-sm border border-[#E8E6DE] rounded-lg bg-white focus:ring-1 focus:ring-[#173B35]" />
    </div>

    <div v-if="isLoading" class="py-16 flex flex-col items-center gap-3">
      <LoaderCircle class="w-8 h-8 animate-spin text-[#173B35]" />
      <p class="text-sm text-[#66706C]">Memuat...</p>
    </div>

    <div v-else-if="error" class="p-6 bg-red-50 border border-red-200 rounded-xl text-center text-sm text-red-700">{{ error }}</div>

    <div v-else-if="filtered.length===0" class="py-16 text-center">
      <Building class="w-12 h-12 text-[#66706C]/30 mx-auto mb-3" />
      <p class="text-sm text-[#66706C]">Belum ada booking penginapan</p>
      <router-link to="/accommodations" class="mt-3 inline-flex px-4 py-2 bg-[#173B35] text-white rounded-lg text-sm font-bold">Lihat Penginapan</router-link>
    </div>

    <div v-else class="grid gap-4">
      <div v-for="b in filtered" :key="b.id" class="bg-white rounded-xl border border-[#E8E6DE] p-5 flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
          <div class="flex items-start justify-between gap-2">
            <div>
              <p class="font-mono text-sm font-black text-[#1D2724]">{{ b.booking_code }}</p>
              <p class="text-sm font-bold text-[#173B35] flex items-center gap-1.5"><Building class="w-4 h-4" /> {{ b.accommodation?.name || 'Penginapan' }}</p>
              <p class="text-xs text-[#66706C] flex items-center gap-1"><MapPin class="w-3 h-3" /> {{ b.accommodation?.address || '-' }}</p>
            </div>
            <span class="px-2.5 py-1 rounded-full text-xs font-bold border" :class="statusTone(b.status)">{{ b.status }}</span>
          </div>
          <div class="flex flex-wrap gap-x-4 gap-y-1 text-xs text-[#66706C] mt-3">
            <span class="flex items-center gap-1"><Calendar class="w-3.5 h-3.5" /> {{ formatDate(b.check_in) }} → {{ formatDate(b.check_out) }}</span>
            <span class="flex items-center gap-1"><Users class="w-3.5 h-3.5" /> {{ b.rooms }} kamar • {{ b.guests }} tamu</span>
            <span class="flex items-center gap-1"><CreditCard class="w-3.5 h-3.5" /> {{ formatCurrency(b.total_price) }}</span>
          </div>
          <p class="text-xs text-[#66706C] mt-1">Tamu: {{ b.guest_name }} • {{ b.guest_phone }}</p>
        </div>
        <div class="flex sm:flex-col gap-2 shrink-0">
          <a v-if="b.status==='pending' && b.payment_url" :href="b.payment_url" target="_blank" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 bg-orange-500 text-white rounded-lg text-xs font-bold hover:bg-orange-600">
            Bayar <ExternalLink class="w-3.5 h-3.5" />
          </a>
          <span v-else-if="b.status==='pending'" class="px-3 py-2 bg-orange-50 border border-orange-200 text-orange-700 rounded-lg text-xs font-bold text-center">Menunggu Bayar</span>
          <router-link :to="`/accommodations/${b.accommodation_id}`" class="px-3 py-2 border border-[#E8E6DE] rounded-lg text-xs font-bold text-center hover:bg-[#F7F5EF]">Lihat Penginapan</router-link>
        </div>
      </div>
    </div>
  </div>
</template>
