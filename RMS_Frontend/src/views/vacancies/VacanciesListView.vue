<template>
  <div class="space-y-6 pb-12">
    <!-- Header with Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-3xl font-extrabold text-neutral-900 tracking-tight">
          {{ authStore.isApplicant ? 'Career Opportunities' : 'Vacancy Management' }}
        </h1>
        <p class="text-sm text-[#7a6e5a] mt-1">
          {{ authStore.isApplicant 
              ? 'Explore open positions across Tora Holding Company subsidiaries' 
              : 'Prepare requisitions, track approvals, publish, and monitor applications' 
          }}
        </p>
      </div>

      <router-link
        v-if="canCreateVacancy"
        to="/vacancies/create"
        class="inline-flex items-center px-5 py-3 rounded-2xl text-sm font-bold w-fit text-white bg-[#db802d] hover:bg-[#c46f20] shadow-sm transition-all cursor-pointer"
      >
        Create Vacancy
      </router-link>
    </div>

    <!-- Vacancy Filter Controls -->
    <div class="bg-white p-4 sm:p-5 rounded-3xl border border-[#f0e9dc] shadow-xs flex flex-wrap items-center gap-3.5">
      <!-- Search input -->
      <div class="flex-1 min-w-[240px]">
        <input
          v-model="vacancyStore.filters.search"
          @input="loadVacancies"
          type="text"
          placeholder="Search by title, reference code, or location"
          class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] bg-neutral-50"
        />
      </div>

      <!-- Company filter (if Holding user) -->
      <select
        v-if="!authStore.user?.subsidiary_id"
        v-model="vacancyStore.filters.subsidiary_id"
        @change="loadVacancies"
        class="pl-3.5 pr-8 py-2.5 rounded-2xl border border-neutral-200 text-xs font-medium focus:outline-none focus:ring-1 focus:ring-[#db802d] bg-neutral-50 cursor-pointer"
      >
        <option value="">All Companies</option>
        <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
      </select>

      <!-- Status filter (internal recruitment staff only) -->
      <select
        v-if="!authStore.isApplicant"
        v-model="vacancyStore.filters.status"
        @change="loadVacancies"
        class="pl-3.5 pr-8 py-2.5 rounded-2xl border border-neutral-200 text-xs font-medium focus:outline-none focus:ring-1 focus:ring-[#db802d] bg-neutral-50 cursor-pointer"
      >
        <option value="">All Statuses</option>
        <option value="Draft">Draft</option>
        <option value="Pending Approval">Pending Approval</option>
        <option value="Approved">Approved</option>
        <option value="Published">Published</option>
        <option value="Closed">Closed</option>
        <option value="Archived">Archived</option>
      </select>

      <!-- Employment Type filter -->
      <select
        v-model="vacancyStore.filters.employment_type"
        @change="loadVacancies"
        class="pl-3.5 pr-8 py-2.5 rounded-2xl border border-neutral-200 text-xs font-medium focus:outline-none focus:ring-1 focus:ring-[#db802d] bg-neutral-50 cursor-pointer"
      >
        <option value="">All Types</option>
        <option value="Full-time">Full-time</option>
        <option value="Part-time">Part-time</option>
        <option value="Contract">Contract</option>
        <option value="Remote">Remote</option>
        <option value="Hybrid">Hybrid</option>
      </select>
    </div>

    <!-- Vacancies List -->
    <div v-if="vacancyStore.loading" class="bg-white rounded-3xl border border-[#f0e9dc] p-16 text-center text-base text-[#7a6e5a]">
      Loading vacancies...
    </div>

    <div v-else-if="vacancyStore.vacancies.length === 0" class="bg-white rounded-3xl border border-[#f0e9dc] p-16 text-center text-base text-neutral-500">
      No vacancies found matching the current criteria.
    </div>

    <!-- Beautiful Modern Vacancy Cards Grid (Requirement #15: Beautiful SaaS Cards) -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div
        v-for="vacancy in vacancyStore.vacancies"
        :key="vacancy.id"
        class="bg-white rounded-3xl border border-[#f0e9dc] p-6 sm:p-7 shadow-xs hover:border-[#db802d]/60 hover:shadow-lg transition-all duration-200 flex flex-col justify-between group relative"
      >
        <div>
          <!-- Top Row: Company Logo & Details + Status Badge -->
          <div class="flex items-start justify-between gap-3 mb-4">
            <div class="flex items-center gap-3.5 min-w-0">
              <!-- Company Logo / Initials with Nice Center / Fit -->
              <div class="w-12 h-12 rounded-2xl bg-transparent border border-neutral-200/80 flex items-center justify-center shrink-0 overflow-hidden shadow-xs">
                <img
                  v-if="vacancy.subsidiary?.logo_url"
                  :src="getLogoUrl(vacancy.subsidiary)"
                  :alt="vacancy.subsidiary?.name"
                  class="w-full h-full object-contain object-center"
                />
                <span v-else class="text-sm font-extrabold text-[#db802d]">
                  {{ (vacancy.subsidiary?.code || vacancy.subsidiary?.name || 'TOR').substring(0, 3).toUpperCase() }}
                </span>
              </div>
              <div class="min-w-0">
                <span class="font-bold text-sm text-neutral-900 truncate block">
                  {{ vacancy.subsidiary?.name }}
                </span>
                <span class="text-xs text-[#7a6e5a] block font-mono">
                  Ref: {{ vacancy.reference_number }}
                </span>
              </div>
            </div>

            <span
              class="px-3 py-1 rounded-full text-xs font-bold shrink-0 tracking-wider"
              :class="getStatusBadgeClass(vacancy.status)"
            >
              {{ vacancy.status }}
            </span>
          </div>

          <!-- Position Title -->
          <h3 class="font-extrabold text-xl text-neutral-900 group-hover:text-[#db802d] transition-colors leading-snug">
            {{ vacancy.title }}
          </h3>

          <!-- Meta Pills: Clean SaaS badges with warm colors -->
          <div class="flex flex-wrap items-center gap-2 mt-4 text-xs font-medium text-neutral-700">
            <span class="inline-flex items-center gap-1.5 bg-[#faf8f5] px-3 py-1.5 rounded-xl border border-[#f0e9dc]">
              <svg class="w-4 h-4 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              {{ vacancy.location }}
            </span>

            <span class="inline-flex items-center gap-1.5 bg-[#faf8f5] px-3 py-1.5 rounded-xl border border-[#f0e9dc]">
              <svg class="w-4 h-4 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              {{ vacancy.employment_type }}
            </span>

            <span class="inline-flex items-center gap-1.5 bg-[#faf8f5] px-3 py-1.5 rounded-xl border border-[#f0e9dc]">
              <svg class="w-4 h-4 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              {{ vacancy.openings }} Opening(s)
            </span>

            <!-- Internal Salary Tag without lock icon (Confidential for internal staff) -->
            <span
              v-if="vacancy.salary_amount && !authStore.isApplicant"
              class="inline-flex items-center gap-1 bg-amber-50 text-amber-900 px-3 py-1.5 rounded-xl text-xs font-bold border border-amber-200"
              title="Confidential Internal Salary"
            >
              {{ vacancy.salary_amount }} {{ vacancy.salary_currency }}
            </span>
          </div>

          <!-- Description snippet -->
          <p class="text-sm text-neutral-600 line-clamp-2 mt-4 leading-relaxed">
            {{ vacancy.description }}
          </p>
        </div>

        <!-- Footer Row -->
        <div class="mt-6 pt-4 border-t border-neutral-100 flex items-center justify-between">
          <div class="text-xs text-[#7a6e5a] flex items-center gap-1">
            <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <span>Closing: <strong class="text-neutral-800">{{ new Date(vacancy.closing_date).toLocaleDateString() }}</strong></span>
          </div>

          <router-link
            :to="`/vacancies/${vacancy.id}`"
            class="px-5 py-2.5 rounded-2xl text-xs font-bold text-white bg-[#db802d] hover:bg-[#c46f20] shadow-sm transition-all cursor-pointer"
          >
            {{ authStore.isApplicant ? 'View & Apply' : 'Manage Vacancy' }}
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useAuthStore } from '../../stores/auth'
import { useOrganizationStore } from '../../stores/organization'
import { useVacancyStore } from '../../stores/vacancy'

const authStore = useAuthStore()
const orgStore = useOrganizationStore()
const vacancyStore = useVacancyStore()

const companies = ref([])

const canCreateVacancy = computed(() => {
  return ['Admin', 'HR Manager', 'Recruiter'].includes(authStore.role)
})

const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'Draft': return 'bg-neutral-100 text-neutral-700'
    case 'Pending Approval': return 'bg-amber-100 text-amber-800'
    case 'Approved': return 'bg-blue-100 text-blue-800'
    case 'Published': return 'bg-emerald-100 text-emerald-800'
    case 'Closed': return 'bg-rose-100 text-rose-800'
    case 'Archived': return 'bg-neutral-200 text-neutral-800'
    default: return 'bg-neutral-100 text-neutral-600'
  }
}

const loadVacancies = async () => {
  await vacancyStore.fetchVacancies()
}

const getLogoUrl = (company) => {
  if (!company) return ''
  const url = typeof company === 'string' ? company : company.logo_url
  if (!url) return ''
  if (url.startsWith('http://') || url.startsWith('https://')) {
    return url
  }
  const apiBase = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  const origin = apiBase.replace(/\/api\/?$/, '')
  return `${origin}${url.startsWith('/') ? '' : '/'}${url}`
}

onMounted(async () => {
  companies.value = await orgStore.fetchCompanies('active')
  await loadVacancies()
})
</script>
