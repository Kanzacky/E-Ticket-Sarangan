<script setup lang="ts">
import { MapPin, Star, Search, ArrowUpDown, ExternalLink, Map } from 'lucide-vue-next'
import { onMounted, ref, watch } from 'vue'
import { getAccommodationsApi, type Accommodation, type PaginatedMeta } from '@/services/accommodation.service'
import Pagination from '@/components/ui/Pagination.vue'
import PublicNavbar from '@/components/layout/PublicNavbar.vue'
import PublicFooter from '@/components/layout/PublicFooter.vue'
import axios from 'axios'

const accommodations = ref<Accommodation[]>([])
const isLoading = ref(true)
const errorMessage = ref('')
const searchQuery = ref('')
const sortBy = ref<string>('rating')
const meta = ref<PaginatedMeta>({ current_page: 1, last_page: 1, per_page: 12, total: 0 })
let searchTimer: ReturnType<typeof setTimeout> | null = null

async function fetchAccommodations() {
  try {
    isLoading.value = true
    const result = await getAccommodationsApi({
      page: meta.value.current_page,
      per_page: 12,
      search: searchQuery.value.trim() || undefined,
      sort: sortBy.value || undefined,
    })
    accommodations.value = result.data
    meta.value = result.meta
  } catch (error: unknown) {
    if (axios.isAxiosError(error) && error.response?.data?.message) {
      errorMessage.value = error.response.data.message as string
    } else {
      errorMessage.value = 'Gagal memuat daftar penginapan.'
    }
  } finally {
    isLoading.value = false
  }
}

function goToPage(page: number) {
  meta.value.current_page = page
  fetchAccommodations()
}

function handleSortChange(sort: string) {
  sortBy.value = sort
  meta.value.current_page = 1
  fetchAccommodations()
}

watch(searchQuery, () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    meta.value.current_page = 1
    fetchAccommodations()
  }, 400)
})

onMounted(() => void fetchAccommodations())

const formatPrice = (price: number) => new Intl.NumberFormat('id-ID').format(price)

function openGoogleMaps(item: Accommodation & { google_maps_link?: string }) {
  const url = item.google_maps_link ||
    `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(item.name + ' ' + item.address)}`
  window.open(url, '_blank', 'noopener,noreferrer')
}

function renderStars(rating: number): string[] {
  return Array.from({ length: 5 }, (_, i) => {
    if (i < Math.floor(rating)) return 'full'
    if (i < rating) return 'half'
    return 'empty'
  })
}
</script>

<template>
  <div class="min-h-screen bg-[#F7F5EF]">
    <PublicNavbar />

    <div class="pt-28 pb-16 px-5 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-[1240px]">

        <!-- Header -->
        <div class="mb-10">
          <p class="text-[#C9965B] text-xs font-bold uppercase tracking-widest mb-2">Rekomendasi Penginapan</p>
          <h1 class="text-3xl md:text-4xl font-black text-[#173B35] mb-3">Penginapan & Villa Terbaik</h1>
          <p class="text-[#66706C] max-w-lg text-base leading-relaxed">
            Rekomendasi penginapan dan villa di sekitar Telaga Sarangan berdasarkan rating terbaik.
            Klik <strong>Buka di Google Maps</strong> untuk melihat ulasan, foto, dan informasi lengkap.
          </p>
        </div>

        <!-- Info Banner -->
        <div class="flex items-start gap-3 bg-[#173B35]/5 border border-[#173B35]/15 rounded-xl p-4 mb-8">
          <Map class="w-5 h-5 text-[#173B35] mt-0.5 shrink-0" />
          <div class="text-sm text-[#4F7465]">
            <span class="font-semibold text-[#173B35]">Data dari OpenStreetMap.</span>
            Penginapan diurutkan berdasarkan rating tertinggi. Klik tombol untuk melihat ulasan lengkap di Google Maps.
          </div>
        </div>

        <!-- Search & Sort -->
        <div v-if="!isLoading && !errorMessage" class="flex flex-col sm:flex-row gap-4 mb-7">
          <div class="relative w-full sm:w-72">
            <Search class="w-4 h-4 absolute left-3 top-2.5 text-[#66706C]" />
            <input
              v-model="searchQuery"
              placeholder="Cari penginapan..."
              class="w-full pl-9 pr-3 py-2 text-sm border border-[#E8E6DE] rounded-lg bg-white focus:ring-1 focus:ring-[#173B35] focus:outline-none"
            />
          </div>
          <div class="relative w-full sm:w-48">
            <ArrowUpDown class="w-4 h-4 absolute left-3 top-2.5 text-[#66706C]" />
            <select
              v-model="sortBy"
              @change="handleSortChange(sortBy)"
              class="w-full pl-9 pr-8 py-2 text-sm border border-[#E8E6DE] rounded-lg bg-white focus:ring-1 focus:ring-[#173B35] appearance-none focus:outline-none"
            >
              <option value="rating">Rating Tertinggi</option>
              <option value="distance">Terdekat dari Sarangan</option>
              <option value="price_asc">Harga Terendah</option>
              <option value="price_desc">Harga Tertinggi</option>
            </select>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="isLoading" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="i in 6" :key="i" class="rounded-xl bg-white border border-[#E8E6DE] overflow-hidden animate-pulse">
            <div class="h-44 bg-[#E8E6DE]"></div>
            <div class="p-5 space-y-3">
              <div class="h-5 bg-[#E8E6DE] rounded w-3/4"></div>
              <div class="h-4 bg-[#E8E6DE] rounded w-full"></div>
              <div class="h-4 bg-[#E8E6DE] rounded w-1/2"></div>
            </div>
          </div>
        </div>

        <!-- Error -->
        <div v-else-if="errorMessage" class="py-16 text-center text-red-600 bg-white rounded-xl border border-red-200">
          <p class="font-medium">{{ errorMessage }}</p>
          <button @click="fetchAccommodations()" class="mt-3 text-sm underline">Coba lagi</button>
        </div>

        <!-- Grid -->
        <div v-else-if="accommodations.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="item in accommodations"
            :key="item.id"
            class="group flex flex-col border border-[#E8E6DE] rounded-xl bg-white overflow-hidden transition-all hover:border-[#4F7465] hover:shadow-lg"
          >
            <!-- Card Header -->
            <div class="relative h-44 bg-gradient-to-br from-[#173B35] to-[#2D6A5A] p-5 flex flex-col justify-between overflow-hidden">
              <!-- Decorative pattern -->
              <div class="absolute inset-0 opacity-10"
                style="background-image: radial-gradient(circle at 20% 50%, #fff 1px, transparent 1px), radial-gradient(circle at 80% 20%, #fff 1px, transparent 1px); background-size: 40px 40px;"></div>
              <div class="absolute inset-0 bg-gradient-to-t from-[#1D2724]/80 to-transparent"></div>

              <!-- Rating Badge -->
              <div class="relative z-10 flex justify-between items-start">
                <span class="inline-flex items-center gap-1.5 rounded-full bg-[#C9965B] px-2.5 py-1 text-xs font-bold text-white">
                  <Star class="h-3.5 w-3.5 fill-white" /> {{ item.rating }}
                </span>
                <span v-if="item.distance_km" class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold text-white backdrop-blur-md border border-white/20">
                  {{ item.distance_km }} km
                </span>
              </div>

              <!-- Name & Address -->
              <div class="relative z-10">
                <h3 class="text-lg font-bold text-white mb-1 leading-tight">{{ item.name }}</h3>
                <p class="text-xs text-white/80 flex items-center gap-1">
                  <MapPin class="h-3 w-3 shrink-0" /> {{ item.address }}
                </p>
              </div>
            </div>

            <!-- Card Body -->
            <div class="p-5 flex-1 flex flex-col justify-between gap-4">
              <!-- Description -->
              <p class="text-sm text-[#66706C] line-clamp-2 leading-relaxed">
                {{ item.description || `Penginapan di sekitar Telaga Sarangan, Magetan.` }}
              </p>

              <!-- Star Rating Visual -->
              <div class="flex items-center gap-1">
                <template v-for="(type, i) in renderStars(Number(item.rating))" :key="i">
                  <svg
                    :class="type === 'empty' ? 'text-[#E8E6DE]' : 'text-[#C9965B]'"
                    class="w-4 h-4"
                    viewBox="0 0 24 24"
                    :fill="type === 'full' ? 'currentColor' : 'none'"
                    :stroke="type === 'empty' ? 'currentColor' : 'none'"
                    stroke-width="1.5"
                  >
                    <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                  </svg>
                </template>
                <span class="text-xs text-[#66706C] ml-1">{{ item.rating }} / 5.0</span>
              </div>

              <!-- Facilities -->
              <div v-if="item.facilities?.length" class="flex flex-wrap gap-1.5">
                <span
                  v-for="fac in item.facilities.slice(0, 3)"
                  :key="fac"
                  class="rounded-md bg-[#F7F5EF] px-2 py-1 text-[11px] font-medium text-[#4F7465]"
                >
                  {{ fac }}
                </span>
                <span v-if="item.facilities.length > 3" class="rounded-md bg-[#F7F5EF] px-2 py-1 text-[11px] font-medium text-[#4F7465]">
                  +{{ item.facilities.length - 3 }} lagi
                </span>
              </div>

              <!-- CTA -->
              <div class="pt-4 border-t border-[#E8E6DE] flex items-center justify-between mt-auto">
                <div>
                  <span class="text-xs text-[#66706C] block mb-0.5">Estimasi mulai dari</span>
                  <span class="text-base font-black text-[#173B35]">
                    Rp{{ formatPrice(item.price_per_night) }}
                    <span class="text-xs font-normal text-[#66706C]">/malam</span>
                  </span>
                </div>
                <button
                  type="button"
                  @click="openGoogleMaps(item as any)"
                  class="shrink-0 inline-flex items-center gap-1.5 rounded-[8px] bg-[#173B35] px-4 py-2 text-sm font-bold text-white transition hover:bg-[#1D2724] active:scale-95"
                >
                  <ExternalLink class="w-3.5 h-3.5" />
                  Google Maps
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Empty -->
        <div v-else class="text-center py-20 text-[#66706C]">
          <Map class="w-14 h-14 mx-auto mb-4 opacity-25 text-[#4F7465]" />
          <p class="font-semibold text-[#173B35] mb-1">Belum ada data penginapan.</p>
          <p class="text-sm">Data akan tersedia setelah admin melakukan sinkronisasi.</p>
        </div>

        <!-- Pagination -->
        <div v-if="!isLoading && !errorMessage && meta.last_page > 1" class="mt-8">
          <Pagination
            :current-page="meta.current_page"
            :last-page="meta.last_page"
            :total="meta.total"
            :per-page="meta.per_page"
            @page-change="goToPage"
          />
        </div>

      </div>
    </div>

    <PublicFooter />
  </div>
</template>
