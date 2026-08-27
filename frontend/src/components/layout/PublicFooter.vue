<script setup lang="ts">
import { useI18n } from 'vue-i18n'
import { MapPin, Clock } from 'lucide-vue-next'
const { t } = useI18n()
</script>

<template>
  <footer class="bg-[#173B35] text-[#F7F5EF] pt-16 pb-8 mt-auto">
    <div class="mx-auto max-w-[1240px] px-5 sm:px-6 lg:px-8">

      <!-- Top Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mb-12">

        <!-- Brand -->
        <div>
          <div class="flex items-center gap-3 mb-4">
            <div
              class="shrink-0 bg-white"
              style="width:44px;height:44px;border-radius:50%;overflow:hidden;border:2px solid rgba(255,255,255,0.3);display:flex;align-items:center;justify-content:center;"
            >
              <img
                src="/images/logo.png"
                alt="e-Ticket Sarangan"
                style="width:100%;height:100%;object-fit:cover;"
              />
            </div>
            <div>
              <span class="block text-base font-bold text-[#C9965B]">{{ t('app.name') }}</span>
              <span class="block text-xs text-white/50">Sistem Tiket Wisata Digital</span>
            </div>
          </div>
          <p class="text-sm text-white/60 leading-relaxed max-w-xs">
            Platform pemesanan tiket resmi Telaga Sarangan. Pesan online, bayar aman, tunjukkan QR di gerbang.
          </p>
        </div>

        <!-- Navigasi -->
        <div>
          <h4 class="text-xs font-bold uppercase tracking-widest text-[#C9965B] mb-4">Navigasi</h4>
          <ul class="space-y-3 text-sm text-white/70">
            <li><router-link to="/" class="hover:text-white transition-colors">Beranda</router-link></li>
            <li>
              <router-link v-if="$route.name !== 'home'" to="/#tiket" class="hover:text-white transition-colors">Tiket Wisata</router-link>
              <a v-else href="#tiket" class="hover:text-white transition-colors">Tiket Wisata</a>
            </li>
            <li>
              <router-link v-if="$route.name !== 'home'" to="/#tentang" class="hover:text-white transition-colors">Tentang Sarangan</router-link>
              <a v-else href="#tentang" class="hover:text-white transition-colors">Tentang Sarangan</a>
            </li>
            <li>
              <router-link v-if="$route.name !== 'home'" to="/#cara-pesan" class="hover:text-white transition-colors">Cara Pemesanan</router-link>
              <a v-else href="#cara-pesan" class="hover:text-white transition-colors">Cara Pemesanan</a>
            </li>
            <li><router-link to="/my-tickets" class="hover:text-white transition-colors">Pesanan Saya</router-link></li>
          </ul>
        </div>

        <!-- Info Lokasi -->
        <div>
          <h4 class="text-xs font-bold uppercase tracking-widest text-[#C9965B] mb-4">Informasi</h4>
          <ul class="space-y-3 text-sm text-white/70">
            <li class="flex items-start gap-2">
              <MapPin class="w-4 h-4 text-[#C9965B] shrink-0 mt-0.5" />
              <span>Jl. Raya Telaga Sarangan, Kec. Plaosan, Magetan, Jawa Timur 63361</span>
            </li>
            <li class="flex items-center gap-2">
              <Clock class="w-4 h-4 text-[#C9965B] shrink-0" />
              <span>Buka setiap hari, 06.00 – 18.00 WIB</span>
            </li>
          </ul>
        </div>
      </div>

      <!-- Bottom -->
      <div class="pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-white/40">
        <p>&copy; {{ new Date().getFullYear() }} {{ t('app.name') }}. Dinas Pariwisata Magetan.</p>
        <p>Seluruh harga adalah harga resmi sesuai Peraturan Daerah.</p>
      </div>
    </div>
  </footer>
</template>
