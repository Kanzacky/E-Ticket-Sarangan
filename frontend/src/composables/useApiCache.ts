/**
 * useApiCache — in-memory cache + stale-while-revalidate untuk API calls.
 *
 * Masalah yang diselesaikan:
 *   - User buka HomeView → /ticket-types & /accommodations di-fetch
 *   - User pindah ke halaman lain, lalu kembali → data di-fetch LAGI (tidak perlu!)
 *   - Setiap fetch cold-start Railway = 10+ detik tunggu
 *
 * Strategi:
 *   - Simpan hasil fetch di memory cache dengan TTL (time-to-live)
 *   - Kalau cache masih segar (< TTL), return cache langsung tanpa fetch
 *   - Kalau cache "stale" (> TTL tapi ada data), return cache dulu sambil refresh di background
 *
 * @param defaultTtlMs Default TTL dalam milidetik (default: 5 menit)
 */

interface CacheEntry<T> {
  data: T
  fetchedAt: number
}

const cache = new Map<string, CacheEntry<unknown>>()

export function useApiCache(defaultTtlMs = 5 * 60 * 1000) {
  /**
   * Ambil data dengan cache. Jika cache ada & segar, return langsung.
   * Jika cache stale, return cache lama dulu, refresh di background.
   *
   * @param key      Cache key unik per endpoint
   * @param fetcher  Fungsi async yang memanggil API
   * @param ttlMs    Opsional override TTL
   * @param onUpdate Callback dipanggil saat background refresh selesai
   */
  async function cachedFetch<T>(
    key: string,
    fetcher: () => Promise<T>,
    options?: {
      ttlMs?: number
      onUpdate?: (data: T) => void
    }
  ): Promise<T> {
    const ttl = options?.ttlMs ?? defaultTtlMs
    const entry = cache.get(key) as CacheEntry<T> | undefined
    const now = Date.now()

    if (entry) {
      const age = now - entry.fetchedAt
      if (age < ttl) {
        // Cache masih segar, return langsung
        return entry.data
      } else {
        // Cache stale: return data lama dulu, refresh di background
        if (options?.onUpdate) {
          fetcher()
            .then((fresh) => {
              cache.set(key, { data: fresh, fetchedAt: Date.now() })
              options.onUpdate!(fresh)
            })
            .catch(() => {/* silent: gunakan cache lama */})
        }
        return entry.data
      }
    }

    // Tidak ada cache: fetch sekarang
    const data = await fetcher()
    cache.set(key, { data, fetchedAt: now })
    return data
  }

  function invalidate(key: string) {
    cache.delete(key)
  }

  function invalidateAll() {
    cache.clear()
  }

  return { cachedFetch, invalidate, invalidateAll }
}
