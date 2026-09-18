<template>
  <div v-if="vacancyStore.loading" class="bg-white rounded-3xl border border-[#f0e9dc] p-12 text-center text-sm text-[#7a6e5a]">
    Loading vacancy details...
  </div>

  <div v-else-if="!vacancy" class="bg-white rounded-3xl border border-[#f0e9dc] p-12 text-center text-red-600">
    Vacancy not found or access restricted.
  </div>

  <div v-else class="max-w-6xl mx-auto space-y-6 pb-12">
    <!-- Top Breadcrumb Back Navigation -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2 text-xs text-[#7a6e5a]">
        <router-link to="/vacancies" class="hover:text-[#db802d] transition-colors flex items-center gap-1 font-medium">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          Vacancies
        </router-link>
        <span>/</span>
        <span class="text-neutral-800 font-medium truncate max-w-[280px]">{{ vacancy.title }}</span>
      </div>

      <router-link
        to="/vacancies"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-neutral-200 bg-white text-xs font-semibold text-neutral-700 hover:bg-neutral-50 hover:border-neutral-300 transition-all shadow-2xs"
      >
        <svg class="w-3.5 h-3.5 text-[#7a6e5a]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Back
      </router-link>
    </div>

    <!-- Top Action / Lifecycle Status Banner -->
    <div class="bg-white rounded-3xl border border-[#f0e9dc] p-6 shadow-xs">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="flex items-center gap-2 mb-1">
            <span class="text-xs font-semibold px-2.5 py-0.5 rounded-md bg-amber-50 text-[#db802d] border border-amber-200">
              {{ vacancy.subsidiary?.name }}
            </span>
            <span
              class="px-2.5 py-0.5 rounded-full text-xs font-bold"
              :class="getStatusBadgeClass(vacancy.status)"
            >
              {{ vacancy.status }}
            </span>
            <span v-if="!vacancy.is_open && vacancy.status === 'Published'" class="text-xs text-rose-600 font-bold ml-1">
              (Expired)
            </span>
          </div>

          <h1 class="text-2xl font-bold text-neutral-900 leading-tight">
            {{ vacancy.title }}
          </h1>
          <div class="text-xs text-[#7a6e5a] font-mono mt-0.5">
            Reference: {{ vacancy.reference_number }}
          </div>
        </div>

        <!-- Internal Recruitment Lifecycle Actions (Recruiter, HR Manager, Admin) -->
        <div v-if="!authStore.isApplicant" class="flex flex-wrap items-center gap-2">
          <!-- Draft -> Submit for approval (Recruiter) -->
          <button
            v-if="vacancy.status === 'Draft'"
            @click="handleSubmitForApproval"
            class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 cursor-pointer shadow-sm transition-all"
          >
            Submit for Approval &rarr;
          </button>

          <!-- Pending Approval -> Approve or Return (HR Manager / Admin per UC-06) -->
          <template v-if="vacancy.status === 'Pending Approval' && canApprove">
            <button
              @click="openApprovalModal('Approved')"
              class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 cursor-pointer shadow-sm transition-all"
            >
              Approve Vacancy
            </button>
            <button
              @click="openApprovalModal('Draft')"
              class="px-3 py-2 rounded-xl text-xs font-semibold text-neutral-700 bg-neutral-100 hover:bg-neutral-200 cursor-pointer transition-all"
            >
              Return for Revision
            </button>
          </template>

          <!-- Approved -> Publish (FR-VAC-006, UC-07) -->
          <button
            v-if="vacancy.status === 'Approved'"
            @click="handlePublish"
            class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 cursor-pointer shadow-sm transition-all"
          >
            Publish to Job Board
          </button>

          <!-- Published -> Close (FR-VAC-007, UC-11) -->
          <button
            v-if="vacancy.status === 'Published'"
            @click="handleClose"
            class="px-3 py-2 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 cursor-pointer transition-all"
          >
            Close Applications
          </button>

          <!-- Closed -> Reopen (accidental closure recovery) -->
          <button
            v-if="vacancy.status === 'Closed'"
            @click="handleReopen"
            class="px-3 py-2 rounded-xl text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 cursor-pointer transition-all"
          >
            Reopen Vacancy
          </button>

          <!-- Closed -> Archive (FR-VAC-008) -->
          <button
            v-if="vacancy.status === 'Closed'"
            @click="handleArchive"
            class="px-3 py-2 rounded-xl text-xs font-semibold text-neutral-600 bg-neutral-100 hover:bg-neutral-200 cursor-pointer transition-all"
          >
            Archive
          </button>

          <!-- View Applicants (Published / Closed / Archived) -->
          <router-link
            v-if="['Published', 'Closed', 'Archived'].includes(vacancy.status)"
            :to="`/vacancies/${vacancy.id}/applicants`"
            class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-[#db802d] bg-amber-50 border border-amber-200 hover:bg-amber-100 transition-all"
          >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            View Applicants
          </router-link>
        </div>

        <!-- Applicant Action -->
        <div v-else>
          <!-- Already applied -->
          <div v-if="applicationStore.hasApplied(vacancy.id)" class="flex items-center gap-3">
            <span class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
              Application Submitted
            </span>
            <button
              v-if="applicationStore.getApplication(vacancy.id)?.status === 'Submitted'"
              @click="handleWithdraw"
              class="px-3 py-2 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 cursor-pointer transition-all"
            >
              Withdraw
            </button>
            <span class="text-xs text-[#7a6e5a] font-medium">
              Status: <strong>{{ applicationStore.getApplication(vacancy.id)?.status }}</strong>
            </span>
          </div>

          <!-- Vacancy open: show Apply button -->
          <button
            v-else-if="vacancy.is_open"
            @click="openApplyModal"
            class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] shadow-md cursor-pointer transition-all"
          >
            Apply for this Vacancy
          </button>

          <!-- Vacancy closed -->
          <div v-else class="px-4 py-2 rounded-xl text-xs font-bold bg-neutral-100 text-neutral-500">
            Applications Closed
          </div>
        </div>
      </div>
    </div>

    <!-- Vacancy Overview Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Main Content (2 cols) -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Details Card -->
        <div class="bg-white rounded-3xl border border-[#f0e9dc] p-6 sm:p-8 shadow-xs space-y-6">
          <div>
            <h2 class="text-sm font-bold tracking-wider text-[#db802d] mb-2">Job Description</h2>
            <p class="text-sm text-neutral-700 leading-relaxed whitespace-pre-line">{{ vacancy.description }}</p>
          </div>

          <div>
            <h2 class="text-sm font-bold tracking-wider text-[#db802d] mb-2">Key Responsibilities</h2>
            <div class="text-sm text-neutral-700 leading-relaxed whitespace-pre-line">{{ vacancy.responsibilities }}</div>
          </div>

          <div>
            <h2 class="text-sm font-bold tracking-wider text-[#db802d] mb-2">Qualifications & Background</h2>
            <div class="text-sm text-neutral-700 leading-relaxed whitespace-pre-line">{{ vacancy.qualifications }}</div>
          </div>

          <div v-if="vacancy.application_requirements">
            <h2 class="text-sm font-bold tracking-wider text-[#db802d] mb-2">Application Requirements</h2>
            <div class="text-sm text-neutral-700 leading-relaxed whitespace-pre-line">{{ vacancy.application_requirements }}</div>
          </div>
        </div>

        <!-- Custom Screening Questions Manager (BR-009) -->
        <div v-if="!authStore.isApplicant" class="bg-white rounded-3xl border border-[#f0e9dc] p-6 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-sm font-bold tracking-wider text-[#db802d]">
                Custom Screening Questions
              </h2>
              <p class="text-xs text-[#7a6e5a] mt-0.5">Vacancy-specific questions required from applicants</p>
            </div>
            <button
              @click="addQuestionRow"
              class="px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] cursor-pointer"
            >
              + Add Question
            </button>
          </div>

          <div v-if="questions.length === 0" class="p-6 text-center text-xs text-neutral-400 border border-dashed border-[#f0e9dc] rounded-2xl">
            No custom screening questions set. Standard application profile details will be collected.
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="(q, idx) in questions"
              :key="idx"
              class="p-4 rounded-2xl bg-[#faf8f5] border border-[#f0e9dc] space-y-3"
            >
              <div class="flex items-center justify-between gap-3">
                <span class="text-xs font-bold text-neutral-500">Q{{ idx + 1 }}</span>
                <button
                  @click="removeQuestionRow(idx)"
                  class="text-xs text-red-500 hover:text-red-700 font-bold cursor-pointer"
                >
                  Remove
                </button>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="sm:col-span-2">
                  <input
                    v-model="q.question_text"
                    type="text"
                    placeholder="Enter question prompt"
                    class="w-full px-3 py-1.5 rounded-xl border border-neutral-200 text-xs focus:outline-none focus:ring-1 focus:ring-[#db802d] bg-white"
                  />
                </div>
                <div>
                  <select
                    v-model="q.question_type"
                    class="w-full px-3 py-1.5 rounded-xl border border-neutral-200 text-xs focus:outline-none focus:ring-1 focus:ring-[#db802d] bg-white"
                  >
                    <option value="text">Short Text</option>
                    <option value="boolean">Yes / No</option>
                    <option value="number">Numeric</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="pt-2 flex justify-end">
              <button
                @click="saveQuestions"
                class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] cursor-pointer"
              >
                Save Questions
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Sidebar meta & Status History (1 col) -->
      <div class="space-y-6">
        <!-- Requisition Overview Card -->
        <div class="bg-white rounded-3xl border border-[#f0e9dc] p-6 shadow-xs space-y-3.5 text-xs">
          <h2 class="text-sm font-bold tracking-wider text-neutral-900 pb-2 border-b border-neutral-100">
            Requisition Details
          </h2>

          <div class="flex justify-between">
            <span class="text-[#7a6e5a]">Company:</span>
            <strong class="text-neutral-800">{{ vacancy.subsidiary?.name }}</strong>
          </div>

          <div class="flex justify-between">
            <span class="text-[#7a6e5a]">Department:</span>
            <strong class="text-neutral-800">{{ vacancy.department?.name || 'General' }}</strong>
          </div>

          <div class="flex justify-between">
            <span class="text-[#7a6e5a]">Location:</span>
            <strong class="text-neutral-800">{{ vacancy.location }}</strong>
          </div>

          <div class="flex justify-between">
            <span class="text-[#7a6e5a]">Employment Type:</span>
            <strong class="text-neutral-800">{{ vacancy.employment_type }}</strong>
          </div>

          <div class="flex justify-between">
            <span class="text-[#7a6e5a]">Openings:</span>
            <strong class="text-neutral-800">{{ vacancy.openings }}</strong>
          </div>

          <div class="flex justify-between">
            <span class="text-[#7a6e5a]">Closing Date:</span>
            <strong class="text-neutral-800">{{ new Date(vacancy.closing_date).toLocaleDateString() }}</strong>
          </div>

          <!-- Internal Salary (Confidential BR-005, clean without lock icon) -->
          <div v-if="!authStore.isApplicant && vacancy.salary_amount" class="p-3 rounded-xl bg-amber-50 border border-amber-200">
            <div class="text-[10px] font-bold text-amber-900">
              Internal Salary (Confidential)
            </div>
            <div class="text-sm font-extrabold text-neutral-900 mt-1">
              {{ vacancy.salary_amount }} {{ vacancy.salary_currency }}
            </div>
          </div>
        </div>

        <!-- Status & Audit History -->
        <div v-if="!authStore.isApplicant && vacancy.status_histories" class="bg-white rounded-3xl border border-[#f0e9dc] p-6 shadow-xs">
          <h2 class="text-sm font-bold tracking-wider text-neutral-900 pb-2 border-b border-neutral-100 mb-4">
            Approval & Status History
          </h2>

          <div class="space-y-4">
            <div
              v-for="h in vacancy.status_histories"
              :key="h.id"
              class="text-xs border-l-2 border-[#ebb630] pl-3 py-1 space-y-0.5"
            >
              <div class="font-bold text-neutral-800 flex items-center justify-between">
                <span>{{ h.from_status ? `${h.from_status} ` : '' }}{{ h.to_status }}</span>
                <span class="text-[10px] text-[#7a6e5a] font-normal">{{ new Date(h.created_at).toLocaleDateString() }}</span>
              </div>
              <div class="text-[11px] text-neutral-500">By: {{ h.changed_by?.name || 'System' }}</div>
              <div v-if="h.notes" class="text-[11px] text-neutral-700 italic bg-[#faf8f5] p-2 rounded-lg mt-1 border border-[#f0e9dc]">
                "{{ h.notes }}"
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Apply Modal -->
    <!-- Apply Modal -->
    <div v-if="showApplyModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4">
      <div class="bg-white rounded-3xl max-w-2xl w-full shadow-2xl border border-[#f0e9dc] flex flex-col max-h-[92vh]">

        <!-- Modal header -->
        <div class="flex items-center justify-between px-7 py-5 border-b border-neutral-100 shrink-0">
          <div>
            <h3 class="font-extrabold text-xl text-neutral-900">Apply for Position</h3>
            <p class="text-xs text-[#7a6e5a] mt-0.5 font-mono">{{ vacancy.title }} &middot; {{ vacancy.reference_number }}</p>
          </div>
          <button @click="showApplyModal = false" class="w-8 h-8 rounded-xl flex items-center justify-center text-neutral-400 hover:text-neutral-700 hover:bg-neutral-100 cursor-pointer transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>

        <!-- Scrollable body -->
        <div class="overflow-y-auto flex-1 px-7 py-6 space-y-5">

          <!-- No CV warning -->
          <div v-if="!profileStore.primaryCv" class="flex items-start gap-3 p-4 rounded-2xl bg-rose-50 border border-rose-200">
            <svg class="w-4 h-4 text-rose-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-xs text-rose-700 font-medium">You have not uploaded a primary CV. <router-link to="/profile" class="underline font-bold">Go to Profile</router-link> to upload one before applying.</p>
          </div>

          <!-- Documents Section -->
          <div>
            <h4 class="text-xs font-bold text-neutral-900 mb-2.5 flex items-center gap-2">
              <svg class="w-3.5 h-3.5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              Your Documents
            </h4>
            <!-- Primary CV -->
            <div v-if="profileStore.primaryCv" class="flex items-center gap-3 p-3 rounded-2xl bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 mb-2">
              <div class="w-8 h-8 rounded-xl bg-amber-100 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-xs font-bold text-neutral-800 truncate">{{ profileStore.primaryCv.original_filename }}</div>
                <div class="text-[10px] text-amber-700 font-medium">Primary CV &middot; will be attached to this application</div>
              </div>
              <svg class="w-4 h-4 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
            </div>
            <!-- Other documents -->
            <div v-for="doc in otherProfileDocs" :key="doc.id" class="flex items-center gap-3 p-2.5 rounded-xl bg-[#faf8f5] border border-[#f0e9dc] mb-1.5">
              <div class="w-7 h-7 rounded-lg bg-neutral-100 flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-xs font-semibold text-neutral-800 truncate">{{ doc.original_filename }}</div>
                <div class="text-[10px] text-[#7a6e5a] capitalize">{{ (doc.type || "document").replace(/_/g, " ") }}</div>
              </div>
            </div>
            <div v-if="!profileStore.primaryCv && !otherProfileDocs.length" class="text-xs text-neutral-400 text-center py-2">
              No documents uploaded. <router-link to="/profile" class="text-[#db802d] underline">Upload from profile</router-link>
            </div>
          </div>

          <!-- Screening Questions (if any) -->
          <div v-if="vacancy.questions && vacancy.questions.length">
            <h4 class="text-xs font-bold text-neutral-900 mb-2.5 flex items-center gap-2">
              <svg class="w-3.5 h-3.5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              Screening Questions <span class="text-[10px] font-normal text-neutral-500">(required)</span>
            </h4>
            <div class="space-y-3">
              <div v-for="(q, qi) in vacancy.questions" :key="q.id" class="p-3.5 rounded-2xl bg-[#faf8f5] border border-[#f0e9dc]">
                <label class="block text-xs font-semibold text-neutral-800 mb-1.5">
                  {{ qi + 1 }}. {{ q.question_text }}
                  <span v-if="q.is_required" class="text-rose-500 ml-0.5">*</span>
                </label>
                <!-- Text answer -->
                <textarea v-if="q.question_type === `text`" v-model="screeningAnswers[qi]" rows="2"
                  class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-xs focus:outline-none focus:ring-1 focus:ring-[#db802d] resize-none"
                  :placeholder="`Your answer...`"></textarea>
                <!-- Yes/No -->
                <div v-else-if="q.question_type === `boolean`" class="flex gap-3">
                  <label class="flex items-center gap-1.5 cursor-pointer text-xs font-medium">
                    <input type="radio" :name="`sq_${qi}`" value="Yes" v-model="screeningAnswers[qi]" class="accent-[#db802d]" /> Yes
                  </label>
                  <label class="flex items-center gap-1.5 cursor-pointer text-xs font-medium">
                    <input type="radio" :name="`sq_${qi}`" value="No" v-model="screeningAnswers[qi]" class="accent-[#db802d]" /> No
                  </label>
                </div>
                <!-- Number -->
                <input v-else-if="q.question_type === `number`" type="number" v-model="screeningAnswers[qi]"
                  class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-xs focus:outline-none focus:ring-1 focus:ring-[#db802d]"
                  placeholder="Enter a number" />
                <!-- Select / Multiple choice -->
                <select v-else-if="q.question_type === `select`" v-model="screeningAnswers[qi]"
                  class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-xs focus:outline-none focus:ring-1 focus:ring-[#db802d] cursor-pointer">
                  <option value="" disabled>Select an option...</option>
                  <option v-for="opt in (q.options || [])" :key="opt" :value="opt">{{ opt }}</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Cover letter -->
          <div>
            <label class="block text-xs font-bold text-neutral-900 mb-1.5 flex items-center gap-2">
              <svg class="w-3.5 h-3.5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
              Cover Letter <span class="text-[10px] font-normal text-neutral-400 ml-1">(optional)</span>
            </label>
            <textarea v-model="coverLetter" rows="4" maxlength="5000"
              placeholder="Introduce yourself and explain why you are a great fit for this position..."
              class="w-full px-4 py-3 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] resize-none"
            ></textarea>
            <div class="text-right text-[10px] text-neutral-400 mt-1">{{ coverLetter.length }}/5000</div>
          </div>

        </div>

        <!-- Footer -->
        <div class="flex items-center justify-end gap-3 px-7 py-5 border-t border-neutral-100 shrink-0">
          <button type="button" @click="showApplyModal = false"
            class="px-5 py-2.5 rounded-2xl text-xs font-semibold text-neutral-600 hover:bg-neutral-100 cursor-pointer transition-all">
            Cancel
          </button>
          <button @click="submitApplication" :disabled="applicationStore.applying || !profileStore.primaryCv"
            class="px-7 py-2.5 rounded-2xl text-xs font-bold text-white bg-[#db802d] hover:bg-[#c46f20] disabled:opacity-50 cursor-pointer transition-all shadow-md shadow-orange-900/10">
            {{ applicationStore.applying ? "Submitting..." : "Submit Application" }}
          </button>
        </div>
      </div>
    </div>

    <!-- Modal for Approval / Revision Decision Notes -->
    <div v-if="showApprovalModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
      <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#f0e9dc]">
        <div class="flex justify-between items-center pb-3 border-b border-neutral-100">
          <h3 class="font-bold text-lg text-neutral-900">
            {{ pendingDecision === 'Approved' ? 'Approve Vacancy' : 'Return for Revision' }}
          </h3>
          <button @click="showApprovalModal = false" class="text-neutral-400 hover:text-neutral-600 font-bold text-xl cursor-pointer">&times;</button>
        </div>
        <form @submit.prevent="submitApprovalDecision" class="mt-4 space-y-4">
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">
              Reviewer Notes (Optional)
            </label>
            <textarea
              v-model="approvalNotes"
              rows="3"
              placeholder="Enter any feedback, approval comments, or required changes..."
              class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
            ></textarea>
          </div>
          <div class="flex items-center justify-end gap-2 pt-2 border-t border-neutral-100">
            <button
              type="button"
              @click="showApprovalModal = false"
              class="px-4 py-2 rounded-xl text-xs font-semibold text-neutral-600 hover:bg-neutral-100 cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-5 py-2 rounded-xl text-xs font-semibold text-white cursor-pointer"
              :class="pendingDecision === 'Approved' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-amber-600 hover:bg-amber-700'"
            >
              Confirm {{ pendingDecision === 'Approved' ? 'Approval' : 'Return' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useApplicationStore } from '../../stores/application'
import { useApplicantProfileStore } from '../../stores/applicantProfile'
import { useAuthStore } from '../../stores/auth'
import { useNotificationStore } from '../../stores/notification'
import { useVacancyStore } from '../../stores/vacancy'

const route = useRoute()
const authStore = useAuthStore()
const vacancyStore = useVacancyStore()
const notificationStore = useNotificationStore()
const applicationStore = useApplicationStore()
const profileStore = useApplicantProfileStore()

const questions = ref([])
const showApprovalModal = ref(false)
const pendingDecision = ref('Approved')
const approvalNotes = ref('')

// Apply modal state
const showApplyModal = ref(false)
const coverLetter = ref('')
const screeningAnswers = ref([])

// Reset answers when modal opens
const openApplyModal = () => {
  screeningAnswers.value = (vacancy.value?.questions || []).map(() => '')
  coverLetter.value = ''
  showApplyModal.value = true
}

const vacancy = computed(() => vacancyStore.currentVacancy)

const canApprove = computed(() => {
  return ['Admin', 'HR Manager'].includes(authStore.role)
})

// All documents except primary CV (for apply modal display)
const otherProfileDocs = computed(() => {
  const all = profileStore.documents || []
  const primaryId = profileStore.primaryCv?.id
  return all.filter(d => d.id !== primaryId)
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

const loadVacancy = async () => {
  const data = await vacancyStore.fetchVacancy(route.params.id)
  if (data && data.questions) {
    questions.value = JSON.parse(JSON.stringify(data.questions))
  }
}

// Apply for Vacancy
const submitApplication = async () => {
  if (!profileStore.primaryCv) {
    notificationStore.error('Please upload a CV in your profile before applying.', 'No CV Found')
    return
  }
  try {
    await applicationStore.apply(vacancy.value.id, {
      cover_letter: coverLetter.value || undefined,
    })
    showApplyModal.value = false
    coverLetter.value = ''
    screeningAnswers.value = []
    notificationStore.success('Your application has been submitted successfully!', 'Application Sent')
  } catch (err) {
    const msg = err.response?.data?.message || err.message || 'Failed to submit application.'
    notificationStore.error(msg, 'Application Error')
  }
}

const handleWithdraw = async () => {
  const app = applicationStore.getApplication(vacancy.value.id)
  if (!app) return
  try {
    await applicationStore.withdraw(app.id)
    notificationStore.info('Your application has been withdrawn.', 'Withdrawn')
  } catch (err) {
    notificationStore.error('Failed to withdraw application.')
  }
}

// â”€â”€ Internal recruitment actions â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
const handleSubmitForApproval = async () => {
  try {
    await vacancyStore.submitForApproval(route.params.id)
    notificationStore.success('Vacancy submitted for approval.')
  } catch (err) {
    notificationStore.error(err.response?.data?.message || 'Error submitting vacancy.')
  }
}

const handleReopen = async () => {
  try {
    await vacancyStore.reopenVacancy(route.params.id)
    notificationStore.success('Vacancy reopened. Applications are now accepted again.')
  } catch (err) {
    notificationStore.error(err.response?.data?.message || 'Error reopening vacancy.')
  }
}

const openApprovalModal = (decision) => {
  pendingDecision.value = decision
  approvalNotes.value = ''
  showApprovalModal.value = true
}

const submitApprovalDecision = async () => {
  try {
    await vacancyStore.approveVacancy(route.params.id, pendingDecision.value, approvalNotes.value || '')
    notificationStore.success(`Vacancy status updated to ${pendingDecision.value}.`)
    showApprovalModal.value = false
  } catch (err) {
    notificationStore.error(err.response?.data?.message || 'Error updating approval.')
  }
}

const handlePublish = async () => {
  try {
    await vacancyStore.publishVacancy(route.params.id)
    notificationStore.success('Vacancy published successfully to job board.')
  } catch (err) {
    notificationStore.error(err.response?.data?.message || 'Error publishing vacancy.')
  }
}

const handleClose = async () => {
  try {
    await vacancyStore.closeVacancy(route.params.id)
    notificationStore.info('Vacancy closed. Applications blocked.')
  } catch (err) {
    notificationStore.error('Error closing vacancy.')
  }
}

const handleArchive = async () => {
  try {
    await vacancyStore.archiveVacancy(route.params.id)
    notificationStore.info('Vacancy archived.')
  } catch (err) {
    notificationStore.error('Error archiving vacancy.')
  }
}

const addQuestionRow = () => {
  questions.value.push({
    question_text: '',
    question_type: 'text',
    is_required: true,
  })
}

const removeQuestionRow = (idx) => {
  questions.value.splice(idx, 1)
}

const saveQuestions = async () => {
  try {
    await vacancyStore.saveQuestions(route.params.id, questions.value)
    notificationStore.success('Screening questions saved.')
  } catch (err) {
    notificationStore.error('Error saving questions.')
  }
}

onMounted(async () => {
  await loadVacancy()
  // Load applicant-specific data if user is an applicant
  if (authStore.isApplicant) {
    await Promise.all([
      applicationStore.fetchMyApplications(),
      profileStore.fetchProfile(),
    ])
  }
})
</script>
