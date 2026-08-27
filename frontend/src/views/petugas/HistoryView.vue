<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { Clock, CheckCircle2, XCircle, QrCode, Search, Ticket } from 'lucide-vue-next'
import { getScanHistoryApi } from '@/services/scanner.service'
import { formatDateTime } from '@/utils/formatters'

const logs = ref<any[]>([])
const isLoading = ref(true)
const searchQuery = ref('')

onMounted(async () => {
  try {
    isLoading.value = true
    const data = await getScanHistoryApi()
    logs.value = data
  } catch (error) {
    console.error('Gagal mengambil riwayat scan', error)
  } finally {
    isLoading.value = false
  }
})

const filteredLogs = computed(() => {
  if (!searchQuery.value) return logs.value
  const query = searchQuery.value.toLowerCase()
  return logs.value.filter((log) => {
    const codeMatch = log.order_code && log.order_code.toLowerCase().includes(query)
    const nameMatch = log.order?.customer_name && log.order.customer_name.toLowerCase().includes(query)
    return codeMatch || nameMatch
  })
})
</script>

<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-black text-[#1D2724]">Riwayat Scan</h1>
        <p class="text-sm font-medium text-[#66706C] mt-1">Daftar seluruh upaya pemindaian tiket (Sukses & Gagal).</p>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="bg-white p-4 rounded-2xl border border-[#E8E6DE] flex flex-col sm:flex-row gap-4">
      <div class="relative flex-1">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <Search class="w-4 h-4 text-[#66706C]" />
        </div>
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari kode booking / nama..."
          class="w-full pl-9 pr-4 py-2 bg-[#F7F5EF] border border-[#E8E6DE] rounded-xl text-sm focus:ring-2 focus:ring-[#173B35] focus:border-transparent outline-none transition-all"
        />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="py-12 text-center bg-white rounded-2xl border border-[#E8E6DE]">
      <div class="animate-pulse flex flex-col items-center">
        <div class="h-10 w-10 bg-[#E8E6DE] rounded-full mb-4"></div>
        <div class="h-4 w-32 bg-[#E8E6DE] rounded"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredLogs.length === 0" class="py-16 text-center bg-white rounded-2xl border border-[#E8E6DE] px-6">
      <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-[#F7F5EF] text-[#66706C]">
        <QrCode class="h-10 w-10" />
      </div>
      <h3 class="mt-4 text-lg font-bold text-[#1D2724]">Belum ada riwayat pemindaian</h3>
      <p class="mt-2 text-sm text-[#66706C]">Data hasil pemindaian tiket (sukses maupun gagal) akan muncul di sini.</p>
    </div>

    <!-- List -->
    <div v-else class="space-y-4">
      <div
        v-for="log in filteredLogs"
        :key="log.id"
        class="rounded-2xl border bg-white p-5 sm:p-6 transition-shadow hover:shadow-sm"
        :class="log.is_valid ? 'border-[#E8E6DE]' : 'border-red-200'"
      >
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4" :class="log.is_valid ? 'border-[#E8E6DE]/50' : 'border-red-100'">
          <div class="flex items-center gap-3">
            <div v-if="log.is_valid" class="bg-emerald-50 p-2 rounded-xl">
              <CheckCircle2 class="h-5 w-5 text-emerald-600" />
            </div>
            <div v-else class="bg-red-50 p-2 rounded-xl">
              <XCircle class="h-5 w-5 text-red-600" />
            </div>
            <div>
              <span class="font-mono text-lg font-black block" :class="log.is_valid ? 'text-[#1D2724]' : 'text-red-700'">
                {{ log.order_code }}
              </span>
              <span v-if="log.order" class="text-sm font-medium" :class="log.is_valid ? 'text-[#66706C]' : 'text-red-600'">
                {{ log.order.customer_name }}
              </span>
              <span v-else class="text-sm font-medium text-[#66706C] italic">Pesanan tidak ditemukan</span>
            </div>
          </div>
          <div class="text-right">
            <span class="text-[10px] font-bold uppercase tracking-wider block mb-1" :class="log.is_valid ? 'text-[#66706C]' : 'text-red-400'">Waktu Scan</span>
            <span class="text-sm font-bold flex items-center gap-1.5 justify-end" :class="log.is_valid ? 'text-[#173B35]' : 'text-red-700'">
              <Clock class="h-4 w-4" />
              {{ formatDateTime(log.created_at) }}
            </span>
          </div>
        </div>

        <div class="mt-4 flex flex-wrap gap-2 text-sm">
          <div v-if="log.is_valid" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#F7F5EF] border border-[#E8E6DE]">
            <span class="text-[#66706C] font-medium">Status:</span>
            <span class="font-bold text-emerald-600">Berhasil</span>
          </div>
          <div v-else class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 border border-red-100">
            <span class="text-red-600/70 font-medium">Alasan Gagal:</span>
            <span class="font-bold text-red-700">{{ log.error_message || 'Tiket Ditolak' }}</span>
          </div>
          
          <div v-if="log.order?.items" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-[#F7F5EF] border border-[#E8E6DE]">
            <Ticket class="h-4 w-4 text-[#C9965B]" />
            <span class="text-[#1D2724] font-bold">{{ log.order.items.reduce((acc: number, item: any) => acc + item.quantity, 0) }} Orang</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
