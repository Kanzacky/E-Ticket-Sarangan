<script setup lang="ts">
import { 
  CreditCard, 
  History, 
  Leaf, 
  MountainSnow, 
  PlusCircle, 
  Ticket, 
  Timer,
  ChevronRight,
  CheckCircle2
} from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'

import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const authStore = useAuthStore()

const features = [
  {
    title: 'Tanpa Antre Panjang',
    description: 'Beli tiket dari rumah dan langsung scan barcode di gerbang masuk. Hemat waktu liburan Anda.',
    icon: Timer,
    color: 'text-amber-500',
    bg: 'bg-amber-100'
  },
  {
    title: 'Pembayaran Fleksibel',
    description: 'Dukung berbagai metode pembayaran mulai dari QRIS, GoPay, hingga transfer bank.',
    icon: CreditCard,
    color: 'text-sky-500',
    bg: 'bg-sky-100'
  },
  {
    title: 'Ramah Lingkungan',
    description: 'Kurangi penggunaan kertas dengan e-Ticket. Satu langkah kecil untuk kelestarian alam.',
    icon: Leaf,
    color: 'text-emerald-500',
    bg: 'bg-emerald-100'
  }
]

const tickets = [
  {
    name: 'Dewasa',
    price: 'Rp 20.000',
    features: ['Akses seluruh area telaga', 'Asuransi keselamatan', 'Berlaku 1 hari penuh']
  },
  {
    name: 'Anak-anak',
    price: 'Rp 10.000',
    features: ['Usia 3-12 tahun', 'Akses seluruh area telaga', 'Asuransi keselamatan']
  }
]
</script>

<template>
  <main class="min-h-screen bg-slate-50 font-sans selection:bg-sky-200">
    <!-- Navigation Overlay -->
    <header class="absolute inset-x-0 top-0 z-50 flex flex-wrap items-center justify-between gap-4 px-6 py-5 lg:px-12">
      <div class="flex items-center gap-2">
        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/20 backdrop-blur-md text-white border border-white/30">
          <MountainSnow class="h-5 w-5" />
        </div>
        <span class="text-lg font-bold text-white tracking-wide">{{ t('app.name') }}</span>
      </div>

      <nav class="flex flex-wrap items-center gap-3">
        <template v-if="authStore.isAuthenticated">
          <router-link
            to="/my-bookings"
            class="flex items-center gap-2 rounded-full px-4 py-2 text-sm font-medium text-white transition hover:bg-white/20 backdrop-blur-md"
          >
            <History class="h-4 w-4" />
            <span class="hidden sm:inline">Tiket Saya</span>
          </router-link>
          <router-link
            to="/booking"
            class="flex items-center gap-2 rounded-full bg-sky-500 px-5 py-2 text-sm font-semibold text-white shadow-lg transition hover:bg-sky-400 hover:scale-105"
          >
            <PlusCircle class="h-4 w-4" />
            <span>Pesan</span>
          </router-link>
        </template>
        <template v-else>
          <router-link
            to="/login"
            class="rounded-full px-5 py-2 text-sm font-medium text-white transition hover:bg-white/20 backdrop-blur-md"
          >
            {{ t('nav.login') }}
          </router-link>
          <router-link
            to="/register"
            class="rounded-full bg-white px-5 py-2 text-sm font-semibold text-sky-900 shadow-lg transition hover:bg-sky-50 hover:scale-105"
          >
            {{ t('nav.register') }}
          </router-link>
        </template>
      </nav>
    </header>

    <!-- Hero Section -->
    <section class="relative flex min-h-[90vh] items-center justify-center overflow-hidden bg-slate-900 px-6 py-32 lg:px-12">
      <!-- Background Image -->
      <div class="absolute inset-0 z-0">
        <img 
          src="/images/sarangan-hero.png" 
          alt="Telaga Sarangan" 
          class="h-full w-full object-cover opacity-60"
        />
        <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/40 to-transparent"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-slate-900/80 to-transparent"></div>
      </div>

      <!-- Hero Content -->
      <div class="relative z-10 w-full max-w-5xl">
        <div class="max-w-2xl">
          <span class="inline-flex items-center gap-2 rounded-full border border-sky-400/30 bg-sky-400/10 px-4 py-1.5 text-sm font-medium text-sky-300 backdrop-blur-md mb-6">
            <Ticket class="h-4 w-4" /> Pemenang INOTEK Award 2026
          </span>
          <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-6xl lg:text-7xl">
            Nikmati Keindahan<br/>
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-400 to-emerald-400">Telaga Sarangan</span>
          </h1>
          <p class="mt-6 text-lg text-slate-300 sm:text-xl leading-relaxed max-w-lg">
            Sistem tiket digital pintar yang memudahkan perjalanan liburan Anda. Bebas antre, cepat, dan ramah lingkungan.
          </p>
          <div class="mt-10 flex flex-wrap items-center gap-4">
            <router-link
              to="/booking"
              class="flex items-center gap-2 rounded-full bg-gradient-to-r from-sky-500 to-sky-600 px-8 py-4 text-base font-bold text-white shadow-lg shadow-sky-500/30 transition hover:scale-105 hover:shadow-sky-500/50"
            >
              Pesan Tiket Sekarang <ChevronRight class="h-5 w-5" />
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-24 px-6 lg:px-12 bg-white">
      <div class="mx-auto max-w-7xl">
        <div class="text-center max-w-2xl mx-auto mb-16">
          <h2 class="text-3xl font-bold text-slate-900 sm:text-4xl">Mengapa Menggunakan e-Ticket?</h2>
          <p class="mt-4 text-slate-600 text-lg">Kami merancang pengalaman terbaik untuk memastikan liburan Anda nyaman sejak dari rumah.</p>
        </div>

        <div class="grid gap-8 md:grid-cols-3">
          <div v-for="feat in features" :key="feat.title" class="rounded-3xl border border-slate-100 bg-slate-50 p-8 transition hover:shadow-xl hover:shadow-slate-200/50">
            <div :class="['mb-6 flex h-14 w-14 items-center justify-center rounded-2xl', feat.bg, feat.color]">
              <component :is="feat.icon" class="h-7 w-7" />
            </div>
            <h3 class="text-xl font-bold text-slate-900 mb-3">{{ feat.title }}</h3>
            <p class="text-slate-600 leading-relaxed">{{ feat.description }}</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-24 px-6 lg:px-12 bg-slate-50">
      <div class="mx-auto max-w-7xl">
        <div class="text-center max-w-2xl mx-auto mb-16">
          <h2 class="text-3xl font-bold text-slate-900 sm:text-4xl">Harga Tiket Masuk</h2>
          <p class="mt-4 text-slate-600 text-lg">Harga resmi yang berlaku untuk kawasan wisata Telaga Sarangan.</p>
        </div>

        <div class="grid gap-8 md:grid-cols-2 max-w-4xl mx-auto">
          <div v-for="ticket in tickets" :key="ticket.name" class="relative rounded-3xl bg-white p-8 shadow-sm border border-slate-200 overflow-hidden flex flex-col">
            <div class="absolute top-0 right-0 w-32 h-32 bg-sky-50 rounded-bl-full -z-10 opacity-50"></div>
            <h3 class="text-2xl font-bold text-slate-900">{{ ticket.name }}</h3>
            <div class="mt-4 flex items-baseline text-4xl font-extrabold text-sky-600">
              {{ ticket.price }}
              <span class="ml-1 text-base font-medium text-slate-500">/orang</span>
            </div>
            <ul class="mt-8 space-y-4 flex-1">
              <li v-for="feat in ticket.features" :key="feat" class="flex items-center gap-3 text-slate-700">
                <CheckCircle2 class="h-5 w-5 text-emerald-500 shrink-0" />
                <span>{{ feat }}</span>
              </li>
            </ul>
            <router-link to="/booking" class="mt-8 block w-full rounded-xl bg-slate-900 px-4 py-3 text-center text-sm font-semibold text-white transition hover:bg-sky-600">
              Pilih Kategori Ini
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer & System Status -->
    <footer class="bg-slate-900 pt-20 pb-10 px-6 lg:px-12 text-slate-400">
      <div class="mx-auto max-w-7xl">
        <div class="grid gap-12 md:grid-cols-3 mb-16">
          <div class="md:col-span-2">
            <div class="flex items-center gap-2 mb-6 text-white">
              <MountainSnow class="h-6 w-6" />
              <span class="text-xl font-bold tracking-wide">{{ t('app.name') }}</span>
            </div>
            <p class="max-w-md text-slate-400 leading-relaxed">
              Inovasi digitalisasi pariwisata untuk mendukung kemajuan ekonomi daerah dan kenyamanan wisatawan di Telaga Sarangan, Magetan.
            </p>
          </div>

          <div>
            <h4 class="text-white font-semibold mb-6">Navigasi</h4>
            <ul class="space-y-4">
              <li><router-link to="/booking" class="hover:text-white transition">Pesan Tiket</router-link></li>
              <li><router-link to="/login" class="hover:text-white transition">Login Wisatawan</router-link></li>
              <li><router-link to="/register" class="hover:text-white transition">Daftar Akun</router-link></li>
            </ul>
          </div>
        </div>

        <div class="border-t border-slate-800 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
          <p class="text-sm">&copy; {{ new Date().getFullYear() }} {{ t('app.name') }}. Hak Cipta Dilindungi.</p>
          <span class="rounded-full border border-slate-700 bg-slate-800 px-3 py-1 text-xs font-medium text-slate-300">
            {{ t('app.inotek') }}
          </span>
        </div>
      </div>
    </footer>
  </main>
</template>
