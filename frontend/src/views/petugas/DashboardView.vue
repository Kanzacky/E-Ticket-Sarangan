<script setup lang="ts">
import { QrCode, CheckCircle, XCircle } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import { ref, onMounted } from 'vue'

const authStore = useAuthStore()
const isLoading = ref(true)

// Mock Data structure based on requirements (should come from API later)
const summary = ref({
  checkins: 0,
  valid: 0,
  invalid: 0
})

onMounted(() => {
  // Simulate API fetch delay
  setTimeout(() => {
    isLoading.value = false
    // Leave data empty to show Empty State as required by Anti-AI slop rule
    // "Jika API belum tersedia: jangan membuat angka palsu."
  }, 800)
})
</script>

<template>
  <div class="space-y-6">
    <div class="bg-white rounded-[12px] p-6 border border-[var(--color-border)] text-center">
      <h2 class="text-xl font-bold text-[var(--color-text-primary)]">Selamat datang, {{ authStore.user?.name?.split(' ')[0] || 'Petugas' }}</h2>
      <p class="text-[var(--color-text-secondary)] mt-1">{{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
      
      <div class="mt-8 mb-4">
        <router-link
          to="/petugas/scanner"
          class="inline-flex flex-col items-center justify-center gap-3 bg-[var(--color-primary)] text-white px-8 py-6 rounded-[12px] hover:bg-[#122c27] transition-all w-full max-w-xs mx-auto outline-none"
        >
          <QrCode class="h-12 w-12" />
          <span class="text-lg font-bold">Mulai Scan Tiket</span>
        </router-link>
      </div>
    </div>

    <!-- Quick Status -->
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
      <div class="bg-white rounded-[12px] p-4 border border-[var(--color-border)] flex flex-col items-center justify-center text-center">
        <p class="text-xs font-bold text-[var(--color-text-secondary)] uppercase tracking-wider mb-2">Check-in Hari Ini</p>
        <div v-if="isLoading" class="h-8 w-12 rounded bg-[var(--color-border)] animate-pulse"></div>
        <p v-else class="text-2xl font-black text-[var(--color-text-primary)]">{{ summary.checkins }}</p>
      </div>
      
      <div class="bg-white rounded-[12px] p-4 border border-[var(--color-border)] flex flex-col items-center justify-center text-center">
        <div class="flex items-center gap-1 mb-2">
          <CheckCircle class="w-3 h-3 text-emerald-600" />
          <p class="text-xs font-bold text-[var(--color-text-secondary)] uppercase tracking-wider">Valid</p>
        </div>
        <div v-if="isLoading" class="h-8 w-12 rounded bg-[var(--color-border)] animate-pulse"></div>
        <p v-else class="text-2xl font-black text-emerald-700">{{ summary.valid }}</p>
      </div>
      
      <div class="bg-white rounded-[12px] p-4 border border-[var(--color-border)] flex flex-col items-center justify-center text-center col-span-2 sm:col-span-1">
        <div class="flex items-center gap-1 mb-2">
          <XCircle class="w-3 h-3 text-red-600" />
          <p class="text-xs font-bold text-[var(--color-text-secondary)] uppercase tracking-wider">Ditolak</p>
        </div>
        <div v-if="isLoading" class="h-8 w-12 rounded bg-[var(--color-border)] animate-pulse"></div>
        <p v-else class="text-2xl font-black text-red-700">{{ summary.invalid }}</p>
      </div>
    </div>
  </div>
</template>
