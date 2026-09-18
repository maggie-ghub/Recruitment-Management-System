<template>
  <div class="min-h-screen bg-[#faf8f5] flex flex-col font-sans">
    <!-- Top Navigation Header -->
    <header class="bg-white border-b border-[#f0e9dc] sticky top-0 z-30 shadow-xs">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
          <!-- Logo & Holding Branding -->
          <div class="flex items-center space-x-3">
            <img src="../assets/TORA_Logo.png" alt="Tora Holding Company Logo" class="h-10 w-auto object-contain" />
            <div>
              <div class="text-lg font-bold text-neutral-900 tracking-tight flex items-center gap-2">
                <span>Tora Holding</span>
                <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-[#db802d] border border-amber-200 font-medium">
                  VMS
                </span>
              </div>
              <p class="text-xs text-[#7a6e5a]">Vacancy & Recruitment Management</p>
            </div>
          </div>

          <!-- User Menu & Context -->
          <div class="flex items-center space-x-4">
            <!-- Company Scope Pill (if applicable) -->
            <div v-if="authStore.user?.subsidiary" class="hidden md:flex items-center px-3 py-1 rounded-lg bg-amber-50/70 border border-amber-200 text-xs font-medium text-[#7a6e5a]">
              <span class="w-2 h-2 rounded-full bg-[#db802d] mr-2"></span>
              Company: <strong class="ml-1 text-neutral-900">{{ authStore.user.subsidiary.name }}</strong>
            </div>
            <div v-else-if="authStore.isAdmin || authStore.isHRManager" class="hidden md:flex items-center px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 text-xs font-medium text-emerald-800">
              <span class="w-2 h-2 rounded-full bg-emerald-500 mr-2"></span>
              Scope: <strong>Holding Group (All Companies)</strong>
            </div>

            <!-- Role Badge -->
            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-[#ebb630]/20 text-[#8f5a11]">
              {{ authStore.role }}
            </span>

            <!-- User Info & Logout -->
            <div class="flex items-center space-x-3 border-l border-[#f0e9dc] pl-4">
              <div class="text-right hidden sm:block">
                <div class="text-sm font-semibold text-neutral-800">{{ authStore.user?.name }}</div>
                <div class="text-xs text-[#7a6e5a]">{{ authStore.user?.email }}</div>
              </div>
              <button
                @click="handleLogout"
                class="p-2 text-[#7a6e5a] hover:text-[#db802d] hover:bg-amber-50 rounded-lg transition-colors"
                title="Logout"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- App Body with Sidebar & Main Content -->
    <div class="flex-1 flex max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-6 gap-6">
      <!-- Sidebar Navigation -->
      <aside class="w-64 shrink-0 hidden md:block">
        <div class="bg-white rounded-2xl border border-[#f0e9dc] p-4 shadow-xs sticky top-24 space-y-6">
          <!-- Navigation Groups -->
          <div>
            <div class="text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] px-3 mb-2">Main Menu</div>
            <nav class="space-y-1">
              <router-link
                to="/"
                class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-all"
                :class="$route.name === 'dashboard' ? 'bg-[#ebb630]/15 text-[#db802d] font-semibold' : 'text-neutral-600 hover:bg-neutral-50'"
              >
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Dashboard
              </router-link>

              <!-- Vacancies Navigation -->
              <router-link
                to="/vacancies"
                class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-all"
                :class="$route.path.startsWith('/vacancies') ? 'bg-[#ebb630]/15 text-[#db802d] font-semibold' : 'text-neutral-600 hover:bg-neutral-50'"
              >
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                {{ authStore.isApplicant ? 'Browse Vacancies' : 'Vacancies' }}
              </router-link>

              <!-- Profile Link for Applicants -->
              <router-link
                v-if="authStore.isApplicant"
                to="/profile"
                class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-all"
                :class="$route.name === 'profile' ? 'bg-[#ebb630]/15 text-[#db802d] font-semibold' : 'text-neutral-600 hover:bg-neutral-50'"
              >
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                My Reusable Profile
              </router-link>
            </nav>
          </div>

          <!-- Administration Group (Admin & HR Manager) -->
          <div v-if="authStore.isAdmin || authStore.isHRManager">
            <div class="text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] px-3 mb-2">Organization & Admin</div>
            <nav class="space-y-1">
              <router-link
                to="/admin/companies"
                class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-all"
                :class="$route.name === 'companies' ? 'bg-[#ebb630]/15 text-[#db802d] font-semibold' : 'text-neutral-600 hover:bg-neutral-50'"
              >
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Companies (Subsidiaries)
              </router-link>

              <router-link
                to="/admin/departments"
                class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-all"
                :class="$route.name === 'departments' ? 'bg-[#ebb630]/15 text-[#db802d] font-semibold' : 'text-neutral-600 hover:bg-neutral-50'"
              >
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                </svg>
                Departments
              </router-link>

              <router-link
                to="/admin/users"
                class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-all"
                :class="$route.name === 'users' ? 'bg-[#ebb630]/15 text-[#db802d] font-semibold' : 'text-neutral-600 hover:bg-neutral-50'"
              >
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                User Management
              </router-link>

              <router-link
                to="/admin/audit-logs"
                class="flex items-center px-3 py-2.5 rounded-xl text-sm font-medium transition-all"
                :class="$route.name === 'audit-logs' ? 'bg-[#ebb630]/15 text-[#db802d] font-semibold' : 'text-neutral-600 hover:bg-neutral-50'"
              >
                <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Audit Logs
              </router-link>
            </nav>
          </div>
        </div>
      </aside>

      <!-- Main Content Outlet -->
      <main class="flex-1 min-w-0">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const handleLogout = async () => {
  await authStore.logout()
  router.push('/login')
}
</script>
