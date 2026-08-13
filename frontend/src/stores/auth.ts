import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

export type UserRole = 'wisatawan' | 'petugas' | 'admin'

export interface AuthUser {
  id: number
  name: string
  email: string
  role: UserRole
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<AuthUser | null>(null)
  const token = ref<string | null>(localStorage.getItem('access_token'))
  const isLoading = ref(false)

  const isAuthenticated = computed(() => token.value !== null && user.value !== null)
  const role = computed<UserRole | null>(() => user.value?.role ?? null)

  function setToken(value: string | null): void {
    token.value = value

    if (value) {
      localStorage.setItem('access_token', value)
    } else {
      localStorage.removeItem('access_token')
    }
  }

  function setUser(value: AuthUser | null): void {
    user.value = value
  }

  function logout(): void {
    setToken(null)
    setUser(null)
  }

  return {
    user,
    token,
    isLoading,
    isAuthenticated,
    role,
    setToken,
    setUser,
    logout,
  }
})
