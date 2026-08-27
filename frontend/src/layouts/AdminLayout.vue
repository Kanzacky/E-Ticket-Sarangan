<script setup lang="ts">
import { ref, computed } from 'vue'
import { RouterView, useRoute } from 'vue-router'
import AdminSidebar from '@/components/layout/AdminSidebar.vue'
import AdminTopbar from '@/components/layout/AdminTopbar.vue'

const sidebarOpen = ref(false)
const route = useRoute()

// Generate a simple page title based on the route path/name
const pageTitle = computed(() => {
  if (route.name === 'admin.dashboard') return 'Dashboard'
  if (route.name === 'admin.bookings') return 'Pesanan'
  if (route.name === 'admin.tickets') return 'Tiket'
  if (route.name === 'admin.ticket-categories') return 'Paket Wisata'
  if (route.name === 'admin.users') return 'Wisatawan'
  if (route.name === 'admin.petugas') return 'Petugas'
  if (route.name === 'admin.payments') return 'Pembayaran'
  if (route.name === 'admin.reports') return 'Laporan'
  if (route.name === 'admin.settings') return 'Pengaturan'
  return 'Admin Panel'
})
</script>

<template>
  <div class="flex min-h-screen bg-[#F7F5EF] text-[#1D2724] font-sans selection:bg-[#4F7465] selection:text-white">
    <!-- Sidebar Component -->
    <AdminSidebar 
      :is-open="sidebarOpen" 
      @close="sidebarOpen = false" 
    />

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 transition-all duration-300">
      
      <!-- Topbar Component -->
      <AdminTopbar 
        :page-title="pageTitle" 
        @toggle-sidebar="sidebarOpen = !sidebarOpen" 
      />

      <!-- Page Content -->
      <main class="flex-1 p-5 sm:p-6 lg:p-8 overflow-y-auto">
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
