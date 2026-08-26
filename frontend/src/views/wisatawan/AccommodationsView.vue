<script setup lang="ts">
import { 
  ChevronRight, 
  LoaderCircle, 
  MapPin, 
  Star, 
  X 
} from 'lucide-vue-next'
import { onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

import { 
  createAccommodationBookingApi, 
  getAccommodationsApi, 
  type Accommodation 
} from '@/services/accommodation.service'
import { useAuthStore } from '@/stores/auth'
import { formatCurrency } from '@/utils/formatters'

const router = useRouter()
const authStore = useAuthStore()

const accommodations = ref<Accommodation[]>([])
const isLoading = ref(true)
const errorMessage = ref('')
const selectedAccommodation = ref<Accommodation | null>(null)
const isSubmitting = ref(false)
const successMessage = ref('')

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
  try {
    isLoading.value = true
    accommodations.value = await getAccommodationsApi()
  } catch (error: unknown) {
    if (axios.isAxiosError(error) && error.response?.data?.message) {
      errorMessage.value = error.response.data.message as string
    } else {
      errorMessage.value = 'Gagal memuat daftar penginapan.'
    }
  } finally {
    isLoading.value = false
  }
})

function openBookingModal(item: Accommodation) {
  selectedAccommodation.value = item
  form.guest_name = authStore.user?.name || ''
  form.guest_phone = authStore.user?.phone || ''
}

function closeModal() {
  selectedAccommodation.value = null
  successMessage.value = ''
}

function calculateNights(): number {
  if (!form.check_in || !form.check_out) return 1
  const start = new Date(form.check_in)
  const end = new Date(form.check_out)
  const diff = Math.ceil((end.getTime() - start.getTime()) / (1000 * 3600 * 24))
  return diff > 0 ? diff : 1
}

function calculateTotal(): number {
  if (!selectedAccommodation.value) return 0
  return selectedAccommodation.value.price_per_night * form.rooms * calculateNights()
}

async function handleBook() {
  if (!selectedAccommodation.value) return
  isSubmitting.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    await createAccommodationBookingApi({
      accommodation_id: selectedAccommodation.value.id,
      check_in: form.check_in,
      check_out: form.check_out,
      rooms: form.rooms,
      guests: form.guests,
      guest_name: form.guest_name,
      guest_phone: form.guest_phone,
      notes: form.notes,
    })

    successMessage.value = 'Reservasi penginapan berhasil dibuat!'
    setTimeout(() => {
      closeModal()
      void router.push({ name: 'wisatawan.history' })
    }, 1500)
  } catch (error: unknown) {
    if (axios.isAxiosError(error) && error.response?.data?.message) {
      errorMessage.value = error.response.data.message as string
    } else {
      errorMessage.value = 'Gagal membuat reservasi.'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-slate-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-slate-900 sm:text-3xl">Daftar Penginapan & Villa</h1>
        <p class="text-sm text-slate-500 mt-1">Temukan tempat menginap terbaik di sekitar Telaga Sarangan</p>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="py-16 text-center text-slate-400 bg-white rounded-2xl border border-slate-200 shadow-sm">
        <LoaderCircle class="mx-auto h-8 w-8 animate-spin text-sky-600" />
        <p class="mt-3 text-sm font-medium">Memuat data penginapan...</p>
      </div>

      <!-- Accommodations Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="item in accommodations" 
          :key="item.id" 
          class="flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md"
        >
          <!-- Card Header / Image Placeholder -->
          <div class="relative h-48 bg-slate-800 p-6 flex flex-col justify-between text-white overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-900/40 to-transparent z-10"></div>
            <div class="relative z-20 flex justify-between items-start">
              <span class="inline-flex items-center gap-1 rounded-full bg-amber-400/90 px-2.5 py-1 text-xs font-bold text-slate-900 backdrop-blur-md">
                <Star class="h-3.5 w-3.5 fill-slate-900" /> {{ item.rating }}
              </span>
              <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold backdrop-blur-md">
                {{ item.available_rooms }} Kamar Tersedia
              </span>
            </div>
            <div class="relative z-20">
              <h3 class="text-xl font-bold">{{ item.name }}</h3>
              <p class="text-xs text-slate-300 flex items-center gap-1 mt-1">
                <MapPin class="h-3.5 w-3.5 shrink-0" /> {{ item.address }}
              </p>
            </div>
          </div>

          <!-- Card Body -->
          <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
            <p class="text-xs text-slate-600 line-clamp-3 leading-relaxed">
              {{ item.description }}
            </p>

            <!-- Facilities Tags -->
            <div v-if="item.facilities?.length" class="flex flex-wrap gap-1.5 pt-2">
              <span 
                v-for="fac in item.facilities" 
                :key="fac" 
                class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600"
              >
                {{ fac }}
              </span>
            </div>

            <!-- Price & CTA -->
            <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
              <div>
                <span class="text-xs text-slate-400 block">Mulai dari</span>
                <span class="text-lg font-extrabold text-sky-600">
                  {{ formatCurrency(item.price_per_night) }}
                  <span class="text-xs font-normal text-slate-500">/malam</span>
                </span>
              </div>

              <button
                type="button"
                class="inline-flex items-center gap-1.5 rounded-xl bg-sky-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-sky-700"
                @click="openBookingModal(item)"
              >
                Reservasi <ChevronRight class="h-4 w-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Booking Modal -->
    <div
      v-if="selectedAccommodation"
      class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm transition-opacity"
    >
      <div class="w-full max-w-lg rounded-2xl bg-white p-6 shadow-xl sm:p-8 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100">
          <div>
            <span class="text-xs text-slate-400 font-semibold uppercase">Reservasi Penginapan</span>
            <h3 class="text-lg font-bold text-slate-900">{{ selectedAccommodation.name }}</h3>
          </div>
          <button
            type="button"
            class="rounded-lg p-1.5 text-slate-400 hover:bg-slate-100 hover:text-slate-700"
            @click="closeModal"
          >
            <X class="h-5 w-5" />
          </button>
        </div>

        <div v-if="successMessage" class="mt-4 rounded-xl bg-emerald-50 p-4 text-emerald-700 text-sm font-semibold border border-emerald-200">
          ✓ {{ successMessage }}
        </div>

        <form v-else class="mt-4 space-y-4" @submit.prevent="handleBook">
          <div v-if="errorMessage" class="rounded-xl bg-red-50 p-3 text-xs text-red-600 border border-red-100">
            {{ errorMessage }}
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Check-in</label>
              <input
                v-model="form.check_in"
                type="date"
                :min="today"
                required
                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 outline-none focus:border-sky-500"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Check-out</label>
              <input
                v-model="form.check_out"
                type="date"
                :min="form.check_in"
                required
                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 outline-none focus:border-sky-500"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Kamar</label>
              <input
                v-model.number="form.rooms"
                type="number"
                min="1"
                :max="selectedAccommodation.available_rooms"
                required
                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 outline-none focus:border-sky-500"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Jumlah Tamu</label>
              <input
                v-model.number="form.guests"
                type="number"
                min="1"
                required
                class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 outline-none focus:border-sky-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Pemesan</label>
            <input
              v-model="form.guest_name"
              type="text"
              required
              class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 outline-none focus:border-sky-500"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">No. WhatsApp</label>
            <input
              v-model="form.guest_phone"
              type="tel"
              required
              class="w-full rounded-xl border border-slate-300 px-3 py-2 text-xs text-slate-900 outline-none focus:border-sky-500"
            />
          </div>

          <!-- Total Summary -->
          <div class="rounded-xl bg-slate-50 p-4 border border-slate-200/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div>
              <span class="text-xs text-slate-500 block">Total Estimasi ({{ calculateNights() }} malam)</span>
              <span class="text-base font-extrabold text-sky-600">{{ formatCurrency(calculateTotal()) }}</span>
            </div>
            <button
              type="submit"
              class="inline-flex items-center gap-2 rounded-xl bg-sky-600 px-5 py-2.5 text-xs font-semibold text-white shadow-sm transition hover:bg-sky-700 disabled:opacity-70"
              :disabled="isSubmitting"
            >
              <LoaderCircle v-if="isSubmitting" class="h-4 w-4 animate-spin" />
              Konfirmasi Reservasi
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>
