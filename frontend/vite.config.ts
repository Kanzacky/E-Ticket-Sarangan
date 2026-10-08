import { fileURLToPath, URL } from 'node:url'

import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'

export default defineConfig({
  plugins: [vue(), tailwindcss()],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url)),
    },
  },
  server: {
    host: true,
    port: 5173,
  },
  build: {
    // Naikkan batas warning chunk (default 500kB → 700kB)
    chunkSizeWarningLimit: 700,
    rollupOptions: {
      output: {
        /**
         * Manual chunk splitting — pisahkan vendor besar ke chunk terpisah
         * sehingga halaman ringan tidak perlu load semua library sekaligus.
         *
         * Strategi:
         *  - vue-core    : Vue, Pinia, Router (selalu dibutuhkan, di-cache lama)
         *  - charts      : Chart.js + vue-chartjs (besar, hanya di admin dashboard)
         *  - qr          : vue-qrcode-reader + qrcode.vue (hanya di scanner & booking)
         *  - vendor-misc : Axios, vue-i18n, lucide (medium, dipakai luas)
         */
        manualChunks(id: string) {
          if (id.includes('node_modules')) {
            if (id.includes('chart.js') || id.includes('vue-chartjs')) {
              return 'chunk-charts'
            }
            if (id.includes('vue-qrcode-reader') || id.includes('qrcode.vue') || id.includes('qrcode')) {
              return 'chunk-qr'
            }
            if (id.includes('vue') || id.includes('pinia') || id.includes('vue-router')) {
              return 'chunk-vue-core'
            }
            return 'chunk-vendor'
          }
        },
      },
    },
  },
})

