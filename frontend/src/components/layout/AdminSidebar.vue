<script setup lang="ts">
import { useRoute } from 'vue-router'
import { 
  LayoutDashboard, 
  ShoppingCart, 
  Ticket, 
  Package,
  Building,
  Users,
  ShieldCheck, 
  CreditCard, 
  BarChart3, 
  Settings,
  X 
} from 'lucide-vue-next'

const props = defineProps<{
  isOpen: boolean
}>()

const emit = defineEmits<{
  (e: 'close'): void
}>()

const route = useRoute()

const navGroups = [
  {
    title: 'UTAMA',
    items: [
      { name: 'Dashboard', path: '/admin/dashboard', icon: LayoutDashboard }
    ]
  },
  {
    title: 'TRANSAKSI',
    items: [
      { name: 'Pesanan', path: '/admin/bookings', icon: ShoppingCart },
      { name: 'Pembayaran', path: '/admin/payments', icon: CreditCard }
    ]
  },
  {
    title: 'PRODUK',
    items: [
      { name: 'Tiket Dasar', path: '/admin/tickets', icon: Ticket },
      { name: 'Paket Wisata', path: '/admin/ticket-categories', icon: Package },
      { name: 'Penginapan', path: '/admin/accommodations', icon: Building }
    ]
  },
  {
    title: 'PENGGUNA',
    items: [
      { name: 'Wisatawan', path: '/admin/users', icon: Users },
      { name: 'Petugas', path: '/admin/petugas', icon: ShieldCheck }
    ]
  },
  {
    title: 'ANALISIS',
    items: [
      { name: 'Laporan', path: '/admin/reports', icon: BarChart3 }
    ]
  },
  {
    title: 'SISTEM',
    items: [
      { name: 'Pengaturan', path: '/admin/settings', icon: Settings }
    ]
  }
]

const isActive = (path: string) => {
  return route.path.startsWith(path)
}
</script>

<template>
  <!-- Mobile Backdrop -->
  <div 
    v-if="isOpen" 
    @click="emit('close')"
    class="fixed inset-0 bg-[#1D2724]/40 backdrop-blur-sm z-40 lg:hidden"
  ></div>

  <!-- Sidebar -->
  <aside 
    class="fixed lg:sticky top-0 left-0 z-50 h-screen w-[260px] bg-[#F7F5EF] border-r border-[#E8E6DE] flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0"
    :class="isOpen ? 'translate-x-0' : '-translate-x-full'"
  >
    <!-- Logo Area -->
    <div class="h-[72px] px-6 flex items-center justify-between border-b border-[#E8E6DE] shrink-0">
      <router-link to="/admin/dashboard" class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-[#173B35] flex items-center justify-center overflow-hidden border border-[#173B35]/20">
          <img src="/images/logo.png" alt="Logo" class="w-full h-full object-cover bg-white" />
        </div>
        <div>
          <h1 class="text-sm font-bold text-[#173B35] tracking-tight leading-tight">e-Ticket Sarangan</h1>
          <p class="text-[10px] font-medium text-[#C9965B] uppercase tracking-wider">Administrator</p>
        </div>
      </router-link>
      <button @click="emit('close')" class="lg:hidden p-1 text-[#66706C] hover:text-[#173B35]">
        <X class="w-5 h-5" />
      </button>
    </div>

    <!-- Navigation Links -->
    <div class="flex-1 overflow-y-auto py-6 px-4 custom-scrollbar">
      <div v-for="(group, gIdx) in navGroups" :key="gIdx" class="mb-6 last:mb-0">
        <p class="px-3 text-[11px] font-bold text-[#66706C] uppercase tracking-wider mb-2">
          {{ group.title }}
        </p>
        <div class="space-y-1">
          <router-link
            v-for="item in group.items"
            :key="item.path"
            :to="item.path"
            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors"
            :class="isActive(item.path) 
              ? 'bg-[#173B35] text-white' 
              : 'text-[#66706C] hover:bg-white hover:text-[#173B35] hover:shadow-sm'"
          >
            <component 
              :is="item.icon" 
              class="w-4 h-4 shrink-0" 
              :class="isActive(item.path) ? 'text-[#C9965B]' : 'text-[#66706C]'" 
            />
            {{ item.name }}
          </router-link>
        </div>
      </div>
    </div>
    
  </aside>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
  width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: #E8E6DE;
  border-radius: 4px;
}
.custom-scrollbar:hover::-webkit-scrollbar-thumb {
  background: #D1CFCD;
}
</style>
