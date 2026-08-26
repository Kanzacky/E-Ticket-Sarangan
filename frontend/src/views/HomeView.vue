<script setup lang="ts">
import { 
  CreditCard, 
  History, 
  Leaf, 
  LoaderCircle, 
  MapPin,
  MountainSnow, 
  PlusCircle, 
  Ticket, 
  Timer,
  CheckCircle2,
  ArrowRight
} from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'

import { useApiHealth } from '@/composables/useApiHealth'
import { useAuthStore } from '@/stores/auth'

const { t } = useI18n()
const authStore = useAuthStore()
const { status, data } = useApiHealth()

const features = [
  {
    title: 'Tanpa Antrean Panjang',
    description: 'Beli tiket dari mana saja. Cukup scan QR code di gerbang masuk dan nikmati liburan Anda seketika.',
    icon: Timer,
    color: 'text-orange-500',
    bg: 'bg-orange-50',
    border: 'border-orange-100'
  },
  {
    title: 'Pembayaran Digital',
    description: 'Bebas ribet dengan dukungan metode pembayaran instan seperti QRIS, GoPay, dan Virtual Account.',
    icon: CreditCard,
    color: 'text-blue-500',
    bg: 'bg-blue-50',
    border: 'border-blue-100'
  },
  {
    title: 'Peduli Lingkungan',
    description: 'Dukung inisiatif pariwisata hijau dengan e-ticket yang sepenuhnya paperless.',
    icon: Leaf,
    color: 'text-emerald-500',
    bg: 'bg-emerald-50',
    border: 'border-emerald-100'
  }
]

const tickets = [
  {
    name: 'Pengunjung Dewasa',
    price: '20.000',
    popular: true,
    features: ['Akses seluruh area publik telaga', 'Termasuk asuransi keselamatan', 'Berlaku untuk 1 hari kunjungan', 'Usia di atas 12 tahun']
  },
  {
    name: 'Pengunjung Anak',
    price: '10.000',
    popular: false,
    features: ['Akses seluruh area publik telaga', 'Termasuk asuransi keselamatan', 'Berlaku untuk 1 hari kunjungan', 'Usia 3 - 12 tahun']
  }
]
</script>

<template>
  <main class="min-h-screen bg-[#F8FAFC] font-sans text-slate-900 selection:bg-sky-200">
    <!-- Navbar -->
    <header class="sticky top-6 z-50 mx-auto max-w-6xl px-4 sm:px-6 bg-white/90 backdrop-blur-xl border-b border-slate-200">
      <div class="flex items-center justify-between rounded-full bg-white/80 px-4 py-3 shadow-[0_8px_30px_rgb(0,0,0,0.04)] backdrop-blur-xl border border-white">
        <div class="flex items-center gap-3 pl-2">
          <div class="flex h-9 w-9 items-center justify-center rounded-full bg-gradient-to-br from-sky-500 to-blue-600 text-white shadow-md shadow-sky-500/20">
            <MountainSnow class="h-5 w-5" />
          </div>
          <span class="text-lg font-extrabold tracking-tight text-slate-800">{{ t('app.name') }}</span>
        </div>

        <nav class="flex items-center gap-2 sm:gap-3 pr-1">
          <template v-if="authStore.isAuthenticated">
            <router-link
              to="/wisatawan/history"
              class="flex items-center gap-2 rounded-full px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900"
            >
              <History class="h-4 w-4" />
              <span class="hidden sm:inline">Pesanan Saya</span>
            </router-link>
            <router-link
              to="/wisatawan/booking"
              class="flex items-center gap-2 rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-md transition-all hover:bg-slate-800 hover:shadow-lg hover:-translate-y-0.5"
            >
              <PlusCircle class="h-4 w-4" />
              <span>Pesan Tiket</span>
            </router-link>
          </template>
          <template v-else>
            <router-link
              to="/login"
              class="rounded-full px-5 py-2 text-sm font-semibold text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900"
            >
              Masuk
            </router-link>
            <router-link
              to="/register"
              class="rounded-full bg-slate-900 px-5 py-2 text-sm font-semibold text-white shadow-md transition-all hover:bg-slate-800 hover:shadow-lg hover:-translate-y-0.5"
            >
              Daftar Akun
            </router-link>
          </template>
        </nav>
      </div>
    </header>

    <!-- Hero Section -->
    <section class="relative pt-6 pb-20 lg:pt-48 lg:pb-32 overflow-hidden">
      <!-- Decorative blobs -->
      <div class="absolute -top-[20%] -right-[10%] -z-10 h-[600px] w-[600px] rounded-full bg-sky-200/50 blur-[100px]"></div>
      <div class="absolute top-[20%] -left-[10%] -z-10 h-[500px] w-[500px] rounded-full bg-blue-200/40 blur-[100px]"></div>

      <div class="mx-auto max-w-7xl px-6 lg:px-12">
        <div class="grid gap-12 lg:grid-cols-2 lg:items-center">
          <!-- Text Content -->
          <div class="max-w-2xl">
            <div class="inline-flex items-center gap-2 rounded-full border border-sky-200 bg-sky-50 px-4 py-2 text-sm font-semibold text-sky-700 mb-8 shadow-sm">
              <Ticket class="h-4 w-4" /> 
              <span>Wisata Digital Telaga Sarangan</span>
            </div>
            
            <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-5xl lg:text-7xl leading-[1.1]">
              Cara modern <br>
              menikmati <br>
              <span class="text-transparent bg-clip-text bg-gradient-to-r from-sky-600 to-blue-600">Telaga Sarangan.</span>
            </h1>
            
            <p class="mt-6 text-lg text-slate-600 leading-relaxed sm:text-xl">
              Tinggalkan cara lama. Pesan tiket masuk dan penginapan secara online, hindari antrean, dan nikmati liburan yang sepenuhnya tanpa beban.
            </p>
            
            <div class="mt-10 flex flex-col sm:flex-row items-center gap-4">
              <router-link
                to="/login"
                class="flex w-full sm:w-auto items-center justify-center gap-2 rounded-full bg-sky-600 px-8 py-4 text-base font-bold text-white shadow-lg shadow-sky-600/30 transition-all hover:bg-sky-700 hover:shadow-sky-600/40 hover:-translate-y-1"
              >
                Mulai Petualangan <ArrowRight class="h-5 w-5" />
              </router-link>
              <router-link
                to="/wisatawan/accommodations"
                class="flex w-full sm:w-auto items-center justify-center gap-2 rounded-full bg-white px-8 py-4 text-base font-bold text-slate-700 shadow-sm border border-slate-200 transition-all hover:bg-slate-50 hover:-translate-y-1"
              >
                Lihat Penginapan
              </router-link>
            </div>
          </div>

          <!-- Image/Visual -->
          <div class="relative lg:h-[600px] w-full rounded-[2.5rem] overflow-hidden shadow-2xl shadow-slate-900/10 border-8 border-white">
            <img 
              src="/images/sarangan-hero.png" 
              alt="Pemandangan Telaga Sarangan" 
              class="absolute inset-0 h-full w-full object-cover transition-transform duration-700 hover:scale-105"
            />
            <div class="absolute inset-0 bg-gradient-to-t from-slate-900/60 via-transparent to-transparent"></div>
            
            <!-- Floating Info Card -->
            <div class="absolute bottom-6 left-6 right-6 rounded-2xl bg-white/90 p-5 backdrop-blur-md shadow-lg border border-white/50 flex items-center justify-between">
              <div>
                <p class="text-sm font-bold text-slate-900">Pesona Alam Magetan</p>
                <p class="text-xs text-slate-600 font-medium">Buka Setiap Hari, 24 Jam</p>
              </div>
              <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-100 text-sky-600">
                <MapPin class="h-5 w-5" />
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Bento Grid -->
    <section class="py-24 bg-white px-6 lg:px-12 relative">
      <div class="mx-auto max-w-7xl">
        <div class="mb-16 max-w-3xl">
          <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl lg:text-5xl">
            Sistem pintar untuk pengalaman liburan terbaik.
          </h2>
          <p class="mt-6 text-lg text-slate-600">
            Kami mendesain ulang setiap aspek dari perjalanan Anda. Dari pembelian tiket hingga memasuki area wisata, semuanya kini ada di genggaman Anda.
          </p>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
          <div v-for="(feat, index) in features" :key="index" class="group relative overflow-hidden rounded-3xl p-8 transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/50" :class="[feat.bg, feat.border, 'border']">
            <div class="absolute top-0 right-0 p-6 opacity-10 transition-transform duration-500 group-hover:scale-110 group-hover:opacity-20">
              <component :is="feat.icon" class="h-32 w-32" :class="feat.color" />
            </div>
            
            <div class="relative z-10">
              <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-2xl bg-white shadow-sm">
                <component :is="feat.icon" class="h-7 w-7" :class="feat.color" />
              </div>
              <h3 class="mb-3 text-2xl font-bold text-slate-900">{{ feat.title }}</h3>
              <p class="text-slate-700 font-medium leading-relaxed">{{ feat.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing Section -->
    <section class="py-24 px-6 lg:px-12 bg-slate-50">
      <div class="mx-auto max-w-7xl">
        <div class="text-center max-w-3xl mx-auto mb-16">
          <h2 class="text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">Harga Tiket Resmi</h2>
          <p class="mt-4 text-lg text-slate-600">Satu harga terjangkau untuk akses penuh ke keindahan alam Telaga Sarangan.</p>
        </div>

        <div class="grid gap-8 md:grid-cols-2 max-w-4xl mx-auto">
          <div
v-for="ticket in tickets" :key="ticket.name" 
               class="relative flex flex-col rounded-[2rem] bg-white p-8 sm:p-10 shadow-sm border transition-all hover:-translate-y-1 hover:shadow-xl hover:shadow-slate-200/50"
               :class="ticket.popular ? 'border-sky-200 ring-4 ring-sky-50' : 'border-slate-200'">
            
            <div v-if="ticket.popular" class="absolute -top-4 right-8 rounded-full bg-gradient-to-r from-sky-500 to-blue-600 px-4 py-1 text-xs font-bold uppercase tracking-wider text-white shadow-sm">
              Paling Sering Dibeli
            </div>

            <h3 class="text-xl font-bold text-slate-500">{{ ticket.name }}</h3>
            <div class="mt-4 flex items-baseline gap-2">
              <span class="text-5xl font-extrabold tracking-tight text-slate-900">Rp{{ ticket.price }}</span>
              <span class="text-base font-semibold text-slate-500">/ orang</span>
            </div>

            <div class="my-8 h-px w-full bg-slate-100"></div>

            <ul class="mb-8 flex-1 space-y-4">
              <li v-for="feat in ticket.features" :key="feat" class="flex items-start gap-3">
                <CheckCircle2 class="h-6 w-6 text-sky-500 shrink-0" />
                <span class="font-medium text-slate-700">{{ feat }}</span>
              </li>
            </ul>

            <router-link
to="/login" 
                 class="block w-full rounded-2xl px-6 py-4 text-center text-sm font-bold transition-all"
                 :class="ticket.popular ? 'bg-sky-600 text-white hover:bg-sky-700 shadow-md shadow-sky-600/20' : 'bg-slate-100 text-slate-900 hover:bg-slate-200'">
              Pilih Tiket Ini
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- Minimal Footer -->
    <footer class="bg-white border-t border-slate-200 pt-16 pb-8 px-6 lg:px-12">
      <div class="mx-auto max-w-7xl flex flex-col md:flex-row justify-between items-center gap-8">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-900 text-white">
            <MountainSnow class="h-5 w-5" />
          </div>
          <div>
            <span class="block text-lg font-bold text-slate-900">{{ t('app.name') }}</span>
            <span class="block text-xs font-medium text-slate-500">Pariwisata Magetan</span>
          </div>
        </div>
        
        <div class="flex flex-wrap items-center justify-center gap-6 text-sm font-semibold text-slate-600">
          <router-link to="/login" class="hover:text-sky-600 transition-colors">Pesan Tiket</router-link>
          <router-link to="/wisatawan/accommodations" class="hover:text-sky-600 transition-colors">Penginapan</router-link>
          <a href="#" class="hover:text-sky-600 transition-colors">Bantuan</a>
        </div>
      </div>

      <!-- System Status Bar -->
      <div class="mx-auto max-w-7xl mt-16 pt-8 border-t border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4">
        <p class="text-sm font-medium text-slate-500">&copy; {{ new Date().getFullYear() }} {{ t('app.name') }}. All rights reserved.</p>
        
        <div class="flex flex-wrap items-center gap-4 text-xs font-bold text-slate-500 bg-slate-50 px-4 py-2 rounded-full border border-slate-200">
          <span class="flex items-center gap-1.5">
            <span class="relative flex h-2.5 w-2.5">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
            </span>
            Sistem Aktif
          </span>
          <span class="w-px h-3 bg-slate-300"></span>
          <span class="flex items-center gap-1.5">
            <LoaderCircle v-if="status === 'checking'" class="h-3 w-3 animate-spin text-sky-500" />
            <span v-else-if="status === 'connected'" class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
            <span v-else class="h-2.5 w-2.5 rounded-full bg-red-500"></span>
            API: {{ status === 'connected' ? 'OK' : status === 'checking' ? 'Mengecek...' : 'Down' }}
          </span>
          <span v-if="status === 'connected'" class="flex items-center gap-1.5">
            <span class="w-px h-3 bg-slate-300 mr-1"></span>
            <span :class="['h-2.5 w-2.5 rounded-full', data?.database === 'connected' ? 'bg-emerald-500' : 'bg-red-500']"></span>
            Database: {{ data?.database === 'connected' ? 'OK' : 'Down' }}
          </span>
        </div>
      </div>
    </footer>
  </main>
</template>
