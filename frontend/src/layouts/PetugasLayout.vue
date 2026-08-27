<script setup lang="ts">
import { ref, computed } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import PetugasSidebar from '@/components/layout/PetugasSidebar.vue'
import PetugasTopbar from '@/components/layout/PetugasTopbar.vue'

const sidebarOpen = ref(false)
const route = useRoute()

// Generate a simple page title based on the route path/name
const pageTitle = computed(() => {
  if (route.name === 'petugas.dashboard') return 'Dashboard'
  if (route.name === 'petugas.scanner') return 'Scan Tiket'
  if (route.name === 'petugas.checkins') return 'Kunjungan Hari Ini'
  if (route.name === 'petugas.bookings') return 'Booking'
  if (route.name === 'petugas.ticket-detail') return 'Detail Tiket'
  if (route.name === 'petugas.users') return 'Wisatawan'
  if (route.name === 'petugas.history') return 'Riwayat'
  if (route.name === 'petugas.profile') return 'Profil Saya'
  return 'Operasional'
})
</script>

<template>
  <div class="flex min-h-screen bg-[#F7F5EF] text-[#1D2724] font-sans selection:bg-[#4F7465] selection:text-white">
    <!-- Sidebar Component -->
    <PetugasSidebar 
      :is-open="sidebarOpen" 
      @close="sidebarOpen = false" 
    />

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
      
      <!-- Topbar Component -->
      <PetugasTopbar 
        :page-title="pageTitle" 
        @toggle-sidebar="sidebarOpen = !sidebarOpen" 
      />

      <!-- Page Content -->
      <main class="flex-1 p-5 sm:p-6 lg:p-8 overflow-y-auto relative">
        <RouterView v-slot="{ Component }">
          <Transition 
            enter-active-class="transition-opacity duration-200" 
            enter-from-class="opacity-0" 
            leave-active-class="transition-opacity duration-150" 
            leave-to-class="opacity-0" 
            mode="out-in"
          >
            <component :is="Component" />
          </Transition>
        </RouterView>
      </main>

    </div>
  </div>
</template>
