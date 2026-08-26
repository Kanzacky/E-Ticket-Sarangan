<script setup lang="ts">
import {
  LogOut,
  Menu,
  MountainSnow,
  X,
  User
} from 'lucide-vue-next'
import { ref } from 'vue'
import { RouterView, useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()
const isMobileMenuOpen = ref(false)

async function handleLogout() {
  await authStore.logout()
  void router.push({ name: 'home' })
}
</script>

<template>
  <div class="flex flex-col min-h-screen bg-[#F7F5EF] font-sans text-[#1D2724] selection:bg-[#4F7465] selection:text-white">
    <!-- Navbar -->
    <header class="sticky top-0 w-full z-50 bg-[#F7F5EF]/90 backdrop-blur-md border-b border-[#173B35]/10">
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
        <div class="flex h-20 items-center justify-between">
          <!-- Logo -->
          <div class="flex-shrink-0 flex items-center gap-3">
            <router-link to="/" class="flex items-center gap-3 group">
              <div class="bg-[#173B35] p-2.5 rounded-xl group-hover:bg-[#1D2724] transition-colors">
                <MountainSnow class="h-6 w-6 text-[#F7F5EF]" />
              </div>
              <span class="text-xl font-bold tracking-tight text-[#173B35]">
                e-Ticket Sarangan
              </span>
            </router-link>
          </div>

          <!-- Desktop Navigation -->
          <nav class="hidden md:flex items-center gap-8">
            <router-link 
              to="/wisatawan/dashboard" 
              class="text-sm font-bold transition-colors hover:text-[#4F7465]"
              active-class="text-[#173B35]"
              :class="$route.name === 'wisatawan.dashboard' ? 'text-[#173B35]' : 'text-[#66706C]'"
            >
              Beranda
            </router-link>
            
            <router-link 
              to="/wisatawan/booking" 
              class="text-sm font-bold transition-colors hover:text-[#4F7465]"
              active-class="text-[#173B35]"
              :class="$route.name === 'wisatawan.booking' ? 'text-[#173B35]' : 'text-[#66706C]'"
            >
              Pesan Tiket
            </router-link>
            
            <router-link 
              to="/wisatawan/history" 
              class="text-sm font-bold transition-colors hover:text-[#4F7465]"
              active-class="text-[#173B35]"
              :class="['wisatawan.history', 'wisatawan.tickets', 'wisatawan.ticket-detail'].includes($route.name as string) ? 'text-[#173B35]' : 'text-[#66706C]'"
            >
              E-Ticket Saya
            </router-link>
          </nav>

          <!-- Desktop Actions -->
          <div class="hidden md:flex items-center gap-4">
            <div class="flex items-center gap-3 border-r border-[#173B35]/20 pr-4 mr-1">
              <div class="text-right">
                <p class="text-xs font-bold text-[#1D2724]">{{ authStore.user?.name }}</p>
                <p class="text-[10px] text-[#66706C]">Wisatawan</p>
              </div>
              <router-link to="/wisatawan/profile" class="w-10 h-10 rounded-full bg-[#173B35]/10 flex items-center justify-center text-[#173B35] hover:bg-[#173B35]/20 transition-colors">
                <User class="w-5 h-5" />
              </router-link>
            </div>
            
            <button 
              @click="handleLogout"
              class="inline-flex items-center justify-center p-2 rounded-xl text-[#66706C] hover:text-red-600 hover:bg-red-50 transition-colors"
              title="Keluar"
            >
              <LogOut class="w-5 h-5" />
            </button>
          </div>

          <!-- Mobile menu button -->
          <div class="flex items-center md:hidden gap-3">
            <router-link to="/wisatawan/profile" class="w-9 h-9 rounded-full bg-[#173B35]/10 flex items-center justify-center text-[#173B35]">
              <User class="w-4 h-4" />
            </router-link>
            <button 
              @click="isMobileMenuOpen = !isMobileMenuOpen"
              class="inline-flex items-center justify-center rounded-xl p-2 text-[#173B35] hover:bg-[#173B35]/10 transition-colors"
            >
              <Menu v-if="!isMobileMenuOpen" class="h-6 w-6" />
              <X v-else class="h-6 w-6" />
            </button>
          </div>
        </div>
      </div>

      <!-- Mobile menu -->
      <div 
        v-if="isMobileMenuOpen" 
        class="md:hidden absolute top-20 left-0 w-full bg-white border-b border-[#173B35]/10 shadow-xl"
      >
        <div class="px-5 pt-4 pb-6 space-y-2">
          <router-link 
            to="/wisatawan/dashboard" 
            class="block px-4 py-3 rounded-xl text-base font-bold text-[#173B35] hover:bg-[#F7F5EF] transition-colors"
            @click="isMobileMenuOpen = false"
          >
            Beranda
          </router-link>
          <router-link 
            to="/wisatawan/booking" 
            class="block px-4 py-3 rounded-xl text-base font-bold text-[#173B35] hover:bg-[#F7F5EF] transition-colors"
            @click="isMobileMenuOpen = false"
          >
            Pesan Tiket
          </router-link>
          <router-link 
            to="/wisatawan/history" 
            class="block px-4 py-3 rounded-xl text-base font-bold text-[#173B35] hover:bg-[#F7F5EF] transition-colors"
            @click="isMobileMenuOpen = false"
          >
            E-Ticket Saya
          </router-link>
          
          <div class="border-t border-[#173B35]/10 my-2 pt-2"></div>
          
          <button 
            @click="() => { isMobileMenuOpen = false; handleLogout() }"
            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-base font-bold text-red-600 hover:bg-red-50 transition-colors"
          >
            <LogOut class="w-5 h-5" />
            Keluar Sistem
          </button>
        </div>
      </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 w-full max-w-[1240px] mx-auto px-5 sm:px-6 lg:px-8 py-6 md:py-10">
      <RouterView />
    </main>
    
    <!-- Simple Footer -->
    <footer class="mt-auto py-8 bg-white border-t border-[#173B35]/10">
      <div class="max-w-[1240px] mx-auto px-5 sm:px-6 lg:px-8 text-center text-sm text-[#66706C]">
        <p>&copy; {{ new Date().getFullYear() }} e-Ticket Sarangan. Pariwisata Magetan.</p>
      </div>
    </footer>
  </div>
</template>
