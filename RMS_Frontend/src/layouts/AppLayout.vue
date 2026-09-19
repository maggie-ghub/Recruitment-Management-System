<template>
  <div class="min-h-screen bg-[#f4f6f3] flex flex-col font-sans">
    <!-- Top Navigation Header -->
    <header class="bg-white/90 backdrop-blur-xl border-b border-[#e4e9e4] sticky top-0 z-30 shadow-[0_1px_12px_rgba(41,51,47,0.04)]">
      <div class="max-w-[1500px] w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between min-h-[76px] items-center gap-4">
          <!-- Logo & Holding Branding -->
          <div class="flex items-center space-x-3.5 min-w-0">
            <div class="h-11 w-11 rounded-2xl bg-[#29332f] flex items-center justify-center shadow-[0_8px_18px_rgba(41,51,47,0.16)] shrink-0 overflow-hidden">
              <img src="../assets/TORA_Logo.png" alt="Tora Holding Company Logo" class="h-9 w-auto object-contain brightness-0 invert" />
            </div>
            <div>
              <div class="text-lg sm:text-xl font-extrabold text-[#29332f] tracking-tight flex items-center gap-2">
                <span>Tora Holding Company</span>
              </div>
              <p class="text-[11px] sm:text-xs text-[#6d716d] font-semibold tracking-wide">Recruitment Management System</p>
            </div>
          </div>

          <!-- User Menu & Context -->
          <div class="flex items-center space-x-2 sm:space-x-4">
            <!-- Company Scope Pill: Fully visible without cutoff -->
            <div v-if="authStore.user?.subsidiary" class="hidden lg:flex items-center px-3.5 py-2 rounded-xl bg-[#f8f1df] border border-[#ead9ac] text-xs font-semibold text-[#765b1e]">
              <span class="w-2 h-2 rounded-full bg-[#db802d] mr-2 shrink-0"></span>
              <span class="text-neutral-500 mr-1">Company:</span>
              <span class="text-neutral-900 font-bold whitespace-normal">{{ authStore.user.subsidiary.name }}</span>
            </div>

            <!-- Role Badge -->
            <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-[11px] font-bold bg-[#f8f1df] text-[#765b1e] border border-[#ead9ac]">
              {{ authStore.role }}
            </span>

            <!-- User Info & Logout -->
            <div class="flex items-center space-x-3 border-l border-[#e4e9e4] pl-3 sm:pl-4">
              <div class="text-right hidden sm:block">
                <div class="text-sm font-bold text-[#29332f]">{{ authStore.user?.name }}</div>
                <div class="text-xs text-[#6d716d] font-medium">{{ authStore.user?.email }}</div>
              </div>
              <button
                @click="handleLogout"
                class="p-2.5 text-[#6d716d] hover:text-[#c96b3b] hover:bg-[#f8f1df] rounded-xl transition-all cursor-pointer"
                title="Sign Out"
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

    <!-- App Body with Sidebar & Main Content (Broad Container, Large Content Width) -->
    <div class="flex-1 flex max-w-[1500px] w-full mx-auto px-4 sm:px-6 lg:px-8 py-5 sm:py-7 gap-6 lg:gap-8">
      <!-- Sidebar Navigation -->
      <aside class="w-68 shrink-0 hidden md:block">
        <div class="bg-white rounded-2xl border border-[#e4e9e4] p-3.5 shadow-[0_8px_24px_rgba(41,51,47,0.045)] sticky top-24 space-y-5">
          <!-- Navigation Groups -->
          <div>
            <div class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-[#8a938b] px-3 mb-2.5">Workspace</div>
            <nav class="space-y-1.5">
              <router-link
                to="/"
                class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
                :class="$route.name === 'dashboard' ? 'bg-[#29332f] text-white shadow-[0_6px_14px_rgba(41,51,47,0.16)]' : 'text-[#5f6962] hover:bg-[#f0f3ef] hover:text-[#29332f]'"
              >
                <!-- Dashboard Icon -->
                <svg class="w-5 h-5 mr-3" :class="$route.name === 'dashboard' ? 'text-[#e9b949]' : 'text-[#c96b3b]'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zm10 0a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                </svg>
                Dashboard
              </router-link>

              <!-- Vacancies Navigation -->
              <router-link
                to="/vacancies"
                class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
                :class="$route.path.startsWith('/vacancies') ? 'bg-[#29332f] text-white shadow-[0_6px_14px_rgba(41,51,47,0.16)]' : 'text-[#5f6962] hover:bg-[#f0f3ef] hover:text-[#29332f]'"
              >
                <!-- Briefcase Icon -->
                <svg class="w-5 h-5 mr-3" :class="$route.path.startsWith('/vacancies') ? 'text-[#e9b949]' : 'text-[#c96b3b]'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                {{ authStore.isApplicant ? 'Browse Vacancies' : 'Vacancies' }}
              </router-link>

              <!-- Profile Link for Applicants -->
              <router-link
                v-if="authStore.isApplicant"
                to="/profile"
                class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
                :class="$route.name === 'profile' ? 'bg-[#29332f] text-white shadow-[0_6px_14px_rgba(41,51,47,0.16)]' : 'text-[#5f6962] hover:bg-[#f0f3ef] hover:text-[#29332f]'"
              >
                <!-- User Profile Icon -->
                <svg class="w-5 h-5 mr-3" :class="$route.name === 'profile' ? 'text-[#e9b949]' : 'text-[#c96b3b]'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Candidate Profile
              </router-link>
            </nav>
          </div>

          <!-- Administration Group (Admin & HR Manager) -->
          <div v-if="authStore.isAdmin || authStore.isHRManager">
            <div class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-[#8a938b] px-3 mb-2.5">Administration</div>
            <nav class="space-y-1.5">
              <router-link
                to="/admin/companies"
                class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
                :class="$route.name === 'companies' ? 'bg-[#29332f] text-white shadow-[0_6px_14px_rgba(41,51,47,0.16)]' : 'text-[#5f6962] hover:bg-[#f0f3ef] hover:text-[#29332f]'"
              >
                <!-- Buildings Icon for Companies -->
                <svg class="w-5 h-5 mr-3" :class="$route.name === 'companies' ? 'text-[#e9b949]' : 'text-[#c96b3b]'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                Companies
              </router-link>

              <router-link
                to="/admin/departments"
                class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
                :class="$route.name === 'departments' ? 'bg-[#29332f] text-white shadow-[0_6px_14px_rgba(41,51,47,0.16)]' : 'text-[#5f6962] hover:bg-[#f0f3ef] hover:text-[#29332f]'"
              >
                <!-- Network / Hierarchy Icon for Departments -->
                <svg class="w-5 h-5 mr-3" :class="$route.name === 'departments' ? 'text-[#e9b949]' : 'text-[#c96b3b]'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Departments
              </router-link>

              <router-link
                to="/admin/users"
                class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
                :class="$route.name === 'users' ? 'bg-[#29332f] text-white shadow-[0_6px_14px_rgba(41,51,47,0.16)]' : 'text-[#5f6962] hover:bg-[#f0f3ef] hover:text-[#29332f]'"
              >
                <!-- User Group Icon for Users -->
                <svg class="w-5 h-5 mr-3" :class="$route.name === 'users' ? 'text-[#e9b949]' : 'text-[#c96b3b]'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                User Management
              </router-link>

              <router-link
                to="/admin/audit-logs"
                class="flex items-center px-3 py-2.5 rounded-xl text-sm font-semibold transition-all"
                :class="$route.name === 'audit-logs' ? 'bg-[#29332f] text-white shadow-[0_6px_14px_rgba(41,51,47,0.16)]' : 'text-[#5f6962] hover:bg-[#f0f3ef] hover:text-[#29332f]'"
              >
                <!-- Shield / Audit Log Icon -->
                <svg class="w-5 h-5 mr-3" :class="$route.name === 'audit-logs' ? 'text-[#e9b949]' : 'text-[#c96b3b]'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                Audit Trail
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
