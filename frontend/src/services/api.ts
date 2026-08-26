import axios, { type AxiosInstance } from 'axios'

import { useAuthStore } from '@/stores/auth'

// Base URL backend production (Vercel Container):
//   https://e-ticket-sarangan-backend.vercel.app/api
// Di-set via env VITE_API_URL (Vite convention) — lihat .env.production
// atau Environment Variables di Vercel Dashboard (frontend project).
// JANGAN pakai localhost atau URL frontend sebagai base URL production.
const apiBaseUrl = import.meta.env.VITE_API_URL

if (!apiBaseUrl) {
  console.warn(
    '[api] VITE_API_URL belum dikonfigurasi. Fallback ke "/api". ' +
      'Set VITE_API_URL di .env.production atau Vercel env agar request ' +
      'mengarah ke backend production.',
  )
}

const api: AxiosInstance = axios.create({
  baseURL: apiBaseUrl || '/api',
  timeout: 15000,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

api.interceptors.request.use((config) => {
  const authStore = useAuthStore()

  if (authStore.token) {
    config.headers.Authorization = `Bearer ${authStore.token}`
  }

  return config
})

api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      authStore.logout()
    }

    return Promise.reject(error)
  },
)

export interface ApiResponse<T = unknown> {
  success: boolean
  message: string
  data: T
  meta?: Record<string, unknown>
}

export interface HealthData {
  status: string
  app: string
  version: string
  database: string
}

export interface HealthResponse {
  success?: boolean
  message?: string
  data?: HealthData
}

/**
 * GET /health — health check backend.
 *
 * Robust terhadap bentuk JSON: status "Terhubung" ditentukan dari HTTP 2xx
 * (axios resolve), bukan dari field `success`. Semua field opsional agar
 * tidak error bila backend mengembalikan struktur berbeda.
 */
export const getHealth = () =>
  api.get<HealthResponse>('/health').then((response) => response.data)

// --- Auth Endpoints ---

export interface AuthUser {
  id: number
  name: string
  email: string
  role: 'wisatawan' | 'petugas' | 'admin'
  phone: string | null
}

export interface AuthResponse {
  user: AuthUser
  access_token: string
}
export interface LoginRequest {
  email: string
  password: string
}

export interface RegisterRequest {
  name: string
  email: string
  phone?: string | null
  password: string
  password_confirmation: string
}

export interface MeResponse {
  user: AuthUser
}

export const loginApi = (data: LoginRequest) =>
  api.post<ApiResponse<AuthResponse>>('/auth/login', data).then((res) => res.data)

export const registerApi = (data: RegisterRequest) =>
  api.post<ApiResponse<AuthResponse>>('/auth/register', data).then((res) => res.data)

export const logoutApi = () => api.post<ApiResponse<null>>('/auth/logout').then((res) => res.data)

export const getMeApi = () => api.get<ApiResponse<MeResponse>>('/auth/me').then((res) => res.data)

export default api
