<script setup lang="ts">
import { CheckCircle, XCircle, ArrowLeft, Ticket as TicketIcon } from 'lucide-vue-next'
import { QrcodeStream } from 'vue-qrcode-reader'
import { ref } from 'vue'
import { scanTicketApi } from '@/services/scanner.service'
import type { ScanResponseData } from '@/services/scanner.service'
import axios from 'axios'

const isScanning = ref(false)
const scanResult = ref<'idle' | 'loading' | 'valid' | 'invalid'>('idle')
const invalidReason = ref('')

// Ticket data from API
const scannedData = ref<ScanResponseData | null>(null)

const cameraError = ref('')

function startScan() {
  isScanning.value = true
  scanResult.value = 'idle'
  invalidReason.value = ''
  cameraError.value = ''
}

function playSound(type: 'success' | 'error') {
  try {
    const ctx = new (window.AudioContext || (window as any).webkitAudioContext)()
    const osc = ctx.createOscillator()
    const gain = ctx.createGain()
    
    osc.connect(gain)
    gain.connect(ctx.destination)
    
    if (type === 'success') {
      osc.type = 'sine'
      osc.frequency.setValueAtTime(800, ctx.currentTime)
      osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + 0.1)
      gain.gain.setValueAtTime(0, ctx.currentTime)
      gain.gain.linearRampToValueAtTime(1, ctx.currentTime + 0.05)
      gain.gain.linearRampToValueAtTime(0, ctx.currentTime + 0.3)
      osc.start()
      osc.stop(ctx.currentTime + 0.3)
    } else {
      osc.type = 'sawtooth'
      osc.frequency.setValueAtTime(150, ctx.currentTime)
      osc.frequency.exponentialRampToValueAtTime(100, ctx.currentTime + 0.3)
      gain.gain.setValueAtTime(0, ctx.currentTime)
      gain.gain.linearRampToValueAtTime(1, ctx.currentTime + 0.05)
      gain.gain.linearRampToValueAtTime(0, ctx.currentTime + 0.3)
      osc.start()
      osc.stop(ctx.currentTime + 0.3)
    }
  } catch (e) {
    console.warn('Audio feedback not supported', e)
  }
}

async function onDecode(result: any) {
  const code = Array.isArray(result) && result.length > 0 ? result[0].rawValue : (typeof result === 'string' ? result : null)
  if (!code) return
  
  isScanning.value = false
  scanResult.value = 'loading'
  
  try {
    const res = await scanTicketApi(code)
    scanResult.value = 'valid'
    playSound('success')
    if (res.data) {
      scannedData.value = res.data
    }
  } catch (error: unknown) {
    scanResult.value = 'invalid'
    playSound('error')
    if (axios.isAxiosError(error) && error.response?.data?.message) {
      invalidReason.value = error.response.data.message
    } else {
      invalidReason.value = 'Terjadi kesalahan sistem saat memverifikasi tiket.'
    }
  }
}

function onInit(promise: Promise<any>) {
  promise.catch(error => {
    isScanning.value = false
    if (error.name === 'NotAllowedError') {
      cameraError.value = 'Akses kamera ditolak.'
    } else if (error.name === 'NotFoundError') {
      cameraError.value = 'Kamera tidak ditemukan di perangkat ini.'
    } else if (error.name === 'NotSupportedError') {
      cameraError.value = 'Konteks tidak aman (butuh HTTPS atau localhost).'
    } else {
      cameraError.value = 'Gagal mengakses kamera: ' + error.message
    }
  })
}

function onCameraOn() {
  isScanning.value = true
  cameraError.value = ''
}

function resetScanner() {
  scanResult.value = 'idle'
  isScanning.value = false
  scannedData.value = null
  invalidReason.value = ''
}

function confirmCheckIn() {
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

    <!-- Scanner Area -->
    <div v-if="scanResult === 'idle'" class="bg-[#1D2724] rounded-3xl overflow-hidden aspect-[3/4] sm:aspect-video relative shadow-xl shadow-[#173B35]/20 border-4 border-white flex flex-col items-center justify-center">
      
      <div v-if="!isScanning" class="absolute inset-0 bg-[url('/images/sarangan-story-2.jpg')] bg-cover bg-center opacity-20 grayscale"></div>
      
      <qrcode-stream 
        v-if="isScanning"
        @detect="onDecode"
        @camera-on="onCameraOn" 
        @init="onInit"
        class="absolute inset-0 w-full h-full object-cover"
      >
        <div class="absolute inset-0 bg-[#1D2724]/20 z-10 flex flex-col items-center justify-center pointer-events-none">
          <div class="relative z-10 p-8 flex flex-col items-center">
            <!-- Target frame -->
            <div class="w-48 h-48 sm:w-64 sm:h-64 border-2 border-white/50 rounded-2xl relative shadow-[0_0_0_4000px_rgba(29,39,36,0.6)]">
              <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-white rounded-tl-2xl"></div>
              <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-white rounded-tr-2xl"></div>
              <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-white rounded-bl-2xl"></div>
              <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-white rounded-br-2xl"></div>
            </div>
          </div>
          <p class="text-white font-medium animate-pulse mt-4 bg-black/50 px-4 py-2 rounded-full">Memindai QR Code...</p>
        </div>
      </qrcode-stream>

      <div v-if="cameraError" class="absolute inset-0 z-20 flex flex-col items-center justify-center p-6 text-center bg-[#1D2724]">
        <XCircle class="w-12 h-12 text-red-500 mb-3" />
        <p class="text-white font-bold">{{ cameraError }}</p>
        <button @click="startScan" class="mt-4 bg-white text-[#173B35] px-4 py-2 rounded-lg font-bold text-sm">Coba Lagi</button>
      </div>

      <button 
        v-if="!isScanning && !cameraError"
        @click="startScan"
        class="absolute z-20 bg-white text-[#173B35] font-bold px-8 py-4 rounded-2xl shadow-lg active:scale-95 transition-transform"
      >
        Mulai Scan
      </button>
    </div>

    <!-- Loading State -->
    <div v-else-if="scanResult === 'loading'" class="bg-white rounded-3xl p-6 border-2 border-slate-200 shadow-lg flex flex-col items-center justify-center aspect-[3/4] sm:aspect-video text-center">
      <div class="animate-spin rounded-full h-16 w-16 border-4 border-[#173B35]/20 border-t-[#173B35] mb-6"></div>
      <h2 class="text-2xl font-black text-slate-800">Memverifikasi Tiket...</h2>
      <p class="text-[#66706C] text-sm mt-2">Mohon tunggu sebentar, sedang mengecek ke server.</p>
    </div>

    <!-- Validation Result: VALID -->
    <div v-else-if="scanResult === 'valid'" class="bg-white rounded-3xl p-6 border-2 border-green-500 shadow-lg shadow-green-500/10">
      <div class="flex flex-col items-center text-center border-b border-[#173B35]/10 pb-6 mb-6">
        <CheckCircle class="w-16 h-16 text-green-500 mb-3" />
        <h2 class="text-2xl font-black text-green-600">Tiket Valid</h2>
        <p class="text-[#66706C] text-sm mt-1">Sistem berhasil memverifikasi tiket.</p>
      </div>
      
      <div v-if="scannedData" class="space-y-4 mb-8">
        <div class="flex justify-between items-center py-2 border-b border-[#F7F5EF]">
          <span class="text-sm text-[#66706C]">Kode</span>
          <span class="font-bold text-[#1D2724]">{{ scannedData.code }}</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-[#F7F5EF]">
          <span class="text-sm text-[#66706C]">Nama</span>
          <span class="font-bold text-[#1D2724]">{{ scannedData.name }}</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-[#F7F5EF]">
          <span class="text-sm text-[#66706C]">Tgl. Kunjungan</span>
          <span class="font-bold text-[#1D2724]">{{ scannedData.date }}</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-[#F7F5EF]">
          <span class="text-sm text-[#66706C]">Jenis Tiket</span>
          <span class="font-bold text-[#1D2724] max-w-[50%] text-right truncate">{{ scannedData.type }}</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-[#F7F5EF]">
          <span class="text-sm text-[#66706C]">Jumlah</span>
          <div class="flex items-center gap-1.5 bg-[#F7F5EF] px-2 py-1 rounded-lg">
            <TicketIcon class="w-4 h-4 text-[#173B35]" />
            <span class="font-bold text-[#173B35]">{{ scannedData.qty }}x</span>
          </div>
        </div>
      </div>
      
      <div class="grid grid-cols-1 gap-3">
        <button @click="confirmCheckIn" class="py-4 px-4 rounded-xl font-bold text-white bg-green-600 active:bg-green-700 shadow-md shadow-green-600/20">
          Tutup & Kembali Scan
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
        <p>Alasan: <strong>{{ invalidReason }}</strong></p>
      </div>
      
      <button @click="resetScanner" class="w-full py-4 px-4 rounded-xl font-bold text-white bg-[#1D2724] active:bg-black shadow-md">
        Scan Ulang
      </button>
    </div>

  </div>
</template>
