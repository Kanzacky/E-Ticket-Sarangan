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
    path: '/booking',
    name: 'booking',
    component: () => import('@/views/booking/BookingView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/booking/success/:orderCode',
    name: 'booking.success',
    component: () => import('@/views/booking/BookingSuccessView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/my-bookings',
    name: 'my-bookings',
    component: () => import('@/views/booking/MyBookingsView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/wisatawan',
    component: () => import('@/layouts/DashboardLayout.vue'),
    meta: { requiresAuth: true, role: 'wisatawan' as UserRole },
    children: [
      {
        path: '',
        redirect: '/my-bookings',
      },
      {
        path: 'dashboard',
        name: 'wisatawan.dashboard',
        component: () => import('@/views/booking/MyBookingsView.vue'),
      },
      {
        path: 'booking',
        name: 'wisatawan.booking',
        component: () => import('@/views/booking/BookingView.vue'),
      },
      {
        path: 'tickets',
        name: 'wisatawan.tickets',
        component: () => import('@/views/booking/MyBookingsView.vue'),
      },
      {
        path: 'tickets/:id',
        name: 'wisatawan.ticket-detail',
        component: () => import('@/views/wisatawan/TicketDetailView.vue'),
      },
      {
        path: 'history',
        name: 'wisatawan.history',
        component: () => import('@/views/booking/MyBookingsView.vue'),
      },
      {
        path: 'profile',
        name: 'wisatawan.profile',
        component: () => import('@/views/wisatawan/ProfileView.vue'),
      },
    ],
  },
  {
    path: '/petugas',
    component: () => import('@/layouts/DashboardLayout.vue'),
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
        path: 'checkins',
        name: 'petugas.checkins',
        component: () => import('@/views/petugas/CheckinsView.vue'),
      },
      {
        path: 'tickets/:id',
        name: 'petugas.ticket-detail',
        component: () => import('@/views/petugas/TicketDetailView.vue'),
      },
    ],
  },
  {
    path: '/admin',
    component: () => import('@/layouts/DashboardLayout.vue'),
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
        component: () => import('@/views/admin/PetugasView.vue'),
      },
      {
        path: 'ticket-categories',
        name: 'admin.ticket-categories',
        component: () => import('@/views/admin/TicketCategoriesView.vue'),
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
        path: 'upgrades',
        name: 'admin.upgrades',
        component: () => import('@/views/admin/UpgradesView.vue'),
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
  scrollBehavior: () => ({ top: 0 }),
})

router.beforeEach((to) => {
  const authStore = useAuthStore()
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
