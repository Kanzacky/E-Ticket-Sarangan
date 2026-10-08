<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import { RouterView } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useServerWakeup } from '@/composables/useServerWakeup'
import api from '@/services/api'

const authStore = useAuthStore()
const { ensureServerAwake } = useServerWakeup()

let keepAliveTimer: ReturnType<typeof setInterval> | null = null

onMounted(() => {
  // 1. Inisialisasi auth (non-blocking)
  void authStore.initialize()

  // 2. Kick wake-up Railway SEGERA — detik pertama app dibuka,
  //    sehingga saat user navigasi ke halaman manapun server sudah siap
  void ensureServerAwake()

  // 3. Keep-alive ping setiap 4 menit — agar Railway TIDAK tidur selama demo
  //    Gunakan /api/ping (tanpa DB) — lebih ringan dari /health
  keepAliveTimer = setInterval(() => {
    if (!document.hidden) {
      api.get('/ping').catch(() => { /* silent */ })
    }
  }, 4 * 60 * 1000) // 4 menit
})

onUnmounted(() => {
  if (keepAliveTimer) clearInterval(keepAliveTimer)
})
</script>

<template>
  <RouterView />
</template>
