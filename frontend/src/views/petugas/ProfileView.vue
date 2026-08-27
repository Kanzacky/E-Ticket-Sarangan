<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { User, Mail, Phone, ShieldCheck } from 'lucide-vue-next'

const authStore = useAuthStore()
</script>

<template>
  <div class="space-y-6 max-w-3xl">
    <div class="mb-6">
      <h1 class="text-2xl font-black text-[#1D2724]">Profil Petugas</h1>
      <p class="text-sm font-medium text-[#66706C] mt-1">Informasi akun operasional Anda.</p>
    </div>

    <div class="bg-white rounded-2xl border border-[#E8E6DE] overflow-hidden">
      <!-- Header banner -->
      <div class="h-32 bg-[#173B35] relative">
        <div class="absolute inset-0 bg-[url('/images/sarangan-story-2.jpg')] bg-cover bg-center opacity-20 grayscale mix-blend-overlay"></div>
      </div>
      
      <!-- Profile Content -->
      <div class="px-6 sm:px-10 pb-10 relative">
        <!-- Avatar -->
        <div class="absolute -top-16 left-6 sm:left-10 w-32 h-32 bg-white rounded-full p-2 border border-[#E8E6DE] shadow-sm">
          <div class="w-full h-full bg-[#F7F5EF] rounded-full flex items-center justify-center text-[#173B35]">
            <User class="w-12 h-12" />
          </div>
        </div>

        <div class="pt-20">
          <div class="flex items-center gap-3 mb-6">
            <h2 class="text-2xl font-bold text-[#1D2724]">{{ authStore.user?.name || 'Petugas' }}</h2>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#173B35]/10 text-[#173B35] text-xs font-bold capitalize">
              <ShieldCheck class="w-3.5 h-3.5" />
              {{ authStore.user?.role || 'petugas' }}
            </span>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
              <div class="bg-[#F7F5EF] p-4 rounded-xl border border-[#E8E6DE]">
                <div class="flex items-center gap-3 mb-1">
                  <Mail class="w-4 h-4 text-[#66706C]" />
                  <span class="text-xs font-bold text-[#66706C] uppercase tracking-wider">Email</span>
                </div>
                <p class="text-sm font-bold text-[#1D2724] pl-7">{{ authStore.user?.email || '-' }}</p>
              </div>

              <div class="bg-[#F7F5EF] p-4 rounded-xl border border-[#E8E6DE]">
                <div class="flex items-center gap-3 mb-1">
                  <Phone class="w-4 h-4 text-[#66706C]" />
                  <span class="text-xs font-bold text-[#66706C] uppercase tracking-wider">Nomor Telepon</span>
                </div>
                <p class="text-sm font-bold text-[#1D2724] pl-7">{{ authStore.user?.phone || '-' }}</p>
              </div>
            </div>
            
            <div class="bg-[#F7F5EF] p-6 rounded-xl border border-[#E8E6DE] flex flex-col justify-center h-full">
              <h3 class="text-sm font-bold text-[#1D2724] mb-2">Informasi Akun</h3>
              <p class="text-sm text-[#66706C]">Akun Anda dikelola oleh Administrator sistem. Untuk mengubah data pribadi atau password, silakan hubungi tim Admin E-Ticket Sarangan.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
