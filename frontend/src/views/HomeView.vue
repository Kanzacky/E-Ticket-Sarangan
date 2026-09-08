<script setup lang="ts">
import { MapPin, Clock, Check, ChevronRight, Ticket, Calendar, Star, Building } from 'lucide-vue-next'
import PublicNavbar from '@/components/layout/PublicNavbar.vue'
import PublicFooter from '@/components/layout/PublicFooter.vue'
import { onMounted, ref, computed } from 'vue'
import { useRouter } from 'vue-router'

import { useApiHealth } from '@/composables/useApiHealth'
import { getTicketTypesApi } from '@/services/order.service'
import { getAccommodationsApi, type Accommodation } from '@/services/accommodation.service'
import type { TicketType } from '@/types/booking.types'
import { useAuthStore } from '@/stores/auth'

const { check } = useApiHealth()
const router = useRouter()
const authStore = useAuthStore()

const ticketTypes = ref<TicketType[]>([])
const isLoadingTickets = ref(true)
const accommodations = ref<Accommodation[]>([])
const isLoadingAccommodations = ref(true)

onMounted(async () => {
  void check()
  
  // Fetch tickets
  getTicketTypesApi()
    .then(res => {
      ticketTypes.value = res.filter(t => t.status === 'ACTIVE')
    })
    .catch(error => console.error('Failed to fetch ticket types', error))
    .finally(() => { isLoadingTickets.value = false })

  // Fetch accommodations
  getAccommodationsApi({ per_page: 3 })
    .then(res => {
      accommodations.value = res.data.slice(0, 3)
    })
    .catch(error => console.error('Failed to fetch accommodations', error))
    .finally(() => { isLoadingAccommodations.value = false })
})

const regularTickets = computed(() => ticketTypes.value.filter(t => !t.name.toLowerCase().includes('paket')))
const packageTickets = computed(() => ticketTypes.value.filter(t => t.name.toLowerCase().includes('paket')))

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

function handlePesanTiket() {
  if (authStore.isAuthenticated) {
    if (authStore.user?.role === 'admin') {
      void router.push('/admin/dashboard')
    } else if (authStore.user?.role === 'petugas') {
      void router.push('/petugas/dashboard')
    } else {
      void router.push('/booking')
    }
  } else {
    void router.push({ name: 'login', query: { redirect: '/booking' } })
  }
}

function handlePesanAkomodasi() {
  if (authStore.isAuthenticated) {
    void router.push('/accommodations')
  } else {
    void router.push({ name: 'login', query: { redirect: '/accommodations' } })
  }
}

const steps = [
  { num: '01', title: 'Pilih Tiket', desc: 'Tentukan jenis tiket dan jumlah pengunjung sesuai kebutuhan.' },
  { num: '02', title: 'Isi Data', desc: 'Isi data pemesan dan pilih tanggal kunjungan yang Anda inginkan.' },
  { num: '03', title: 'Bayar', desc: 'Selesaikan pembayaran secara aman lewat gateway Xendit.' },
  { num: '04', title: 'Tunjukkan QR', desc: 'Scan QR e-ticket di gerbang masuk, dan nikmati Sarangan.' },
]

const faqs = [
  {
    q: 'Apakah tiket bisa dibatalkan?',
    a: 'Tiket yang sudah dibayar tidak dapat dibatalkan. Pastikan tanggal kunjungan sesuai sebelum melakukan pembayaran.',
  },
  {
    q: 'Berapa lama QR code berlaku?',
    a: 'QR code berlaku pada tanggal kunjungan yang tercantum pada tiket Anda.',
  },
  {
    q: 'Apakah bisa pesan untuk orang lain?',
    a: 'Bisa. Isi nama pemesan atas nama siapa pun, cukup pastikan membawa e-ticket saat kunjungan.',
  },
  {
    q: 'Bagaimana jika pembayaran gagal?',
    a: 'Pesanan dengan status "Menunggu Pembayaran" akan otomatis kadaluarsa dalam 24 jam. Anda dapat membuat pesanan baru.',
  },
]
</script>

<template>
  <main class="min-h-screen bg-[#F7F5EF] font-sans text-[#1D2724] selection:bg-[#4F7465] selection:text-white flex flex-col">
    <!-- Navbar -->
    <PublicNavbar :transparent-top="true" />

    <!-- ============================================================ -->
    <!-- HERO — Fokus, tidak terlalu tinggi                           -->
    <!-- ============================================================ -->
    <section id="beranda" class="relative flex items-end min-h-[100svh] md:min-h-[calc(100vh-84px)] pt-20">
      <!-- Background Image -->
      <div class="absolute inset-0 z-0">
        <img
          src="/images/sarangan-hero-2.jpg"
          alt="Telaga Sarangan"
          class="h-full w-full object-cover object-center"
        />
        <!-- Overlay gelap merata untuk keterbacaan teks -->
        <div class="absolute inset-0 bg-[#0d1e1b]/65"></div>
        <!-- Gradien bawah untuk depth -->
        <div class="absolute inset-0 bg-gradient-to-t from-[#0a1713] via-[#0d1e1b]/40 to-transparent"></div>
        <!-- Gradien kiri untuk text area (hanya sisi kiri) -->
        <div class="absolute inset-0 bg-gradient-to-r from-[#0d1e1b]/70 via-[#0d1e1b]/20 to-transparent"></div>
      </div>

      <div class="relative z-10 w-full mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8 pb-16 md:pb-20">
        <div class="max-w-2xl">
          <!-- Label -->
          <p
            class="text-sm font-bold uppercase tracking-widest mb-4"
            style="color: #E8B97A !important;"
          >
            Wisata Telaga · Magetan · Jawa Timur
          </p>

          <h1
            class="text-[2.5rem] md:text-5xl font-black leading-[1.1] mb-5 tracking-tight"
            style="color: #ffffff !important;"
          >
            Jelajahi Keindahan<br />Sarangan.
          </h1>

          <p
            class="text-base md:text-lg mb-8 max-w-lg leading-relaxed font-medium"
            style="color: rgba(255,255,255,0.90) !important;"
          >
            Pesan tiket wisata Telaga Sarangan secara online. Pilih tanggal, bayar, dan tunjukkan e-ticket di gerbang — tanpa perlu antre.
          </p>

          <div class="flex flex-col sm:flex-row gap-3">
            <button
              @click="handlePesanTiket"
              class="inline-flex items-center justify-center gap-2 rounded-[10px] bg-[#C9965B] px-7 py-3.5 text-base font-bold text-[#1D2724] transition hover:bg-[#b0814a] active:scale-[0.98]"
            >
              {{ ['admin', 'petugas'].includes(authStore.user?.role || '') ? 'Dashboard' : 'Pesan Tiket' }}
              <ChevronRight class="w-5 h-5" />
            </button>
            <a
              href="#tiket"
              class="inline-flex items-center justify-center rounded-[10px] border border-white/30 px-7 py-3.5 text-base font-bold text-white transition hover:bg-white/10"
            >
              Lihat Tiket
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- INFO STRIP — Jam buka, lokasi, dll                           -->
    <!-- ============================================================ -->
    <section class="bg-[#173B35] py-5 border-t-4 border-[#C9965B]">
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-white text-sm">
          <div class="flex items-center gap-3">
            <Clock class="w-5 h-5 text-[#C9965B] shrink-0" />
            <div>
              <p class="font-bold">Jam Buka</p>
              <p class="text-white/70 text-xs">06.00 – 18.00 WIB</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <MapPin class="w-5 h-5 text-[#C9965B] shrink-0" />
            <div>
              <p class="font-bold">Lokasi</p>
              <p class="text-white/70 text-xs">Magetan, Jawa Timur</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <Ticket class="w-5 h-5 text-[#C9965B] shrink-0" />
            <div>
              <p class="font-bold">Pemesanan</p>
              <p class="text-white/70 text-xs">100% Online</p>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <Calendar class="w-5 h-5 text-[#C9965B] shrink-0" />
            <div>
              <p class="font-bold">Tiket Berlaku</p>
              <p class="text-white/70 text-xs">Sesuai Tanggal Pilihan</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- TICKET CATALOG — Bagian paling penting                       -->
    <!-- ============================================================ -->
    <section id="tiket" class="py-20 bg-white">
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="mb-12">
          <p class="text-[#C9965B] text-xs font-bold uppercase tracking-widest mb-2">Katalog Tiket</p>
          <h2 class="text-3xl md:text-4xl font-black text-[#173B35] mb-3">Pilih Tiket Wisata</h2>
          <p class="text-[#66706C] max-w-lg text-base">
            Temukan tiket yang sesuai dengan rencana perjalananmu. Data harga resmi dari sistem.
          </p>
        </div>

        <!-- Loading -->
        <div v-if="isLoadingTickets" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 3" :key="i" class="h-48 rounded-xl bg-[#F7F5EF] animate-pulse"></div>
        </div>

        <!-- Tickets -->
        <div v-else-if="ticketTypes.length" class="space-y-12">
          
          <!-- Regular Tickets -->
          <div v-if="regularTickets.length">
            <h3 class="text-2xl font-bold text-[#1D2724] mb-6">Tiket Masuk</h3>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="ticket in regularTickets"
                :key="ticket.id"
                class="group flex flex-col border border-[#E8E6DE] rounded-xl bg-white overflow-hidden transition-all hover:border-[#4F7465] hover:shadow-md"
              >
                <!-- Card Top -->
                <div class="p-6 flex-1">
                  <div class="flex items-start justify-between mb-3">
                    <h3 class="text-lg font-bold text-[#1D2724]">{{ ticket.name }}</h3>
                    <span class="text-xs font-bold text-[#4F7465] bg-[#4F7465]/10 px-2 py-0.5 rounded-full shrink-0 ml-2">
                      Tersedia
                    </span>
                  </div>
                  <p class="text-sm text-[#66706C] mb-5 leading-relaxed min-h-[40px]">
                    {{ ticket.description || 'Tiket masuk area wisata Telaga Sarangan.' }}
                  </p>

                  <!-- Inclusions -->
                  <ul class="space-y-1.5 text-xs text-[#66706C] mb-6">
                    <li class="flex items-center gap-2">
                      <Check class="w-3.5 h-3.5 text-[#4F7465] shrink-0" />
                      Akses area wisata Telaga Sarangan
                    </li>
                    <li class="flex items-center gap-2">
                      <Check class="w-3.5 h-3.5 text-[#4F7465] shrink-0" />
                      Berlaku pada tanggal yang dipilih
                    </li>
                    <li class="flex items-center gap-2">
                      <Check class="w-3.5 h-3.5 text-[#4F7465] shrink-0" />
                      E-ticket langsung ke perangkat
                    </li>
                  </ul>
                </div>

                <!-- Card Bottom -->
                <div class="border-t border-[#E8E6DE] px-6 py-4 flex items-center justify-between">
                  <div>
                    <p class="text-xs text-[#66706C] font-medium">Harga per orang</p>
                    <p class="text-xl font-black text-[#173B35]">Rp{{ formatPrice(ticket.price) }}</p>
                  </div>
                  <button
                    @click="handlePesanTiket"
                    class="shrink-0 inline-flex items-center gap-1.5 rounded-[8px] bg-[#173B35] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#122c27] group-hover:translate-x-0.5 active:scale-95"
                  >
                    Pesan
                    <ChevronRight class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Package Tickets -->
          <div v-if="packageTickets.length">
            <h3 class="text-2xl font-bold text-[#1D2724] mb-6">Paket Wisata</h3>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                v-for="ticket in packageTickets"
                :key="ticket.id"
                class="group flex flex-col border border-[#E8E6DE] rounded-xl bg-white overflow-hidden transition-all hover:border-[#4F7465] hover:shadow-md"
              >
                <!-- Card Top -->
                <div class="p-6 flex-1">
                  <div class="flex items-start justify-between mb-3">
                    <h3 class="text-lg font-bold text-[#1D2724]">{{ ticket.name }}</h3>
                    <span class="text-xs font-bold text-[#4F7465] bg-[#4F7465]/10 px-2 py-0.5 rounded-full shrink-0 ml-2">
                      Tersedia
                    </span>
                  </div>
                  <p class="text-sm text-[#66706C] mb-5 leading-relaxed min-h-[40px]">
                    {{ ticket.description || 'Paket wisata menarik di Telaga Sarangan.' }}
                  </p>

                  <!-- Inclusions -->
                  <ul class="space-y-1.5 text-xs text-[#66706C] mb-6">
                    <li class="flex items-center gap-2">
                      <Check class="w-3.5 h-3.5 text-[#4F7465] shrink-0" />
                      Akses area wisata Telaga Sarangan
                    </li>
                    <li class="flex items-center gap-2">
                      <Check class="w-3.5 h-3.5 text-[#4F7465] shrink-0" />
                      Berlaku pada tanggal yang dipilih
                    </li>
                    <li class="flex items-center gap-2">
                      <Check class="w-3.5 h-3.5 text-[#4F7465] shrink-0" />
                      E-ticket langsung ke perangkat
                    </li>
                  </ul>
                </div>

                <!-- Card Bottom -->
                <div class="border-t border-[#E8E6DE] px-6 py-4 flex items-center justify-between">
                  <div>
                    <p class="text-xs text-[#66706C] font-medium">Harga paket</p>
                    <p class="text-xl font-black text-[#173B35]">Rp{{ formatPrice(ticket.price) }}</p>
                  </div>
                  <button
                    @click="handlePesanTiket"
                    class="shrink-0 inline-flex items-center gap-1.5 rounded-[8px] bg-[#173B35] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#122c27] group-hover:translate-x-0.5 active:scale-95"
                  >
                    Pesan
                    <ChevronRight class="w-4 h-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty -->
        <div v-else class="text-center py-16 text-[#66706C]">
          <Ticket class="w-12 h-12 mx-auto mb-3 opacity-30" />
          <p class="font-medium">Belum ada tiket yang tersedia saat ini.</p>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- ACCOMMODATIONS — Penginapan & Villa                          -->
    <!-- ============================================================ -->
    <section id="akomodasi" class="py-20 bg-[#F7F5EF]">
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
        <!-- Section Header -->
        <div class="flex flex-col md:flex-row md:items-end justify-between mb-12 gap-4">
          <div>
            <p class="text-[#C9965B] text-xs font-bold uppercase tracking-widest mb-2">Akomodasi Penginapan</p>
            <h2 class="text-3xl md:text-4xl font-black text-[#173B35] mb-3">Tempat Menginap Terbaik</h2>
            <p class="text-[#66706C] max-w-lg text-base">
              Berbagai pilihan penginapan dan villa di sekitar Telaga Sarangan untuk melengkapi liburan Anda.
            </p>
          </div>
          <button
            @click="handlePesanAkomodasi"
            class="shrink-0 inline-flex items-center gap-1.5 text-sm font-bold text-[#173B35] hover:text-[#4F7465] transition group"
          >
            Lihat Semua Penginapan
            <ChevronRight class="w-4 h-4 transition-transform group-hover:translate-x-1" />
          </button>
        </div>

        <!-- Loading -->
        <div v-if="isLoadingAccommodations" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 3" :key="i" class="h-[360px] rounded-xl bg-white animate-pulse border border-[#E8E6DE]"></div>
        </div>

        <!-- Accommodations -->
        <div v-else-if="accommodations.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="item in accommodations"
            :key="item.id"
            class="group flex flex-col border border-[#E8E6DE] rounded-xl bg-white overflow-hidden transition-all hover:border-[#4F7465] hover:shadow-md"
          >
            <!-- Card Header / Image Placeholder -->
            <div class="relative h-48 bg-[#173B35] p-6 flex flex-col justify-between text-white overflow-hidden">
              <div class="absolute inset-0 bg-gradient-to-t from-[#1D2724] via-[#1D2724]/40 to-transparent z-10"></div>
              <div class="relative z-20 flex justify-between items-start">
                <span class="inline-flex items-center gap-1 rounded-full bg-[#C9965B] px-2.5 py-1 text-xs font-bold text-[#1D2724] backdrop-blur-md">
                  <Star class="h-3.5 w-3.5 fill-[#1D2724]" /> {{ item.rating }}
                </span>
                <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold backdrop-blur-md border border-white/30">
                  {{ item.available_rooms }} Kamar
                </span>
              </div>
              <div class="relative z-20">
                <h3 class="text-xl font-bold text-white mb-1">{{ item.name }}</h3>
                <p class="text-xs text-white/80 flex items-center gap-1">
                  <MapPin class="h-3.5 w-3.5 shrink-0" /> {{ item.address }}
                </p>
              </div>
            </div>

            <!-- Card Body -->
            <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
              <p class="text-sm text-[#66706C] line-clamp-2 leading-relaxed">
                {{ item.description }}
              </p>

              <!-- Facilities Tags -->
              <div v-if="item.facilities?.length" class="flex flex-wrap gap-1.5 pt-2">
                <span 
                  v-for="fac in item.facilities.slice(0, 3)" 
                  :key="fac" 
                  class="rounded-md bg-[#F7F5EF] px-2 py-1 text-[11px] font-medium text-[#4F7465]"
                >
                  {{ fac }}
                </span>
                <span v-if="item.facilities.length > 3" class="rounded-md bg-[#F7F5EF] px-2 py-1 text-[11px] font-medium text-[#4F7465]">
                  +{{ item.facilities.length - 3 }} lagi
                </span>
              </div>

              <!-- Price & CTA -->
              <div class="pt-5 border-t border-[#E8E6DE] flex items-center justify-between mt-auto">
                <div>
                  <span class="text-xs text-[#66706C] block mb-0.5">Mulai dari</span>
                  <span class="text-lg font-black text-[#173B35]">
                    Rp{{ formatPrice(item.price_per_night) }}
                    <span class="text-xs font-normal text-[#66706C]">/malam</span>
                  </span>
                </div>

                <button
                  type="button"
                  class="shrink-0 inline-flex items-center gap-1.5 rounded-[8px] bg-white border-2 border-[#173B35] px-4 py-2 text-sm font-bold text-[#173B35] transition hover:bg-[#F7F5EF] active:scale-95"
                  @click="handlePesanAkomodasi"
                >
                  Lihat
                </button>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Empty -->
        <div v-else class="text-center py-16 text-[#66706C]">
          <Building class="w-12 h-12 mx-auto mb-3 opacity-30 text-[#4F7465]" />
          <p class="font-medium text-[#173B35]">Belum ada penginapan yang tersedia saat ini.</p>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- ABOUT SARANGAN — Informasi wisata                            -->
    <!-- ============================================================ -->
    <section id="tentang" class="py-20">
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-10 md:gap-16 items-center">
          <div class="relative h-[300px] md:h-[420px] rounded-xl overflow-hidden">
            <img
              src="/images/sarangan-story-2.jpg"
              alt="Danau Sarangan"
              class="w-full h-full object-cover transition-transform duration-700 hover:scale-105"
            />
          </div>
          <div class="order-first lg:order-none">
            <p class="text-[#C9965B] text-xs font-bold uppercase tracking-widest mb-3">Tentang Sarangan</p>
            <h2 class="text-3xl md:text-4xl font-black text-[#173B35] mb-6 leading-tight">
              Alam yang tenang,<br />udara yang segar.
            </h2>
            <div class="space-y-4 text-[#66706C] text-base leading-relaxed">
              <p>
                Telaga Sarangan terletak di lereng Gunung Lawu, Kabupaten Magetan, Jawa Timur. Berada di ketinggian 1.287 meter di atas permukaan laut, destinasi ini menawarkan udara sejuk dan pemandangan alam yang menenangkan.
              </p>
              <p>
                Nikmati perahu tradisional di atas telaga, kuliner khas pegunungan, dan hamparan hijau yang asri. Sebuah perjalanan yang sederhana namun berkesan.
              </p>
            </div>

            <!-- Quick Info -->
            <div class="mt-8 grid grid-cols-2 gap-4">
              <div class="border border-[#173B35]/10 rounded-xl p-4 bg-white">
                <p class="text-xs text-[#66706C] font-medium mb-1">Ketinggian</p>
                <p class="font-bold text-[#1D2724]">1.287 mdpl</p>
              </div>
              <div class="border border-[#173B35]/10 rounded-xl p-4 bg-white">
                <p class="text-xs text-[#66706C] font-medium mb-1">Luas Telaga</p>
                <p class="font-bold text-[#1D2724]">±30 Hektar</p>
              </div>
              <div class="border border-[#173B35]/10 rounded-xl p-4 bg-white">
                <p class="text-xs text-[#66706C] font-medium mb-1">Jam Buka</p>
                <p class="font-bold text-[#1D2724]">06.00 – 18.00</p>
              </div>
              <div class="border border-[#173B35]/10 rounded-xl p-4 bg-white">
                <p class="text-xs text-[#66706C] font-medium mb-1">Lokasi</p>
                <p class="font-bold text-[#1D2724]">Magetan, Jatim</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- CARA BOOKING — 4 langkah sederhana                          -->
    <!-- ============================================================ -->
    <section id="cara-pesan" class="py-20 bg-white border-t border-[#173B35]/10">
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
        <div class="mb-12">
          <p class="text-[#C9965B] text-xs font-bold uppercase tracking-widest mb-2">Cara Pemesanan</p>
          <h2 class="text-3xl md:text-4xl font-black text-[#173B35]">Mudah dalam 4 langkah.</h2>
        </div>

        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8">
          <div v-for="step in steps" :key="step.num" class="flex flex-col">
            <span class="text-[#C9965B] font-black text-4xl mb-4 font-mono">{{ step.num }}</span>
            <h3 class="font-bold text-[#1D2724] text-lg mb-2">{{ step.title }}</h3>
            <p class="text-[#66706C] text-sm leading-relaxed">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- MAPS LOCATION — Pertahankan dari versi lama                  -->
    <!-- ============================================================ -->
    <section class="py-20">
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row gap-10 md:gap-16 items-start">
          <div class="w-full md:w-1/3 shrink-0">
            <p class="text-[#C9965B] text-xs font-bold uppercase tracking-widest mb-3">Lokasi</p>
            <h2 class="text-3xl font-black text-[#173B35] mb-4">Temukan Sarangan</h2>
            <p class="text-[#66706C] mb-6 leading-relaxed text-sm">
              Akses dari pusat Magetan ±30 menit. Jalan mulus, cocok ditempuh dengan kendaraan pribadi maupun umum.
            </p>
            <div class="bg-white border border-[#173B35]/10 p-5 rounded-xl">
              <p class="text-xs text-[#66706C] font-medium mb-1">Alamat</p>
              <p class="text-sm text-[#1D2724] font-medium leading-relaxed">
                Jl. Raya Telaga Sarangan,<br />
                Kec. Plaosan, Kabupaten Magetan,<br />
                Jawa Timur 63361
              </p>
            </div>
          </div>
          <div class="w-full md:flex-1 h-[360px] md:h-[440px] rounded-xl overflow-hidden border border-[#173B35]/10">
            <iframe
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15814.708891583348!2d111.2064972!3d-7.6653878!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e798e3b48455555%3A0x6b29eb9e4f526367!2sTelaga%20Sarangan!5e0!3m2!1sen!2sid!4v1700000000000!5m2!1sen!2sid"
              width="100%"
              height="100%"
              style="border:0;"
              allowfullscreen="false"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"
            ></iframe>
          </div>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- FAQ — Pertanyaan yang sering ditanya                         -->
    <!-- ============================================================ -->
    <section class="py-20 bg-white border-t border-[#173B35]/10">
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-3 gap-12">
          <div>
            <p class="text-[#C9965B] text-xs font-bold uppercase tracking-widest mb-3">FAQ</p>
            <h2 class="text-3xl font-black text-[#173B35] mb-4 leading-tight">Pertanyaan yang sering ditanyakan.</h2>
            <p class="text-[#66706C] text-sm">Tidak menemukan jawaban? Hubungi kami di media sosial resmi Dinas Pariwisata Magetan.</p>
          </div>
          <div class="lg:col-span-2 space-y-0 divide-y divide-[#173B35]/10">
            <div v-for="faq in faqs" :key="faq.q" class="py-5">
              <h3 class="font-bold text-[#1D2724] mb-2">{{ faq.q }}</h3>
              <p class="text-[#66706C] text-sm leading-relaxed">{{ faq.a }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ============================================================ -->
    <!-- VISUAL STORY — Foto area wisata                              -->
    <!-- ============================================================ -->
    <section class="bg-[#173B35]">
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8 py-20">
        <div class="grid lg:grid-cols-2 gap-12 items-center">

          <!-- Text Side -->
          <div>
            <p
              class="text-xs font-bold uppercase tracking-widest mb-3"
              style="color: #C9965B !important;"
            >
              Eksplorasi
            </p>
            <h2
              class="text-3xl md:text-4xl font-black mb-5 leading-tight"
              style="color: #ffffff !important; letter-spacing: -0.02em;"
            >
              Keindahan yang<br />menanti untuk dijelajahi.
            </h2>
            <p
              class="text-base mb-8 max-w-md leading-relaxed"
              style="color: rgba(255,255,255,0.75) !important;"
            >
              Jalan setapak berkabut, aroma pinus, dan udara pagi yang segar. Sarangan bukan sekadar tempat — melainkan ruang untuk kembali terhubung dengan alam.
            </p>
            <button
              @click="handlePesanTiket"
              class="inline-flex items-center gap-2 rounded-[10px] bg-[#C9965B] px-7 py-3.5 text-base font-bold text-[#1D2724] transition hover:bg-[#b0814a] active:scale-[0.98]"
            >
              Pesan Tiket Sekarang
              <ChevronRight class="w-5 h-5" />
            </button>
          </div>

          <!-- Image Side -->
          <div class="h-[380px] lg:h-[440px] rounded-xl overflow-hidden">
            <img
              src="/images/sarangan-trail-2.png"
              alt="Telaga Sarangan dari tepi"
              class="w-full h-full object-cover transition-transform duration-700 hover:scale-105"
            />
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <PublicFooter />
  </main>
</template>
