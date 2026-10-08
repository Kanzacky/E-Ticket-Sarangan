import axios, { type AxiosInstance, type AxiosRequestConfig } from 'axios'
import { useAuthStore } from '@/stores/auth'

const apiBaseUrl = import.meta.env.VITE_API_URL

if (!apiBaseUrl) {
  console.warn(
    '[api] VITE_API_URL belum dikonfigurasi. Fallback ke "/api". ' +
      'Set VITE_API_URL di .env.production atau Vercel env.',
  )
}

const api: AxiosInstance = axios.create({
  baseURL: apiBaseUrl || '/api',
  timeout: 12000, // 12s — Cukup untuk Railway cold start, mencegah loading terlalu lama
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
})

// ─── Request interceptor: pasang token ─────────────────────────────────────
api.interceptors.request.use((config) => {
  const authStore = useAuthStore()
  if (authStore.token) {
    config.headers.Authorization = `Bearer ${authStore.token}`
  }
  return config
})

// ─── Response interceptor: retry otomatis + logout 401 ────────────────────
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    const config = error.config as AxiosRequestConfig & { _retryCount?: number }

    // Logout kalau 401
    if (error.response?.status === 401) {
      const authStore = useAuthStore()
      authStore.logout()
      return Promise.reject(error)
    }

    // Retry otomatis untuk network error atau 5xx (bukan 4xx client error)
    const isNetworkError = !error.response
    const isServerError = error.response?.status >= 500
    const shouldRetry = isNetworkError || isServerError

    if (shouldRetry && config && !config._retryCount) {
      config._retryCount = 0
    }

    const MAX_RETRIES = 2
    if (shouldRetry && config && (config._retryCount ?? 0) < MAX_RETRIES) {
      config._retryCount = (config._retryCount ?? 0) + 1
      const delay = config._retryCount * 1500 // 1.5s, 3s
      await new Promise((resolve) => setTimeout(resolve, delay))
      return api(config)
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
