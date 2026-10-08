/**
 * useServerWakeup — "pings" the backend first before sending actual data requests.
 *
 * Masalah Railway cold start:
 *   Ketika Railway "sleeping", semua request yang datang bersamaan akan timeout.
 *   Solusi: ping /health dulu (1 request), tunggu respon, baru tembak request lain.
 *
 * Fitur:
 *   - Singleton: hanya 1 wake-up aktif per session browser
 *   - Auto-resolve: kalau server sudah panas (isWarm), langsung resolve
 *   - Timeout berjenjang: 15s untuk wake-up, setelah itu tetap resolve (graceful)
 */

import { getHealth } from '@/services/api'

let wakeupPromise: Promise<void> | null = null
let isServerWarm = false

export function useServerWakeup() {
  /**
   * Panggil ini sebelum fetch data penting.
   * Akan resolve setelah server terbukti aktif (atau timeout 15s).
   */
  async function ensureServerAwake(): Promise<void> {
    if (isServerWarm) return

    if (!wakeupPromise) {
      wakeupPromise = new Promise<void>((resolve) => {
        const timeout = setTimeout(() => {
          // Tetap resolve meski timeout agar fetch data tidak terblokir selamanya
          resolve()
        }, 15_000)

        getHealth()
          .then(() => {
            isServerWarm = true
            clearTimeout(timeout)
            resolve()
          })
          .catch(() => {
            clearTimeout(timeout)
            resolve() // graceful: tetap lanjut meski health check gagal
          })
      })
    }

    return wakeupPromise
  }

  /** Reset state (untuk testing / logout) */
  function resetWakeup() {
    wakeupPromise = null
    isServerWarm = false
  }

  return { ensureServerAwake, resetWakeup, isServerWarm: () => isServerWarm }
}
