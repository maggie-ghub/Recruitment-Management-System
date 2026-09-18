<template>
  <div class="space-y-6 pb-12">
    <!-- Header banner -->
    <div class="bg-white rounded-3xl border border-[#f0e9dc] p-6 sm:p-8 shadow-xs">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-50 text-[#db802d] text-xs font-semibold border border-amber-200 mb-2">
            <span>Reusable Profile ("Enter once, reuse many times")</span>
          </div>
          <h1 class="text-2xl sm:text-3xl font-extrabold text-neutral-900 tracking-tight">
            {{ authStore.user?.name }}'s Candidate Profile
          </h1>
          <p class="text-xs sm:text-sm text-[#7a6e5a] mt-1 max-w-2xl">
            Maintain your professional background, credentials, and resume here. When you apply to any open position across Tora Holding subsidiaries, this profile will automatically populate your application.
          </p>
        </div>

        <!-- Completeness gauge card -->
        <div class="bg-[#faf8f5] p-4 rounded-2xl border border-[#f0e9dc] shrink-0 min-w-[240px]">
          <div class="flex items-center justify-between mb-1.5">
            <span class="text-xs font-bold uppercase tracking-wider text-[#7a6e5a]">Profile Completeness</span>
            <span class="text-sm font-extrabold text-[#db802d]">{{ profileStore.completenessPercentage }}%</span>
          </div>
          <!-- Progress bar -->
          <div class="w-full h-2.5 bg-neutral-200 rounded-full overflow-hidden">
            <div
              class="h-full bg-gradient-to-r from-[#ebb630] to-[#db802d] transition-all duration-500 rounded-full"
              :style="{ width: `${profileStore.completenessPercentage}%` }"
            ></div>
          </div>
          <div class="mt-2 text-[11px] font-medium" :class="profileStore.isReadyToApply ? 'text-emerald-700' : 'text-amber-700'">
            <span v-if="profileStore.isReadyToApply">✓ Ready to submit applications</span>
            <span v-else>{{ profileStore.missingItems.length }} item(s) need completion</span>
          </div>
        </div>
      </div>

      <!-- Missing Information Alert (FR-APP-005) -->
      <div
        v-if="profileStore.missingItems && profileStore.missingItems.length > 0"
        class="mt-6 p-4 rounded-2xl bg-amber-50/70 border border-amber-200 text-xs text-amber-900 flex items-start gap-3"
      >
        <svg class="w-5 h-5 text-[#db802d] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div class="flex-1">
          <div class="font-bold text-[#8f5a11] mb-1">Required Information to complete before applying (FR-APP-005):</div>
          <ul class="list-disc list-inside space-y-0.5 text-neutral-700">
            <li v-for="(item, idx) in profileStore.missingItems" :key="idx">{{ item }}</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Main Grid: 2 Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column: Personal info & CV Upload (1 col) -->
      <div class="space-y-6">
        <!-- 1. Primary CV / Resume Card (FR-APP-003, NFR-007) -->
        <div class="bg-white rounded-3xl border border-[#f0e9dc] p-6 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-bold text-neutral-900 flex items-center gap-2">
              <svg class="w-5 h-5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              CV / Resume Document
            </h2>
            <span v-if="profileStore.primaryCv" class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
              Active CV
            </span>
          </div>

          <!-- Existing CV Card -->
          <div v-if="profileStore.primaryCv" class="p-4 rounded-2xl bg-amber-50/40 border border-amber-200 mb-4">
            <div class="flex items-start gap-3">
              <div class="w-10 h-10 rounded-xl bg-[#db802d] text-white flex items-center justify-center font-bold text-xs uppercase shrink-0">
                {{ profileStore.primaryCv.original_filename.split('.').pop() }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-xs font-bold text-neutral-900 truncate">
                  {{ profileStore.primaryCv.original_filename }}
                </div>
                <div class="text-[11px] text-[#7a6e5a] mt-0.5">
                  Size: {{ formatBytes(profileStore.primaryCv.file_size) }} • Uploaded: {{ new Date(profileStore.primaryCv.created_at).toLocaleDateString() }}
                </div>
              </div>
            </div>
            <div class="mt-3 pt-3 border-t border-amber-200/60 flex items-center gap-2">
              <button
                @click="profileStore.downloadDocument(profileStore.primaryCv)"
                class="flex-1 py-1.5 px-3 rounded-xl bg-white border border-neutral-200 text-xs font-semibold text-neutral-700 hover:bg-neutral-50 transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
              >
                <svg class="w-3.5 h-3.5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download
              </button>
            </div>
          </div>

          <!-- Upload / Replace CV Input -->
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-2">
              {{ profileStore.primaryCv ? 'Replace Current CV' : 'Upload New CV (PDF / DOCX)' }}
            </label>
            <div class="border-2 border-dashed border-[#f0e9dc] rounded-2xl p-4 text-center hover:border-[#ebb630] transition-colors bg-[#faf8f5]/50">
              <input
                ref="cvFileInput"
                type="file"
                accept=".pdf,.docx,.doc"
                @change="handleCvSelect"
                class="hidden"
              />
              <button
                type="button"
                @click="$refs.cvFileInput.click()"
                :disabled="profileStore.uploadingCv"
                class="px-4 py-2 rounded-xl bg-[#db802d] hover:bg-[#c46f20] text-white text-xs font-semibold transition-all cursor-pointer disabled:opacity-50"
              >
                {{ profileStore.uploadingCv ? 'Uploading & Validating...' : 'Select CV File' }}
              </button>
              <p class="text-[11px] text-neutral-500 mt-2">
                PDF or DOCX format only, maximum 5MB (FR-APP-003)
              </p>
            </div>
          </div>
        </div>

        <!-- 2. Personal & Contact Details Card -->
        <div class="bg-white rounded-3xl border border-[#f0e9dc] p-6 shadow-xs">
          <h2 class="text-base font-bold text-neutral-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>
            Personal & Contact
          </h2>

          <form @submit.prevent="savePersonalInfo" class="space-y-3.5">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Phone Number *</label>
              <input
                v-model="personalForm.phone"
                type="text"
                placeholder="+251 91 234 5678"
                required
                class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#ebb630]"
              />
            </div>

            <div class="grid grid-cols-2 gap-3">
              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">City / Region</label>
                <input
                  v-model="personalForm.city"
                  type="text"
                  placeholder="e.g. Addis Ababa"
                  class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#ebb630]"
                />
              </div>

              <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Country</label>
                <input
                  v-model="personalForm.country"
                  type="text"
                  placeholder="Ethiopia"
                  class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#ebb630]"
                />
              </div>
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Date of Birth</label>
              <input
                v-model="personalForm.date_of_birth"
                type="date"
                class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#ebb630]"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Professional Summary / Bio</label>
              <textarea
                v-model="personalForm.bio"
                rows="3"
                placeholder="Brief summary of your professional background, goals, and key competencies..."
                class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:outline-none focus:ring-2 focus:ring-[#ebb630]"
              ></textarea>
            </div>

            <button
              type="submit"
              :disabled="savingPersonal"
              class="w-full py-2 px-4 rounded-xl text-xs font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] transition-colors cursor-pointer disabled:opacity-50"
            >
              {{ savingPersonal ? 'Saving...' : 'Save Contact Info' }}
            </button>
          </form>
        </div>

        <!-- 3. Skills Tag Cloud (FR-APP-001) -->
        <div class="bg-white rounded-3xl border border-[#f0e9dc] p-6 shadow-xs">
          <h2 class="text-base font-bold text-neutral-900 mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Professional Skills
          </h2>

          <!-- Add skill form -->
          <form @submit.prevent="handleAddSkill" class="flex gap-2 mb-4">
            <input
              v-model="skillForm.name"
              type="text"
              placeholder="e.g. Laravel, Python, Supply Chain"
              required
              class="flex-1 px-3 py-1.5 rounded-xl border border-neutral-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#ebb630]"
            />
            <select
              v-model="skillForm.proficiency"
              class="px-2 py-1.5 rounded-xl border border-neutral-200 text-xs focus:outline-none focus:ring-2 focus:ring-[#ebb630] bg-white"
            >
              <option value="Beginner">Beginner</option>
              <option value="Intermediate">Intermediate</option>
              <option value="Advanced">Advanced</option>
              <option value="Expert">Expert</option>
            </select>
            <button
              type="submit"
              class="px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] cursor-pointer"
            >
              Add
            </button>
          </form>

          <!-- Skills tags -->
          <div v-if="profileStore.skills && profileStore.skills.length > 0" class="flex flex-wrap gap-2">
            <div
              v-for="s in profileStore.skills"
              :key="s.id"
              class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs bg-amber-50 border border-amber-200 text-neutral-800"
            >
              <span class="font-bold text-neutral-900">{{ s.name }}</span>
              <span class="text-[10px] text-[#db802d] font-semibold">({{ s.proficiency }})</span>
              <button
                @click="profileStore.deleteSkill(s.id)"
                class="text-neutral-400 hover:text-red-500 font-bold ml-1 cursor-pointer"
              >&times;</button>
            </div>
          </div>
          <div v-else class="text-xs text-neutral-400 italic">
            No skills recorded yet. Add your core competencies above.
          </div>
        </div>
      </div>

      <!-- Right Column: Education, Work Experience & Supporting Docs (2 cols) -->
      <div class="lg:col-span-2 space-y-6">
        <!-- 4. Work Experience (FR-APP-001) -->
        <div class="bg-white rounded-3xl border border-[#f0e9dc] p-6 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-base font-bold text-neutral-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Work Experience
              </h2>
              <p class="text-xs text-[#7a6e5a] mt-0.5">Chronological record of past roles and accomplishments</p>
            </div>
            <button
              @click="showExpModal = true"
              class="px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] cursor-pointer"
            >
              + Add Experience
            </button>
          </div>

          <div v-if="profileStore.workExperiences && profileStore.workExperiences.length > 0" class="space-y-3">
            <div
              v-for="exp in profileStore.workExperiences"
              :key="exp.id"
              class="p-4 rounded-2xl bg-[#faf8f5] border border-[#f0e9dc] flex items-start justify-between gap-4"
            >
              <div>
                <h3 class="font-bold text-sm text-neutral-900">{{ exp.job_title }}</h3>
                <div class="text-xs font-semibold text-[#db802d]">{{ exp.company_name }}</div>
                <div class="text-[11px] text-[#7a6e5a] mt-0.5">
                  {{ exp.start_date }} &mdash; {{ exp.is_current ? 'Present' : exp.end_date }}
                </div>
                <p v-if="exp.responsibilities" class="text-xs text-neutral-600 mt-2 leading-relaxed">
                  {{ exp.responsibilities }}
                </p>
              </div>
              <button
                @click="profileStore.deleteWorkExperience(exp.id)"
                class="text-xs text-red-500 hover:text-red-700 font-medium px-2 py-1 rounded-md hover:bg-red-50 cursor-pointer shrink-0"
              >
                Delete
              </button>
            </div>
          </div>
          <div v-else class="p-8 text-center text-xs text-neutral-400 bg-[#faf8f5]/50 rounded-2xl border border-dashed border-[#f0e9dc]">
            No work experience recorded. Click "+ Add Experience" to record your history.
          </div>
        </div>

        <!-- 5. Education History (FR-APP-001) -->
        <div class="bg-white rounded-3xl border border-[#f0e9dc] p-6 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-base font-bold text-neutral-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                </svg>
                Education History
              </h2>
              <p class="text-xs text-[#7a6e5a] mt-0.5">Degrees, certifications, and academic institutions</p>
            </div>
            <button
              @click="showEduModal = true"
              class="px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] cursor-pointer"
            >
              + Add Education
            </button>
          </div>

          <div v-if="profileStore.educations && profileStore.educations.length > 0" class="space-y-3">
            <div
              v-for="edu in profileStore.educations"
              :key="edu.id"
              class="p-4 rounded-2xl bg-[#faf8f5] border border-[#f0e9dc] flex items-start justify-between gap-4"
            >
              <div>
                <h3 class="font-bold text-sm text-neutral-900">{{ edu.degree }} in {{ edu.field_of_study }}</h3>
                <div class="text-xs font-semibold text-[#db802d]">{{ edu.institution }}</div>
                <div class="text-[11px] text-[#7a6e5a] mt-0.5">
                  {{ edu.start_date }} &mdash; {{ edu.is_current ? 'Present' : edu.end_date }}
                  <span v-if="edu.grade" class="ml-2 px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-800 font-bold">GPA: {{ edu.grade }}</span>
                </div>
              </div>
              <button
                @click="profileStore.deleteEducation(edu.id)"
                class="text-xs text-red-500 hover:text-red-700 font-medium px-2 py-1 rounded-md hover:bg-red-50 cursor-pointer shrink-0"
              >
                Delete
              </button>
            </div>
          </div>
          <div v-else class="p-8 text-center text-xs text-neutral-400 bg-[#faf8f5]/50 rounded-2xl border border-dashed border-[#f0e9dc]">
            No education entries added yet.
          </div>
        </div>

        <!-- 6. Permitted Supporting Documents (FR-APP-004) -->
        <div class="bg-white rounded-3xl border border-[#f0e9dc] p-6 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-base font-bold text-neutral-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Supporting Documents (Certificates & Degrees)
              </h2>
              <p class="text-xs text-[#7a6e5a] mt-0.5">Permitted documents securely stored and served via authorized streams (FR-APP-004, NFR-007)</p>
            </div>
            <button
              @click="showDocModal = true"
              class="px-3 py-1.5 rounded-xl text-xs font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] cursor-pointer"
            >
              + Upload Document
            </button>
          </div>

          <div v-if="supportingDocs && supportingDocs.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div
              v-for="doc in supportingDocs"
              :key="doc.id"
              class="p-3.5 rounded-2xl bg-[#faf8f5] border border-[#f0e9dc] flex items-center justify-between"
            >
              <div class="min-w-0 flex-1 mr-2">
                <span class="text-[10px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-800">
                  {{ doc.type }}
                </span>
                <div class="text-xs font-bold text-neutral-800 truncate mt-1">
                  {{ doc.original_filename }}
                </div>
                <div class="text-[10px] text-[#7a6e5a]">{{ formatBytes(doc.file_size) }}</div>
              </div>
              <div class="flex items-center gap-1">
                <button
                  @click="profileStore.downloadDocument(doc)"
                  class="p-1.5 text-[#db802d] hover:bg-amber-50 rounded-lg"
                  title="Download"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                  </svg>
                </button>
                <button
                  @click="profileStore.deleteDocument(doc.id)"
                  class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg"
                  title="Delete"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
          <div v-else class="p-6 text-center text-xs text-neutral-400 bg-[#faf8f5]/50 rounded-2xl border border-dashed border-[#f0e9dc]">
            No extra supporting documents uploaded.
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Add Education -->
    <div v-if="showEduModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
      <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#f0e9dc]">
        <div class="flex justify-between items-center pb-3 border-b border-neutral-100">
          <h3 class="font-bold text-lg text-neutral-900">Add Education Record</h3>
          <button @click="showEduModal = false" class="text-neutral-400 hover:text-neutral-600 font-bold text-xl cursor-pointer">&times;</button>
        </div>
        <form @submit.prevent="handleSaveEdu" class="mt-4 space-y-3">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Institution Name *</label>
            <input v-model="eduForm.institution" type="text" required placeholder="e.g. Addis Ababa University" class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]" />
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Degree / Qualification *</label>
            <input v-model="eduForm.degree" type="text" required placeholder="e.g. Bachelor of Science, Master of Arts" class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]" />
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Field of Study *</label>
            <input v-model="eduForm.field_of_study" type="text" required placeholder="e.g. Computer Science, Accounting" class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Start Date *</label>
              <input v-model="eduForm.start_date" type="date" required class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]" />
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">End Date</label>
              <input v-model="eduForm.end_date" type="date" :disabled="eduForm.is_current" class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630] disabled:bg-neutral-100" />
            </div>
          </div>
          <div class="flex items-center gap-2">
            <input v-model="eduForm.is_current" type="checkbox" id="currentEdu" class="rounded text-[#db802d] focus:ring-[#ebb630]" />
            <label for="currentEdu" class="text-xs text-neutral-700">Currently studying here</label>
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Grade / GPA (Optional)</label>
            <input v-model="eduForm.grade" type="text" placeholder="e.g. 3.85 / 4.00" class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]" />
          </div>
          <div class="pt-3 flex justify-end gap-2 border-t border-neutral-100">
            <button type="button" @click="showEduModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-neutral-600 hover:bg-neutral-100 cursor-pointer">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] cursor-pointer">Save Education</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Add Work Experience -->
    <div v-if="showExpModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
      <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#f0e9dc]">
        <div class="flex justify-between items-center pb-3 border-b border-neutral-100">
          <h3 class="font-bold text-lg text-neutral-900">Add Work Experience</h3>
          <button @click="showExpModal = false" class="text-neutral-400 hover:text-neutral-600 font-bold text-xl cursor-pointer">&times;</button>
        </div>
        <form @submit.prevent="handleSaveExp" class="mt-4 space-y-3">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Company / Organization *</label>
            <input v-model="expForm.company_name" type="text" required placeholder="e.g. Commercial Bank of Ethiopia" class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]" />
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Job Title *</label>
            <input v-model="expForm.job_title" type="text" required placeholder="e.g. Senior Software Engineer" class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]" />
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Start Date *</label>
              <input v-model="expForm.start_date" type="date" required class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]" />
            </div>
            <div>
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">End Date</label>
              <input v-model="expForm.end_date" type="date" :disabled="expForm.is_current" class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630] disabled:bg-neutral-100" />
            </div>
          </div>
          <div class="flex items-center gap-2">
            <input v-model="expForm.is_current" type="checkbox" id="currentExp" class="rounded text-[#db802d] focus:ring-[#ebb630]" />
            <label for="currentExp" class="text-xs text-neutral-700">Currently working here</label>
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Key Responsibilities / Impact</label>
            <textarea v-model="expForm.responsibilities" rows="3" placeholder="Bullet points or summary of your accomplishments..." class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]"></textarea>
          </div>
          <div class="pt-3 flex justify-end gap-2 border-t border-neutral-100">
            <button type="button" @click="showExpModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-neutral-600 hover:bg-neutral-100 cursor-pointer">Cancel</button>
            <button type="submit" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] cursor-pointer">Save Experience</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Upload Supporting Document -->
    <div v-if="showDocModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
      <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-[#f0e9dc]">
        <div class="flex justify-between items-center pb-3 border-b border-neutral-100">
          <h3 class="font-bold text-lg text-neutral-900">Upload Supporting Document</h3>
          <button @click="showDocModal = false" class="text-neutral-400 hover:text-neutral-600 font-bold text-xl cursor-pointer">&times;</button>
        </div>
        <form @submit.prevent="handleSaveDoc" class="mt-4 space-y-3">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Document Type *</label>
            <select v-model="docForm.type" required class="w-full px-3 py-2 rounded-xl border border-neutral-200 text-sm focus:ring-2 focus:ring-[#ebb630]">
              <option value="certificate">Professional Certificate</option>
              <option value="degree">Degree / Diploma Transcript</option>
              <option value="other">Other Recommendation / Reference</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1">Select File (PDF, DOCX, PNG, JPG - max 5MB)</label>
            <input type="file" ref="supportDocInput" required class="w-full text-xs text-neutral-600" />
          </div>
          <div class="pt-3 flex justify-end gap-2 border-t border-neutral-100">
            <button type="button" @click="showDocModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-neutral-600 hover:bg-neutral-100 cursor-pointer">Cancel</button>
            <button type="submit" :disabled="profileStore.uploadingDoc" class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] cursor-pointer disabled:opacity-50">
              {{ profileStore.uploadingDoc ? 'Uploading...' : 'Upload' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useApplicantProfileStore } from '../../stores/applicantProfile'
import { useAuthStore } from '../../stores/auth'

const authStore = useAuthStore()
const profileStore = useApplicantProfileStore()

const savingPersonal = ref(false)
const showEduModal = ref(false)
const showExpModal = ref(false)
const showDocModal = ref(false)
const supportDocInput = ref(null)

const personalForm = reactive({
  phone: '',
  city: '',
  country: 'Ethiopia',
  date_of_birth: '',
  bio: '',
})

const skillForm = reactive({
  name: '',
  proficiency: 'Intermediate',
})

const eduForm = reactive({
  institution: '',
  degree: '',
  field_of_study: '',
  start_date: '',
  end_date: '',
  is_current: false,
  grade: '',
})

const expForm = reactive({
  company_name: '',
  job_title: '',
  start_date: '',
  end_date: '',
  is_current: false,
  responsibilities: '',
})

const docForm = reactive({
  type: 'certificate',
})

const supportingDocs = computed(() => {
  return (profileStore.documents || []).filter((d) => !d.is_primary_cv)
})

const formatBytes = (bytes, decimals = 1) => {
  if (!bytes) return '0 B'
  const k = 1024
  const dm = decimals < 0 ? 0 : decimals
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i]
}

const loadProfile = async () => {
  const data = await profileStore.fetchProfile()
  if (data && data.profile) {
    personalForm.phone = data.profile.phone || ''
    personalForm.city = data.profile.city || ''
    personalForm.country = data.profile.country || 'Ethiopia'
    personalForm.date_of_birth = data.profile.date_of_birth ? data.profile.date_of_birth.substring(0, 10) : ''
    personalForm.bio = data.profile.bio || ''
  }
}

const savePersonalInfo = async () => {
  savingPersonal.value = true
  try {
    await profileStore.updatePersonalInfo(personalForm)
    alert('Personal information saved successfully.')
  } catch (err) {
    alert('Error saving information.')
  } finally {
    savingPersonal.value = false
  }
}

const handleCvSelect = async (e) => {
  const file = e.target.files[0]
  if (!file) return
  if (file.size > 5 * 1024 * 1024) {
    alert('CV file must be under 5MB (FR-APP-003).')
    return
  }
  try {
    await profileStore.uploadCv(file)
    alert('CV uploaded successfully.')
  } catch (err) {
    alert(err.response?.data?.message || 'Error uploading CV.')
  }
}

const handleAddSkill = async () => {
  try {
    await profileStore.addSkill(skillForm)
    skillForm.name = ''
  } catch (e) {
    alert('Error adding skill.')
  }
}

const handleSaveEdu = async () => {
  try {
    await profileStore.addEducation(eduForm)
    showEduModal.value = false
    eduForm.institution = ''
    eduForm.degree = ''
    eduForm.field_of_study = ''
    eduForm.start_date = ''
    eduForm.end_date = ''
    eduForm.is_current = false
    eduForm.grade = ''
  } catch (e) {
    alert('Error saving education record.')
  }
}

const handleSaveExp = async () => {
  try {
    await profileStore.addWorkExperience(expForm)
    showExpModal.value = false
    expForm.company_name = ''
    expForm.job_title = ''
    expForm.start_date = ''
    expForm.end_date = ''
    expForm.is_current = false
    expForm.responsibilities = ''
  } catch (e) {
    alert('Error saving work experience.')
  }
}

const handleSaveDoc = async () => {
  const file = supportDocInput.value?.files[0]
  if (!file) {
    alert('Please select a document file.')
    return
  }
  try {
    await profileStore.uploadSupportingDocument(docForm.type, file)
    showDocModal.value = false
  } catch (e) {
    alert(e.response?.data?.message || 'Error uploading document.')
  }
}

onMounted(() => {
  loadProfile()
})
</script>
