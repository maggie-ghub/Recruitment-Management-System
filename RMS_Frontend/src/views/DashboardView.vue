<template>
  <div class="space-y-6">
    <!-- Header banner (Modern SaaS High-Contrast, Enriched Typography) -->
    <div class="bg-white rounded-3xl p-7 sm:p-9 border border-[#f0e9dc] shadow-xs relative overflow-hidden">
      <div class="max-w-4xl">
        <div class="inline-flex items-center text-[#db802d] text-lg font-bold tracking-wider mb-3.5">
          <span>Tora Holding Company - Vacancy Management System</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900 tracking-tight">
          Welcome Back, {{ authStore.user?.name }}!
        </h1>
        <p class="mt-2.5 text-neutral-600 text-base leading-relaxed">
          <span v-if="authStore.user?.subsidiary" class="ml-1 text-neutral-700 font-medium">
            Assigned to <strong>{{ authStore.user.subsidiary.name }}</strong>
          </span>
        </p>
      </div>
    </div>

    <!-- Stats / Quick Overview (3-Column SaaS Cards, Large & Clear) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="bg-white p-7 rounded-3xl border border-[#f0e9dc] shadow-xs hover:border-[#db802d]/50 transition-all">
        <div class="flex items-center justify-between">
          <div class="text-xs font-bold tracking-wider text-[#7a6e5a]">Current Role</div>
          <div class="w-9 h-9 rounded-2xl bg-amber-50 flex items-center justify-center text-[#db802d]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
          </div>
        </div>
        <div class="mt-4 text-3xl font-extrabold text-neutral-900">{{ authStore.role }}</div>
      </div>

      <div class="bg-white p-6 rounded-2xl border border-[#f0e9dc] shadow-xs hover:border-[#db802d]/50 transition-all">
        <div class="flex items-center justify-between">
          <div class="text-xs font-bold tracking-wider text-[#7a6e5a] break-words">Organizational Scope</div>
          <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center text-[#db802d]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
        </div>
        <div class="mt-3 text-xl font-extrabold text-neutral-900 truncate">
          {{ authStore.user?.subsidiary?.name || 'Tora Holding Company' }}
        </div>
        <div class="mt-1 text-xs text-emerald-600 font-semibold flex items-center gap-1">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
          Data isolation active
        </div>
      </div>

      <div class="bg-white p-6 rounded-2xl border border-[#f0e9dc] shadow-xs hover:border-[#db802d]/50 transition-all">
        <div class="flex items-center justify-between">
          <div class="text-xs font-bold tracking-wider text-[#7a6e5a]">Account Status</div>
          <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-600">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
        <div class="mt-3 flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
          <span class="text-2xl font-extrabold text-neutral-900 capitalize">{{ authStore.user?.status || 'Active' }}</span>
        </div>
        <div class="mt-1 text-xs text-neutral-500">Fully verified & enabled</div>
      </div>
    </div>

    <!-- Action Shortcuts based on role -->
    <div class="bg-white p-6 rounded-3xl border border-[#f0e9dc] shadow-xs">
      <h2 class="text-base font-bold text-neutral-900 mb-4 flex items-center gap-2">
        <svg class="w-5 h-5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        Quick Actions & Next Steps
      </h2>

      <div v-if="authStore.isAdmin || authStore.isHRManager" class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <router-link
          to="/admin/companies"
          class="p-4 rounded-2xl border border-neutral-200 hover:border-[#ebb630] hover:bg-amber-50/30 transition-all block group"
        >
          <div class="font-bold text-neutral-800 group-hover:text-[#db802d] transition-colors">Manage Companies</div>
          <p class="text-xs text-neutral-500 mt-1">Configure subsidiary entities, registration codes, and status.</p>
        </router-link>

        <router-link
          to="/admin/departments"
          class="p-4 rounded-2xl border border-neutral-200 hover:border-[#ebb630] hover:bg-amber-50/30 transition-all block group"
        >
          <div class="font-bold text-neutral-800 group-hover:text-[#db802d] transition-colors">Manage Departments</div>
          <p class="text-xs text-neutral-500 mt-1">Configure business units and structural departments per company.</p>
        </router-link>

        <router-link
          to="/admin/users"
          class="p-4 rounded-2xl border border-neutral-200 hover:border-[#ebb630] hover:bg-amber-50/30 transition-all block group"
        >
          <div class="font-bold text-neutral-800 group-hover:text-[#db802d] transition-colors">Manage Users</div>
          <p class="text-xs text-neutral-500 mt-1">Assign roles (Recruiter, Hiring Manager, HR Manager) and company isolation.</p>
        </router-link>
      </div>

      <div v-else-if="authStore.isApplicant" class="p-6 rounded-2xl bg-amber-50/50 border border-amber-200">
        <div class="font-bold text-neutral-900 text-lg">Applicant Center Ready</div>
        <p class="text-sm text-neutral-600 mt-1 max-w-xl">
          Welcome to Tora Holding career opportunities. Complete your reusable profile (education, experience, CV) once and apply to multiple job openings.
        </p>
        <div class="mt-4">
          <router-link
            to="/profile"
            class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] transition-all"
          >
            Setup Reusable Profile
          </router-link>
        </div>
      </div>

      <div v-else class="p-6 rounded-2xl bg-[#faf8f5] border border-[#f0e9dc]">
        <div class="font-bold text-neutral-900 text-lg">Recruitment & Hiring Workflow</div>
        <p class="text-sm text-neutral-600 mt-1 max-w-xl">
          Assigned scope: <strong>{{ authStore.user?.subsidiary?.name || 'All Holding Companies' }}</strong>. Prepare job requisitions, track approvals, and manage candidate pipelines.
        </p>
        <div class="mt-4">
          <router-link
            to="/vacancies"
            class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] transition-all"
          >
            Go to Vacancy Management
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
</script>
