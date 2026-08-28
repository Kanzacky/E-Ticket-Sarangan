<script setup lang="ts">
import { 
  ChevronRight, 
  MapPin, 
  Star,
  Building,
  Search,
} from 'lucide-vue-next'
import { useI18n } from 'vue-i18n'
import { onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

import { getAccommodationsApi, type Accommodation, type PaginatedMeta } from '@/services/accommodation.service'
import Pagination from '@/components/ui/Pagination.vue'

const router = useRouter()

const { t } = useI18n()
const accommodations = ref<Accommodation[]>([])
const isLoading = ref(true)
const errorMessage = ref('')
const searchQuery = ref('')
const meta = ref<PaginatedMeta>({ current_page: 1, last_page: 1, per_page: 12, total: 0 })
let searchTimer: ReturnType<typeof setTimeout> | null = null

async function fetchAccommodations() {
  try {
    isLoading.value = true
    const result = await getAccommodationsApi({
      page: meta.value.current_page,
      per_page: 12,
      search: searchQuery.value.trim() || undefined,
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

watch(searchQuery, () => {
  if (searchTimer) clearTimeout(searchTimer)
  searchTimer = setTimeout(() => {
    meta.value.current_page = 1
    fetchAccommodations()
  }, 400)
})

onMounted(() => void fetchAccommodations())

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('id-ID').format(price)
}

function handleLihatDetail(id: number) {
  void router.push(`/accommodations/${id}`)
}
</script>

<template>
  <div class="min-h-screen bg-[#F7F5EF] pt-28 pb-12 px-5 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-[1240px]">
      
      <!-- Header -->
      <div class="mb-12">
        <p class="text-[#C9965B] text-xs font-bold uppercase tracking-widest mb-2">{{ t('accommodation.section') }}</p>
        <h1 class="text-3xl md:text-4xl font-black text-[#173B35] mb-3">{{ t('accommodation.title') }}</h1>
        <p class="text-[#66706C] max-w-lg text-base">
          {{ t('accommodation.subtitle') }}
        </p>
      </div>

      <!-- Search -->
      <div v-if="!isLoading && !errorMessage" class="relative w-full sm:w-72 mb-6">
        <Search class="w-4 h-4 absolute left-3 top-2.5 text-[#66706C]" />
        <input v-model="searchQuery" :placeholder="t('accommodation.search_placeholder')" class="w-full pl-9 pr-3 py-2 text-sm border border-[#E8E6DE] rounded-lg bg-white focus:ring-1 focus:ring-[#173B35]" />
      </div>

      <!-- Loading State -->
      <div v-if="isLoading" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="i in 6" :key="i" class="h-[360px] rounded-xl bg-white animate-pulse border border-[#E8E6DE]"></div>
      </div>
      
      <div v-else-if="errorMessage" class="py-16 text-center text-red-600 bg-white rounded-xl border border-red-200">
        <p class="font-medium">{{ errorMessage }}</p>
      </div>

      <!-- Accommodations Grid -->
      <div v-else-if="accommodations.length" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="item in accommodations"
          :key="item.id"
          class="group flex flex-col border border-[#E8E6DE] rounded-xl bg-white overflow-hidden transition-all hover:border-[#4F7465] hover:shadow-md"
        >
          <!-- Card Header / Image Placeholder -->
          <div class="relative h-48 bg-[#173B35] p-6 flex flex-col justify-between text-white overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-t from-[#1D2724] via-[#1D2724]/40 to-transparent z-10"></div>
            <div class="relative z-20 flex justify-between items-start">
              <span class="inline-flex items-center gap-1 rounded-full bg-[#C9965B] px-2.5 py-1 text-xs font-bold text-[#1D2724] backdrop-blur-md">
                <Star class="h-3.5 w-3.5 fill-[#1D2724]" /> {{ item.rating }}
              </span>
              <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold backdrop-blur-md border border-white/30">
                {{ item.available_rooms }} Kamar
              </span>
            </div>
            <div class="relative z-20">
              <h3 class="text-xl font-bold text-white mb-1">{{ item.name }}</h3>
              <p class="text-xs text-white/80 flex items-center gap-1">
                <MapPin class="h-3.5 w-3.5 shrink-0" /> {{ item.address }}
              </p>
            </div>
          </div>

          <!-- Card Body -->
          <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
            <p class="text-sm text-[#66706C] line-clamp-2 leading-relaxed">
              {{ item.description }}
            </p>

            <!-- Facilities Tags -->
            <div v-if="item.facilities?.length" class="flex flex-wrap gap-1.5 pt-2">
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

            <!-- Price & CTA -->
            <div class="pt-5 border-t border-[#E8E6DE] flex items-center justify-between mt-auto">
              <div>
                <span class="text-xs text-[#66706C] block mb-0.5">{{ t('accommodation.starting_from') }}</span>
                <span class="text-lg font-black text-[#173B35]">
                  Rp{{ formatPrice(item.price_per_night) }}
                  <span class="text-xs font-normal text-[#66706C]">{{ t('accommodation.per_night') }}</span>
                </span>
              </div>

              <button
                type="button"
                class="shrink-0 inline-flex items-center gap-1.5 rounded-[8px] bg-white border-2 border-[#173B35] px-4 py-2 text-sm font-bold text-[#173B35] transition hover:bg-[#F7F5EF] active:scale-95"
                @click="handleLihatDetail(item.id)"
              >
                Lihat <ChevronRight class="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Empty -->
      <div v-else class="text-center py-16 text-[#66706C]">
        <Building class="w-12 h-12 mx-auto mb-3 opacity-30 text-[#4F7465]" />
        <p class="font-medium text-[#173B35]">Belum ada penginapan yang tersedia saat ini.</p>
      </div>

      <!-- Pagination -->
      <div v-if="!isLoading && !errorMessage" class="mt-6">
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
</template>
