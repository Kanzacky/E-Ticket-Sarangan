<script setup lang="ts">
import { useAuthStore } from '@/stores/auth'
import { useAdminDashboard } from '@/composables/useAdminDashboard'
import { Banknote, Ticket, Users } from 'lucide-vue-next'

const { isLoading, summary, recentOrders, error } = useAdminDashboard()
const authStore = useAuthStore()
</script>

<template>
  <div class="py-8">
    <!-- Header -->
    <header class="mb-6">
      <div class="flex items-center justify-between">
        <h2 class="text-2xl font-bold text-[var(--color-primary)]">Dashboard Admin</h2>
        <div class="flex items-center gap-3">
          <span class="hidden text-sm font-medium text-[var(--color-text-secondary)] sm:block">{{ authStore.user?.name }}</span>
          <span class="hidden rounded-[10px] bg-[var(--color-primary)]/10 px-2 py-0.5 text-xs font-semibold text-[var(--color-primary)] sm:block uppercase">{{ authStore.role }}</span>
        </div>
      </div>
    </header>

    <!-- Error Message -->
    <div v-if="error" class="mb-4 rounded-xl bg-red-50 p-4 text-red-700">
      <p class="text-sm">{{ error }}</p>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="mb-6">
      <div class="flex items-center justify-center gap-3">
        <svg class="h-5 w-5 animate-spin text-sky-500" viewBox="0 0 24 24">
          <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="3"/>
          <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z" fill="none" stroke="currentColor" stroke-width="3"/>
        </svg>
        <span>Memuat dashboard...</span>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <!-- Revenue Card -->
      <div class="rounded-[12px] border border-[var(--color-border)] bg-white p-6">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-[10px] bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
            <Banknote class="h-6 w-6 text-[#173B35]" />
          </div>
          <div>
            <p class="text-sm font-medium text-[var(--color-text-secondary)]">Pendapatan Hari Ini</p>
            <p class="text-2xl font-bold text-[var(--color-text-primary)]">{{ formatCurrency(summary.revenue) }}</p>
          </div>
        </div>
      </div>

      <!-- Orders Card -->
      <div class="rounded-[12px] border border-[var(--color-border)] bg-white p-6">
        <div class="flex items-center justify-between">
          <p class="text-sm font-medium text-[var(--color-text-secondary)]">Total Pesanan</p>
          <p class="text-2xl font-bold text-[var(--color-text-primary)]">{{ summary.orders }}</p>
        </div>
      </div>

      <!-- Tickets Card -->
      <div class="rounded-[12px] border border-[var(--color-border)] bg-white p-6">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-[10px] bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
            <Ticket class="h-6 w-6 text-[#173B35]" />
          </div>
          <div>
            <p class="text-sm font-medium text-[var(--color-text-secondary)]">Total Tiket Terjual</p>
            <p class="text-2xl font-bold text-[var(--color-text-primary)]">{{ summary.tickets }}</p>
          </div>
        </div>
      </div>

      <!-- Visitors Card -->
      <div class="rounded-[12px] border border-[var(--color-border)] bg-white p-6">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-[10px] bg-[var(--color-primary)]/10 text-[var(--color-primary)]">
            <Users class="h-6 w-6 text-[#173B35]" />
          </div>
          <div>
            <p class="text-sm font-medium text-[var(--color-text-secondary)]">Total Pengunjung</p>
            <p class="text-2xl font-bold text-[var(--color-text-primary)]">{{ summary.visitors }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Orders Section -->
    <div class="mt-8 rounded-[12px] border border-[var(--color-border)] bg-white overflow-hidden">
      <div class="px-6 py-5 border-b border-[var(--color-border)]">
        <h3 class="text-lg font-bold text-[var(--color-text-primary)]">Pesanan Terbaru</h3>
      </div>

      <div class="p-4">
        <div v-if="isLoading" class="h-12 w-12 flex items-center justify-center">
          <svg class="h-5 w-5 animate-spin text-sky-500" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="3"/>
            <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z" fill="none" stroke="currentColor" stroke-width="3"/>
          </svg>
        </div>

        <div v-else-if="recentOrders.length === 0" class="py-8 text-center text-sm text-[var(--color-text-secondary)]">
          <svg class="h-6 w-6 mx-auto mb-3 text-[var(--color-text-secondary)]/50" viewBox="0 0 24 24">
            <path d="M19 13h-6l-2-2L6 18l2 2H2v-4h12v4h-4z" />
          </svg>
          <p>Belum ada transaksi</p>
          <p class="mt-1 text-xs text-[var(--color-text-secondary)]/70">Transaksi terbaru akan muncul di sini.</p>
        </div>

        <div v-else class="overflow-x-auto">
          <table class="w-full text-left text-sm text-[var(--color-text-secondary)]">
            <thead class="bg-[var(--color-background)] text-xs uppercase text-[var(--color-text-primary)]">
              <tr>
                <th scope="col" class="px-6 py-4 font-bold">Kode Booking</th>
                <th scope="col" class="px-6 py-4 font-bold">Nama</th>
                <th scope="col" class="px-6 py-4 font-bold">Tgl. Kunjungan</th>
                <th scope="col" class="px-6 py-4 font-bold">Jumlah</th>
                <th scope="col" class="px-6 py-4 font-bold">Total</th>
                <th scope="col" class="px-6 py-4 font-bold">Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="order in recentOrders" :key="order.id" class="border-b border-[var(--color-border)] last:border-0">
                <td class="px-6 py-4 font-medium text-[var(--color-text-primary)]">{{ order.order_code }}</td>
                <td class="px-6 py-4">{{ order.customer_name }}</td>
                <td class="px-6 py-4">{{ formatDate(order.visit_date) }}</td>
                <td class="px-6 py-4">{{ order.total_quantity }} orang</td>
                <td class="px-6 py-4">{{ formatCurrency(order.total_amount) }}</td>
                <td class="px-6 py-4">
                  <span 
                    class="inline-flex rounded-[10px] px-2 py-1 text-xs font-semibold"
                    :class="order.status === 'PAID' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : order.status === 'CANCELLED' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                  >
                    {{ order.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
export default {
  methods: {
    formatCurrency(value: number) {
      return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(value)
    },
    formatDate(dateString: string) {
      if (!dateString) return '-'
      return new Date(dateString).toLocaleDateString('id-ID')
    }
  }
}
</script>