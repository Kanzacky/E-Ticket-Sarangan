import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router'

import { useAuthStore, type UserRole } from '@/stores/auth'

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/HomeView.vue'),
    meta: { public: true },
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/LoginView.vue'),
    meta: { public: true, guest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/views/auth/RegisterView.vue'),
    meta: { public: true, guest: true },
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('@/views/auth/ForgotPasswordView.vue'),
    meta: { public: true, guest: true },
  },
  {
    path: '/reset-password',
    name: 'reset-password',
    component: () => import('@/views/auth/ResetPasswordView.vue'),
    meta: { public: true, guest: true },
  },
  {
    path: '/reset-password/:token',
    name: 'reset-password-token',
    component: () => import('@/views/auth/ResetPasswordView.vue'),
    meta: { public: true, guest: true },
  },
  {
    path: '/booking/success/:orderCode',
    name: 'booking.success',
    component: () => import('@/views/booking/BookingSuccessView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/booking/:orderCode/success',
    redirect: (to) => {
      return { path: `/booking/success/${to.params.orderCode}` }
    }
  },
  {
    path: '/_wisatawan',
    component: () => import('@/layouts/WisatawanLayout.vue'),
    meta: { requiresAuth: true, role: 'wisatawan' as UserRole },
    children: [
      {
        path: '/booking',
        name: 'wisatawan.booking',
        component: () => import('@/views/booking/BookingView.vue'),
      },
      {
        path: '/my-tickets',
        name: 'wisatawan.tickets',
        component: () => import('@/views/booking/MyBookingsView.vue'),
      },
      {
        path: '/my-tickets/:id',
        name: 'wisatawan.ticket-detail',
        component: () => import('@/views/wisatawan/TicketDetailView.vue'),
      },
      {
        path: '/profile',
        name: 'wisatawan.profile',
        component: () => import('@/views/wisatawan/ProfileView.vue'),
      },
      {
        path: '/accommodations',
        name: 'wisatawan.accommodations',
        component: () => import('@/views/wisatawan/AccommodationsView.vue'),
      },
      {
        path: '/accommodations/:id',
        name: 'wisatawan.accommodation-detail',
        component: () => import('@/views/wisatawan/AccommodationDetailView.vue'),
      },
      {
        path: '/my-accommodations',
        name: 'wisatawan.accommodation-bookings',
        component: () => import('@/views/wisatawan/MyAccommodationBookingsView.vue'),
      },
    ],
  },
  {
    path: '/petugas',
    component: () => import('@/layouts/PetugasLayout.vue'),
    meta: { requiresAuth: true, role: 'petugas' as UserRole },
    children: [
      {
        path: 'dashboard',
        name: 'petugas.dashboard',
        component: () => import('@/views/petugas/DashboardView.vue'),
      },
      {
        path: 'scanner',
        name: 'petugas.scanner',
        component: () => import('@/views/petugas/ScannerView.vue'),
      },
      {
        path: 'visits',
        name: 'petugas.visits',
        component: () => import('@/views/petugas/VisitsView.vue'),
      },
      {
        path: 'bookings',
        name: 'petugas.bookings',
        component: () => import('@/views/petugas/BookingsView.vue'),
      },
      {
        path: 'bookings/:id',
        name: 'petugas.booking-detail',
        component: () => import('@/views/petugas/BookingDetailView.vue'),
      },
      {
        path: 'users',
        name: 'petugas.users',
        component: () => import('@/views/petugas/UsersView.vue'),
      },
      {
        path: 'history',
        name: 'petugas.history',
        component: () => import('@/views/petugas/HistoryView.vue'),
      },
      {
        path: 'profile',
        name: 'petugas.profile',
        component: () => import('@/views/petugas/ProfileView.vue'),
      },
    ],
  },
  {
    path: '/admin',
    component: () => import('@/layouts/AdminLayout.vue'),
    meta: { requiresAuth: true, role: 'admin' as UserRole },
    children: [
      {
        path: 'dashboard',
        name: 'admin.dashboard',
        component: () => import('@/views/admin/DashboardView.vue'),
      },
      {
        path: 'users',
        name: 'admin.users',
        component: () => import('@/views/admin/UsersView.vue'),
      },
      {
        path: 'petugas',
        name: 'admin.petugas',
        component: () => import('@/views/admin/UsersView.vue'),
      },
      {
        path: 'ticket-categories',
        name: 'admin.ticket-categories',
        component: () => import('@/views/admin/TicketCategoriesView.vue'),
      },
      {
        path: 'accommodations',
        name: 'admin.accommodations',
        component: () => import('@/views/admin/AccommodationsView.vue'),
      },
      {
        path: 'bookings',
        name: 'admin.bookings',
        component: () => import('@/views/admin/BookingsView.vue'),
      },
      {
        path: 'payments',
        name: 'admin.payments',
        component: () => import('@/views/admin/PaymentsView.vue'),
      },
      {
        path: 'tickets',
        name: 'admin.tickets',
        component: () => import('@/views/admin/TicketsView.vue'),
      },
      {
        path: 'checkins',
        name: 'admin.checkins',
        component: () => import('@/views/admin/CheckinsView.vue'),
      },
      {
        path: 'analytics',
        name: 'admin.analytics',
        component: () => import('@/views/admin/AnalyticsView.vue'),
      },
      {
        path: 'reports',
        name: 'admin.reports',
        component: () => import('@/views/admin/ReportsView.vue'),
      },
      {
        path: 'audit-logs',
        name: 'admin.audit-logs',
        component: () => import('@/views/admin/AuditLogsView.vue'),
      },
      {
        path: 'settings',
        name: 'admin.settings',
        component: () => import('@/views/admin/SettingsView.vue'),
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFoundView.vue'),
    meta: { public: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, _from, savedPosition) {
    if (to.hash) {
      return {
        el: to.hash,
        behavior: 'smooth',
      }
    }
    if (savedPosition) {
      return savedPosition
    }
    return { top: 0 }
  },
})

router.beforeEach(async (to) => {
  const authStore = useAuthStore()
  
  if (!authStore.isInitialized) {
    await authStore.initialize()
  }

  const isPublic = to.meta.public === true

  if (isPublic && !to.meta.guest) {
    return true
  }

  if (to.meta.guest && authStore.isAuthenticated) {
    return { name: 'home' }
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.requiresAuth && to.meta.role) {
    const routeRole = to.meta.role as UserRole

    if (authStore.role !== routeRole) {
      return { name: 'home' }
    }
  }

  return true
})

export default router
