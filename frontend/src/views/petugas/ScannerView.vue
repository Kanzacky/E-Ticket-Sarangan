<script setup lang="ts">
import { CheckCircle, XCircle, Ticket as TicketIcon, AlertTriangle } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { QrcodeStream } from 'vue-qrcode-reader'
import { ref } from 'vue'
import { scanTicketApi } from '@/services/scanner.service'
import type { ScanResponseData } from '@/services/scanner.service'
import axios from 'axios'

const { t } = useI18n()
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
      invalidReason.value = t('scanner.wait')
    }
  }
}

function onInit(promise: Promise<any>) {
  promise.catch(error => {
    isScanning.value = false
    if (error.name === 'NotAllowedError') {
      cameraError.value = t('scanner.camera_denied')
    } else if (error.name === 'NotFoundError') {
      cameraError.value = t('scanner.camera_not_found')
    } else if (error.name === 'NotSupportedError') {
      cameraError.value = t('scanner.camera_https')
    } else {
      cameraError.value = t('scanner.camera_failed') + ' ' + error.message
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
  <div class="space-y-6 pb-6">
    <div class="mb-6">
      <h1 class="text-2xl font-black text-[#173B35]">{{ t('scanner.title') }}</h1>
      <p class="text-sm font-medium text-[#66706C] mt-1">{{ t('scanner.desc') }}</p>
    </div>

    <!-- Scanner Area -->
    <div v-if="scanResult === 'idle'" class="bg-[#1D2724] rounded-2xl overflow-hidden aspect-[4/3] sm:aspect-video relative shadow-xl shadow-[#173B35]/10 border border-[#E8E6DE] flex flex-col items-center justify-center">
      
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
            <div class="w-48 h-48 sm:w-64 sm:h-64 border-2 border-white/50 rounded-xl relative shadow-[0_0_0_4000px_rgba(29,39,36,0.6)]">
              <div class="absolute -top-1 -left-1 w-6 h-6 border-t-4 border-l-4 border-white rounded-tl-xl"></div>
              <div class="absolute -top-1 -right-1 w-6 h-6 border-t-4 border-r-4 border-white rounded-tr-xl"></div>
              <div class="absolute -bottom-1 -left-1 w-6 h-6 border-b-4 border-l-4 border-white rounded-bl-xl"></div>
              <div class="absolute -bottom-1 -right-1 w-6 h-6 border-b-4 border-r-4 border-white rounded-br-xl"></div>
            </div>
          </div>
          <p class="text-white font-medium animate-pulse mt-4 bg-black/50 px-4 py-2 rounded-lg">{{ t('scanner.scanning') }}</p>
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
        class="absolute z-20 bg-white text-[#173B35] font-bold px-8 py-4 rounded-xl shadow-lg hover:scale-105 active:scale-95 transition-all"
      >
        {{ t('scanner.start_scan') }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-else-if="scanResult === 'loading'" class="bg-white rounded-2xl p-6 border border-[#E8E6DE] shadow-sm flex flex-col items-center justify-center aspect-[4/3] sm:aspect-video text-center">
      <div class="animate-spin rounded-full h-16 w-16 border-4 border-[#173B35]/20 border-t-[#173B35] mb-6"></div>
      <h2 class="text-2xl font-black text-[#1D2724]">{{ t('scanner.verifying') }}</h2>
      <p class="text-[#66706C] text-sm mt-2">{{ t('scanner.wait') }}</p>
    </div>

    <!-- Validation Result: VALID -->
    <div v-else-if="scanResult === 'valid'" class="bg-white rounded-2xl p-6 border border-emerald-200 shadow-sm max-w-lg mx-auto">
      <div class="flex flex-col items-center text-center border-b border-[#E8E6DE] pb-6 mb-6">
        <CheckCircle class="w-16 h-16 text-emerald-500 mb-3" />
        <h2 class="text-2xl font-black text-emerald-600">{{ t('scanner.valid') }}</h2>
        <p class="text-[#66706C] text-sm mt-1">{{ t('scanner.verified') }}</p>
      </div>
      
      <div v-if="scannedData" class="space-y-4 mb-8">
        <div class="flex justify-between items-center py-2 border-b border-[#E8E6DE]/50">
          <span class="text-sm font-medium text-[#66706C]">{{ t('scanner.booking_code') }}</span>
          <span class="font-bold text-[#1D2724]">{{ scannedData.code }}</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-[#E8E6DE]/50">
          <span class="text-sm font-medium text-[#66706C]">{{ t('scanner.visitor_name') }}</span>
          <span class="font-bold text-[#1D2724]">{{ scannedData.name }}</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-[#E8E6DE]/50">
          <span class="text-sm font-medium text-[#66706C]">{{ t('scanner.visit_date') }}</span>
          <span class="font-bold text-[#1D2724]">{{ scannedData.date }}</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-[#E8E6DE]/50">
          <span class="text-sm font-medium text-[#66706C]">{{ t('scanner.ticket_type') }}</span>
          <span class="font-bold text-[#1D2724] text-right truncate max-w-[150px] sm:max-w-[200px]">{{ scannedData.type }}</span>
        </div>
        <div class="flex justify-between items-center py-2 border-b border-[#E8E6DE]/50">
          <span class="text-sm font-medium text-[#66706C]">{{ t('scanner.quantity') }}</span>
          <div class="flex items-center gap-1.5 bg-[#F7F5EF] px-2.5 py-1 rounded-lg">
            <TicketIcon class="w-4 h-4 text-[#C9965B]" />
            <span class="font-bold text-[#1D2724]">{{ scannedData.qty }} Orang</span>
          </div>
        </div>
      </div>
      
      <div class="grid grid-cols-1 gap-3">
        <button @click="confirmCheckIn" class="w-full py-3.5 px-4 rounded-xl font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-sm transition-colors">
          {{ t('scanner.close_and_back') }}
        </button>
      </div>
    </div>

    <!-- Validation Result: INVALID -->
    <div v-else-if="scanResult === 'invalid'" class="bg-white rounded-2xl p-6 border border-red-200 shadow-sm max-w-lg mx-auto">
      <div class="flex flex-col items-center text-center border-b border-[#E8E6DE] pb-6 mb-6">
        <XCircle class="w-16 h-16 text-red-500 mb-3" />
        <h2 class="text-2xl font-black text-red-600">{{ t('scanner.invalid') }}</h2>
        <p class="text-[#66706C] text-sm mt-1">{{ t('scanner.rejected') }}</p>
      </div>
      
      <div class="bg-red-50 text-red-700 p-4 rounded-xl mb-8 flex items-start gap-3 text-sm font-medium border border-red-100">
        <AlertTriangle class="w-5 h-5 shrink-0 mt-0.5" />
        <div>
          <span class="block font-bold mb-0.5">{{ t('scanner.reason') }}</span>
          <span>{{ invalidReason }}</span>
        </div>
      </div>
      
      <button @click="resetScanner" class="w-full py-3.5 px-4 rounded-xl font-bold text-white bg-[#1D2724] hover:bg-black shadow-sm transition-colors">
        {{ t('scanner.scan_again') }}
      </button>
    </div>

  </div>
</template>
