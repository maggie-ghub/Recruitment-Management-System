<template>
  <div class="space-y-6">
    <!-- Header banner -->
    <div class="bg-gradient-to-r from-amber-500 via-[#db802d] to-[#7a6e5a] rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
      <div class="absolute -right-10 -bottom-10 w-48 h-48 bg-white/10 rounded-full blur-2xl"></div>
      <div class="relative z-10 max-w-2xl">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/20 backdrop-blur-md text-xs font-semibold mb-3">
          <span>Module 1: Authentication & Organization Architecture</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
          Welcome back, {{ authStore.user?.name }}!
        </h1>
        <p class="mt-2 text-white/90 text-sm leading-relaxed">
          Tora Holding Company Vacancy Management System (VMS). Your active role is
          <strong class="underline decoration-[#ebb630]">{{ authStore.role }}</strong>
          <span v-if="authStore.user?.subsidiary"> assigned to <strong>{{ authStore.user.subsidiary.name }}</strong></span>.
        </p>
      </div>
    </div>

    <!-- Stats / Quick Overview -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
      <div class="bg-white p-5 rounded-2xl border border-[#f0e9dc] shadow-xs">
        <div class="text-xs font-semibold uppercase tracking-wider text-[#7a6e5a]">Current Role</div>
        <div class="mt-2 text-xl font-bold text-neutral-800">{{ authStore.role }}</div>
        <div class="mt-1 text-xs text-neutral-500">Security role defined</div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-[#f0e9dc] shadow-xs">
        <div class="text-xs font-semibold uppercase tracking-wider text-[#7a6e5a]">Organizational Scope</div>
        <div class="mt-2 text-xl font-bold text-neutral-800 truncate">
          {{ authStore.user?.subsidiary?.name || 'Holding Company (All)' }}
        </div>
        <div class="mt-1 text-xs text-emerald-600 font-medium">Data isolation active</div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-[#f0e9dc] shadow-xs">
        <div class="text-xs font-semibold uppercase tracking-wider text-[#7a6e5a]">Account Status</div>
        <div class="mt-2 flex items-center gap-2">
          <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
          <span class="text-xl font-bold text-neutral-800 capitalize">{{ authStore.user?.status || 'Active' }}</span>
        </div>
        <div class="mt-1 text-xs text-neutral-500">Fully verified & enabled</div>
      </div>

      <div class="bg-white p-5 rounded-2xl border border-[#f0e9dc] shadow-xs">
        <div class="text-xs font-semibold uppercase tracking-wider text-[#7a6e5a]">System Release</div>
        <div class="mt-2 text-xl font-bold text-neutral-800">Version 2.1 (Final)</div>
        <div class="mt-1 text-xs text-amber-700">Tora Holding & Subsidiaries</div>
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

      <div v-if="authStore.isAdmin || authStore.isHRManager" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
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
          Welcome to Tora Holding career opportunities. Complete your reusable profile (education, experience, CV) once and apply to multiple job openings across all subsidiaries without re-typing.
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

      <div v-else class="p-6 rounded-2xl bg-neutral-50 border border-neutral-200">
        <div class="font-bold text-neutral-900">Recruitment & Hiring Workflow</div>
        <p class="text-sm text-neutral-600 mt-1">
          Assigned to company: <strong>{{ authStore.user?.subsidiary?.name || 'All Holding Companies' }}</strong>. Ready for Module 2: Vacancy Creation, Approval, and Publication.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
</script>
