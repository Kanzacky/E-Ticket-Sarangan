<script setup lang="ts">
import { ScanLine, CheckCircle, XCircle, ArrowLeft, Ticket as TicketIcon } from 'lucide-vue-next'
import { ref } from 'vue'

const isScanning = ref(false)
const scanResult = ref<'idle' | 'valid' | 'invalid'>('idle')

// Mock ticket data for UI demonstration
const mockTicket = ref({
  code: 'ETK-20260826-ABC123',
  name: 'Budi Santoso',
  date: '26 Agustus 2026',
  type: 'Dewasa',
  qty: 2
})

function startScan() {
  isScanning.value = true
  scanResult.value = 'idle'
  
  // Simulate scanning process
  setTimeout(() => {
    isScanning.value = false
    // Randomly succeed or fail for UI demo
    scanResult.value = Math.random() > 0.5 ? 'valid' : 'invalid'
  }, 2000)
}

function resetScanner() {
  scanResult.value = 'idle'
  isScanning.value = false
}

function confirmCheckIn() {
  // Simulate confirmation
  alert('Check-in berhasil!')
  resetScanner()
}
</script>

<template>
  <div class="space-y-6 pb-20">
    <div class="flex items-center gap-3 mb-6">
      <router-link to="/petugas/dashboard" class="p-2 -ml-2 rounded-lg text-[#66706C] hover:bg-white hover:text-[#173B35]">
        <ArrowLeft class="w-5 h-5" />
      </router-link>
      <div>
        <h1 class="text-xl font-bold text-[#1D2724] leading-tight">Scan Tiket</h1>
        <p class="text-xs text-[#66706C]">Arahkan kamera ke QR Code wisatawan</p>
      </div>
    </div>

    <!-- Scanner Area Placeholder -->
    <div v-if="scanResult === 'idle'" class="bg-[#1D2724] rounded-3xl overflow-hidden aspect-[3/4] relative shadow-xl shadow-[#173B35]/20 border-4 border-white flex flex-col items-center justify-center">
      
      <div class="absolute inset-0 bg-[url('/images/sarangan-story-2.jpg')] bg-cover bg-center opacity-20 grayscale"></div>
      
      <div v-if="isScanning" class="absolute inset-0 bg-[#1D2724]/60 backdrop-blur-sm z-10 flex flex-col items-center justify-center">
        <ScanLine class="w-16 h-16 text-white animate-pulse mb-4" />
        <p class="text-white font-medium animate-pulse">Memindai QR Code...</p>
      </div>

      <div class="relative z-10 p-8 flex flex-col items-center">
        <!-- Target frame -->
        <div class="w-48 h-48 border-2 border-white/50 rounded-2xl relative">
          <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-white rounded-tl-2xl"></div>
          <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-white rounded-tr-2xl"></div>
          <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-white rounded-bl-2xl"></div>
          <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-white rounded-br-2xl"></div>
        </div>
      </div>
      
      <button 
        v-if="!isScanning"
        @click="startScan"
        class="absolute bottom-8 z-20 bg-white text-[#173B35] font-bold px-8 py-4 rounded-2xl shadow-lg active:scale-95 transition-transform"
      >
        Mulai Scan
      </button>
    </div>

    <!-- Validation Result: VALID -->
    <div v-else-if="scanResult === 'valid'" class="bg-white rounded-3xl p-6 border-2 border-green-500 shadow-lg shadow-green-500/10">
      <div class="flex flex-col items-center text-center border-b border-[#173B35]/10 pb-6 mb-6">
        <CheckCircle class="w-16 h-16 text-green-500 mb-3" />
        <h2 class="text-2xl font-black text-green-600">Tiket Valid</h2>
        <p class="text-[#66706C] text-sm mt-1">Sistem berhasil memverifikasi tiket.</p>
      </div>
      
      <div class="space-y-4 mb-8">
        <div class="flex justify-between items-center py-2 border-b border-[#F7F5EF]">
          <span class="text-sm text-[#66706C]">Kode</span>
          <span class="font-bold text-[#1D2724]">{{ mockTicket.code }}</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-[#F7F5EF]">
          <span class="text-sm text-[#66706C]">Nama</span>
          <span class="font-bold text-[#1D2724]">{{ mockTicket.name }}</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-[#F7F5EF]">
          <span class="text-sm text-[#66706C]">Tgl. Kunjungan</span>
          <span class="font-bold text-[#1D2724]">{{ mockTicket.date }}</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-[#F7F5EF]">
          <span class="text-sm text-[#66706C]">Jenis Tiket</span>
          <span class="font-bold text-[#1D2724]">{{ mockTicket.type }}</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-[#F7F5EF]">
          <span class="text-sm text-[#66706C]">Jumlah</span>
          <div class="flex items-center gap-1.5 bg-[#F7F5EF] px-2 py-1 rounded-lg">
            <TicketIcon class="w-4 h-4 text-[#173B35]" />
            <span class="font-bold text-[#173B35]">{{ mockTicket.qty }}x</span>
          </div>
        </div>
      </div>
      
      <div class="grid grid-cols-2 gap-3">
        <button @click="resetScanner" class="py-4 px-4 rounded-xl font-bold text-[#66706C] bg-[#F7F5EF] active:bg-[#e8e6df]">
          Batal
        </button>
        <button @click="confirmCheckIn" class="py-4 px-4 rounded-xl font-bold text-white bg-green-600 active:bg-green-700 shadow-md shadow-green-600/20">
          Check-in
        </button>
      </div>
    </div>

    <!-- Validation Result: INVALID -->
    <div v-else-if="scanResult === 'invalid'" class="bg-white rounded-3xl p-6 border-2 border-red-500 shadow-lg shadow-red-500/10">
      <div class="flex flex-col items-center text-center border-b border-[#173B35]/10 pb-6 mb-6">
        <XCircle class="w-16 h-16 text-red-500 mb-3" />
        <h2 class="text-2xl font-black text-red-600">Tiket Ditolak</h2>
        <p class="text-[#66706C] text-sm mt-1">Gagal memverifikasi tiket.</p>
      </div>
      
      <div class="bg-red-50 text-red-700 p-4 rounded-2xl mb-8 flex gap-3 text-sm font-medium">
        <span>⚠️</span>
        <p>Alasan: <strong>Tiket sudah digunakan</strong> pada 26 Agustus 2026 09:14 WIB.</p>
      </div>
      
      <button @click="resetScanner" class="w-full py-4 px-4 rounded-xl font-bold text-white bg-[#1D2724] active:bg-black shadow-md">
        Scan Ulang
      </button>
    </div>

  </div>
</template>
