<script setup lang="ts">
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>

<template>
  <footer class="bg-white py-16 mt-auto">
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
          <router-link to="/" class="hover:text-[#C9965B]">Beranda</router-link>
          <router-link v-if="$route.name !== 'home'" to="/#tiket" class="hover:text-[#C9965B]">Tiket</router-link>
          <a v-else href="#tiket" class="hover:text-[#C9965B]">Tiket</a>
          <router-link v-if="$route.name !== 'home'" to="/#tentang" class="hover:text-[#C9965B]">Tentang</router-link>
          <a v-else href="#tentang" class="hover:text-[#C9965B]">Tentang</a>
          <router-link to="/booking" class="hover:text-[#C9965B]">Pesan Tiket</router-link>
        </div>
      </div>
      
      <div class="pt-8 border-t border-[#173B35]/10 flex flex-col md:flex-row items-center justify-between gap-4 text-sm text-[#8B928F]">
        <p>&copy; {{ new Date().getFullYear() }} {{ t('app.name') }}. Pariwisata Magetan.</p>
      </div>
    </div>
  </footer>
</template>
