<script setup lang="ts">
import { RouterView } from 'vue-router'
import PublicNavbar from '@/components/layout/PublicNavbar.vue'
import PublicFooter from '@/components/layout/PublicFooter.vue'
</script>

<template>
  <div class="flex flex-col min-h-screen bg-[#F7F5EF] font-sans text-[#1D2724] selection:bg-[#4F7465] selection:text-white">
    <PublicNavbar />

    <!-- Main Content -->
    <main class="flex-grow flex flex-col pt-24 pb-16">
      <div class="mx-auto w-full max-w-[1240px] px-5 sm:px-6 lg:px-8 flex-1">
        <RouterView />
      </div>
    </main>
    
    <PublicFooter />
  </div>
</template>
