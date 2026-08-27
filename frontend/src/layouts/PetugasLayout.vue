<script setup lang="ts">
import {
  CalendarCheck,
  Home,
  LogOut,
  QrCode,
  Ticket
} from 'lucide-vue-next'
import { RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

async function handleLogout() {
  await authStore.logout()
  void router.push({ name: 'login' })
}
</script>

<template>
  <div class="flex flex-col min-h-screen bg-[#F7F5EF] text-[#1D2724] font-sans selection:bg-[#4F7465] selection:text-white pb-20 md:pb-0 md:pl-24">
    <!-- Top Header Mobile -->
    <header class="sticky top-0 z-30 flex items-center justify-between border-b border-[#173B35]/10 bg-white/90 backdrop-blur-md px-4 py-3 shadow-sm md:hidden">
      <div class="flex items-center gap-2">
        <div class="flex items-center justify-center">
          <img src="/images/logo.png" alt="Logo" class="h-6 w-auto" />
        </div>
        <span class="text-sm font-bold tracking-wide">Petugas Gate</span>
      </div>
      
      <button
        class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-bold text-red-600 transition hover:bg-red-50"
        type="button"
        @click="handleLogout"
      >
        <LogOut class="h-4 w-4" />
      </button>
    </header>

    <!-- Sidebar Desktop / Bottom Nav Mobile -->
    <nav class="fixed bottom-0 left-0 right-0 z-40 flex h-16 w-full items-center justify-around border-t border-[#173B35]/10 bg-white px-2 pb-safe md:inset-y-0 md:w-24 md:flex-col md:justify-start md:border-r md:border-t-0 md:py-6 md:px-3 shadow-[0_-4px_20px_rgba(0,0,0,0.02)] md:shadow-none">
      
      <!-- Desktop Logo -->
      <div class="hidden md:flex flex-col items-center mb-8 gap-2">
        <div class="flex items-center justify-center mt-4">
          <img src="/images/logo.png" alt="Logo" class="h-8 w-auto" />
        </div>
      </div>

      <router-link
        to="/petugas/dashboard"
        class="flex flex-col items-center justify-center gap-1 rounded-xl p-2 md:w-full md:py-4 transition-colors group"
        active-class="text-[#173B35]"
        exact-active-class="text-[#173B35]"
      >
        <div class="flex items-center justify-center w-10 h-10 rounded-full group-[.router-link-exact-active]:bg-[#173B35]/10 group-hover:bg-[#173B35]/5 transition-colors text-[#66706C] group-[.router-link-exact-active]:text-[#173B35]">
          <Home class="h-5 w-5" />
        </div>
        <span class="text-[10px] font-bold text-[#66706C] group-[.router-link-exact-active]:text-[#173B35]">Beranda</span>
      </router-link>

      <router-link
        to="/petugas/scanner"
        class="relative flex flex-col items-center justify-center gap-1 p-2 md:w-full md:py-4 transition-colors group -mt-6 md:mt-0"
        active-class="text-[#173B35]"
      >
        <div class="flex items-center justify-center w-14 h-14 md:w-12 md:h-12 rounded-full bg-[#173B35] text-white shadow-lg shadow-[#173B35]/30 group-hover:bg-[#4F7465] transition-colors border-4 border-[#F7F5EF] md:border-none">
          <QrCode class="h-6 w-6" />
        </div>
        <span class="text-[10px] font-bold text-[#173B35] md:text-[#66706C] md:group-[.router-link-active]:text-[#173B35] mt-1">Scan</span>
      </router-link>

      <router-link
        to="/petugas/checkins"
        class="flex flex-col items-center justify-center gap-1 rounded-xl p-2 md:w-full md:py-4 transition-colors group"
        active-class="text-[#173B35]"
      >
        <div class="flex items-center justify-center w-10 h-10 rounded-full group-[.router-link-active]:bg-[#173B35]/10 group-hover:bg-[#173B35]/5 transition-colors text-[#66706C] group-[.router-link-active]:text-[#173B35]">
          <CalendarCheck class="h-5 w-5" />
        </div>
        <span class="text-[10px] font-bold text-[#66706C] group-[.router-link-active]:text-[#173B35]">Riwayat</span>
      </router-link>

      <!-- Desktop Logout -->
      <div class="hidden md:flex mt-auto w-full">
        <button
          class="flex flex-col items-center justify-center gap-1 rounded-xl p-2 w-full py-4 transition-colors group hover:text-red-600"
          type="button"
          @click="handleLogout"
        >
          <div class="flex items-center justify-center w-10 h-10 rounded-full group-hover:bg-red-50 transition-colors text-[#66706C] group-hover:text-red-600">
            <LogOut class="h-5 w-5" />
          </div>
          <span class="text-[10px] font-bold text-[#66706C] group-hover:text-red-600">Keluar</span>
        </button>
      </div>
    </nav>

    <!-- Main Content Area -->
    <main class="flex-1 w-full max-w-lg mx-auto p-4 md:p-6 lg:p-8 relative">
      <RouterView />
    </main>
  </div>
</template>
