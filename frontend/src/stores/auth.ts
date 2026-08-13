import { defineStore } from 'pinia'
import { computed, ref } from 'vue'

import { getMeApi, loginApi, logoutApi, registerApi, type AuthUser } from '@/services/api'

export type UserRole = 'wisatawan' | 'petugas' | 'admin'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<AuthUser | null>(null)
  const token = ref<string | null>(localStorage.getItem('access_token'))
  const isLoading = ref(false)
  const isInitialized = ref(false)

  const isAuthenticated = computed(() => token.value !== null && user.value !== null)
  const role = computed<UserRole | null>(() => (user.value?.role as UserRole) ?? null)

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

  async function login(credentials: any) {
    isLoading.value = true
    try {
      const response = await loginApi(credentials)
      setToken(response.data.access_token)
      setUser(response.data.user)
      return response
    } finally {
      isLoading.value = false
    }
  }

  async function register(data: any) {
    isLoading.value = true
    try {
      const response = await registerApi(data)
      setToken(response.data.access_token)
      setUser(response.data.user)
      return response
    } finally {
      isLoading.value = false
    }
  }

  async function logout(): Promise<void> {
    if (token.value) {
      try {
        await logoutApi()
      } catch (e) {
        // ignore errors on logout
      }
    }
    setToken(null)
    setUser(null)
  }

  async function initialize() {
    if (isInitialized.value) return
    
    if (token.value) {
      isLoading.value = true
      try {
        const response = await getMeApi()
        setUser(response.data.user)
      } catch (e) {
        // token might be invalid
        setToken(null)
        setUser(null)
      } finally {
        isLoading.value = false
      }
    }
    
    isInitialized.value = true
  }

  return {
    user,
    token,
    isLoading,
    isInitialized,
    isAuthenticated,
    role,
    setToken,
    setUser,
    login,
    register,
    logout,
    initialize,
  }
})
