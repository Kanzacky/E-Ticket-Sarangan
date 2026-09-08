<script setup lang="ts">
import { 
  ChevronLeft,
  MapPin, 
  Star,
  CheckCircle2,
  AlertCircle,
  Navigation,
} from 'lucide-vue-next'
import { onMounted, reactive, ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'

import { 
  getAccommodationApi, 
  createAccommodationBookingApi,
  type Accommodation 
} from '@/services/accommodation.service'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const accommodation = ref<Accommodation | null>(null)
const isLoading = ref(true)
const errorMessage = ref('')
const successMessage = ref('')
const isSubmitting = ref(false)

const today = new Date().toISOString().slice(0, 10)
const tomorrow = new Date(Date.now() + 86400000).toISOString().slice(0, 10)

const form = reactive({
  check_in: today,
  check_out: tomorrow,
  rooms: 1,
  guests: 2,
  guest_name: authStore.user?.name || '',
  guest_phone: authStore.user?.phone || '',
  notes: '',
})

onMounted(async () => {
  const id = Number(route.params.id)
  if (!id) {
    errorMessage.value = 'ID penginapan tidak valid.'
    isLoading.value = false
    return
  }

  try {
    isLoading.value = true
    accommodation.value = await getAccommodationApi(id)
  } catch (error: unknown) {
    if (axios.isAxiosError(error) && error.response?.data?.message) {
      errorMessage.value = error.response.data.message as string
    } else {
      errorMessage.value = 'Gagal memuat detail penginapan.'
    }
  } finally {
    isLoading.value = false
  }
})

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

const formatDistance = (km: number | undefined | null) => {
  if (!km) return ''
  return km < 1 ? `${Math.round(km * 1000)} m` : `${km} km`
}

const openDirections = () => {
  if (!accommodation.value) return
  const lat = accommodation.value.latitude
  const lng = accommodation.value.longitude
  if (!lat || !lng) return
  // Google Maps directions from Telaga Sarangan (-7.67, 111.216) to accommodation
  const url = `https://www.google.com/maps/dir/-7.67,111.216/${lat},${lng}/`
  window.open(url, '_blank')
}

const totalNights = computed(() => {
  if (!form.check_in || !form.check_out) return 1
  const start = new Date(form.check_in)
  const end = new Date(form.check_out)
  const diff = Math.ceil((end.getTime() - start.getTime()) / (1000 * 3600 * 24))
  return diff > 0 ? diff : 1
})

const totalPrice = computed(() => {
  if (!accommodation.value) return 0
  return accommodation.value.price_per_night * form.rooms * totalNights.value
})

async function handleBook() {
  if (!accommodation.value) return
  if (!authStore.isAuthenticated) {
    void router.push({ name: 'login', query: { redirect: route.fullPath } })
    return
  }

  isSubmitting.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const booking = await createAccommodationBookingApi({
      accommodation_id: accommodation.value.id,
      check_in: form.check_in,
      check_out: form.check_out,
      rooms: form.rooms,
      guests: form.guests,
      guest_name: form.guest_name,
      guest_phone: form.guest_phone,
      notes: form.notes,
    })

    if (booking.payment_url) {
      successMessage.value = 'Reservasi berhasil! Mengalihkan ke pembayaran Xendit...'
      setTimeout(() => { window.location.href = booking.payment_url as string }, 800)
    } else {
      successMessage.value = 'Reservasi berhasil dibuat! Mengalihkan ke riwayat pesanan...'
      setTimeout(() => {
        void router.push({ name: 'wisatawan.tickets' })
      }, 1500)
    }
  } catch (error: unknown) {
    if (axios.isAxiosError(error) && error.response?.data?.message) {
      errorMessage.value = error.response.data.message as string
    } else {
      errorMessage.value = 'Gagal membuat reservasi. Pastikan data valid dan kamar tersedia.'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-[#F7F5EF] pt-28 pb-12 px-5 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1240px]">
      
      <!-- Back Button -->
      <button 
        @click="router.back()"
        class="inline-flex items-center gap-1.5 text-sm font-bold text-[#66706C] hover:text-[#173B35] transition mb-8 group"
      >
        <ChevronLeft class="w-4 h-4 transition-transform group-hover:-translate-x-1" />
        Kembali ke Daftar Penginapan
      </button>

      <!-- Loading State -->
      <div v-if="isLoading" class="animate-pulse flex flex-col lg:flex-row gap-8">
        <div class="lg:w-2/3 h-[400px] bg-white rounded-xl border border-[#E8E6DE]"></div>
        <div class="lg:w-1/3 h-[500px] bg-white rounded-xl border border-[#E8E6DE]"></div>
      </div>
      
      <!-- Error State -->
      <div v-else-if="errorMessage && !accommodation" class="py-16 text-center text-red-600 bg-white rounded-xl border border-red-200">
        <p class="font-medium">{{ errorMessage }}</p>
      </div>

      <!-- Detail Content -->
      <div v-else-if="accommodation" class="flex flex-col lg:flex-row gap-8 items-start">
        
        <!-- Left: Info -->
        <div class="w-full lg:w-2/3 space-y-8">
          
          <!-- Image/Hero Header -->
          <div class="relative h-[300px] sm:h-[400px] bg-[#173B35] rounded-xl overflow-hidden shadow-sm">
            <!-- Background Image if available -->
            <img 
              v-if="accommodation.image_url" 
              :src="accommodation.image_url" 
              class="absolute inset-0 w-full h-full object-cover" 
              alt="Accommodation image"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-[#1D2724] via-transparent to-transparent z-10" :class="{'opacity-80': accommodation.image_url}"></div>
            
            <div class="absolute top-6 left-6 z-20 flex gap-2">
              <span class="inline-flex items-center gap-1.5 rounded-full bg-[#C9965B] px-3 py-1.5 text-sm font-bold text-[#1D2724] shadow-sm">
                <Star class="h-4 w-4 fill-[#1D2724]" /> {{ accommodation.rating }}
              </span>
            </div>
            
            <div class="absolute bottom-6 left-6 z-20 pr-6">
              <h1 class="text-3xl sm:text-4xl font-black text-white mb-2">{{ accommodation.name }}</h1>
              <p class="text-sm text-white/80 flex items-center gap-1.5">
                <MapPin class="h-4 w-4 shrink-0" /> {{ accommodation.address }}
              </p>
              <p v-if="accommodation.distance_km !== null && accommodation.distance_km !== undefined" class="text-sm text-white/90 flex items-center gap-1.5 mt-1">
                <MapPin class="h-4 w-4 shrink-0" /> {{ formatDistance(accommodation.distance_km) }} dari Telaga Sarangan
              </p>
            </div>
          </div>

          <!-- Description -->
          <div class="bg-white rounded-xl border border-[#E8E6DE] p-6 sm:p-8 shadow-sm">
            <h2 class="text-xl font-bold text-[#1D2724] mb-4">Tentang Penginapan Ini</h2>
            <p class="text-[#66706C] leading-relaxed">
              {{ accommodation.description || 'Tidak ada deskripsi yang tersedia untuk penginapan ini.' }}
            </p>

            <!-- Facilities -->
            <div v-if="accommodation.facilities?.length" class="mt-8">
              <h3 class="text-sm font-bold text-[#1D2724] uppercase tracking-wider mb-4">Fasilitas Utama</h3>
              <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                <div 
                  v-for="fac in accommodation.facilities" 
                  :key="fac"
                  class="flex items-center gap-2 text-sm text-[#4F7465]"
                >
                  <CheckCircle2 class="w-4 h-4 text-[#C9965B]" />
                  {{ fac }}
                </div>
              </div>
            </div>
          </div>

        </div>

        <!-- Right: Booking Form -->
        <div class="w-full lg:w-1/3 bg-white rounded-xl border border-[#E8E6DE] shadow-sm sticky top-24">
          <div class="p-6 border-b border-[#E8E6DE]">
            <p class="text-sm text-[#66706C] font-medium mb-1">Mulai dari</p>
            <p class="text-2xl font-black text-[#173B35]">
              Rp{{ formatPrice(accommodation.price_per_night) }}
              <span class="text-sm font-normal text-[#66706C]">/malam</span>
            </p>
            <p class="text-xs font-medium mt-2" :class="accommodation.available_rooms > 0 ? 'text-[#4F7465]' : 'text-red-500'">
              Sisa {{ accommodation.available_rooms }} kamar tersedia
            </p>
            <button 
              v-if="accommodation.latitude && accommodation.longitude"
              type="button"
              @click="openDirections"
              class="mt-3 w-full inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-bold text-[#173B35] border border-[#173B35] rounded-lg hover:bg-[#F7F5EF] transition-colors"
            >
              <Navigation class="w-4 h-4" />
              Lihat Rute
            </button>
          </div>

          <form @submit.prevent="handleBook" class="p-6 space-y-5">
            <!-- Messages -->
            <div v-if="successMessage" class="p-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg flex items-center gap-2">
              <CheckCircle2 class="w-4 h-4 shrink-0" />
              {{ successMessage }}
            </div>
            
            <div v-if="errorMessage && !successMessage" class="p-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg flex items-start gap-2">
              <AlertCircle class="w-4 h-4 shrink-0 mt-0.5" />
              {{ errorMessage }}
            </div>

            <!-- Check-in & Check-out -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Check-in</label>
                <input 
                  type="date" 
                  v-model="form.check_in" 
                  class="w-full text-sm px-3.5 py-2.5 rounded-lg border-[#E8E6DE] bg-white focus:ring-[#173B35] focus:border-[#173B35]"
                  required
                  :min="today"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Check-out</label>
                <input 
                  type="date" 
                  v-model="form.check_out" 
                  class="w-full text-sm px-3.5 py-2.5 rounded-lg border-[#E8E6DE] bg-white focus:ring-[#173B35] focus:border-[#173B35]"
                  required
                  :min="form.check_in"
                />
              </div>
            </div>

            <!-- Kamar & Tamu -->
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Kamar</label>
                <input 
                  type="number" 
                  v-model="form.rooms" 
                  class="w-full text-sm px-3.5 py-2.5 rounded-lg border-[#E8E6DE] bg-white focus:ring-[#173B35] focus:border-[#173B35]"
                  required
                  min="1"
                  :max="accommodation.available_rooms"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-[#1D2724] mb-1.5">Tamu</label>
                <input 
                  type="number" 
                  v-model="form.guests" 
                  class="w-full text-sm px-3.5 py-2.5 rounded-lg border-[#E8E6DE] bg-white focus:ring-[#173B35] focus:border-[#173B35]"
                  required
                  min="1"
                />
              </div>
            </div>
            
            <div class="pt-4 border-t border-[#E8E6DE]">
              <p class="text-sm font-bold text-[#1D2724] mb-3">Detail Pemesan</p>
              
              <div class="space-y-4">
                <div>
                  <label class="block text-xs font-medium text-[#66706C] mb-1">Nama Lengkap</label>
                  <input 
                    type="text" 
                    v-model="form.guest_name" 
                    class="w-full text-sm px-3.5 py-2.5 rounded-lg border-[#E8E6DE] bg-white focus:ring-[#173B35] focus:border-[#173B35]"
                    required
                    placeholder="Sesuai KTP"
                  />
                </div>
                
                <div>
                  <label class="block text-xs font-medium text-[#66706C] mb-1">No. WhatsApp</label>
                  <input 
                    type="tel" 
                    v-model="form.guest_phone" 
                    class="w-full text-sm px-3.5 py-2.5 rounded-lg border-[#E8E6DE] bg-white focus:ring-[#173B35] focus:border-[#173B35]"
                    required
                    placeholder="08123xxx"
                  />
                </div>

                <div>
                  <label class="block text-xs font-medium text-[#66706C] mb-1">Catatan Tambahan (Opsional)</label>
                  <textarea 
                    v-model="form.notes" 
                    rows="2"
                    class="w-full text-sm px-3.5 py-2.5 rounded-lg border-[#E8E6DE] bg-white focus:ring-[#173B35] focus:border-[#173B35]"
                    placeholder="Permintaan khusus..."
                  ></textarea>
                </div>
              </div>
            </div>

            <!-- Total Price Summary -->
            <div class="bg-[#F7F5EF] rounded-lg p-4 mt-4">
              <div class="flex justify-between items-center text-sm mb-2 text-[#4F7465]">
                <span>Rp{{ formatPrice(accommodation.price_per_night) }} x {{ form.rooms }} kamar x {{ totalNights }} malam</span>
              </div>
              <div class="flex justify-between items-center font-black text-[#173B35] text-lg">
                <span>Total Biaya</span>
                <span>Rp{{ formatPrice(totalPrice) }}</span>
              </div>
            </div>

            <button
              type="submit"
              :disabled="isSubmitting || accommodation.available_rooms < 1"
              class="w-full flex justify-center py-3.5 px-4 rounded-[10px] shadow-sm text-sm font-bold text-[#1D2724] bg-[#C9965B] hover:bg-[#b0814a] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#C9965B] disabled:opacity-50 disabled:cursor-not-allowed transition"
            >
              {{ isSubmitting ? 'Memproses...' : 'Buat Reservasi Sekarang' }}
            </button>
            <p v-if="!authStore.isAuthenticated" class="text-center text-xs text-[#66706C] mt-2">
              Anda akan diarahkan ke halaman login terlebih dahulu.
            </p>

          </form>
        </div>
      </div>

    </div>
  </div>
</template>
