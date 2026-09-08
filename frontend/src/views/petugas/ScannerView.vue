<script setup lang="ts">
import { CheckCircle, XCircle, AlertTriangle, HelpCircle, RefreshCw, CameraOff, RotateCcw, Loader2, Clock, ShieldOff } from 'lucide-vue-next'
import { QrcodeStream } from 'vue-qrcode-reader'
import { ref } from 'vue'
import { scanTicketApi } from '@/services/scanner.service'
import type { ScanResponseData } from '@/services/scanner.service'
import axios from 'axios'

// ─── State ───────────────────────────────────────────────────────────────────
type ScanState = 'scanning' | 'loading' | 'valid' | 'used' | 'expired' | 'unpaid' | 'notfound' | 'invalid' | 'camera_error'

const scanState = ref<ScanState>('scanning')
const scannedData = ref<ScanResponseData | null>(null)
const errorMessage = ref('')
const cameraError = ref('')
const isCameraReady = ref(false)
const facingMode = ref<'environment' | 'user'>('environment')

// Debounce: prevent duplicate scan within 1.5s
let lastScannedCode = ''
let lastScanTime = 0

// ─── Camera & Scanning Logic ──────────────────────────────────────────────────
function onCameraReady() {
  isCameraReady.value = true
  cameraError.value = ''
}

function onCameraError(error: Error) {
  isCameraReady.value = false
  if (error.name === 'NotAllowedError') {
    cameraError.value = 'Akses kamera ditolak. Mohon izinkan akses kamera di pengaturan browser.'
  } else if (error.name === 'NotFoundError') {
    cameraError.value = 'Kamera tidak ditemukan pada perangkat ini.'
  } else if (error.name === 'NotSupportedError') {
    cameraError.value = 'Halaman membutuhkan koneksi HTTPS untuk mengakses kamera.'
  } else if (error.name === 'OverconstrainedError') {
    // Switch to any available camera
    facingMode.value = 'user'
    cameraError.value = ''
  } else {
    cameraError.value = `Gagal mengakses kamera: ${error.message}`
  }
  if (cameraError.value) scanState.value = 'camera_error'
}

function onInit(promise: Promise<any>) {
  promise.then(() => {
    isCameraReady.value = true
  }).catch(onCameraError)
}

function switchCamera() {
  facingMode.value = facingMode.value === 'environment' ? 'user' : 'environment'
  isCameraReady.value = false
}

async function onDecode(result: any) {
  const code = Array.isArray(result) && result.length > 0
    ? result[0].rawValue
    : (typeof result === 'string' ? result : null)

  if (!code) return

  // Debounce: skip duplicate scan within 1.5s
  const now = Date.now()
  if (code === lastScannedCode && now - lastScanTime < 1500) return
  lastScannedCode = code
  lastScanTime = now

  scanState.value = 'loading'
  scannedData.value = null
  errorMessage.value = ''

  try {
    const res = await scanTicketApi(code)
    scannedData.value = res.data ?? null
    scanState.value = 'valid'
    playSound('success')
  } catch (error: unknown) {
    playSound('error')

    if (axios.isAxiosError(error)) {
      const msg = error.response?.data?.message || ''
      const status = error.response?.status

      if (status === 404) {
        scanState.value = 'notfound'
      } else if (msg.includes('kedaluwarsa')) {
        scanState.value = 'expired'
      } else if (msg.includes('Sudah digunakan') || msg.includes('sudah digunakan') || msg.includes('scanned_at')) {
        scanState.value = 'used'
      } else if (msg.includes('LUNAS') || msg.includes('pembayaran')) {
        scanState.value = 'unpaid'
      } else {
        scanState.value = 'invalid'
        errorMessage.value = msg || 'Tiket tidak dapat diverifikasi.'
      }
    } else {
      scanState.value = 'invalid'
      errorMessage.value = 'Terjadi kesalahan saat menghubungi server.'
    }
  }
}

function resetScanner() {
  scanState.value = 'scanning'
  scannedData.value = null
  errorMessage.value = ''
  lastScannedCode = ''
  lastScanTime = 0
  if (cameraError.value) {
    cameraError.value = ''
    isCameraReady.value = false
  }
}

// ─── Audio Feedback ───────────────────────────────────────────────────────────
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
      osc.frequency.exponentialRampToValueAtTime(1200, ctx.currentTime + 0.12)
      gain.gain.setValueAtTime(0, ctx.currentTime)
      gain.gain.linearRampToValueAtTime(0.4, ctx.currentTime + 0.05)
      gain.gain.linearRampToValueAtTime(0, ctx.currentTime + 0.3)
      osc.start(); osc.stop(ctx.currentTime + 0.3)
    } else {
      osc.type = 'sawtooth'
      osc.frequency.setValueAtTime(200, ctx.currentTime)
      osc.frequency.exponentialRampToValueAtTime(100, ctx.currentTime + 0.25)
      gain.gain.setValueAtTime(0, ctx.currentTime)
      gain.gain.linearRampToValueAtTime(0.3, ctx.currentTime + 0.05)
      gain.gain.linearRampToValueAtTime(0, ctx.currentTime + 0.3)
      osc.start(); osc.stop(ctx.currentTime + 0.3)
    }
  } catch (_) { /* silently fail */ }
}
</script>

<template>
  <div class="flex flex-col gap-5 pb-8 max-w-2xl mx-auto lg:max-w-3xl">

    <!-- ── Page Header ──────────────────────────────────────────────────── -->
    <div class="flex items-start justify-between">
      <div>
        <h1 class="text-2xl font-black text-[#173B35] tracking-tight">Scan Tiket</h1>
        <p class="text-sm font-medium text-[#66706C] mt-0.5">
          Arahkan kamera ke QR Code tiket pengunjung untuk verifikasi.
        </p>
      </div>
      <!-- Camera status pill -->
      <div
        class="flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold border transition-all"
        :class="isCameraReady && scanState === 'scanning'
          ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
          : 'bg-[#F0EEE8] text-[#66706C] border-[#E2DFD8]'"
      >
        <span
          class="w-2 h-2 rounded-full"
          :class="isCameraReady && scanState === 'scanning' ? 'bg-emerald-500 animate-pulse' : 'bg-[#66706C]'"
        ></span>
        {{ isCameraReady && scanState === 'scanning' ? 'Kamera Aktif' : 'Menginisialisasi...' }}
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════
         SCANNER VIEWPORT
    ══════════════════════════════════════════════════════════════════════ -->
    <div class="relative rounded-2xl overflow-hidden bg-[#111916] shadow-lg shadow-black/20 border border-[#1D2724]/50"
         style="aspect-ratio: 1 / 1; max-height: 480px;">

      <!-- Camera feed via vue-qrcode-reader -->
      <qrcode-stream
        v-if="scanState === 'scanning' || scanState === 'loading'"
        :constraints="{ facingMode }"
        @detect="onDecode"
        @camera-on="onCameraReady"
        @init="onInit"
        class="absolute inset-0 w-full h-full"
        :style="{ objectFit: 'cover' }"
      >
        <!-- Overlay: dark vignette + scan frame -->
        <div class="absolute inset-0 pointer-events-none">
          <!-- Corner-dimmed overlay -->
          <div class="absolute inset-0"
               style="background: radial-gradient(circle at center, transparent 34%, rgba(10,18,14,0.75) 70%);">
          </div>

          <!-- Scan frame -->
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="relative" style="width: min(52%, 220px); aspect-ratio: 1 / 1;">
              <!-- Corner markers -->
              <span class="absolute top-0 left-0 w-7 h-7 border-t-[3px] border-l-[3px] border-white rounded-tl-lg"></span>
              <span class="absolute top-0 right-0 w-7 h-7 border-t-[3px] border-r-[3px] border-white rounded-tr-lg"></span>
              <span class="absolute bottom-0 left-0 w-7 h-7 border-b-[3px] border-l-[3px] border-white rounded-bl-lg"></span>
              <span class="absolute bottom-0 right-0 w-7 h-7 border-b-[3px] border-r-[3px] border-white rounded-br-lg"></span>

              <!-- Animated scan line -->
              <div class="absolute inset-x-0 h-[2px] bg-gradient-to-r from-transparent via-[#A3D9A5] to-transparent rounded-full scan-line"></div>
            </div>
          </div>

          <!-- Bottom instruction -->
          <div class="absolute bottom-0 inset-x-0 px-4 py-5 flex flex-col items-center gap-1">
            <p class="text-white/90 text-sm font-semibold">Posisikan QR Code di area scan</p>
            <p class="text-white/50 text-xs">Pastikan QR Code tidak terpotong dan terlihat jelas</p>
          </div>

          <!-- Loading dimmer when verifying -->
          <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0">
            <div v-if="scanState === 'loading'"
                 class="absolute inset-0 bg-black/60 flex flex-col items-center justify-center gap-4">
              <Loader2 class="w-10 h-10 text-white animate-spin" />
              <p class="text-white font-semibold text-sm">Memverifikasi tiket...</p>
            </div>
          </Transition>
        </div>
      </qrcode-stream>

      <!-- Camera not ready placeholder (before camera initializes) -->
      <div v-if="(scanState === 'scanning') && !isCameraReady && !cameraError"
           class="absolute inset-0 flex flex-col items-center justify-center gap-3 bg-[#111916]">
        <Loader2 class="w-8 h-8 text-white/40 animate-spin" />
        <p class="text-white/40 text-sm">Menghubungkan kamera...</p>
      </div>

      <!-- Camera error state -->
      <div v-if="scanState === 'camera_error'"
           class="absolute inset-0 flex flex-col items-center justify-center gap-4 p-8 bg-[#111916] text-center">
        <div class="w-14 h-14 rounded-full bg-red-900/30 flex items-center justify-center">
          <CameraOff class="w-7 h-7 text-red-400" />
        </div>
        <div>
          <p class="text-white font-bold mb-1">Kamera Tidak Dapat Diakses</p>
          <p class="text-white/50 text-sm">{{ cameraError }}</p>
        </div>
        <button
          @click="resetScanner"
          class="mt-2 px-5 py-2.5 rounded-xl bg-white text-[#173B35] text-sm font-bold hover:bg-[#F7F5EF] transition-colors flex items-center gap-2"
        >
          <RotateCcw class="w-4 h-4" /> Coba Lagi
        </button>
      </div>

      <!-- ── RESULT OVERLAYS ──────────────────────────────────────────── -->
      <!-- SUCCESS -->
      <Transition
        enter-active-class="transition-all duration-300 ease-out"
        enter-from-class="opacity-0 scale-95"
        leave-active-class="transition-all duration-150 ease-in"
        leave-to-class="opacity-0 scale-95"
      >
        <div v-if="scanState === 'valid'"
             class="absolute inset-0 bg-white flex flex-col items-center justify-center p-6 text-center overflow-y-auto">
          <!-- Icon -->
          <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mb-4 flex-shrink-0">
            <CheckCircle class="w-9 h-9 text-emerald-600" />
          </div>
          <h2 class="text-xl font-black text-emerald-700 mb-1">Tiket Valid</h2>
          <p class="text-[#66706C] text-xs mb-5">Check-in berhasil diproses oleh sistem.</p>

          <!-- Data rows -->
          <div v-if="scannedData" class="w-full max-w-xs space-y-2.5 text-left mb-6">
            <div class="flex justify-between items-start gap-3 py-2 border-b border-[#F0EEE8]">
              <span class="text-xs text-[#66706C] font-medium shrink-0">Kode</span>
              <span class="text-xs font-bold text-[#1D2724] text-right font-mono">{{ scannedData.code }}</span>
            </div>
            <div class="flex justify-between items-start gap-3 py-2 border-b border-[#F0EEE8]">
              <span class="text-xs text-[#66706C] font-medium shrink-0">Nama</span>
              <span class="text-xs font-bold text-[#1D2724] text-right">{{ scannedData.name }}</span>
            </div>
            <div class="flex justify-between items-start gap-3 py-2 border-b border-[#F0EEE8]">
              <span class="text-xs text-[#66706C] font-medium shrink-0">Tanggal</span>
              <span class="text-xs font-bold text-[#1D2724] text-right">{{ scannedData.date }}</span>
            </div>
            <div class="flex justify-between items-start gap-3 py-2 border-b border-[#F0EEE8]">
              <span class="text-xs text-[#66706C] font-medium shrink-0">Jenis</span>
              <span class="text-xs font-bold text-[#1D2724] text-right">{{ scannedData.type }}</span>
            </div>
            <div class="flex justify-between items-center gap-3 py-2">
              <span class="text-xs text-[#66706C] font-medium shrink-0">Pengunjung</span>
              <span class="text-xs font-black text-[#173B35] bg-emerald-50 px-2.5 py-1 rounded-lg">{{ scannedData.qty }} Orang</span>
            </div>
          </div>

          <button
            @click="resetScanner"
            class="w-full max-w-xs py-3 rounded-xl font-bold text-sm text-white bg-[#173B35] hover:bg-[#112a25] transition-colors shadow-sm"
          >
            Scan Berikutnya
          </button>
        </div>
      </Transition>

      <!-- USED -->
      <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 scale-95">
        <div v-if="scanState === 'used'"
             class="absolute inset-0 bg-white flex flex-col items-center justify-center p-6 text-center">
          <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mb-4">
            <AlertTriangle class="w-9 h-9 text-amber-600" />
          </div>
          <h2 class="text-xl font-black text-amber-700 mb-1">Tiket Sudah Digunakan</h2>
          <p class="text-[#66706C] text-sm mb-6 max-w-xs">Tiket ini telah digunakan sebelumnya. Pengunjung tidak dapat masuk kembali dengan tiket yang sama.</p>
          <button @click="resetScanner" class="w-full max-w-xs py-3 rounded-xl font-bold text-sm text-white bg-[#1D2724] hover:bg-black transition-colors">
            Scan Ulang
          </button>
        </div>
      </Transition>

      <!-- EXPIRED -->
      <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 scale-95">
        <div v-if="scanState === 'expired'"
             class="absolute inset-0 bg-white flex flex-col items-center justify-center p-6 text-center">
          <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
            <Clock class="w-9 h-9 text-slate-500" />
          </div>
          <h2 class="text-xl font-black text-slate-700 mb-1">Tiket Kedaluwarsa</h2>
          <p class="text-[#66706C] text-sm mb-6 max-w-xs">Masa berlaku tiket ini telah habis. Pengunjung perlu membeli tiket baru.</p>
          <button @click="resetScanner" class="w-full max-w-xs py-3 rounded-xl font-bold text-sm text-white bg-[#1D2724] hover:bg-black transition-colors">
            Scan Ulang
          </button>
        </div>
      </Transition>

      <!-- UNPAID -->
      <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 scale-95">
        <div v-if="scanState === 'unpaid'"
             class="absolute inset-0 bg-white flex flex-col items-center justify-center p-6 text-center">
          <div class="w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center mb-4">
            <ShieldOff class="w-9 h-9 text-orange-500" />
          </div>
          <h2 class="text-xl font-black text-orange-600 mb-1">Pembayaran Belum Lunas</h2>
          <p class="text-[#66706C] text-sm mb-6 max-w-xs">Tiket ini belum dibayar. Pengunjung harus menyelesaikan pembayaran terlebih dahulu.</p>
          <button @click="resetScanner" class="w-full max-w-xs py-3 rounded-xl font-bold text-sm text-white bg-[#1D2724] hover:bg-black transition-colors">
            Scan Ulang
          </button>
        </div>
      </Transition>

      <!-- NOT FOUND -->
      <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 scale-95">
        <div v-if="scanState === 'notfound'"
             class="absolute inset-0 bg-white flex flex-col items-center justify-center p-6 text-center">
          <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mb-4">
            <HelpCircle class="w-9 h-9 text-slate-400" />
          </div>
          <h2 class="text-xl font-black text-slate-600 mb-1">Tiket Tidak Ditemukan</h2>
          <p class="text-[#66706C] text-sm mb-6 max-w-xs">QR Code ini tidak terdaftar dalam sistem e-Ticket Sarangan. Pastikan QR Code benar.</p>
          <button @click="resetScanner" class="w-full max-w-xs py-3 rounded-xl font-bold text-sm text-white bg-[#1D2724] hover:bg-black transition-colors">
            Scan Ulang
          </button>
        </div>
      </Transition>

      <!-- INVALID / GENERIC ERROR -->
      <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 scale-95">
        <div v-if="scanState === 'invalid'"
             class="absolute inset-0 bg-white flex flex-col items-center justify-center p-6 text-center">
          <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mb-4">
            <XCircle class="w-9 h-9 text-red-500" />
          </div>
          <h2 class="text-xl font-black text-red-600 mb-1">Tiket Tidak Valid</h2>
          <p v-if="errorMessage" class="text-[#66706C] text-sm mb-6 max-w-xs">{{ errorMessage }}</p>
          <p v-else class="text-[#66706C] text-sm mb-6 max-w-xs">Tiket ini tidak dapat diverifikasi oleh sistem.</p>
          <button @click="resetScanner" class="w-full max-w-xs py-3 rounded-xl font-bold text-sm text-white bg-[#1D2724] hover:bg-black transition-colors">
            Scan Ulang
          </button>
        </div>
      </Transition>
    </div>

    <!-- ── Camera Controls ──────────────────────────────────────────────── -->
    <div
      v-if="scanState === 'scanning' || scanState === 'loading'"
      class="flex items-center justify-center gap-3"
    >
      <button
        @click="switchCamera"
        :disabled="scanState === 'loading'"
        class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E2DFD8] bg-white text-[#1D2724] text-sm font-semibold hover:bg-[#F7F5EF] disabled:opacity-40 disabled:cursor-not-allowed transition-colors shadow-sm"
        title="Balik Kamera"
      >
        <RefreshCw class="w-4 h-4" />
        Balik Kamera
      </button>
    </div>

    <!-- ── Status Legend (visible during scanning) ─────────────────────── -->
    <div v-if="scanState === 'scanning'" class="grid grid-cols-2 sm:grid-cols-4 gap-2">
      <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-emerald-50 border border-emerald-100">
        <span class="w-2 h-2 rounded-full bg-emerald-500 flex-shrink-0"></span>
        <span class="text-xs font-semibold text-emerald-700">Valid</span>
      </div>
      <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-amber-50 border border-amber-100">
        <span class="w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></span>
        <span class="text-xs font-semibold text-amber-700">Sudah Digunakan</span>
      </div>
      <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-50 border border-slate-200">
        <span class="w-2 h-2 rounded-full bg-slate-400 flex-shrink-0"></span>
        <span class="text-xs font-semibold text-slate-600">Kedaluwarsa</span>
      </div>
      <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-red-50 border border-red-100">
        <span class="w-2 h-2 rounded-full bg-red-500 flex-shrink-0"></span>
        <span class="text-xs font-semibold text-red-700">Tidak Valid</span>
      </div>
    </div>

  </div>
</template>

<style scoped>
/* Animated scanning line */
.scan-line {
  animation: scan 2s ease-in-out infinite;
}

@keyframes scan {
  0%   { top: 4%; opacity: 0; }
  10%  { opacity: 1; }
  90%  { opacity: 1; }
  100% { top: 94%; opacity: 0; }
}
</style>
