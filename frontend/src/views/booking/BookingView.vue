<script setup lang="ts">
import {
  AlertCircle,
  Calendar,
  ChevronRight,
  Info,
  LoaderCircle,
  Minus,
  Plus,
  Ticket,
} from 'lucide-vue-next'
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

import { createOrderApi, getTicketTypesApi } from '@/services/order.service'
import { useAuthStore } from '@/stores/auth'
import type { CreateOrderPayload, TicketType } from '@/types/booking.types'
import { formatCurrency, formatDate } from '@/utils/formatters'

const router = useRouter()
const authStore = useAuthStore()

// State
const isLoadingTickets = ref(true)
const isSubmitting = ref(false)
const errorMessage = ref('')
const ticketTypes = ref<TicketType[]>([])

// Min date: today in YYYY-MM-DD
const today: string = new Date().toISOString().slice(0, 10)

// Form data
const form = reactive({
  visit_date: today,
  customer_name: authStore.user?.name || '',
  customer_email: authStore.user?.email || '',
  customer_phone: authStore.user?.phone || '',
})

// Ticket quantities: key is ticket_type_id, value is quantity
const selectedQuantities = reactive<Record<number, number>>({})

// Fetch ticket types on mount
onMounted(async () => {
  try {
    isLoadingTickets.value = true
    const types = await getTicketTypesApi()
    ticketTypes.value = types
    types.forEach((type) => {
      selectedQuantities[type.id] = 0
    })
  } catch (error: unknown) {
    if (axios.isAxiosError(error) && error.response?.data?.message) {
      errorMessage.value = error.response.data.message as string
    } else {
      errorMessage.value = 'Gagal memuat jenis tiket. Silakan coba lagi.'
    }
  } finally {
    isLoadingTickets.value = false
  }

  // Pre-fill user data if available
  if (authStore.user) {
    if (!form.customer_name) form.customer_name = authStore.user.name || ''
    if (!form.customer_email) form.customer_email = authStore.user.email || ''
    if (!form.customer_phone && authStore.user.phone) form.customer_phone = authStore.user.phone
  }
})

// Calculations
const selectedItems = computed(() => {
  return ticketTypes.value
    .filter((t) => (selectedQuantities[t.id] ?? 0) > 0)
    .map((t) => {
      const qty = selectedQuantities[t.id] ?? 0
      return {
        ticketType: t,
        quantity: qty,
        subtotal: t.price * qty,
      }
    })
})

const totalQuantity = computed(() => {
  return Object.values(selectedQuantities).reduce((sum, qty) => sum + qty, 0)
})

const totalAmount = computed(() => {
  return selectedItems.value.reduce((sum, item) => sum + item.subtotal, 0)
})

const isFormValid = computed(() => {
  return (
    form.visit_date.length > 0 &&
    form.visit_date >= today &&
    form.customer_name.trim().length > 0 &&
    form.customer_email.trim().length > 0 &&
    form.customer_phone.trim().length >= 8 &&
    totalQuantity.value > 0
  )
})

// Quantity handlers
function incrementQty(ticket: TicketType) {
  const current = selectedQuantities[ticket.id] ?? 0
  if (current < ticket.quota) {
    selectedQuantities[ticket.id] = current + 1
  }
}

function decrementQty(ticket: TicketType) {
  const current = selectedQuantities[ticket.id] ?? 0
  if (current > 0) {
    selectedQuantities[ticket.id] = current - 1
  }
}

// Removed openReview function

async function handleConfirmBooking() {
  errorMessage.value = ''
  if (!isFormValid.value) {
    if (totalQuantity.value === 0) {
      errorMessage.value = 'Silakan pilih minimal 1 tiket terlebih dahulu.'
    } else {
      errorMessage.value = 'Mohon lengkapi seluruh data kunjungan dan data pemesan.'
    }
    return
  }
  isSubmitting.value = true
  errorMessage.value = ''

  const payload: CreateOrderPayload = {
    visit_date: form.visit_date,
    customer_name: form.customer_name.trim(),
    customer_email: form.customer_email.trim(),
    customer_phone: form.customer_phone.trim(),
    items: selectedItems.value.map((item) => ({
      ticket_type_id: item.ticketType.id,
      quantity: item.quantity,
    })),
  }

  try {
    const createdOrder = await createOrderApi(payload)
    
    // Redirect langsung ke payment gateway jika URL pembayaran Xendit tersedia
    if (createdOrder.payment_url && createdOrder.status === 'PENDING') {
      window.location.href = createdOrder.payment_url
    } else {
      void router.push({
        name: 'booking.success',
        params: { orderCode: createdOrder.order_code },
      })
    }
  } catch (error: unknown) {
    if (axios.isAxiosError(error)) {
      const status = error.response?.status
      if (status === 401) {
        errorMessage.value = 'Sesi Anda telah berakhir. Silakan login terlebih dahulu.'
        void router.push({ name: 'login', query: { redirect: '/booking' } })
      } else if (status === 409) {
        errorMessage.value = error.response?.data?.message || 'Kuota tiket tidak mencukupi untuk tanggal yang dipilih.'
      } else if (status === 422) {
        errorMessage.value = error.response?.data?.message || 'Terdapat data booking yang tidak valid.'
      } else {
        errorMessage.value = 'Terjadi kesalahan server. Silakan coba lagi nanti.'
      }
    } else {
      errorMessage.value = 'Terjadi kesalahan server. Silakan coba lagi nanti.'
    }
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="min-h-screen bg-[var(--color-background)] font-sans">
    <!-- Page Header -->  
    <div class="bg-white border-b border-[#173B35]/10 sticky top-[72px] z-10">
      <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between py-4">
          <div class="flex items-center gap-3">
            <router-link
              to="/"
              class="flex h-9 w-9 items-center justify-center rounded-lg border border-[#173B35]/15 text-[#173B35] transition hover:bg-[#F7F5EF]"
              title="Kembali ke Beranda"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
            </router-link>
            <div>
              <h1 class="text-base font-bold text-[var(--color-primary)]">Pemesanan Tiket</h1>
              <p class="text-xs text-[var(--color-text-secondary)]">Telaga Sarangan, Magetan</p>
            </div>
          </div>

          <router-link
            to="/my-tickets"
            class="hidden sm:inline-flex items-center gap-2 rounded-lg border border-[var(--color-border)] bg-white px-3.5 py-2 text-sm font-medium text-[var(--color-text-primary)] transition hover:border-[var(--color-secondary)]"
          >
            <Ticket class="h-4 w-4 text-[var(--color-primary)]" />
            Pesanan Saya
          </router-link>
        </div>
      </div>
    </div>

    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 py-8">

      <!-- Alert Error -->
      <div
        v-if="errorMessage"
        class="mb-6 flex items-start gap-3 rounded-[12px] border border-red-200 bg-red-50 p-4 text-red-700"
      >
        <AlertCircle class="h-5 w-5 flex-shrink-0 mt-0.5 text-red-600" />
        <div class="flex-1 text-sm font-medium">{{ errorMessage }}</div>
      </div>

      <!-- Grid Content -->
      <div class="grid grid-cols-1 gap-8 lg:grid-cols-12">
        <!-- Main Form (Left 7 Cols) -->
        <div class="space-y-6 lg:col-span-7">
          <!-- Step 1: Tanggal Kunjungan -->
          <div class="rounded-[12px] border border-[var(--color-border)] bg-white p-6">
            <div class="flex items-center gap-2 mb-4">
              <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[var(--color-primary)] text-xs font-bold text-white">1</span>
              <h2 class="text-lg font-semibold text-[var(--color-primary)]">Pilih Tanggal Kunjungan</h2>
            </div>

            <div>
              <label for="visit_date" class="block text-sm font-medium text-[var(--color-text-primary)] mb-1.5">
                Tanggal Rencana Kedatangan
              </label>
              <div class="relative">
                <input
                  id="visit_date"
                  v-model="form.visit_date"
                  type="date"
                  :min="today"
                  required
                  class="w-full rounded-[10px] border border-[var(--color-border)] bg-[var(--color-background)] px-4 py-2.5 text-sm text-[var(--color-text-primary)] outline-none transition focus:border-[var(--color-secondary)] focus:bg-white focus:ring-4 focus:ring-[var(--color-secondary)]/10"
                />
              </div>
              <p class="mt-2 flex items-center gap-1.5 text-xs text-[var(--color-text-secondary)]">
                <Info class="h-3.5 w-3.5 text-[var(--color-primary)]" /> Tiket berlaku pada tanggal yang dipilih.
              </p>
            </div>
          </div>

          <!-- Step 2: Pilih Jenis Tiket -->
          <div class="rounded-[12px] border border-[var(--color-border)] bg-white p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-2">
                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[var(--color-primary)] text-xs font-bold text-white">2</span>
                <h2 class="text-lg font-semibold text-[var(--color-primary)]">Pilih Jenis Tiket</h2>
              </div>
              <span class="text-xs font-medium text-[var(--color-text-secondary)]">Harga resmi Perda</span>
            </div>

            <!-- Loading State -->
            <div v-if="isLoadingTickets" class="py-12 text-center text-[var(--color-text-secondary)]">
              <LoaderCircle class="mx-auto h-8 w-8 animate-spin text-[var(--color-primary)]" />
              <p class="mt-2 text-sm">Memuat tarif tiket...</p>
            </div>

            <!-- Ticket List -->
            <div v-else class="space-y-4">
              <div
                v-for="ticket in ticketTypes"
                :key="ticket.id"
                class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 rounded-[12px] border p-4 transition"
                :class="(selectedQuantities[ticket.id] ?? 0) > 0 ? 'border-[var(--color-secondary)] bg-[var(--color-background)]' : 'border-[var(--color-border)] hover:border-[var(--color-secondary)]'"
              >
                <div class="flex-1">
                  <div class="flex items-center gap-2">
                    <h3 class="font-semibold text-[var(--color-text-primary)]">{{ ticket.name }}</h3>
                    <span class="inline-flex items-center rounded-full bg-[var(--color-primary)]/10 px-2 py-0.5 text-xs font-medium text-[var(--color-primary)] border border-[var(--color-primary)]/20">
                      Tersedia
                    </span>
                  </div>
                  <p v-if="ticket.description" class="mt-1 text-xs text-[var(--color-text-secondary)]">
                    {{ ticket.description }}
                  </p>
                  <p class="mt-2 text-base font-bold text-[var(--color-primary)]">
                    {{ formatCurrency(ticket.price) }}
                    <span class="text-xs font-normal text-[var(--color-text-secondary)]">/ orang</span>
                  </p>
                </div>

                <!-- Stepper Counter -->
                <div class="flex items-center justify-end gap-3 self-end sm:self-center">
                  <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-[10px] border border-[var(--color-border)] bg-white text-[var(--color-text-primary)] transition hover:bg-[var(--color-background)] disabled:opacity-40"
                    :disabled="(selectedQuantities[ticket.id] ?? 0) <= 0"
                    @click="decrementQty(ticket)"
                  >
                    <Minus class="h-4 w-4" />
                  </button>
                  <span class="w-8 text-center font-bold text-[var(--color-text-primary)] text-base">
                    {{ selectedQuantities[ticket.id] ?? 0 }}
                  </span>
                  <button
                    type="button"
                    class="flex h-9 w-9 items-center justify-center rounded-[10px] bg-[var(--color-primary)] text-white transition hover:bg-[#122c27] disabled:opacity-40"
                    :disabled="(selectedQuantities[ticket.id] ?? 0) >= ticket.quota"
                    @click="incrementQty(ticket)"
                  >
                    <Plus class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Step 3: Data Pengunjung -->
          <div class="rounded-[12px] border border-[var(--color-border)] bg-white p-6">
            <div class="flex items-center gap-2 mb-4">
              <span class="flex h-7 w-7 items-center justify-center rounded-full bg-[var(--color-primary)] text-xs font-bold text-white">3</span>
              <h2 class="text-lg font-semibold text-[var(--color-primary)]">Data Pemesan / Penanggung Jawab</h2>
            </div>

            <div class="space-y-4">
              <div>
                <label for="name" class="block text-sm font-medium text-[var(--color-text-primary)] mb-1">
                  Nama Lengkap Pemesan <span class="text-red-500">*</span>
                </label>
                <input
                  id="name"
                  v-model="form.customer_name"
                  type="text"
                  required
                  placeholder="Contoh: Budi Santoso"
                  class="w-full rounded-[10px] border border-[var(--color-border)] px-4 py-3 text-sm text-[var(--color-text-primary)] outline-none transition focus:border-[var(--color-secondary)] focus:ring-4 focus:ring-[var(--color-secondary)]/10 bg-[var(--color-background)] focus:bg-white"
                />
              </div>

              <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <label for="email" class="block text-sm font-medium text-[var(--color-text-primary)] mb-1">
                    Alamat Email <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="email"
                    v-model="form.customer_email"
                    type="email"
                    required
                    placeholder="nama@email.com"
                    class="w-full rounded-[10px] border border-[var(--color-border)] px-4 py-3 text-sm text-[var(--color-text-primary)] outline-none transition focus:border-[var(--color-secondary)] focus:ring-4 focus:ring-[var(--color-secondary)]/10 bg-[var(--color-background)] focus:bg-white"
                  />
                </div>

                <div>
                  <label for="phone" class="block text-sm font-medium text-[var(--color-text-primary)] mb-1">
                    No. Handphone / WhatsApp <span class="text-red-500">*</span>
                  </label>
                  <input
                    id="phone"
                    v-model="form.customer_phone"
                    type="tel"
                    required
                    placeholder="08123456789"
                    class="w-full rounded-[10px] border border-[var(--color-border)] px-4 py-3 text-sm text-[var(--color-text-primary)] outline-none transition focus:border-[var(--color-secondary)] focus:ring-4 focus:ring-[var(--color-secondary)]/10 bg-[var(--color-background)] focus:bg-white"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar Summary (Right 5 Cols) -->
        <div class="lg:col-span-5">
          <div class="sticky top-28 rounded-[12px] border border-[var(--color-border)] bg-white p-6">
            <h2 class="text-lg font-bold text-[var(--color-primary)] pb-3 border-b border-[var(--color-border)]">
              Ringkasan Pesanan
            </h2>

            <div class="mt-4 space-y-3">
              <div class="flex items-center justify-between text-sm text-[var(--color-text-secondary)]">
                <span class="flex items-center gap-1.5">
                  <Calendar class="h-4 w-4" /> Tanggal
                </span>
                <span class="font-medium text-[var(--color-text-primary)]">{{ formatDate(form.visit_date) || '-' }}</span>
              </div>

              <div class="border-t border-dashed border-[var(--color-border)] my-3" />

              <!-- Items Breakdown -->
              <div v-if="selectedItems.length === 0" class="py-6 text-center text-xs text-[var(--color-text-secondary)]">
                Belum ada tiket yang dipilih
              </div>

              <div v-else class="space-y-2.5">
                <div
                  v-for="item in selectedItems"
                  :key="item.ticketType.id"
                  class="flex items-center justify-between text-sm"
                >
                  <div>
                    <span class="font-medium text-[var(--color-text-primary)]">{{ item.ticketType.name }}</span>
                    <div class="text-xs text-[var(--color-text-secondary)]">
                      {{ item.quantity }} x {{ formatCurrency(item.ticketType.price) }}
                    </div>
                  </div>
                  <span class="font-semibold text-[var(--color-text-primary)]">{{ formatCurrency(item.subtotal) }}</span>
                </div>
              </div>

              <div class="border-t border-[var(--color-border)] my-4" />

              <!-- Total Row -->
              <div class="flex items-center justify-between text-sm text-[var(--color-text-secondary)]">
                <span>Total Tiket</span>
                <span class="font-bold text-[var(--color-text-primary)]">{{ totalQuantity }} orang</span>
              </div>

              <div class="flex items-baseline justify-between pt-1">
                <span class="text-base font-bold text-[var(--color-text-primary)]">Total Biaya</span>
                <span class="text-2xl font-extrabold text-[var(--color-primary)]">{{ formatCurrency(totalAmount) }}</span>
              </div>
            </div>

            <!-- Action Button -->
            <button
              type="button"
              class="mt-6 flex w-full items-center justify-center gap-2 rounded-[10px] bg-[var(--color-primary)] py-3 text-sm font-semibold text-white transition hover:bg-[#122c27] disabled:cursor-not-allowed disabled:opacity-50"
              :disabled="!isFormValid || isSubmitting"
              @click="handleConfirmBooking"
            >
              <LoaderCircle v-if="isSubmitting" class="h-4 w-4 animate-spin" />
              <span v-else>Langsung ke Pembayaran</span>
              <ChevronRight v-if="!isSubmitting" class="h-4 w-4" />
            </button>

            <p class="mt-3 text-center text-xs text-[var(--color-text-secondary)]">
              Anda akan diarahkan ke halaman pembayaran aman (Xendit).
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
