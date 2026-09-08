<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { Menu, Search, User, LogOut } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import NotificationBell from '@/components/ui/NotificationBell.vue'

const props = defineProps<{
  pageTitle?: string
}>()

const emit = defineEmits<{
  (e: 'toggleSidebar'): void
}>()

const authStore = useAuthStore()
const router = useRouter()
const isProfileOpen = ref(false)

async function handleLogout() {
  await authStore.logout()
  void router.push('/login')
}
</script>

<template>
  <header class="h-[72px] bg-white border-b border-[#E8E6DE] sticky top-0 z-30 flex items-center justify-between px-5 sm:px-6 lg:px-8">
    
    <!-- Left: Mobile Menu Toggle & Title -->
    <div class="flex items-center gap-4">
      <button 
        @click="emit('toggleSidebar')"
        class="lg:hidden p-2 -ml-2 rounded-lg text-[#66706C] hover:text-[#173B35] hover:bg-[#F7F5EF] transition-colors"
      >
        <Menu class="w-5 h-5" />
      </button>
      
      <h2 v-if="pageTitle" class="text-lg font-bold text-[#1D2724] hidden sm:block">
        {{ pageTitle }}
      </h2>
    </div>

    <!-- Right: Search, Notifications, Profile -->
    <div class="flex items-center gap-4">
      
      <!-- Search (Visual Only for now) -->
      <div class="hidden md:flex relative group">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <Search class="w-4 h-4 text-[#66706C] group-focus-within:text-[#173B35]" />
        </div>
        <input 
          type="text" 
          placeholder="Cari..." 
          class="block w-64 pl-9 pr-3 py-2 border border-[#E8E6DE] rounded-full text-sm placeholder-[#66706C] bg-[#F7F5EF] focus:outline-none focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35] focus:bg-white transition-colors"
        >
      </div>

      <div class="h-6 w-px bg-[#E8E6DE] hidden md:block"></div>

      <!-- Notifications -->
      <NotificationBell />

      <!-- Profile Dropdown -->
      <div class="relative">
        <button 
          @click="isProfileOpen = !isProfileOpen"
          class="flex items-center gap-2.5 p-1 rounded-full hover:bg-[#F7F5EF] transition-colors"
        >
          <div class="w-8 h-8 rounded-full bg-[#173B35]/10 flex items-center justify-center text-[#173B35]">
            <User class="w-4 h-4" />
          </div>
          <div class="hidden sm:block text-left">
            <p class="text-sm font-bold text-[#1D2724] leading-tight">{{ authStore.user?.name || 'Petugas' }}</p>
            <p class="text-[10px] font-medium text-[#66706C] leading-tight capitalize">{{ authStore.user?.role || 'Petugas' }}</p>
          </div>
        </button>

        <!-- Dropdown Menu -->
        <div 
          v-if="isProfileOpen" 
          @click.away="isProfileOpen = false"
          class="absolute right-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-[#E8E6DE] py-1 z-50 overflow-hidden"
        >
          <div class="px-4 py-3 border-b border-[#E8E6DE] sm:hidden">
            <p class="text-sm font-bold text-[#1D2724] truncate">{{ authStore.user?.name || 'Petugas' }}</p>
            <p class="text-xs text-[#66706C] truncate">{{ authStore.user?.email || 'petugas@example.com' }}</p>
          </div>
          <router-link 
            to="/petugas/profile" 
            @click="isProfileOpen = false"
            class="flex items-center gap-2 px-4 py-2.5 text-sm text-[#1D2724] hover:bg-[#F7F5EF]"
          >
            <User class="w-4 h-4 text-[#66706C]" />
            Profil Saya
          </router-link>
          <button 
            @click="handleLogout"
            class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-red-600 font-medium hover:bg-red-50 transition-colors border-t border-[#E8E6DE]"
          >
            <LogOut class="w-4 h-4" />
            Keluar
          </button>
        </div>
      </div>

    </div>
  </header>
</template>
