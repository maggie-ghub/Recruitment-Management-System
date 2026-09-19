<template>
  <div class="min-h-screen space-y-6">

    <!-- ── Page Header ─────────────────────────────────────────────────── -->
    <div class="flex flex-col sm:flex-row sm:items-end gap-4 justify-between">
      <div>
        <nav class="flex items-center gap-1.5 text-[11px] text-[#7a6e5a] mb-3 font-medium">
          <router-link to="/vacancies" class="hover:text-[#db802d] transition-colors">Vacancies</router-link>
          <svg class="w-3 h-3 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          <router-link :to="`/vacancies/${vacancyId}`" class="hover:text-[#db802d] transition-colors truncate max-w-[160px]">{{ vacancy?.title || '…' }}</router-link>
          <svg class="w-3 h-3 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
          <span class="text-neutral-800">Applicants</span>
        </nav>
        <h1 class="text-3xl font-extrabold text-neutral-900 leading-tight">Applicant Pipeline</h1>
        <p v-if="vacancy" class="text-sm text-[#7a6e5a] mt-1 font-mono">
          {{ vacancy.title }} &nbsp;·&nbsp; {{ vacancy.reference_number }}
        </p>
      </div>

      <!-- Stats row -->
      <div v-if="!loading" class="flex flex-wrap gap-2 sm:self-end">
        <div v-for="stat in pipelineStats" :key="stat.label"
          class="px-3 py-2 rounded-2xl border text-xs font-bold transition-all cursor-pointer"
          :class="filterStatus === stat.value
            ? 'bg-[#db802d] text-white border-[#db802d] shadow-md shadow-orange-900/15'
            : `${stat.bg} ${stat.color} ${stat.border} hover:shadow-sm`"
          @click="filterStatus = filterStatus === stat.value ? '' : stat.value"
        >
          <span class="text-base font-extrabold">{{ stat.count }}</span>
          <span class="ml-1.5 font-semibold">{{ stat.label }}</span>
        </div>
      </div>
    </div>

    <!-- ── Toolbar ──────────────────────────────────────────────────────── -->
    <div class="flex flex-wrap items-center gap-3 bg-white px-5 py-3.5 rounded-3xl border border-[#f0e9dc] shadow-xs">
      <div class="flex-1 min-w-[200px] relative">
        <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input v-model="search" type="text" placeholder="Search name or email…"
          class="w-full pl-10 pr-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#db802d]/20 focus:border-[#db802d] bg-neutral-50 transition-all"/>
      </div>
      <select v-model="sortBy"
        class="pl-3.5 pr-7 py-2.5 rounded-2xl border border-neutral-200 text-xs font-medium focus:outline-none bg-neutral-50 cursor-pointer">
        <option value="newest">Newest First</option>
        <option value="oldest">Oldest First</option>
        <option value="name">Name A–Z</option>
      </select>
      <button @click="loadApplications"
        class="flex items-center gap-1.5 px-4 py-2.5 rounded-2xl text-xs font-semibold bg-[#faf8f5] border border-[#f0e9dc] text-[#7a6e5a] hover:border-[#db802d]/40 hover:text-[#db802d] transition-all cursor-pointer">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
        Refresh
      </button>
      <span class="text-xs text-neutral-400 font-medium ml-auto">{{ filteredApplications.length }} result{{ filteredApplications.length !== 1 ? 's' : '' }}</span>
    </div>

    <!-- ── Loading ──────────────────────────────────────────────────────── -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="n in 6" :key="n" class="bg-white rounded-3xl border border-neutral-100 p-5 animate-pulse">
        <div class="flex items-center gap-3 mb-4">
          <div class="w-12 h-12 rounded-2xl bg-neutral-100"></div>
          <div class="flex-1 space-y-1.5"><div class="h-3 bg-neutral-100 rounded w-3/4"></div><div class="h-2 bg-neutral-100 rounded w-1/2"></div></div>
        </div>
        <div class="h-2 bg-neutral-100 rounded w-full mb-2"></div>
        <div class="h-2 bg-neutral-100 rounded w-2/3"></div>
      </div>
    </div>

    <!-- ── Empty ────────────────────────────────────────────────────────── -->
    <div v-else-if="filteredApplications.length === 0" class="bg-white rounded-3xl border border-[#f0e9dc] p-16 text-center shadow-xs">
      <div class="w-20 h-20 rounded-3xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100 flex items-center justify-center mx-auto mb-5">
        <svg class="w-9 h-9 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
      </div>
      <p class="text-base font-bold text-neutral-700">{{ filterStatus || search ? 'No matches found' : 'No applications yet' }}</p>
      <p class="text-sm text-neutral-400 mt-1.5">{{ filterStatus || search ? 'Try clearing your filters.' : 'Applications will appear here once candidates apply.' }}</p>
      <button v-if="filterStatus || search" @click="filterStatus='';search=''" class="mt-4 px-5 py-2 rounded-2xl text-xs font-semibold bg-neutral-100 text-neutral-600 hover:bg-neutral-200 cursor-pointer transition-all">Clear Filters</button>
    </div>

    <!-- ── Cards Grid ───────────────────────────────────────────────────── -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
      <div
        v-for="app in filteredApplications"
        :key="app.id"
        class="group bg-white rounded-3xl border border-[#f0e9dc] shadow-xs hover:shadow-lg hover:border-[#db802d]/20 transition-all duration-200 flex flex-col overflow-hidden"
      >
        <!-- Card top accent -->
        <div class="h-1 w-full" :class="getStatusAccent(app.status)"></div>

        <div class="p-5 flex-1 flex flex-col">
          <!-- Applicant info -->
          <div class="flex items-start justify-between gap-3 mb-4">
            <div class="flex items-center gap-3">
              <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 text-base font-extrabold"
                :style="getAvatarStyle(app.applicant?.name)">
                {{ (app.applicant?.name || '?').charAt(0).toUpperCase() }}
              </div>
              <div class="min-w-0">
                <div class="font-bold text-neutral-900 text-sm leading-tight truncate">{{ app.applicant?.name || '—' }}</div>
                <div class="text-xs text-[#7a6e5a] truncate">{{ app.applicant?.email || '—' }}</div>
              </div>
            </div>
            <span class="shrink-0 px-2.5 py-1 rounded-full text-[10px] font-bold" :class="getStatusClass(app.status)">
              {{ app.status }}
            </span>
          </div>

          <!-- Applied date + CV -->
          <div class="space-y-1.5 mb-4">
            <div class="flex items-center gap-2 text-[11px] text-neutral-500">
              <svg class="w-3 h-3 shrink-0 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
              Applied {{ new Date(app.created_at).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) }}
            </div>
            <div v-if="app.cv_document" class="flex items-center gap-2 text-[11px] text-blue-600 font-medium">
              <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              <span class="truncate max-w-[160px]">{{ app.cv_document.original_filename }}</span>
            </div>
          </div>

          <!-- Quick status change -->
          <div class="mt-auto pt-4 border-t border-neutral-100 flex items-center gap-2">
            <select
              v-if="app.status !== 'Withdrawn'"
              v-model="app._pendingStatus"
              @change="updateStatus(app)"
              class="flex-1 pl-2.5 pr-6 py-1.5 rounded-xl border border-neutral-200 text-[11px] font-medium focus:outline-none focus:ring-1 focus:ring-[#db802d] bg-white cursor-pointer"
            >
              <option value="" disabled>Move to stage…</option>
              <option value="Under Review">Under Review</option>
              <option value="Shortlisted">Shortlisted</option>
              <option value="Interview Scheduled">Interview Scheduled</option>
              <option value="Offer Extended">Offer Extended</option>
              <option value="Hired">Hired</option>
              <option value="Rejected">Rejected</option>
            </select>
            <span v-else class="text-[11px] text-neutral-400 italic flex-1">Withdrawn by applicant</span>

            <button
              @click="openProfile(app)"
              class="shrink-0 px-3 py-1.5 rounded-xl text-[11px] font-bold bg-[#db802d] text-white hover:bg-[#c46f20] cursor-pointer transition-all shadow-sm shadow-orange-900/10"
            >
              View Profile
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- Full Applicant Profile — Right Side Drawer                        -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <Transition name="drawer">
      <div v-if="profileModal.show" class="fixed inset-0 z-50 flex justify-end">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="profileModal.show = false"></div>

        <!-- Panel -->
        <div class="relative z-10 w-full max-w-[680px] h-full bg-white shadow-2xl flex flex-col overflow-hidden">

          <!-- Drawer header -->
          <div class="bg-gradient-to-r from-[#faf8f5] to-amber-50/60 border-b border-[#f0e9dc] px-7 py-5 shrink-0">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-xl font-extrabold shrink-0 shadow-inner"
                :style="getAvatarStyle(profileModal.data?.user?.name)">
                {{ (profileModal.data?.user?.name || '?').charAt(0).toUpperCase() }}
              </div>
              <div class="flex-1 min-w-0">
                <h2 class="font-extrabold text-xl text-neutral-900 leading-tight truncate">{{ profileModal.data?.user?.name || '—' }}</h2>
                <p class="text-xs text-[#7a6e5a] mt-0.5">{{ profileModal.data?.user?.email || '—' }}</p>
                <span class="inline-block mt-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold" :class="getStatusClass(profileModal.application?.status)">
                  {{ profileModal.application?.status }}
                </span>
              </div>
              <button @click="profileModal.show = false" class="w-9 h-9 rounded-xl flex items-center justify-center text-neutral-400 hover:text-neutral-700 hover:bg-white/70 cursor-pointer transition-all shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
          </div>

          <!-- Loading skeleton -->
          <div v-if="profileModal.loading" class="flex-1 p-7 space-y-4">
            <div v-for="n in 5" :key="n" class="bg-neutral-50 rounded-2xl p-4 animate-pulse">
              <div class="h-3 bg-neutral-200 rounded w-1/3 mb-3"></div>
              <div class="h-2 bg-neutral-100 rounded w-full mb-1.5"></div>
              <div class="h-2 bg-neutral-100 rounded w-2/3"></div>
            </div>
          </div>

          <!-- Content -->
          <div v-else class="flex-1 overflow-y-auto">

            <!-- Tab bar -->
            <div class="flex border-b border-neutral-100 px-7 gap-1 shrink-0 bg-white sticky top-0 z-10">
              <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                class="px-4 py-3 text-xs font-bold border-b-2 transition-all cursor-pointer"
                :class="activeTab === tab.id
                  ? 'text-[#db802d] border-[#db802d]'
                  : 'text-neutral-500 border-transparent hover:text-neutral-700 hover:border-neutral-200'">
                {{ tab.label }}
                <span v-if="tab.count > 0" class="ml-1.5 px-1.5 py-0.5 rounded-full text-[9px] font-extrabold"
                  :class="activeTab === tab.id ? 'bg-[#db802d] text-white' : 'bg-neutral-100 text-neutral-500'">
                  {{ tab.count }}
                </span>
              </button>
            </div>

            <div class="p-7 space-y-5">

              <!-- ── OVERVIEW tab ──────────────────────────────────────── -->
              <template v-if="activeTab === 'overview'">

                <!-- Cover Letter -->
                <div v-if="profileModal.application?.cover_letter" class="relative p-5 rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50/40 border border-amber-200">
                  <div class="absolute -top-2.5 left-5 px-2.5 py-0.5 rounded-full bg-[#db802d] text-white text-[10px] font-bold">Cover Letter</div>
                  <p class="text-sm text-neutral-700 leading-relaxed whitespace-pre-line mt-1">{{ profileModal.application.cover_letter }}</p>
                </div>
                <div v-else class="flex items-center gap-2 text-xs text-neutral-400 p-3 rounded-xl bg-neutral-50 border border-neutral-100">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                  No cover letter provided.
                </div>

                <!-- Screening Questions and Answers -->
                <div v-if="profileModal.application?.vacancy?.questions?.length" class="bg-white rounded-2xl border border-[#f0e9dc] p-5">
                  <h3 class="text-xs font-bold text-neutral-900 mb-3 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Screening Questions
                  </h3>
                  <div class="space-y-3">
                    <div v-for="(question, index) in profileModal.application.vacancy.questions" :key="question.id" class="text-xs">
                      <div class="font-semibold text-neutral-800">{{ index + 1 }}. {{ question.question_text }}</div>
                      <div class="mt-1 p-2.5 rounded-xl bg-[#faf8f5] border border-[#f0e9dc] text-neutral-600 whitespace-pre-line">
                        {{ profileModal.application.screening_answers?.[question.id] || 'No answer provided.' }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Personal Info grid -->
                <div class="bg-white rounded-2xl border border-[#f0e9dc] p-5">
                  <h3 class="text-xs font-bold text-neutral-900 mb-3 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Personal Information
                  </h3>
                  <div v-if="profileModal.profile" class="grid grid-cols-2 gap-3 text-xs">
                    <div v-if="profileModal.profile.phone"><span class="text-[#7a6e5a]">Phone</span><div class="font-semibold text-neutral-800 mt-0.5">{{ profileModal.profile.phone }}</div></div>
                    <div v-if="profileModal.profile.date_of_birth"><span class="text-[#7a6e5a]">Date of Birth</span><div class="font-semibold text-neutral-800 mt-0.5">{{ profileModal.profile.date_of_birth }}</div></div>
                    <div v-if="profileModal.profile.city || profileModal.profile.country" class="col-span-2">
                      <span class="text-[#7a6e5a]">Location</span>
                      <div class="font-semibold text-neutral-800 mt-0.5">{{ [profileModal.profile.city, profileModal.profile.country].filter(Boolean).join(', ') }}</div>
                    </div>
                  </div>
                  <p v-if="profileModal.profile?.bio" class="text-xs text-neutral-600 mt-3 pt-3 border-t border-neutral-100 leading-relaxed italic">{{ profileModal.profile.bio }}</p>
                  <p v-if="!profileModal.profile" class="text-xs text-neutral-400">No personal info on file.</p>
                </div>

                <!-- Skills -->
                <div v-if="profileModal.skills?.length" class="bg-white rounded-2xl border border-[#f0e9dc] p-5">
                  <h3 class="text-xs font-bold text-neutral-900 mb-3 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Skills
                  </h3>
                  <div class="flex flex-wrap gap-1.5">
                    <span v-for="skill in profileModal.skills" :key="skill.id"
                      class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#faf8f5] border border-[#f0e9dc] text-[#7a6e5a]">
                      {{ skill.name }}<span v-if="skill.level" class="text-neutral-400 ml-1">· {{ skill.level }}</span>
                    </span>
                  </div>
                </div>
              </template>

              <!-- ── EDUCATION tab ─────────────────────────────────────── -->
              <template v-if="activeTab === 'education'">
                <div v-if="!profileModal.educations?.length" class="text-center py-8 text-sm text-neutral-400">No education history on file.</div>
                <div v-for="edu in profileModal.educations" :key="edu.id"
                  class="relative bg-white rounded-2xl border border-[#f0e9dc] p-5 hover:border-amber-200 transition-all">
                  <div class="absolute left-0 top-5 bottom-5 w-0.5 rounded-full bg-amber-300 ml-0 -translate-x-px"></div>
                  <div class="font-bold text-neutral-900 text-sm">{{ edu.degree }} <span class="font-normal text-[#7a6e5a]">in {{ edu.field_of_study }}</span></div>
                  <div class="text-xs text-[#7a6e5a] mt-0.5 font-medium">{{ edu.institution }}</div>
                  <div class="text-[10px] text-neutral-400 mt-1 flex items-center gap-2">
                    <span>{{ edu.start_date }} — {{ edu.is_current ? 'Present' : (edu.end_date || '—') }}</span>
                    <span v-if="edu.grade" class="px-1.5 py-0.5 rounded bg-neutral-100 text-neutral-600">GPA: {{ edu.grade }}</span>
                  </div>
                </div>
              </template>

              <!-- ── EXPERIENCE tab ────────────────────────────────────── -->
              <template v-if="activeTab === 'experience'">
                <div v-if="!profileModal.experiences?.length" class="text-center py-8 text-sm text-neutral-400">No work experience on file.</div>
                <div v-for="exp in profileModal.experiences" :key="exp.id"
                  class="bg-white rounded-2xl border border-[#f0e9dc] p-5 hover:border-amber-200 transition-all">
                  <div class="flex items-start justify-between gap-2">
                    <div>
                      <div class="font-bold text-neutral-900 text-sm">{{ exp.job_title }}</div>
                      <div class="text-xs text-[#7a6e5a] font-medium mt-0.5">{{ exp.company_name }}</div>
                    </div>
                    <span v-if="exp.is_current" class="shrink-0 px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-100 text-emerald-700">Current</span>
                  </div>
                  <div class="text-[10px] text-neutral-400 mt-1">{{ exp.start_date }} — {{ exp.is_current ? 'Present' : (exp.end_date || '—') }}</div>
                  <p v-if="exp.responsibilities" class="text-xs text-neutral-600 mt-3 leading-relaxed whitespace-pre-line border-t border-neutral-100 pt-3">{{ exp.responsibilities }}</p>
                </div>
              </template>

              <!-- ── DOCUMENTS tab ─────────────────────────────────────── -->
              <template v-if="activeTab === 'documents'">
                <div v-if="!profileModal.documents?.length" class="text-center py-8 text-sm text-neutral-400">No documents uploaded.</div>
                <div v-for="doc in profileModal.documents" :key="doc.id"
                  class="flex items-center gap-3.5 p-4 rounded-2xl bg-white border border-[#f0e9dc] hover:border-amber-200 hover:shadow-sm transition-all">
                  <!-- Icon -->
                  <div class="w-10 h-10 rounded-2xl flex items-center justify-center shrink-0"
                    :class="doc.type === 'cv' ? 'bg-amber-100' : doc.type === 'application_letter' ? 'bg-blue-100' : 'bg-neutral-100'">
                    <svg class="w-5 h-5" :class="doc.type === 'cv' ? 'text-amber-600' : doc.type === 'application_letter' ? 'text-blue-600' : 'text-neutral-500'"
                      fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <div class="text-xs font-bold text-neutral-800 truncate">{{ doc.original_filename }}</div>
                    <div class="text-[10px] text-[#7a6e5a] capitalize mt-0.5">
                      {{ (doc.type || 'document').replace(/_/g, ' ') }}
                      <span class="mx-1.5 text-neutral-300">·</span>
                      {{ doc.mime_type || '' }}
                    </div>
                  </div>
                  <div class="flex gap-1.5 shrink-0">
                    <button @click="previewDoc(doc)"
                      class="px-3 py-1.5 rounded-xl text-[10px] font-bold bg-blue-50 text-blue-700 hover:bg-blue-100 cursor-pointer transition-all">
                      Preview
                    </button>
                    <button @click="downloadDoc(doc)"
                      class="px-3 py-1.5 rounded-xl text-[10px] font-bold bg-amber-50 text-amber-700 hover:bg-amber-100 cursor-pointer transition-all">
                      Download
                    </button>
                  </div>
                </div>
              </template>

            </div>
          </div>

          <!-- Drawer footer: quick status change -->
          <div v-if="!profileModal.loading && profileModal.application?.status !== 'Withdrawn'"
            class="shrink-0 px-7 py-4 border-t border-neutral-100 bg-[#faf8f5] flex items-center gap-3">
            <span class="text-xs font-semibold text-neutral-600">Move to:</span>
            <select v-model="drawerPendingStatus" @change="updateStatusFromDrawer"
              class="flex-1 pl-3 pr-7 py-2 rounded-xl border border-neutral-200 text-xs font-medium focus:outline-none focus:ring-1 focus:ring-[#db802d] bg-white cursor-pointer">
              <option value="" disabled>Select stage…</option>
              <option value="Under Review">Under Review</option>
              <option value="Shortlisted">Shortlisted</option>
              <option value="Interview Scheduled">Interview Scheduled</option>
              <option value="Offer Extended">Offer Extended</option>
              <option value="Hired">Hired</option>
              <option value="Rejected">Rejected</option>
            </select>
          </div>

        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { computed, reactive, ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../api/client'
import { useNotificationStore } from '../../stores/notification'

const API_BASE = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace(/\/$/, '')

const route = useRoute()
const notificationStore = useNotificationStore()

const vacancyId = computed(() => route.params.id)
const vacancy = ref(null)
const applications = ref([])
const loading = ref(false)
const search = ref('')
const filterStatus = ref('')
const sortBy = ref('newest')
const activeTab = ref('overview')
const drawerPendingStatus = ref('')

const profileModal = reactive({
  show: false,
  loading: false,
  application: null,
  data: null,
  profile: null,
  educations: [],
  experiences: [],
  skills: [],
  documents: [],
})

// ── Pipeline stats for the filter badges ────────────────────────────────
const STATUS_META = {
  Submitted:           { bg: 'bg-blue-50',    color: 'text-blue-700',    border: 'border-blue-200' },
  'Under Review':      { bg: 'bg-amber-50',   color: 'text-amber-700',   border: 'border-amber-200' },
  Shortlisted:         { bg: 'bg-emerald-50', color: 'text-emerald-700', border: 'border-emerald-200' },
  'Interview Scheduled': { bg: 'bg-purple-50', color: 'text-purple-700', border: 'border-purple-200' },
  'Offer Extended':    { bg: 'bg-cyan-50',    color: 'text-cyan-700',    border: 'border-cyan-200' },
  Hired:               { bg: 'bg-green-100',  color: 'text-green-800',   border: 'border-green-200' },
  Rejected:            { bg: 'bg-red-50',     color: 'text-red-700',     border: 'border-red-200' },
  Withdrawn:           { bg: 'bg-neutral-100',color: 'text-neutral-500', border: 'border-neutral-200' },
}

const pipelineStats = computed(() => {
  const counts = {}
  applications.value.forEach(a => { counts[a.status] = (counts[a.status] || 0) + 1 })
  return Object.entries(counts).map(([status, count]) => ({
    label: status, value: status, count,
    ...(STATUS_META[status] || { bg: 'bg-neutral-100', color: 'text-neutral-600', border: 'border-neutral-200' }),
  }))
})

// ── Tabs definition ──────────────────────────────────────────────────────
const tabs = computed(() => [
  { id: 'overview',    label: 'Overview',    count: 0 },
  { id: 'education',   label: 'Education',   count: profileModal.educations?.length || 0 },
  { id: 'experience',  label: 'Experience',  count: profileModal.experiences?.length || 0 },
  { id: 'documents',   label: 'Documents',   count: profileModal.documents?.length || 0 },
])

// ── Data loading ─────────────────────────────────────────────────────────
const loadApplications = async () => {
  loading.value = true
  try {
    const [vacRes, appRes] = await Promise.all([
      api.get(`/vacancies/${vacancyId.value}`),
      api.get(`/vacancies/${vacancyId.value}/applications`),
    ])
    vacancy.value = vacRes.data.data
    applications.value = appRes.data.data.map(a => ({ ...a, _pendingStatus: '' }))
  } catch (err) {
    notificationStore.error(err.response?.data?.message || 'Failed to load applications.', 'Error')
  } finally {
    loading.value = false
  }
}

// ── Filtering + Sorting ──────────────────────────────────────────────────
const filteredApplications = computed(() => {
  let list = [...applications.value]
  if (filterStatus.value) list = list.filter(a => a.status === filterStatus.value)
  if (search.value.trim()) {
    const q = search.value.trim().toLowerCase()
    list = list.filter(a => a.applicant?.name?.toLowerCase().includes(q) || a.applicant?.email?.toLowerCase().includes(q))
  }
  if (sortBy.value === 'newest')  list.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  if (sortBy.value === 'oldest')  list.sort((a, b) => new Date(a.created_at) - new Date(b.created_at))
  if (sortBy.value === 'name')    list.sort((a, b) => (a.applicant?.name || '').localeCompare(b.applicant?.name || ''))
  return list
})

// ── Status update (from card) ────────────────────────────────────────────
const updateStatus = async (app) => {
  const newStatus = app._pendingStatus
  if (!newStatus || newStatus === app.status) { app._pendingStatus = ''; return }
  try {
    await api.patch(`/applications/${app.id}/status`, { status: newStatus })
    app.status = newStatus
    app._pendingStatus = ''
    if (profileModal.application?.id === app.id) profileModal.application.status = newStatus
    notificationStore.success(`Moved to "${newStatus}".`)
  } catch (err) {
    app._pendingStatus = ''
    notificationStore.error(err.response?.data?.message || 'Failed to update status.', 'Error')
  }
}

// ── Status update (from drawer) ──────────────────────────────────────────
const updateStatusFromDrawer = async () => {
  const newStatus = drawerPendingStatus.value
  if (!newStatus || !profileModal.application) { drawerPendingStatus.value = ''; return }
  const app = applications.value.find(a => a.id === profileModal.application.id)
  if (app) app._pendingStatus = newStatus
  await updateStatus(app || profileModal.application)
  drawerPendingStatus.value = ''
}

// ── Open full profile drawer ─────────────────────────────────────────────
const openProfile = async (app) => {
  profileModal.show    = true
  profileModal.loading = true
  profileModal.application = app
  profileModal.data    = { user: app.applicant }
  profileModal.profile = null
  profileModal.educations  = []
  profileModal.experiences = []
  profileModal.skills      = []
  profileModal.documents   = []
  activeTab.value          = 'overview'
  drawerPendingStatus.value = ''

  try {
    const res = await api.get(`/applicant-profiles/${app.applicant?.id || app.user_id}`)
    profileModal.profile     = res.data.profile
    profileModal.educations  = res.data.educations
    profileModal.experiences = res.data.experiences
    profileModal.skills      = res.data.skills
    profileModal.documents   = res.data.documents
    const matchingApplication = (res.data.applications || []).find(item => item.id === app.id)
    if (matchingApplication) profileModal.application = matchingApplication
  } catch (err) {
    notificationStore.error(err.response?.data?.message || 'Failed to load profile.', 'Error')
  } finally {
    profileModal.loading = false
  }
}

// ── Document handlers ────────────────────────────────────────────────────
const previewDoc = async (doc) => {
  try {
    const token = localStorage.getItem('vms_auth_token')
    const res = await fetch(`${API_BASE}/applicant/documents/${doc.id}/view`, {
      headers: { Authorization: `Bearer ${token}`, Accept: '*/*' },
    })
    if (!res.ok) throw new Error()
    const url = URL.createObjectURL(await res.blob())
    window.open(url, '_blank')
  } catch {
    notificationStore.error('Unable to preview this document.', 'Preview Error')
  }
}

const downloadDoc = async (doc) => {
  try {
    const tokenRes = await api.post(`/applicant/documents/${doc.id}/request-token`)
    window.location.href = `${API_BASE}/documents/stream/${tokenRes.data.token}`
  } catch {
    notificationStore.error('Failed to download document.', 'Download Error')
  }
}

// ── Styling helpers ──────────────────────────────────────────────────────
const AVATAR_PALETTES = [
  ['#FFF3E0', '#E65100'], ['#E8F5E9', '#1B5E20'], ['#EDE7F6', '#4527A0'],
  ['#E3F2FD', '#0D47A1'], ['#FCE4EC', '#880E4F'], ['#F3E5F5', '#6A1B9A'],
]
const getAvatarStyle = (name = '') => {
  const i = name.charCodeAt(0) % AVATAR_PALETTES.length
  const [bg, color] = AVATAR_PALETTES[i]
  return { backgroundColor: bg, color }
}
const getStatusAccent = (status) => {
  switch (status) {
    case 'Submitted':           return 'bg-blue-400'
    case 'Under Review':        return 'bg-amber-400'
    case 'Shortlisted':         return 'bg-emerald-400'
    case 'Interview Scheduled': return 'bg-purple-400'
    case 'Offer Extended':      return 'bg-cyan-400'
    case 'Hired':               return 'bg-green-500'
    case 'Rejected':            return 'bg-red-400'
    case 'Withdrawn':           return 'bg-neutral-300'
    default:                    return 'bg-neutral-200'
  }
}
const getStatusClass = (status) => {
  const m = STATUS_META[status]
  return m ? `${m.bg} ${m.color} border ${m.border}` : 'bg-neutral-100 text-neutral-600'
}

onMounted(loadApplications)
</script>

<style scoped>
.drawer-enter-active, .drawer-leave-active {
  transition: opacity 0.25s ease;
}
.drawer-enter-active .relative,
.drawer-leave-active .relative {
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.drawer-enter-from { opacity: 0; }
.drawer-enter-from .relative { transform: translateX(100%); }
.drawer-leave-to { opacity: 0; }
.drawer-leave-to .relative { transform: translateX(100%); }
</style>
