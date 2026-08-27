<script setup lang="ts">
import { 
  LoaderCircle, 
  MapPin,
  Menu,
  X
} from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { onMounted, onUnmounted, ref } from 'vue'

import { useApiHealth } from '@/composables/useApiHealth'
import { useAuthStore } from '@/stores/auth'
import { getTicketTypesApi } from '@/services/order.service'
import type { TicketType } from '@/types/booking.types'

const { t } = useI18n()
const authStore = useAuthStore()
const { check } = useApiHealth()

const isMobileMenuOpen = ref(false)
const ticketTypes = ref<TicketType[]>([])
const isLoadingTickets = ref(true)

const isScrolled = ref(false)

const handleScroll = () => {
  isScrolled.value = window.scrollY > 50
}

onMounted(async () => {
  window.addEventListener('scroll', handleScroll)
  handleScroll() // Initialize state
  
  void check()
  try {
    const res = await getTicketTypesApi()
    ticketTypes.value = res.filter(t => t.status === 'ACTIVE')
  } catch (error) {
    console.error('Failed to fetch ticket types', error)
  } finally {
    isLoadingTickets.value = false
  }
})

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll)
})

const features = [
  {
    title: 'Tiket Digital',
    description: 'Pesan dari mana saja tanpa antrean panjang.',
  },
  {
    title: 'Booking Online',
    description: 'Pilih tanggal kunjungan dan amankan tiketmu.',
  },
  {
    title: 'QR Check-in',
    description: 'Scan cepat di gerbang, langsung masuk.',
  },
  {
    title: 'Pembayaran Aman',
    description: 'Beragam pilihan metode pembayaran digital.',
  }
]

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID').format(price)
}
</script>

<template>
  <main class="min-h-screen bg-[#F7F5EF] font-sans text-[#1D2724] selection:bg-[#4F7465] selection:text-white">
    <!-- Navbar -->
    <header 
      class="fixed top-0 w-full z-50 border-b transition-all duration-300"
      :class="isScrolled ? 'bg-[#F7F5EF]/95 backdrop-blur-md border-[#173B35]/10 shadow-sm py-0' : 'bg-transparent border-transparent py-2'"
    >
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-20">
          <div class="flex items-center gap-3">
            <div class="flex items-center justify-center">
              <img src="/images/logo.png" alt="Logo" class="h-10 w-auto" />
            </div>
            <span class="text-xl font-bold tracking-tight transition-colors" :class="isScrolled ? 'text-[#173B35]' : 'text-white'">{{ t('app.name') }}</span>
          </div>

          <!-- Desktop Nav -->
          <nav class="hidden lg:flex items-center gap-8">
            <a href="#beranda" class="text-sm font-semibold transition-colors" :class="isScrolled ? 'text-[#1D2724] hover:text-[#4F7465]' : 'text-white/90 hover:text-white'">Beranda</a>
            <a href="#tiket" class="text-sm font-semibold transition-colors" :class="isScrolled ? 'text-[#1D2724] hover:text-[#4F7465]' : 'text-white/90 hover:text-white'">Tiket</a>
            <a href="#cara-pesan" class="text-sm font-semibold transition-colors" :class="isScrolled ? 'text-[#1D2724] hover:text-[#4F7465]' : 'text-white/90 hover:text-white'">Cara Pesan</a>
            <a href="#tentang" class="text-sm font-semibold transition-colors" :class="isScrolled ? 'text-[#1D2724] hover:text-[#4F7465]' : 'text-white/90 hover:text-white'">Tentang Sarangan</a>
          </nav>

          <div class="hidden lg:flex items-center gap-4">
            <template v-if="authStore.isAuthenticated">
              <router-link
                to="/my-tickets"
                class="text-sm font-semibold transition-colors"
                :class="isScrolled ? 'text-[#1D2724] hover:text-[#4F7465]' : 'text-white/90 hover:text-white'"
              >
                Pesanan Saya
              </router-link>
            </template>
            <template v-else>
              <router-link
                to="/login?redirect=/booking"
                class="text-sm font-semibold transition-colors"
                :class="isScrolled ? 'text-[#1D2724] hover:text-[#4F7465]' : 'text-white/90 hover:text-white'"
              >
                Masuk
              </router-link>
            </template>
            
            <router-link
              to="/booking"
              class="flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold transition-all"
              :class="isScrolled ? 'bg-[#173B35] text-white hover:bg-[#1D2724]' : 'bg-white text-[#173B35] hover:bg-white/90'"
            >
              Pesan Tiket
            </router-link>
          </div>

          <!-- Mobile menu button -->
          <button @click="isMobileMenuOpen = !isMobileMenuOpen" class="lg:hidden p-2 transition-colors" :class="isScrolled ? 'text-[#173B35]' : 'text-white'">
            <Menu v-if="!isMobileMenuOpen" class="h-6 w-6" />
            <X v-else class="h-6 w-6" />
          </button>
        </div>
      </div>

      <!-- Mobile Nav -->
      <div v-if="isMobileMenuOpen" class="lg:hidden bg-[#F7F5EF] border-t border-[#173B35]/10 px-5 pt-4 pb-6 space-y-4 shadow-lg absolute w-full">
        <a href="#beranda" @click="isMobileMenuOpen = false" class="block text-base font-semibold text-[#1D2724]">Beranda</a>
        <a href="#tiket" @click="isMobileMenuOpen = false" class="block text-base font-semibold text-[#1D2724]">Tiket</a>
        <a href="#cara-pesan" @click="isMobileMenuOpen = false" class="block text-base font-semibold text-[#1D2724]">Cara Pesan</a>
        <a href="#tentang" @click="isMobileMenuOpen = false" class="block text-base font-semibold text-[#1D2724]">Tentang Sarangan</a>
        <hr class="border-[#173B35]/10" />
        <template v-if="authStore.isAuthenticated">
          <router-link to="/my-tickets" class="block text-base font-semibold text-[#1D2724]">Pesanan Saya</router-link>
        </template>
        <template v-else>
          <router-link to="/login?redirect=/booking" class="block text-base font-semibold text-[#1D2724]">Masuk</router-link>
        </template>
        <router-link to="/booking" class="block w-full text-center rounded-lg bg-[#173B35] px-5 py-3 text-base font-semibold text-white mt-4">Pesan Tiket</router-link>
      </div>
    </header>

    <!-- Hero Section -->
    <section id="beranda" class="relative pt-20 lg:pt-0 lg:h-screen lg:min-h-[700px] flex items-center">
      <div class="absolute inset-0 z-0">
        <img 
          src="/images/sarangan-hero-2.jpg" 
          alt="Telaga Sarangan" 
          class="h-full w-full object-cover"
        />
        <div class="absolute inset-0 bg-[#1D2724]/60 mix-blend-multiply"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-[#1D2724]/80 via-transparent to-transparent"></div>
      </div>

      <div class="relative z-10 mx-auto w-full max-w-[1240px] px-5 sm:px-6 lg:px-8 py-20 mt-10 md:mt-0 lg:py-0">
        <div class="max-w-2xl text-white">
          <div class="inline-block border border-white/20 bg-black/20 backdrop-blur-md rounded-full px-4 py-1.5 text-xs font-bold uppercase tracking-widest mb-6">
            Wisata Alam Magetan
          </div>
          
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight mb-6 text-white drop-shadow-sm">
            Nikmati Sarangan,<br />mulai perjalananmu dari sini.
          </h1>
          
          <p class="text-lg md:text-xl mb-10 text-white/90 font-medium max-w-xl leading-relaxed">
            Pesan tiket kunjungan dengan mudah, tentukan tanggal kedatangan, dan simpan e-ticket langsung di perangkatmu tanpa perlu antre panjang.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4">
            <router-link
              to="/booking"
              class="flex items-center justify-center rounded-[10px] bg-[var(--color-primary)] px-8 py-4 text-base font-bold text-white transition-colors hover:bg-[#122c27]"
            >
              Pesan Tiket
            </router-link>
            <a
              href="#tentang"
              class="flex items-center justify-center rounded-[10px] bg-white/10 backdrop-blur-md border border-white/30 px-8 py-4 text-base font-bold text-white transition-colors hover:bg-white/20"
            >
              Lihat Informasi
            </a>
          </div>
        </div>
      </div>
    </section>

    <!-- Information Strip -->
    <section class="bg-[#173B35] py-8 border-t-4 border-[#C9965B]">
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 text-white text-center">
          <div v-for="feat in features" :key="feat.title" class="flex flex-col items-center">
            <span class="text-lg font-bold mb-1">{{ feat.title }}</span>
            <span class="text-sm text-white/70">{{ feat.description }}</span>
          </div>
        </div>
      </div>
    </section>

    <!-- Storytelling Section -->
    <section id="tentang" class="py-24">
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-10 md:gap-16 items-center">
          <div class="relative overflow-hidden rounded-2xl h-[300px] md:h-[400px] lg:h-[600px] shadow-xl">
            <img src="/images/sarangan-story-2.jpg" alt="Danau Sarangan" class="w-full h-full object-cover transition-transform duration-700 hover:scale-105" />
          </div>
          <div class="order-first lg:order-none">
            <h2 class="text-3xl md:text-4xl font-bold text-[#173B35] mb-6">
              Satu tempat, banyak cerita.
            </h2>
            <div class="space-y-6 text-lg text-[#66706C] leading-relaxed">
              <p>
                Terletak di lereng Gunung Lawu, Telaga Sarangan menawarkan pesona alam yang tenang dengan udara sejuk khas pegunungan. Tempat yang sempurna untuk melepas penat dan menciptakan kenangan bersama keluarga.
              </p>
              <p>
                Rasakan harmoni alam dengan berbagai aktivitas, dari menyusuri telaga dengan perahu tradisional hingga menikmati kuliner lokal yang menghangatkan suasana. Perjalanan wisata Anda kini dimulai dengan langkah yang lebih mudah melalui sistem tiket digital.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Ticket Preview Section -->
    <section id="tiket" class="py-24 bg-white">
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
        <div class="text-center mb-16 max-w-2xl mx-auto">
          <h2 class="text-3xl md:text-4xl font-bold text-[#173B35] mb-4">Pilih Tiket Kunjungan</h2>
          <p class="text-lg text-[#66706C]">Dapatkan akses penuh ke area publik Telaga Sarangan dengan harga resmi.</p>
        </div>

        <div v-if="isLoadingTickets" class="flex justify-center py-12">
          <LoaderCircle class="h-8 w-8 animate-spin text-[#4F7465]" />
        </div>
        
        <div v-else-if="ticketTypes.length" class="grid sm:grid-cols-2 lg:grid-cols-3 gap-8 justify-center">
          <div v-for="ticket in ticketTypes" :key="ticket.id" class="border border-[var(--color-border)] rounded-[12px] p-8 hover:border-[var(--color-secondary)] transition-colors flex flex-col bg-[var(--color-surface)]">
            <h3 class="text-xl font-bold text-[var(--color-primary)] mb-2">{{ ticket.name }}</h3>
            <p class="text-sm text-[var(--color-text-secondary)] mb-6 h-10">{{ ticket.description || 'Akses masuk Telaga Sarangan' }}</p>
            
            <div class="mb-8">
              <span class="text-3xl font-bold text-[var(--color-text-primary)]">Rp{{ formatPrice(ticket.price) }}</span>
            </div>
            
            <div class="mt-auto">
              <router-link to="/booking" class="block w-full text-center rounded-[10px] bg-[var(--color-primary)] px-4 py-3 text-sm font-semibold text-white hover:bg-[#122c27] transition-colors">
                Pilih Tiket
              </router-link>
            </div>
          </div>
        </div>
        
        <div v-else class="text-center text-[#66706C] py-8">
          Belum ada tiket yang tersedia saat ini.
        </div>
      </div>
    </section>

    <!-- How It Works Section -->
    <section id="cara-pesan" class="py-24 bg-white border-t border-[#173B35]/10">
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row gap-12 lg:gap-24">
          <div class="lg:w-1/3">
            <h2 class="text-3xl md:text-4xl font-bold text-[#173B35] mb-6">Cara Pemesanan</h2>
            <p class="text-lg text-[#66706C]">Langkah mudah untuk mendapatkan tiket digital Telaga Sarangan tanpa perlu mengantre di lokasi.</p>
          </div>
          
          <div class="lg:w-2/3 grid sm:grid-cols-2 gap-x-12 gap-y-16">
            <div class="relative pl-6 border-l border-[#173B35]/20">
              <span class="text-[#4F7465] font-bold text-sm tracking-widest absolute -left-[-1px] top-0 -translate-x-full pr-6">01</span>
              <h3 class="text-xl font-bold text-[#1D2724] mb-3">Pilih Tanggal</h3>
              <p class="text-[#66706C] text-sm leading-relaxed">Tentukan hari kunjungan terbaik Anda bersama keluarga atau kerabat.</p>
            </div>
            <div class="relative pl-6 border-l border-[#173B35]/20">
              <span class="text-[#4F7465] font-bold text-sm tracking-widest absolute -left-[-1px] top-0 -translate-x-full pr-6">02</span>
              <h3 class="text-xl font-bold text-[#1D2724] mb-3">Pilih Tiket</h3>
              <p class="text-[#66706C] text-sm leading-relaxed">Tentukan jumlah pengunjung dewasa dan anak sesuai kebutuhan.</p>
            </div>
            <div class="relative pl-6 border-l border-[#173B35]/20">
              <span class="text-[#4F7465] font-bold text-sm tracking-widest absolute -left-[-1px] top-0 -translate-x-full pr-6">03</span>
              <h3 class="text-xl font-bold text-[#1D2724] mb-3">Bayar</h3>
              <p class="text-[#66706C] text-sm leading-relaxed">Selesaikan pembayaran secara online dengan metode digital pilihan Anda.</p>
            </div>
            <div class="relative pl-6 border-l border-[#173B35]/20">
              <span class="text-[#4F7465] font-bold text-sm tracking-widest absolute -left-[-1px] top-0 -translate-x-full pr-6">04</span>
              <h3 class="text-xl font-bold text-[#1D2724] mb-3">Scan QR</h3>
              <p class="text-[#66706C] text-sm leading-relaxed">Tunjukkan e-ticket pada petugas di gerbang masuk, dan nikmati waktu Anda.</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Maps Location -->
    <section class="py-24 bg-white">
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row gap-12 items-center">
          <div class="w-full md:w-1/3">
            <div class="flex items-center gap-3 text-[#173B35] mb-4">
              <MapPin class="w-8 h-8" />
              <h2 class="text-3xl font-bold">Lokasi Kami</h2>
            </div>
            <p class="text-lg text-[#66706C] mb-6">
              Telaga Sarangan berlokasi di lereng Gunung Lawu, Kabupaten Magetan, Jawa Timur. Akses jalan yang mulus menjadikannya destinasi yang mudah dikunjungi.
            </p>
            <div class="bg-[#F7F5EF] p-6 rounded-xl border border-[#173B35]/10">
              <h4 class="font-bold text-[#1D2724] mb-2">Alamat</h4>
              <p class="text-[#66706C] text-sm leading-relaxed">
                Jl. Raya Telaga Sarangan,<br/>
                Kec. Plaosan, Kabupaten Magetan,<br/>
                Jawa Timur 63361
              </p>
            </div>
          </div>
          <div class="w-full md:w-2/3 h-[400px] md:h-[500px] rounded-2xl overflow-hidden shadow-lg border border-[#173B35]/10">
            <iframe 
              src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15814.708891583348!2d111.2064972!3d-7.6653878!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e798e3b48455555%3A0x6b29eb9e4f526367!2sTelaga%20Sarangan!5e0!3m2!1sen!2sid!4v1700000000000!5m2!1sen!2sid" 
              width="100%" 
              height="100%" 
              style="border:0;" 
              allowfullscreen="false" 
              loading="lazy" 
              referrerpolicy="no-referrer-when-downgrade">
            </iframe>
          </div>
        </div>
      </div>
    </section>

    <!-- Visual Storytelling Tambahan -->
    <section class="py-24 bg-[#173B35] text-white">
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-24 items-center">
          <div>
            <h2 class="text-3xl md:text-5xl font-bold mb-6 leading-tight">Keindahan yang menanti untuk dijelajahi.</h2>
            <p class="text-lg text-white/80 mb-8 max-w-md">Jalan setapak berkabut, aroma pinus, dan udara pagi yang segar. Sarangan bukan sekadar tempat, melainkan ruang untuk kembali terhubung dengan alam.</p>
          </div>
          <div class="h-[500px] rounded-2xl overflow-hidden shadow-xl">
            <img src="/images/sarangan-trail-2.png" alt="Keindahan Sarangan" class="w-full h-full object-cover transition-transform duration-700 hover:scale-105" />
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Akhir -->
    <section class="py-32 bg-[#F7F5EF] text-center border-b border-[#173B35]/10">
      <div class="mx-auto max-w-[800px] px-5">
        <h2 class="text-4xl md:text-5xl font-bold text-[#173B35] mb-6">Siap menikmati Sarangan?</h2>
        <p class="text-xl text-[#66706C] mb-10">Pesan tiketmu sekarang sebelum berangkat, dan rasakan kemudahannya.</p>
        <router-link
          to="/booking"
          class="inline-flex items-center justify-center rounded-lg bg-[#173B35] px-10 py-5 text-lg font-bold text-white transition-colors hover:bg-[#1D2724]"
        >
          Pesan Tiket
        </router-link>
      </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white py-16">
      <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-8 mb-12">
          <div class="flex items-center gap-3">
            <div class="flex items-center justify-center grayscale contrast-200 opacity-80">
              <img src="/images/logo.png" alt="Logo" class="h-10 w-auto" />
            </div>
            <div>
              <span class="block text-xl font-bold text-[var(--color-primary)]">{{ t('app.name') }}</span>
              <span class="block text-sm text-[#66706C]">Sistem Tiket Wisata Digital</span>
            </div>
          </div>
          
          <div class="flex flex-wrap justify-center gap-8 text-sm font-semibold text-[#1D2724]">
            <a href="#beranda" class="hover:text-[#C9965B]">Beranda</a>
            <a href="#tiket" class="hover:text-[#C9965B]">Tiket</a>
            <a href="#tentang" class="hover:text-[#C9965B]">Tentang</a>
            <router-link to="/booking" class="hover:text-[#C9965B]">Pesan Tiket</router-link>
          </div>
        </div>
        
        <div class="pt-8 border-t border-[#173B35]/10 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-[#8B928F]">
          <p>&copy; {{ new Date().getFullYear() }} {{ t('app.name') }}. Pariwisata Magetan.</p>
        </div>
      </div>
    </footer>
  </main>
</template>
