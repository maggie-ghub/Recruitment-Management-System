import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

// Layouts
import AppLayout from '../layouts/AppLayout.vue'

// Views
import LoginView from '../views/auth/LoginView.vue'
import RegisterView from '../views/auth/RegisterView.vue'
import DashboardView from '../views/DashboardView.vue'
import CompaniesView from '../views/admin/CompaniesView.vue'
import DepartmentsView from '../views/admin/DepartmentsView.vue'
import UsersView from '../views/admin/UsersView.vue'
import AuditLogsView from '../views/admin/AuditLogsView.vue'
import ProfileView from '../views/applicant/ProfileView.vue'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginView,
    meta: { guestOnly: true },
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterView,
    meta: { guestOnly: true },
  },
  {
    path: '/',
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: DashboardView,
      },
      {
        path: 'profile',
        name: 'profile',
        component: ProfileView,
      },
      // Admin / HR Management Routes
      {
        path: 'admin/companies',
        name: 'companies',
        component: CompaniesView,
        meta: { roles: ['Admin', 'HR Manager'] },
      },
      {
        path: 'admin/departments',
        name: 'departments',
        component: DepartmentsView,
        meta: { roles: ['Admin', 'HR Manager'] },
      },
      {
        path: 'admin/users',
        name: 'users',
        component: UsersView,
        meta: { roles: ['Admin', 'HR Manager'] },
      },
      {
        path: 'admin/audit-logs',
        name: 'audit-logs',
        component: AuditLogsView,
        meta: { roles: ['Admin', 'HR Manager'] },
      },
      // Vacancies Routes (Module 3)
      {
        path: 'vacancies',
        name: 'vacancies',
        component: () => import('../views/vacancies/VacanciesListView.vue'),
      },
      {
        path: 'vacancies/create',
        name: 'vacancy-create',
        component: () => import('../views/vacancies/VacancyCreateView.vue'),
        meta: { roles: ['Admin', 'HR Manager', 'Recruiter'] },
      },
      {
        path: 'vacancies/:id',
        name: 'vacancy-detail',
        component: () => import('../views/vacancies/VacancyDetailView.vue'),
      },
      // Module 4: Applicant Management (HR / Recruiter / Admin)
      {
        path: 'vacancies/:id/applicants',
        name: 'vacancy-applicants',
        component: () => import('../views/vacancies/VacancyApplicantsView.vue'),
        meta: { roles: ['Admin', 'HR Manager', 'Recruiter', 'Hiring Manager'] },
      },
    ],
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/',
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior: () => ({ top: 0 }),
})

// ─── Track if we have already verified the session this page load ───────────
let sessionVerified = false

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  // ── Server-side session verification (once per full page load) ────────────
  // If the user appears logged in locally but we haven't confirmed with the
  // server yet (e.g. tab reopened after token expiry), silently verify now.
  if (authStore.token && !sessionVerified) {
    sessionVerified = true
    try {
      await authStore.fetchCurrentUser()   // will call logout() on 401
    } catch {
      // fetchCurrentUser already handles 401 → logout
    }
  }

  // ── Route protection ──────────────────────────────────────────────────────
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    // Save intended destination so user lands back after login
    const redirect = to.fullPath !== '/' ? to.fullPath : undefined
    return next({ name: 'login', query: redirect ? { redirect } : {} })
  }

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return next({ name: 'dashboard' })
  }

  if (to.meta.roles && to.meta.roles.length > 0) {
    const userRole = authStore.role
    if (!to.meta.roles.includes(userRole)) {
      return next({ name: 'dashboard' })
    }
  }

  next()
})

export default router
