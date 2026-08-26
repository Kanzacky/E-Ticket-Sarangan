<script setup lang="ts">
import { onMounted, ref, computed } from 'vue'
import { Clock, CheckCircle2, XCircle, QrCode, Search, Ticket, Users } from 'lucide-vue-next'
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
  <div class="space-y-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Check-in</h1>
        <p class="text-sm text-slate-500">Daftar seluruh upaya pemindaian tiket (Sukses & Gagal).</p>
      </div>
      <div class="relative w-full sm:w-64">
        <Search class="absolute left-3 top-2.5 h-4 w-4 text-slate-400" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari kode booking / nama..."
          class="w-full rounded-xl border border-slate-200 bg-white pl-10 pr-4 py-2 text-sm outline-none transition focus:border-[#173B35] focus:ring-1 focus:ring-[#173B35]"
        />
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="py-12 text-center text-slate-500 bg-white rounded-2xl border border-slate-200">
      <div class="animate-pulse flex flex-col items-center">
        <div class="h-10 w-10 bg-slate-200 rounded-full mb-4"></div>
        <div class="h-4 w-32 bg-slate-200 rounded"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="filteredLogs.length === 0" class="py-16 text-center bg-white rounded-2xl border border-slate-200 shadow-sm px-6">
      <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-slate-50 text-slate-400">
        <QrCode class="h-10 w-10" />
      </div>
      <h3 class="mt-4 text-lg font-bold text-slate-900">Belum ada riwayat pemindaian</h3>
      <p class="mt-2 text-sm text-slate-500">Data hasil pemindaian tiket (sukses maupun gagal) akan muncul di sini.</p>
    </div>

    <!-- List -->
    <div v-else class="space-y-4">
      <div
        v-for="log in filteredLogs"
        :key="log.id"
        class="rounded-2xl border bg-white p-5 sm:p-6 shadow-sm transition hover:shadow-md"
        :class="log.is_valid ? 'border-slate-200' : 'border-red-200'"
      >
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b pb-4" :class="log.is_valid ? 'border-slate-100' : 'border-red-100'">
          <div class="flex items-center gap-3">
            <div v-if="log.is_valid" class="bg-emerald-100 p-2 rounded-xl">
              <CheckCircle2 class="h-5 w-5 text-emerald-600" />
            </div>
            <div v-else class="bg-red-100 p-2 rounded-xl">
              <XCircle class="h-5 w-5 text-red-600" />
            </div>
            <div>
              <span class="font-mono text-lg font-black block" :class="log.is_valid ? 'text-slate-900' : 'text-red-900'">
                {{ log.order_code }}
              </span>
              <span v-if="log.order" class="text-sm font-medium" :class="log.is_valid ? 'text-slate-600' : 'text-red-600'">
                {{ log.order.customer_name }}
              </span>
              <span v-else class="text-sm font-medium text-slate-400 italic">Pesanan tidak ditemukan</span>
            </div>
          </div>
          <div class="text-right">
            <span class="text-xs font-semibold uppercase tracking-wider block mb-1" :class="log.is_valid ? 'text-slate-400' : 'text-red-400'">Waktu Scan</span>
            <span class="text-sm font-bold flex items-center gap-1.5 justify-end" :class="log.is_valid ? 'text-[#173B35]' : 'text-red-700'">
              <Clock class="h-4 w-4" />
              {{ formatDateTime(log.created_at) }}
            </span>
          </div>
        </div>

        <!-- Details for valid logs -->
        <div v-if="log.is_valid && log.order" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm bg-slate-50 rounded-xl p-4 border border-slate-100">
          <div>
            <span class="text-xs font-semibold text-slate-500 block mb-1">Rincian Tiket</span>
            <div class="font-medium text-slate-900 space-y-1">
              <div v-for="item in log.order.items" :key="item.id" class="flex items-center gap-2">
                <Ticket class="h-3 w-3 text-slate-400" />
                {{ item.ticket_type?.name }} ({{ item.quantity }}x)
              </div>
            </div>
          </div>
          <div>
            <span class="text-xs font-semibold text-slate-500 block mb-1">Total Pengunjung Masuk</span>
            <span class="font-bold text-slate-900 flex items-center gap-2 text-base">
              <Users class="h-4 w-4 text-[#173B35]" />
              {{ log.order.total_quantity }} Orang
            </span>
          </div>
        </div>

        <!-- Reason for invalid logs -->
        <div v-else class="mt-4 text-sm bg-red-50 text-red-700 rounded-xl p-4 border border-red-100 flex gap-2">
          <span class="font-bold">Alasan ditolak:</span>
          <span>{{ log.reason || 'QR Code tidak valid.' }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
