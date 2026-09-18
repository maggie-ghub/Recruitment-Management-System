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
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: 'login' })
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
