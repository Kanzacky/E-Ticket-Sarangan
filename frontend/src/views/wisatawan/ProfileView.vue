<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import { User, Mail, Phone, MapPin, ChevronLeft, LogOut, Edit2, Info, X, Save } from 'lucide-vue-next'

const authStore = useAuthStore()
const router = useRouter()

const isEditModalOpen = ref(false)
const isSubmitting = ref(false)
const editForm = ref({
  name: authStore.user?.name || '',
  phone: authStore.user?.phone || '',
  email: authStore.user?.email || '',
  password: '', // Optional
})

function openEditModal() {
  editForm.value = {
    name: authStore.user?.name || '',
    phone: authStore.user?.phone || '',
    email: authStore.user?.email || '',
    password: '',
  }
  isEditModalOpen.value = true
}

async function handleUpdateProfile() {
  isSubmitting.value = true
  const res = await authStore.updateProfile({
    name: editForm.value.name,
    phone: editForm.value.phone,
    email: editForm.value.email,
    password: editForm.value.password || undefined,
  })
  
  isSubmitting.value = false
  if (res.success) {
    isEditModalOpen.value = false
    alert('Profil berhasil diperbarui!')
  } else {
    alert(res.message || 'Gagal memperbarui profil.')
  }
}

async function handleLogout() {
  await authStore.logout()
  void router.push({ name: 'home' })
}
</script>

<template>
  <div class="space-y-8">
    <!-- Header -->
    <div class="flex items-center gap-3">
      <router-link
        to="/my-tickets"
        class="flex h-10 w-10 items-center justify-center rounded-xl bg-white border border-[#173B35]/10 text-[#173B35] shadow-sm transition hover:bg-[#F7F5EF]"
      >
        <ChevronLeft class="h-5 w-5" />
      </router-link>
      <div>
        <h1 class="text-2xl font-bold text-[#1D2724] tracking-tight">Profil Saya</h1>
        <p class="text-sm text-[#66706C]">Informasi akun wisatawan</p>
      </div>
    </div>

    <!-- Profile Content -->
    <div class="max-w-2xl bg-white rounded-2xl border border-[#173B35]/10 shadow-sm overflow-hidden">
      <!-- Cover & Avatar -->
      <div class="h-32 bg-[#173B35] relative">
        <div class="absolute inset-0 bg-[url('/images/sarangan-hero-2.jpg')] bg-cover bg-center opacity-30 mix-blend-overlay"></div>
      </div>
      
      <div class="px-6 sm:px-8 pb-8">
        <!-- Avatar -->
        <div class="w-24 h-24 rounded-full border-4 border-white bg-[#F7F5EF] text-[#173B35] flex items-center justify-center -mt-12 shadow-sm relative z-10">
          <User class="w-10 h-10" />
        </div>
        
        <!-- Info -->
        <div class="mt-4 flex flex-col sm:flex-row sm:items-end justify-between gap-6">
          <div>
            <h2 class="text-2xl font-black text-[#1D2724]">{{ authStore.user?.name || 'Wisatawan' }}</h2>
            <p class="text-[#4F7465] font-medium flex items-center gap-1.5 mt-1">
              <MapPin class="w-4 h-4" />
              Wisatawan Sarangan
            </p>
          </div>
          
          <div class="flex flex-col sm:flex-row gap-3">
            <button 
              type="button"
              @click="openEditModal"
              class="inline-flex items-center justify-center gap-2 rounded-xl bg-[#F7F5EF] px-5 py-2.5 text-sm font-bold text-[#1D2724] transition-all hover:bg-[#e8e6df]"
            >
              <Edit2 class="w-4 h-4" />
              Edit Profil
            </button>
            <button 
              @click="handleLogout"
              class="inline-flex items-center justify-center gap-2 rounded-xl bg-red-50 text-red-600 px-5 py-2.5 text-sm font-bold transition-all hover:bg-red-100"
            >
              <LogOut class="w-4 h-4" />
              Keluar
            </button>
          </div>
        </div>
        
        <hr class="border-[#173B35]/10 my-8" />
        
        <!-- Details -->
        <div class="space-y-6">
          <h3 class="text-lg font-bold text-[#1D2724] mb-4">Detail Kontak</h3>
          
          <div class="grid sm:grid-cols-2 gap-6">
            <div class="flex gap-4">
              <div class="w-10 h-10 rounded-full bg-[#173B35]/5 text-[#173B35] flex items-center justify-center shrink-0">
                <Mail class="w-5 h-5" />
              </div>
              <div>
                <p class="text-sm font-medium text-[#66706C]">Email</p>
                <p class="text-base font-bold text-[#1D2724] truncate">{{ authStore.user?.email || '-' }}</p>
              </div>
            </div>
            
            <div class="flex gap-4">
              <div class="w-10 h-10 rounded-full bg-[#173B35]/5 text-[#173B35] flex items-center justify-center shrink-0">
                <Phone class="w-5 h-5" />
              </div>
              <div>
                <p class="text-sm font-medium text-[#66706C]">Nomor Telepon</p>
                <p class="text-base font-bold text-[#1D2724]">{{ authStore.user?.phone || '-' }}</p>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Info Banner -->
        <div class="mt-8 rounded-xl bg-[#173B35]/5 p-4 flex items-start gap-3">
          <Info class="w-5 h-5 text-[#173B35] shrink-0 mt-0.5" />
          <p class="text-sm text-[#1D2724] leading-relaxed">
            Data profil Anda digunakan untuk mempercepat proses pengisian formulir pemesanan tiket. Pastikan email dan nomor telepon Anda selalu aktif untuk menerima e-ticket.
          </p>
        </div>
        
      </div>
    </div>

    <!-- Edit Profile Modal -->
    <div v-if="isEditModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-[#1D2724]/60 backdrop-blur-sm">
      <div class="bg-white rounded-2xl w-full max-w-md shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <div class="flex items-center justify-between p-5 border-b border-[#E8E6DE]">
          <h3 class="text-lg font-bold text-[#1D2724]">Edit Profil</h3>
          <button @click="isEditModalOpen = false" class="p-2 text-[#66706C] hover:bg-[#F7F5EF] rounded-full transition-colors">
            <X class="w-5 h-5" />
          </button>
        </div>
        
        <form @submit.prevent="handleUpdateProfile" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-bold text-[#1D2724] mb-1.5">Nama Lengkap</label>
            <input 
              v-model="editForm.name" 
              type="text" 
              required
              class="w-full text-sm px-4 py-2.5 border border-[#E8E6DE] rounded-xl focus:ring-2 focus:ring-[#173B35] focus:border-[#173B35]"
            >
          </div>
          <div>
            <label class="block text-sm font-bold text-[#1D2724] mb-1.5">Email</label>
            <input 
              v-model="editForm.email" 
              type="email" 
              required
              class="w-full text-sm px-4 py-2.5 border border-[#E8E6DE] rounded-xl focus:ring-2 focus:ring-[#173B35] focus:border-[#173B35]"
            >
          </div>
          <div>
            <label class="block text-sm font-bold text-[#1D2724] mb-1.5">Nomor Telepon</label>
            <input 
              v-model="editForm.phone" 
              type="tel" 
              required
              class="w-full text-sm px-4 py-2.5 border border-[#E8E6DE] rounded-xl focus:ring-2 focus:ring-[#173B35] focus:border-[#173B35]"
            >
          </div>
          <div>
            <label class="block text-sm font-bold text-[#1D2724] mb-1.5">Password Baru (Opsional)</label>
            <input 
              v-model="editForm.password" 
              type="password" 
              placeholder="Kosongkan jika tidak ingin mengubah password"
              class="w-full text-sm px-4 py-2.5 border border-[#E8E6DE] rounded-xl focus:ring-2 focus:ring-[#173B35] focus:border-[#173B35]"
            >
          </div>
          
          <div class="pt-4 flex justify-end gap-3">
            <button 
              type="button" 
              @click="isEditModalOpen = false"
              class="px-5 py-2.5 text-sm font-bold text-[#66706C] hover:bg-[#F7F5EF] rounded-xl transition-colors"
            >
              Batal
            </button>
            <button 
              type="submit" 
              :disabled="isSubmitting"
              class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-[#173B35] text-white text-sm font-bold rounded-xl hover:bg-[#112a26] transition-colors disabled:opacity-50"
            >
              <Save class="w-4 h-4" v-if="!isSubmitting" />
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
