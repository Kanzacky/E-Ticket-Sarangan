<script setup lang="ts">
import {
  LayoutDashboard,
  LogOut,
  Menu,
  Ticket,
  Users,
  X
} from 'lucide-vue-next'
import { ref } from 'vue'
import { useI18n } from 'vue-i18n'
import { RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const { t } = useI18n()
const authStore = useAuthStore()
const sidebarOpen = ref(false)

async function handleLogout() {
  await authStore.logout()
  void router.push({ name: 'login' })
}
</script>

<template>
  <div class="flex min-h-screen bg-[#F7F5EF] text-[#1D2724] font-sans selection:bg-[#4F7465] selection:text-white">
    <!-- Mobile Sidebar Backdrop -->
    <div 
      v-if="sidebarOpen" 
      class="fixed inset-0 z-40 bg-[#1D2724]/60 backdrop-blur-sm lg:hidden transition-opacity"
      @click="sidebarOpen = false" 
    />

    <!-- Sidebar -->
    <aside
      class="fixed inset-y-0 left-0 z-50 w-72 transform bg-[#173B35] text-white transition-transform duration-300 lg:static lg:translate-x-0"
      :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    >
      <div class="flex h-full flex-col">
        <!-- Logo Area -->
        <div class="flex items-center justify-between px-6 py-6 border-b border-white/10">
          <router-link to="/" class="flex items-center gap-3 hover:opacity-80 transition-opacity">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-[#C9965B] shadow-sm">
              <Ticket class="h-5 w-5 text-[#173B35]" />
            </div>
            <div>
              <span class="block text-sm font-bold tracking-wide">{{ t('app.name') }}</span>
              <span class="block text-xs text-[#F7F5EF]/60 font-medium">Administrator</span>
            </div>
          </router-link>
          
          <button @click="sidebarOpen = false" class="lg:hidden p-2 text-white/70 hover:text-white hover:bg-white/10 rounded-lg">
            <X class="w-5 h-5" />
          </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 space-y-1.5 px-4 py-6 overflow-y-auto">
          <p class="px-4 text-[10px] font-bold uppercase tracking-widest text-white/40 mb-3">Utama</p>
          
          <router-link
            to="/admin/dashboard"
            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-white/70 transition-all hover:bg-white/10 hover:text-white group"
            active-class="bg-[#4F7465] text-white shadow-md font-bold"
          >
            <LayoutDashboard class="h-4 w-4" />
            Dashboard
          </router-link>

          <p class="px-4 text-[10px] font-bold uppercase tracking-widest text-white/40 mb-3 mt-8">Manajemen</p>

          <router-link
            to="/admin/users"
            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-medium text-white/70 transition-all hover:bg-white/10 hover:text-white group"
            active-class="bg-[#4F7465] text-white shadow-md font-bold"
          >
            <Users class="h-4 w-4" />
            Pengguna
          </router-link>
        </nav>

        <!-- Bottom Profile/Logout -->
        <div class="p-4 border-t border-white/10 bg-black/10">
          <div class="flex items-center gap-3 px-4 py-3 mb-2 rounded-xl bg-white/5 border border-white/10">
            <div class="w-8 h-8 rounded-full bg-[#4F7465] flex items-center justify-center text-xs font-bold uppercase shadow-inner">
              {{ authStore.user?.name?.charAt(0) || 'A' }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-white truncate">{{ authStore.user?.name }}</p>
              <p class="text-xs text-white/50 truncate">{{ authStore.user?.email }}</p>
            </div>
          </div>
          
          <button
            type="button"
            class="flex w-full items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-[#F7F5EF] transition-all hover:bg-red-500/20 hover:text-red-200"
            @click="handleLogout"
          >
            <LogOut class="h-4 w-4" />
            Keluar Sistem
          </button>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex min-w-0 flex-1 flex-col h-screen overflow-hidden">
      <!-- Top Header -->
      <header class="sticky top-0 z-30 flex items-center justify-between border-b border-[#173B35]/10 bg-white/90 backdrop-blur-md px-6 py-4 shadow-sm">
        <div class="flex items-center gap-4">
          <button
            class="rounded-lg p-2 text-[#66706C] hover:bg-[#F7F5EF] hover:text-[#173B35] transition-colors lg:hidden"
            type="button"
            @click="sidebarOpen = true"
          >
            <Menu class="h-5 w-5" />
          </button>
          
          <h2 class="text-lg font-bold text-[#1D2724] hidden sm:block">
            Dashboard Admin
          </h2>
        </div>
        
        <div class="flex items-center gap-4">
          <div class="text-sm font-medium text-[#66706C] bg-[#F7F5EF] px-4 py-2 rounded-full border border-[#173B35]/10 hidden md:block">
            {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-auto p-4 md:p-6 lg:p-8 relative">
        <div class="max-w-7xl mx-auto space-y-6">
          <RouterView />
        </div>
      </main>
    </div>
  </div>
</template>
