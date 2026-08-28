<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { Save, Globe, Mail, Phone, MapPin, Building, CreditCard } from 'lucide-vue-next'
import api from '@/services/api'

const isSubmitting = ref(false)
const isLoading = ref(true)
const showSuccess = ref(false)
const error = ref('')

const form = ref({
  siteName: 'e-Ticket Telaga Sarangan',
  siteDescription: 'Pesan tiket wisata Telaga Sarangan secara online dengan mudah dan cepat.',
  contactEmail: 'info@sarangan.com',
  contactPhone: '+62 811-1234-5678',
  address: 'Jl. Raya Telaga Sarangan, Magetan, Jawa Timur',
  operationalHours: 'Senin - Minggu: 07:00 - 17:00 WIB',
  paymentGateway: 'production',
  taxRate: 11,
})

const fetchSettings = async () => {
  isLoading.value = true
  error.value = ''
  try {
    const res = await api.get('/admin/settings')
    if (res.data.success) {
      const d = res.data.data
      form.value.siteName = d.site_name || form.value.siteName
      form.value.siteDescription = d.site_description || form.value.siteDescription
      form.value.contactEmail = d.contact_email || form.value.contactEmail
      form.value.contactPhone = d.contact_phone || form.value.contactPhone
      form.value.address = d.address || form.value.address
      form.value.operationalHours = d.operational_hours || form.value.operationalHours
      form.value.paymentGateway = d.payment_gateway || form.value.paymentGateway
      form.value.taxRate = d.tax_rate ? Number(d.tax_rate) : form.value.taxRate
    }
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal memuat pengaturan'
  } finally { isLoading.value = false }
}

onMounted(fetchSettings)

const saveSettings = async () => {
  isSubmitting.value = true
  error.value = ''
  try {
    const payload = {
      site_name: form.value.siteName,
      site_description: form.value.siteDescription,
      contact_email: form.value.contactEmail,
      contact_phone: form.value.contactPhone,
      address: form.value.address,
      operational_hours: form.value.operationalHours,
      payment_gateway: form.value.paymentGateway,
      tax_rate: form.value.taxRate,
    }
    const res = await api.patch('/admin/settings', payload)
    if (res.data.success) {
      showSuccess.value = true
      setTimeout(() => { showSuccess.value = false }, 3000)
    }
  } catch (e: any) {
    error.value = e.response?.data?.message || 'Gagal menyimpan pengaturan'
  } finally { isSubmitting.value = false }
}
</script>

<template>
  <div class="space-y-6 max-w-4xl mx-auto pb-12">
    <!-- Header -->
    <div>
      <h1 class="text-2xl font-black text-[#173B35]">Pengaturan Sistem</h1>
      <p class="text-sm font-medium text-[#66706C] mt-1">Konfigurasi informasi website dan parameter operasional.</p>
    </div>

    <div v-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl">{{ error }}</div>
    <div v-if="isLoading" class="p-8 text-center text-sm text-[#66706C]">Memuat pengaturan...</div>

    <form v-else @submit.prevent="saveSettings" class="space-y-6">
      
      <!-- General Settings -->
      <div class="bg-white rounded-xl border border-[#E8E6DE] shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-[#E8E6DE] bg-[#F7F5EF]/50">
          <h2 class="text-base font-bold text-[#1D2724] flex items-center gap-2">
            <Globe class="w-5 h-5 text-[#66706C]" /> Informasi Website
          </h2>
        </div>
        <div class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Nama Website</label>
              <input 
                v-model="form.siteName" 
                type="text" required
                class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              >
            </div>
            <div>
              <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Jam Operasional Wisata</label>
              <input 
                v-model="form.operationalHours" 
                type="text" required
                class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              >
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Deskripsi Singkat (SEO)</label>
            <textarea 
              v-model="form.siteDescription" 
              rows="2" required
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- Contact Info -->
      <div class="bg-white rounded-xl border border-[#E8E6DE] shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-[#E8E6DE] bg-[#F7F5EF]/50">
          <h2 class="text-base font-bold text-[#1D2724] flex items-center gap-2">
            <Building class="w-5 h-5 text-[#66706C]" /> Kontak & Lokasi
          </h2>
        </div>
        <div class="p-6 space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-bold text-[#1D2724] mb-1.5 flex items-center gap-1"><Mail class="w-3.5 h-3.5"/> Email Bantuan</label>
              <input 
                v-model="form.contactEmail" 
                type="email" required
                class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              >
            </div>
            <div>
              <label class="block text-xs font-bold text-[#1D2724] mb-1.5 flex items-center gap-1"><Phone class="w-3.5 h-3.5"/> Telepon / WhatsApp</label>
              <input 
                v-model="form.contactPhone" 
                type="text" required
                class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              >
            </div>
          </div>
          <div>
            <label class="block text-xs font-bold text-[#1D2724] mb-1.5 flex items-center gap-1"><MapPin class="w-3.5 h-3.5"/> Alamat Lengkap</label>
            <textarea 
              v-model="form.address" 
              rows="2" required
              class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
            ></textarea>
          </div>
        </div>
      </div>

      <!-- System & Payments -->
      <div class="bg-white rounded-xl border border-[#E8E6DE] shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-[#E8E6DE] bg-[#F7F5EF]/50">
          <h2 class="text-base font-bold text-[#1D2724] flex items-center gap-2">
            <CreditCard class="w-5 h-5 text-[#66706C]" /> Sistem & Pembayaran
          </h2>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Mode Payment Gateway</label>
              <select 
                v-model="form.paymentGateway"
                class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              >
                <option value="sandbox">Sandbox (Testing)</option>
                <option value="production">Production (Live)</option>
              </select>
              <p class="text-xs text-[#66706C] mt-1.5">Peringatan: Mode Production akan memproses pembayaran asli.</p>
            </div>
            <div>
              <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Pajak / PPN (%)</label>
              <input 
                v-model="form.taxRate" 
                type="number" min="0" max="100" required
                class="w-full text-sm px-3.5 py-2.5 rounded-lg border border-[#E8E6DE] bg-white focus:ring-1 focus:ring-[#173B35] focus:border-[#173B35]"
              >
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex items-center justify-end gap-4 pt-4 border-t border-[#E8E6DE]">
        <span v-if="showSuccess" class="text-sm font-bold text-emerald-600 transition-opacity">
          Pengaturan berhasil disimpan!
        </span>
        
        <button 
          type="button"
          class="px-5 py-2.5 text-sm font-bold text-[#66706C] hover:text-[#1D2724] hover:bg-[#F7F5EF] rounded-lg transition-colors"
        >
          Batal
        </button>
        <button 
          type="submit"
          :disabled="isSubmitting"
          class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#173B35] text-white text-sm font-bold rounded-lg hover:bg-[#112a26] focus:ring-2 focus:ring-offset-2 focus:ring-[#173B35] disabled:opacity-50 transition-colors"
        >
          <Save class="w-4 h-4" />
          {{ isSubmitting ? 'Menyimpan...' : 'Simpan Perubahan' }}
        </button>
      </div>
      
    </form>
  </div>
</template>
