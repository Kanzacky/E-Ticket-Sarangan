<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Menu, X, User, LogOut, ChevronRight } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'
import NotificationBell from '@/components/ui/NotificationBell.vue'

const props = defineProps<{
  transparentTop?: boolean
}>()

const { t } = useI18n()
const authStore = useAuthStore()
const router = useRouter()

const isMobileMenuOpen = ref(false)
const scrollY = ref(0)

const handleScroll = () => {
  scrollY.value = window.scrollY
}

onMounted(() => {
  if (props.transparentTop) {
    window.addEventListener('scroll', handleScroll)
    handleScroll()
  }
})

onUnmounted(() => {
  if (props.transparentTop) {
    window.removeEventListener('scroll', handleScroll)
  }
})

const isScrolled = computed(() => {
  if (!props.transparentTop) return true
  return scrollY.value > 60
})

const navLinkClass = computed(() =>
  isScrolled.value
    ? 'text-[#1D2724] hover:text-[#4F7465]'
    : 'text-white/90 hover:text-white'
)

async function handleLogout() {
  isMobileMenuOpen.value = false
  await authStore.logout()
  void router.push({ name: 'home' })
}

function handlePesanTiket() {
  isMobileMenuOpen.value = false
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
</script>

<template>
  <header
    class="fixed top-0 w-full z-50 transition-all duration-300"
    :class="isScrolled
      ? 'bg-[#F7F5EF]/97 backdrop-blur-lg border-b border-[#173B35]/10 shadow-sm'
      : 'bg-transparent border-b border-transparent'"
  >
    <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-[72px]">

        <!-- Logo -->
        <router-link to="/" class="flex items-center gap-2.5 shrink-0 min-w-0">
          <!-- Logo lingkaran - pakai style inline untuk paksa clip -->
          <div
            class="shrink-0 transition-all duration-300 bg-white"
            style="width:42px;height:42px;border-radius:50%;overflow:hidden;display:flex;align-items:center;justify-content:center;"
            :style="isScrolled
              ? 'border: 2px solid rgba(23,59,53,0.4);'
              : 'border: 2px solid rgba(255,255,255,0.7);'"
          >
            <img
              src="/images/logo.png"
              alt="e-Ticket Sarangan"
              style="width:100%;height:100%;object-fit:cover;"
            />
          </div>
          <!-- Sembunyikan teks nama di layar mobile agar tidak sesak -->
          <span
            class="hidden sm:inline text-base font-bold tracking-tight transition-colors truncate"
            :class="isScrolled ? 'text-[#173B35]' : 'text-white'"
          >
            {{ t('app.name') }}
          </span>
        </router-link>

        <!-- Desktop Navigation -->
        <nav class="hidden lg:flex items-center gap-6">
          <router-link to="/" class="text-sm font-semibold transition-colors" :class="navLinkClass">Beranda</router-link>
          <router-link to="/accommodations" class="text-sm font-semibold transition-colors" :class="navLinkClass">Penginapan</router-link>

          <router-link :to="{ path: '/', hash: '#tiket' }" class="text-sm font-semibold transition-colors" :class="navLinkClass">Tiket</router-link>
          <router-link :to="{ path: '/', hash: '#tentang' }" class="text-sm font-semibold transition-colors" :class="navLinkClass">Tentang</router-link>
          <router-link :to="{ path: '/', hash: '#cara-pesan' }" class="text-sm font-semibold transition-colors" :class="navLinkClass">Cara Booking</router-link>

          <template v-if="authStore.isAuthenticated && !['admin', 'petugas'].includes(authStore.user?.role || '')">
            <router-link to="/my-tickets" class="text-sm font-semibold transition-colors" :class="navLinkClass">
              Pesanan Saya
            </router-link>
            <router-link to="/my-accommodations" class="text-sm font-semibold transition-colors" :class="navLinkClass">
              Penginapan Saya
            </router-link>
          </template>
        </nav>

        <!-- Desktop Right Actions -->
        <div class="hidden lg:flex items-center gap-3">
          <!-- Authenticated -->
          <template v-if="authStore.isAuthenticated">
            <NotificationBell :class="isScrolled ? '[&>div>button]:text-[#66706C] [&>div>button]:hover:bg-[#F7F5EF]' : '[&>div>button]:text-white/80 [&>div>button]:hover:bg-white/10'" />
            <div
              class="flex items-center gap-3 border-r pr-3 mr-1"
              :class="isScrolled ? 'border-[#173B35]/15' : 'border-white/20'"
            >
              <!-- User info + profile link -->
              <router-link to="/profile" class="flex items-center gap-2.5 group">
                <div
                  class="w-9 h-9 rounded-full flex items-center justify-center transition-colors"
                  :class="isScrolled ? 'bg-[#173B35]/10 text-[#173B35] group-hover:bg-[#173B35]/20' : 'bg-white/15 text-white group-hover:bg-white/25'"
                >
                  <User class="w-4 h-4" />
                </div>
                <div class="text-right hidden xl:block">
                  <p class="text-xs font-bold leading-tight" :class="isScrolled ? 'text-[#1D2724]' : 'text-white'">
                    {{ authStore.user?.name?.split(' ')[0] }}
                  </p>
                  <p class="text-[10px] leading-tight" :class="isScrolled ? 'text-[#66706C]' : 'text-white/60'">
                    Wisatawan
                  </p>
                </div>
              </router-link>

              <!-- Logout -->
              <button
                @click="handleLogout"
                class="p-2 rounded-lg transition-colors"
                :class="isScrolled ? 'text-[#66706C] hover:text-red-600 hover:bg-red-50' : 'text-white/70 hover:text-white hover:bg-white/10'"
                title="Keluar"
              >
                <LogOut class="w-4 h-4" />
              </button>
            </div>

            <!-- CTA: Pesan Tiket -->
            <button
              @click="handlePesanTiket"
              class="flex items-center gap-1.5 rounded-lg px-5 py-2.5 text-sm font-bold transition-all"
              :class="isScrolled ? 'bg-[#173B35] text-white hover:bg-[#1D2724]' : 'bg-white text-[#173B35] hover:bg-white/90'"
            >
              {{ ['admin', 'petugas'].includes(authStore.user?.role || '') ? 'Dashboard' : 'Pesan Tiket' }}
            </button>
          </template>

          <!-- Guest -->
          <template v-else>
            <router-link
              to="/login"
              class="text-sm font-semibold transition-colors"
              :class="navLinkClass"
            >
              Masuk
            </router-link>
            <router-link
              to="/register"
              class="text-sm font-bold transition-colors border rounded-lg px-4 py-2"
              :class="isScrolled ? 'border-[#173B35]/20 text-[#173B35] hover:bg-[#173B35]/5' : 'border-white/40 text-white hover:bg-white/10'"
            >
              Daftar
            </router-link>
            <button
              @click="handlePesanTiket"
              class="flex items-center gap-1.5 rounded-lg px-5 py-2.5 text-sm font-bold transition-all"
              :class="isScrolled ? 'bg-[#173B35] text-white hover:bg-[#1D2724]' : 'bg-white text-[#173B35] hover:bg-white/90'"
            >
              Pesan Tiket
            </button>
          </template>
        </div>

        <!-- Mobile: Pesan Tiket + Hamburger -->
        <div class="lg:hidden flex items-center gap-1.5 shrink-0">
          <button
            @click="handlePesanTiket"
            class="rounded-lg font-bold transition-all"
            :class="isScrolled ? 'bg-[#173B35] text-white' : 'bg-white text-[#173B35]'"
            style="padding: 7px 12px; font-size: 12px; white-space: nowrap;"
          >
            {{ ['admin', 'petugas'].includes(authStore.user?.role || '') ? 'Dashboard' : 'Pesan' }}
          </button>
          <button
            @click="isMobileMenuOpen = !isMobileMenuOpen"
            class="p-2 rounded-lg transition-colors"
            :class="isScrolled ? 'text-[#173B35]' : 'text-white'"
            :aria-label="isMobileMenuOpen ? 'Tutup menu' : 'Buka menu'"
          >
            <X v-if="isMobileMenuOpen" class="h-5 w-5" />
            <Menu v-else class="h-5 w-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Drawer -->
    <Transition
      enter-active-class="transition-all duration-200 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
      leave-active-class="transition-all duration-150 ease-in"
      leave-from-class="opacity-100 translate-y-0"
      leave-to-class="opacity-0 -translate-y-2"
    >
      <div
        v-if="isMobileMenuOpen"
        class="lg:hidden bg-[#F7F5EF] border-t border-[#173B35]/10 shadow-xl absolute w-full overflow-hidden"
      >
        <nav class="px-5 py-5 space-y-1">
          <!-- Nav links -->
          <router-link
            to="/"
            @click="isMobileMenuOpen = false"
            class="flex items-center justify-between py-3 text-[#1D2724] font-semibold border-b border-[#173B35]/8"
          >
            Beranda
          </router-link>

          <router-link
            to="/accommodations"
            @click="isMobileMenuOpen = false"
            class="flex items-center justify-between py-3 text-[#1D2724] font-semibold border-b border-[#173B35]/8"
          >
            Penginapan
          </router-link>

          <template v-if="$route.name === 'home'">
            <a href="#tiket" @click="isMobileMenuOpen = false" class="flex items-center justify-between py-3 text-[#1D2724] font-semibold border-b border-[#173B35]/8">Tiket</a>
            <a href="#tentang" @click="isMobileMenuOpen = false" class="flex items-center justify-between py-3 text-[#1D2724] font-semibold border-b border-[#173B35]/8">Tentang Sarangan</a>
            <a href="#cara-pesan" @click="isMobileMenuOpen = false" class="flex items-center justify-between py-3 text-[#1D2724] font-semibold border-b border-[#173B35]/8">Cara Booking</a>
          </template>
          <template v-else>
            <router-link to="/#tiket" @click="isMobileMenuOpen = false" class="flex items-center justify-between py-3 text-[#1D2724] font-semibold border-b border-[#173B35]/8">Tiket</router-link>
            <router-link to="/#tentang" @click="isMobileMenuOpen = false" class="flex items-center justify-between py-3 text-[#1D2724] font-semibold border-b border-[#173B35]/8">Tentang Sarangan</router-link>
            <router-link to="/#cara-pesan" @click="isMobileMenuOpen = false" class="flex items-center justify-between py-3 text-[#1D2724] font-semibold border-b border-[#173B35]/8">Cara Booking</router-link>
          </template>

          <!-- Auth-specific links -->
          <template v-if="authStore.isAuthenticated">
            <template v-if="!['admin', 'petugas'].includes(authStore.user?.role || '')">
              <router-link to="/my-tickets" @click="isMobileMenuOpen = false" class="flex items-center justify-between py-3 text-[#1D2724] font-semibold border-b border-[#173B35]/8">
                Pesanan Saya
                <ChevronRight class="w-4 h-4 text-[#66706C]" />
              </router-link>
              <router-link to="/my-accommodations" @click="isMobileMenuOpen = false" class="flex items-center justify-between py-3 text-[#1D2724] font-semibold border-b border-[#173B35]/8">
                Penginapan Saya
                <ChevronRight class="w-4 h-4 text-[#66706C]" />
              </router-link>
            </template>
            <router-link to="/profile" @click="isMobileMenuOpen = false" class="flex items-center justify-between py-3 text-[#1D2724] font-semibold border-b border-[#173B35]/8">
              Profil Saya
              <ChevronRight class="w-4 h-4 text-[#66706C]" />
            </router-link>

            <!-- User info -->
            <div class="flex items-center gap-3 py-3 mt-2">
              <div class="w-10 h-10 rounded-full bg-[#173B35]/10 text-[#173B35] flex items-center justify-center">
                <User class="w-5 h-5" />
              </div>
              <div>
                <p class="font-bold text-[#1D2724] text-sm">{{ authStore.user?.name }}</p>
                <p class="text-xs text-[#66706C]">{{ authStore.user?.email }}</p>
              </div>
            </div>

            <button
              @click="handleLogout"
              class="w-full mt-2 flex items-center gap-2 py-3 text-red-600 font-semibold text-sm"
            >
              <LogOut class="w-4 h-4" />
              Keluar dari Akun
            </button>
          </template>

          <!-- Guest -->
          <template v-else>
            <router-link
              to="/login"
              @click="isMobileMenuOpen = false"
              class="flex items-center justify-between py-3 text-[#1D2724] font-semibold border-b border-[#173B35]/8"
            >
              Masuk
            </router-link>
            <router-link
              to="/register"
              @click="isMobileMenuOpen = false"
              class="flex items-center justify-between py-3 text-[#1D2724] font-semibold"
            >
              Buat Akun
            </router-link>
          </template>
        </nav>
      </div>
    </Transition>
  </header>
</template>
