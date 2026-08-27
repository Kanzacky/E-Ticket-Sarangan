<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { Menu, X, User, LogOut } from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { useAuthStore } from '@/stores/auth'
import { useRouter } from 'vue-router'

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
  if (!props.transparentTop) return true // Always solid if not transparentTop
  return scrollY.value > 50
})

async function handleLogout() {
  await authStore.logout()
  void router.push({ name: 'home' })
}
</script>

<template>
  <header 
    class="fixed top-0 w-full z-50 border-b transition-all duration-300"
    :class="isScrolled ? 'bg-[#F7F5EF]/95 backdrop-blur-md border-[#173B35]/10 shadow-sm py-0' : 'bg-transparent border-transparent py-2'"
  >
    <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20">
        <router-link to="/" class="flex items-center gap-3">
          <div class="flex items-center justify-center">
            <img src="/images/logo.png" alt="Logo" class="h-10 w-auto" />
          </div>
          <span class="text-xl font-bold tracking-tight transition-colors" :class="isScrolled ? 'text-[#173B35]' : 'text-white'">{{ t('app.name') }}</span>
        </router-link>

        <!-- Desktop Nav -->
        <nav class="hidden lg:flex items-center gap-8">
          <router-link to="/" class="text-sm font-semibold transition-colors" :class="isScrolled ? 'text-[#1D2724] hover:text-[#4F7465]' : 'text-white/90 hover:text-white'">Beranda</router-link>
          
          <router-link v-if="$route.name !== 'home'" to="/#tiket" class="text-sm font-semibold transition-colors" :class="isScrolled ? 'text-[#1D2724] hover:text-[#4F7465]' : 'text-white/90 hover:text-white'">Tiket</router-link>
          <a v-else href="#tiket" class="text-sm font-semibold transition-colors" :class="isScrolled ? 'text-[#1D2724] hover:text-[#4F7465]' : 'text-white/90 hover:text-white'">Tiket</a>
          
          <router-link v-if="$route.name !== 'home'" to="/#tentang" class="text-sm font-semibold transition-colors" :class="isScrolled ? 'text-[#1D2724] hover:text-[#4F7465]' : 'text-white/90 hover:text-white'">Tentang Sarangan</router-link>
          <a v-else href="#tentang" class="text-sm font-semibold transition-colors" :class="isScrolled ? 'text-[#1D2724] hover:text-[#4F7465]' : 'text-white/90 hover:text-white'">Tentang Sarangan</a>
        </nav>

        <div class="hidden lg:flex items-center gap-4">
          <template v-if="authStore.isAuthenticated">
            <router-link
              to="/my-tickets"
              class="text-sm font-semibold transition-colors"
              :class="isScrolled ? 'text-[#1D2724] hover:text-[#4F7465]' : 'text-white/90 hover:text-white'"
            >
              E-Ticket Saya
            </router-link>
            
            <!-- User Menu -->
            <div class="flex items-center gap-3 border-l pl-4 ml-2" :class="isScrolled ? 'border-[#173B35]/20' : 'border-white/20'">
              <div class="text-right">
                <p class="text-xs font-bold" :class="isScrolled ? 'text-[#1D2724]' : 'text-white'">{{ authStore.user?.name }}</p>
                <p class="text-[10px]" :class="isScrolled ? 'text-[#66706C]' : 'text-white/70'">Wisatawan</p>
              </div>
              <router-link to="/profile" class="w-10 h-10 rounded-full flex items-center justify-center transition-colors" :class="isScrolled ? 'bg-[#173B35]/10 text-[#173B35] hover:bg-[#173B35]/20' : 'bg-white/10 text-white hover:bg-white/20'">
                <User class="w-5 h-5" />
              </router-link>
              <button 
                @click="handleLogout"
                class="inline-flex items-center justify-center p-2 rounded-xl transition-colors"
                :class="isScrolled ? 'text-[#66706C] hover:text-red-600 hover:bg-red-50' : 'text-white/70 hover:text-white hover:bg-white/10'"
                title="Keluar"
              >
                <LogOut class="w-5 h-5" />
              </button>
            </div>
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
    <div v-if="isMobileMenuOpen" class="lg:hidden bg-[#F7F5EF] border-t border-[#173B35]/10 px-5 pt-4 pb-6 space-y-4 shadow-lg absolute w-full max-h-[80vh] overflow-y-auto">
      <router-link to="/" @click="isMobileMenuOpen = false" class="block text-base font-semibold text-[#1D2724]">Beranda</router-link>
      <router-link v-if="$route.name !== 'home'" to="/#tiket" @click="isMobileMenuOpen = false" class="block text-base font-semibold text-[#1D2724]">Tiket</router-link>
      <a v-else href="#tiket" @click="isMobileMenuOpen = false" class="block text-base font-semibold text-[#1D2724]">Tiket</a>
      
      <router-link v-if="$route.name !== 'home'" to="/#tentang" @click="isMobileMenuOpen = false" class="block text-base font-semibold text-[#1D2724]">Tentang Sarangan</router-link>
      <a v-else href="#tentang" @click="isMobileMenuOpen = false" class="block text-base font-semibold text-[#1D2724]">Tentang Sarangan</a>
      
      <hr class="border-[#173B35]/10" />
      
      <template v-if="authStore.isAuthenticated">
        <router-link to="/my-tickets" @click="isMobileMenuOpen = false" class="block text-base font-semibold text-[#1D2724]">E-Ticket Saya</router-link>
        <router-link to="/profile" @click="isMobileMenuOpen = false" class="block text-base font-semibold text-[#1D2724]">Profil Saya</router-link>
        <button @click="() => { isMobileMenuOpen = false; handleLogout(); }" class="block text-base font-semibold text-red-600 text-left w-full mt-4">Keluar</button>
      </template>
      <template v-else>
        <router-link to="/login?redirect=/booking" @click="isMobileMenuOpen = false" class="block text-base font-semibold text-[#1D2724]">Masuk</router-link>
      </template>
      
      <router-link to="/booking" @click="isMobileMenuOpen = false" class="block w-full text-center rounded-lg bg-[#173B35] px-5 py-3 text-base font-semibold text-white mt-4">Pesan Tiket</router-link>
    </div>
  </header>
</template>
