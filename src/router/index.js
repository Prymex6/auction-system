import { createRouter, createWebHistory } from 'vue-router'
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import api from '@/services/api'

export const routeReady = ref(false)

const routes = [
  {
    path: '/',
    name: 'Home',
    component: () => import('@/pages/Home.vue'),
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/pages/Login.vue'),
    meta: { requiresGuest: true },
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/pages/Register.vue'),
    meta: { requiresGuest: true },
  },
  {
    path: '/auctions',
    name: 'AuctionList',
    component: () => import('@/pages/AuctionList.vue'),
  },
  {
    path: '/auctions/:id',
    name: 'AuctionDetail',
    component: () => import('@/pages/AuctionDetail.vue'),
  },
  {
    path: '/create-auction',
    name: 'CreateAuction',
    component: () => import('@/pages/CreateAuction.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/auctions/:id/edit',
    name: 'EditAuction',
    component: () => import('@/pages/EditAuction.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/profile',
    name: 'Profile',
    component: () => import('@/pages/Profile.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/profile/:id',
    name: 'UserProfile',
    component: () => import('@/pages/UserProfile.vue'),
  },
  {
    path: '/messages',
    name: 'Messages',
    component: () => import('@/pages/Messages.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/settings',
    redirect: '/profile?tab=settings',
  },
  {
    path: '/admin',
    name: 'AdminDashboard',
    component: () => import('@/pages/AdminDashboard.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
  },
  {
    path: '/blog',
    name: 'Blog',
    component: () => import('@/pages/Blog.vue'),
  },
  {
    path: '/blog/:slug',
    name: 'BlogDetail',
    component: () => import('@/pages/BlogDetail.vue'),
  },
  ...[
    ['/terms', 'Terms', 'terms'],
    ['/privacy', 'Privacy', 'privacy'],
    ['/cookies', 'Cookies', 'cookies'],
    ['/help', 'Help', 'help'],
    ['/how-it-works', 'HowItWorks', 'how-it-works'],
    ['/contact', 'Contact', 'contact'],
    ['/faq', 'Faq', 'faq'],
    ['/security', 'Security', 'security'],
  ].map(([path, name, pageSlug]) => ({
    path,
    name,
    component: () => import('@/pages/StaticPage.vue'),
    meta: { pageSlug },
  })),
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/pages/NotFound.vue'),
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach(async (to, from, next) => {
  routeReady.value = false
  const authStore = useAuthStore()

  // Initialize user if not already done
  if (authStore.token && !authStore.user) {
    try {
      await authStore.fetchMe()
    } catch (err) {
      console.error('Session validation failed:', err)
      await authStore.logout()

      if (to.meta.requiresAuth) {
        next('/login?session_expired=1')
        return
      }
    }
  }

  // Check if route requires authentication
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
    return
  }

  // Check if route requires guest (not authenticated)
  if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next('/')
    return
  }

  // Check if route requires admin
  if (to.meta.requiresAdmin && !authStore.user?.is_admin) {
    next('/')
    return
  }

  next()
})

router.afterEach((to) => {
  routeReady.value = true

  // Counting visits ourselves, without cookies and so without consent; see
  // PageViewController. Sent and forgotten: navigation must not wait for it,
  // and must not break if it fails, including synchronously in a test where
  // the client is only partly mocked.
  try {
    api.post('/track', { path: to.fullPath }).catch(() => {})
  } catch {
    // A missed page view is never worth interrupting somebody's navigation.
  }
})

export default router
