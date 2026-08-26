<script setup lang="ts">
import { Ticket, MapPin, ArrowRight } from 'lucide-vue-next'
import { useAuthStore } from '@/stores/auth'
import { ref, onMounted } from 'vue'

const authStore = useAuthStore()
const isLoading = ref(true)

// Mock Data structure based on requirements (should come from API later)
const upcomingTrip = ref<any>(null) // Null implies no upcoming trips

onMounted(() => {
  // Simulate API fetch delay
  setTimeout(() => {
    isLoading.value = false
    // Leave data empty to show Empty State as required by Anti-AI slop rule
    // "Jika belum ada booking: Empty state: 'Belum ada perjalanan.'"
  }, 800)
})
</script>

<template>
  <div class="space-y-12">
    
    <!-- Welcoming Hero Area -->
    <div class="relative overflow-hidden rounded-3xl bg-[#173B35]">
      <div class="absolute inset-0">
        <img 
          src="/images/sarangan-hero-2.jpg" 
          alt="Sarangan" 
          class="h-full w-full object-cover mix-blend-overlay opacity-30" 
        />
        <div class="absolute inset-0 bg-gradient-to-r from-[#173B35] to-transparent"></div>
      </div>
      
      <div class="relative z-10 px-6 py-12 md:py-16 md:px-12 flex flex-col md:w-2/3">
        <h1 class="text-3xl md:text-4xl font-black text-white tracking-tight mb-2">
          Selamat datang, {{ authStore.user?.name?.split(' ')[0] || 'Wisatawan' }}!
        </h1>
        <p class="text-[#F7F5EF]/80 text-lg md:text-xl font-medium mb-8">
          Siap menikmati kesejukan alam Sarangan?
        </p>
        
        <div>
          <router-link
            to="/wisatawan/booking"
            class="inline-flex items-center justify-center gap-2 bg-[#C9965B] text-[#173B35] px-6 py-3.5 rounded-xl font-bold hover:bg-[#b0814a] transition-colors shadow-lg active:scale-95"
          >
            Pesan Tiket Sekarang
            <ArrowRight class="w-5 h-5" />
          </router-link>
        </div>
      </div>
    </div>

    <!-- Upcoming Trip Section -->
    <section class="max-w-3xl">
      <div class="flex items-center gap-3 mb-6">
        <MapPin class="w-6 h-6 text-[#4F7465]" />
        <h2 class="text-2xl font-bold text-[#1D2724] tracking-tight">Perjalanan Berikutnya</h2>
      </div>
      
      <div v-if="isLoading" class="bg-white rounded-2xl p-6 border border-[#173B35]/10 shadow-sm animate-pulse">
        <div class="h-6 w-1/3 bg-slate-200 rounded mb-4"></div>
        <div class="h-4 w-1/2 bg-slate-200 rounded mb-8"></div>
        <div class="h-12 w-full bg-slate-200 rounded-xl"></div>
      </div>
      
      <!-- Empty State -->
      <div v-else-if="!upcomingTrip" class="bg-white rounded-2xl p-10 border border-[#173B35]/10 shadow-sm text-center flex flex-col items-center">
        <div class="w-16 h-16 bg-[#F7F5EF] rounded-full flex items-center justify-center mb-4">
          <Ticket class="w-8 h-8 text-[#66706C]" />
        </div>
        <h3 class="text-lg font-bold text-[#1D2724] mb-1">Belum ada perjalanan</h3>
        <p class="text-[#66706C] text-sm mb-6 max-w-md">Anda belum memiliki pesanan aktif atau tiket yang akan datang. Yuk, rencanakan liburan Anda sekarang!</p>
        <router-link
          to="/wisatawan/booking"
          class="inline-flex items-center justify-center bg-[#173B35] text-white px-6 py-3 rounded-xl font-bold hover:bg-[#1D2724] transition-colors active:scale-95"
        >
          Lihat Pilihan Tiket
        </router-link>
      </div>

      <!-- Filled State -->
      <div v-else class="bg-white rounded-2xl p-6 sm:p-8 border-2 border-[#4F7465] shadow-lg shadow-[#4F7465]/10 relative overflow-hidden">
        <div class="absolute top-0 right-0 bg-[#4F7465] text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl uppercase tracking-wider">
          Aktif
        </div>
        
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
          <div>
            <p class="text-[#66706C] text-sm font-medium mb-1">Kunjungan ke Sarangan</p>
            <p class="text-2xl font-black text-[#1D2724] mb-2">{{ upcomingTrip.date }}</p>
            <div class="flex items-center gap-4 text-sm">
              <span class="bg-[#F7F5EF] text-[#173B35] px-2 py-1 rounded-md font-bold">{{ upcomingTrip.qty }} Tiket</span>
              <span class="text-[#66706C]">Kode: <span class="font-bold text-[#1D2724]">{{ upcomingTrip.code }}</span></span>
            </div>
          </div>
          
          <router-link
            :to="`/wisatawan/tickets/${upcomingTrip.id}`"
            class="inline-flex items-center justify-center bg-[#F7F5EF] text-[#173B35] px-6 py-3 rounded-xl font-bold hover:bg-[#e8e6df] transition-colors border border-[#173B35]/10 whitespace-nowrap active:scale-95"
          >
            Lihat E-Ticket
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>
