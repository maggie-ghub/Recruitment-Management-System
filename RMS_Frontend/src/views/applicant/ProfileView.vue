<template>
  <div class="space-y-6 pb-12">
    <!-- Header banner with Contact summary and Completeness -->
    <div class="bg-white rounded-3xl border border-[#f0e9dc] p-7 sm:p-9 shadow-xs">
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
        <div class="space-y-2.5 max-w-3xl">
          <div class="flex items-center gap-3">
            <h1 class="text-3xl sm:text-4xl font-extrabold text-neutral-900 tracking-tight">
              {{ authStore.user?.name }}
            </h1>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-[#db802d] border border-amber-200 tracking-wider">
              Candidate
            </span>
          </div>

          <!-- Read-only Contact Information Bar directly under Candidate Name -->
          <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm sm:text-base text-[#7a6e5a] pt-1">
            <span class="flex items-center gap-2 font-medium text-neutral-800">
              <svg class="w-4 h-4 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              {{ authStore.user?.email }}
            </span>
            <span class="text-neutral-300">•</span>
            <span class="flex items-center gap-2 font-medium text-neutral-800">
              <svg class="w-4 h-4 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
              </svg>
              {{ personalForm.phone || 'Phone not set' }}
            </span>
            <span class="text-neutral-300">•</span>
            <span class="flex items-center gap-2 font-medium text-neutral-800">
              <svg class="w-4 h-4 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              {{ personalForm.city ? `${personalForm.city}, ${personalForm.country}` : personalForm.country }}
            </span>
            <button
              @click="isEditingPersonal = !isEditingPersonal"
              type="button"
              class="inline-flex items-center gap-1.5 ml-2 text-xs font-bold text-[#db802d] hover:text-[#c46f20] hover:underline cursor-pointer"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
              </svg>
              {{ isEditingPersonal ? 'Close Form' : 'Edit Contact Info' }}
            </button>
          </div>

          <p v-if="personalForm.bio" class="text-sm text-neutral-600 italic pt-1 line-clamp-2">
            "{{ personalForm.bio }}"
          </p>
        </div>

        <!-- Completeness gauge card -->
        <div class="bg-[#faf8f5] p-6 rounded-3xl border border-[#f0e9dc] shrink-0 min-w-[280px]">
          <div class="flex items-center justify-between mb-2.5">
            <span class="text-xs font-bold tracking-wider text-[#7a6e5a]">Profile Completeness</span>
            <span class="text-lg font-extrabold text-[#db802d]">{{ profileStore.completenessPercentage }}%</span>
          </div>
          <!-- Progress bar -->
          <div class="w-full h-3 bg-neutral-200 rounded-full overflow-hidden">
            <div
              class="h-full bg-gradient-to-r from-[#ebb630] to-[#db802d] transition-all duration-500 rounded-full"
              :style="{ width: `${profileStore.completenessPercentage}%` }"
            ></div>
          </div>
          <div class="mt-3 text-xs font-semibold" :class="profileStore.isReadyToApply ? 'text-emerald-700' : 'text-amber-800'">
            <span v-if="profileStore.isReadyToApply" class="flex items-center gap-1.5">
              <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              Ready To Submit Applications
            </span>
            <span v-else>{{ profileStore.missingItems.length }} Item(s) Needed Before Applying</span>
          </div>
        </div>
      </div>

      <!-- Missing Information Alert -->
      <div
        v-if="profileStore.missingItems && profileStore.missingItems.length > 0"
        class="mt-6 p-5 rounded-2xl bg-amber-50/70 border border-amber-200 text-sm text-amber-900 flex items-start gap-3.5"
      >
        <svg class="w-5 h-5 text-[#db802d] shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <div class="flex-1">
          <div class="font-bold text-[#8f5a11] mb-1 text-sm">Required Information To Complete Before Applying:</div>
          <ul class="list-disc list-inside space-y-0.5 text-neutral-700 text-xs">
            <li v-for="(item, idx) in profileStore.missingItems" :key="idx">{{ item }}</li>
          </ul>
        </div>
      </div>

      <!-- Collapsible / Toggleable Contact Edit Panel -->
      <div v-if="isEditingPersonal" class="mt-6 pt-6 border-t border-neutral-100">
        <h3 class="text-base font-bold text-neutral-900 mb-3 flex items-center gap-2">
          <svg class="w-4 h-4 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
          </svg>
          Edit Contact & Bio Details
        </h3>
        <form @submit.prevent="savePersonalInfo" novalidate class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Phone Number *</label>
            <input
              v-model="personalForm.phone"
              type="text"
              placeholder="Enter phone number"
              class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              :class="personalErrors.phone ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="personalErrors.phone" class="text-xs text-red-600 mt-1 font-medium">{{ personalErrors.phone }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">City / Region</label>
            <input
              v-model="personalForm.city"
              type="text"
              placeholder="Enter city or region"
              class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Country</label>
            <input
              v-model="personalForm.country"
              type="text"
              placeholder="Enter country"
              class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
            />
          </div>

          <div class="sm:col-span-1">
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Date Of Birth</label>
            <input
              v-model="personalForm.date_of_birth"
              type="date"
              class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
            />
          </div>

          <div class="sm:col-span-2">
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Professional Summary / Bio</label>
            <textarea
              v-model="personalForm.bio"
              rows="2"
              placeholder="Enter professional summary"
              class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
            ></textarea>
          </div>

          <div class="sm:col-span-3 flex items-center justify-end gap-2.5 pt-2">
            <button
              type="button"
              @click="isEditingPersonal = false"
              class="px-5 py-2.5 rounded-xl text-xs font-semibold text-neutral-600 hover:bg-neutral-100 cursor-pointer"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="savingPersonal"
              class="px-6 py-2.5 rounded-xl text-xs font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] transition-colors cursor-pointer disabled:opacity-50"
            >
              {{ savingPersonal ? 'Saving...' : 'Save Contact Info' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Section 2: Full-Width Professional Skills Card -->
    <div class="bg-white rounded-3xl border border-[#f0e9dc] p-7 sm:p-8 shadow-xs w-full">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-4 pb-3 border-b border-neutral-100">
        <div>
          <h2 class="text-xl font-bold text-neutral-900 flex items-center gap-2">
            <svg class="w-5 h-5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Professional Skills
          </h2>
          <p class="text-xs text-[#7a6e5a] mt-0.5">Showcase your technical capabilities and key competencies</p>
        </div>

        <!-- Add Skill Form Inline -->
        <form @submit.prevent="handleAddSkill" novalidate class="flex flex-wrap sm:flex-nowrap items-center gap-2 w-full sm:w-auto">
          <div class="flex-1 sm:w-64">
            <input
              v-model="skillForm.name"
              type="text"
              placeholder="Enter skill name"
              class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              :class="skillError ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="skillError" class="text-xs text-red-600 mt-1 font-medium">{{ skillError }}</p>
          </div>
          <select
            v-model="skillForm.proficiency"
            class="pl-3.5 pr-8 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] bg-white cursor-pointer"
          >
            <option value="Beginner">Beginner</option>
            <option value="Intermediate">Intermediate</option>
            <option value="Advanced">Advanced</option>
            <option value="Expert">Expert</option>
          </select>
          <button
            type="submit"
            class="px-5 py-2.5 rounded-2xl text-xs font-bold text-white bg-[#db802d] hover:bg-[#c46f20] cursor-pointer shrink-0 transition-all"
          >
            Add Skill
          </button>
        </form>
      </div>

      <!-- Skills tags: Full Width Container -->
      <div class="w-full">
        <div v-if="profileStore.skills && profileStore.skills.length > 0" class="flex flex-wrap gap-2.5 w-full">
          <div
            v-for="s in profileStore.skills"
            :key="s.id"
            class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs bg-amber-50 border border-amber-200 text-neutral-800 transition-all hover:border-[#db802d]"
          >
            <span class="font-bold text-neutral-900 text-sm">{{ s.name }}</span>
            <span class="text-xs text-[#db802d] font-semibold">({{ s.proficiency }})</span>
            <button
              @click="handleDeleteSkill(s.id)"
              type="button"
              class="text-neutral-400 hover:text-red-500 font-bold ml-1 text-base cursor-pointer shrink-0 leading-none"
              title="Remove Skill"
            >&times;</button>
          </div>
        </div>
        <div v-else class="text-sm text-neutral-400 italic py-4">
          No skills recorded yet. Add your core competencies above to highlight your background.
        </div>
      </div>
    </div>

    <!-- Main Grid: 2 Columns for Experience, Education & Documents -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Left Column: Primary CV & Application Letter -->
      <div class="space-y-6">
        <!-- 1. Primary CV / Resume Card -->
        <div class="bg-white rounded-3xl border border-[#f0e9dc] p-7 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-neutral-900 flex items-center gap-2">
              <svg class="w-5 h-5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              CV / Resume Document
            </h2>
            <span v-if="profileStore.primaryCv" class="px-3 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
              Active CV
            </span>
          </div>

          <!-- Existing CV Card -->
          <div v-if="profileStore.primaryCv" class="p-5 rounded-2xl bg-[#faf8f5] border border-[#f0e9dc] mb-4">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 rounded-2xl bg-white border border-amber-200 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs">
                <!-- Authentic Adobe Acrobat PDF Icon -->
                <img
                  v-if="isPdf(profileStore.primaryCv.original_filename)"
                  :src="pdfIcon"
                  alt="PDF"
                  class="w-8 h-8 object-contain"
                />
                <svg v-else class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 13h6m-6 4h6" />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-bold text-neutral-900 truncate">
                  {{ profileStore.primaryCv.original_filename }}
                </div>
                <div class="text-xs text-[#7a6e5a] mt-1">
                  {{ formatBytes(profileStore.primaryCv.file_size) }} • {{ new Date(profileStore.primaryCv.created_at).toLocaleDateString() }}
                </div>
              </div>
            </div>
            <div class="mt-4 pt-3.5 border-t border-[#f0e9dc] flex items-center gap-3">
              <button
                @click="openViewer(profileStore.primaryCv)"
                class="flex-1 py-2 px-3 rounded-xl bg-white border border-neutral-200 text-xs font-semibold text-neutral-700 hover:bg-neutral-50 transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs"
              >
                <svg class="w-4 h-4 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Preview Document
              </button>
              <button
                @click="handleDownload(profileStore.primaryCv)"
                class="flex-1 py-2 px-3 rounded-xl bg-white border border-neutral-200 text-xs font-semibold text-neutral-700 hover:bg-neutral-50 transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs"
              >
                <svg class="w-4 h-4 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download
              </button>
            </div>
          </div>

          <!-- Upload / Replace CV Input -->
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-2">
              {{ profileStore.primaryCv ? 'Replace Current CV' : 'Upload CV File' }}
            </label>
            <div class="border border-dashed border-[#db802d]/40 rounded-2xl p-5 text-center hover:border-[#db802d] transition-all bg-[#faf8f5]">
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
                class="px-5 py-2.5 rounded-xl bg-[#db802d] hover:bg-[#c46f20] text-white text-xs font-bold transition-all cursor-pointer disabled:opacity-50"
              >
                {{ profileStore.uploadingCv ? 'Uploading...' : 'Select CV (Up To 15MB)' }}
              </button>
              <p class="text-xs text-neutral-500 mt-2">
                PDF or DOCX format only, maximum 15MB
              </p>
            </div>
          </div>
        </div>

        <!-- 2. Application Letter Card -->
        <div class="bg-white rounded-3xl border border-[#f0e9dc] p-7 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-neutral-900 flex items-center gap-2">
              <svg class="w-5 h-5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              Application Letter
            </h2>
            <span v-if="appLetterDoc" class="px-3 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-[#db802d]">
              Uploaded
            </span>
          </div>

          <!-- Existing Application Letter -->
          <div v-if="appLetterDoc" class="p-5 rounded-2xl bg-[#faf8f5] border border-[#f0e9dc] mb-4">
            <div class="flex items-start gap-4">
              <div class="w-12 h-12 rounded-2xl bg-white border border-amber-200 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs">
                <img
                  v-if="isPdf(appLetterDoc.original_filename)"
                  :src="pdfIcon"
                  alt="PDF"
                  class="w-8 h-8 object-contain"
                />
                <svg v-else class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 13h6m-6 4h6" />
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <div class="text-sm font-bold text-neutral-900 truncate">
                  {{ appLetterDoc.original_filename }}
                </div>
                <div class="text-xs text-[#7a6e5a] mt-1">
                  {{ formatBytes(appLetterDoc.file_size) }} • {{ new Date(appLetterDoc.created_at).toLocaleDateString() }}
                </div>
              </div>
            </div>
            <div class="mt-4 pt-3.5 border-t border-[#f0e9dc] flex items-center gap-3">
              <button
                @click="openViewer(appLetterDoc)"
                class="flex-1 py-2 px-3 rounded-xl bg-white border border-neutral-200 text-xs font-semibold text-neutral-700 hover:bg-neutral-50 transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs"
              >
                <svg class="w-4 h-4 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Preview Document
              </button>
              <button
                @click="handleDownload(appLetterDoc)"
                class="flex-1 py-2 px-3 rounded-xl bg-white border border-neutral-200 text-xs font-semibold text-neutral-700 hover:bg-neutral-50 transition-colors flex items-center justify-center gap-1.5 cursor-pointer shadow-2xs"
              >
                <svg class="w-4 h-4 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Download
              </button>
            </div>
          </div>

          <!-- Upload Application Letter -->
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-2">
              {{ appLetterDoc ? 'Replace Application Letter' : 'Upload Application Letter' }}
            </label>
            <div class="border border-dashed border-[#db802d]/40 rounded-2xl p-5 text-center hover:border-[#db802d] transition-all bg-[#faf8f5]">
              <input
                ref="appLetterInput"
                type="file"
                accept=".pdf,.docx,.doc"
                @change="handleAppLetterSelect"
                class="hidden"
              />
              <button
                type="button"
                @click="$refs.appLetterInput.click()"
                :disabled="uploadingAppLetter"
                class="px-5 py-2.5 rounded-xl bg-[#db802d] hover:bg-[#c46f20] text-white text-xs font-bold transition-all cursor-pointer disabled:opacity-50"
              >
                {{ uploadingAppLetter ? 'Uploading...' : 'Select Letter File' }}
              </button>
              <p class="text-xs text-neutral-500 mt-2">
                PDF or DOCX format, up to 15MB
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column: Work Experience, Education & Supporting Docs -->
      <div class="space-y-6">
        <!-- 3. Work Experience -->
        <div class="bg-white rounded-3xl border border-[#f0e9dc] p-7 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-lg font-bold text-neutral-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
                Work Experience
              </h2>
              <p class="text-xs text-[#7a6e5a] mt-0.5">Chronological record of past roles</p>
            </div>
            <button
              @click="openAddExp"
              class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-[#db802d] hover:bg-[#c46f20] cursor-pointer"
            >
              + Add Experience
            </button>
          </div>

          <div v-if="profileStore.workExperiences && profileStore.workExperiences.length > 0" class="space-y-3">
            <div
              v-for="exp in profileStore.workExperiences"
              :key="exp.id"
              class="p-4 rounded-2xl bg-[#faf8f5] border border-[#f0e9dc] flex items-start justify-between gap-4 hover:border-[#db802d]/40 transition-all"
            >
              <div class="min-w-0 flex-1">
                <h3 class="font-bold text-sm text-neutral-900">{{ exp.job_title }}</h3>
                <div class="text-xs font-semibold text-[#db802d]">{{ exp.company_name }}</div>
                <div class="text-xs text-[#7a6e5a] mt-1 font-medium">
                  {{ formatDateClean(exp.start_date) }} - {{ exp.is_current ? 'Present' : formatDateClean(exp.end_date) }}
                </div>
                <p v-if="exp.responsibilities" class="text-xs text-neutral-600 mt-2 leading-relaxed">
                  {{ exp.responsibilities }}
                </p>
              </div>
              <div class="flex items-center gap-1 shrink-0">
                <button
                  @click="openEditExp(exp)"
                  class="text-xs text-[#db802d] hover:text-[#c46f20] font-bold px-2 py-1 rounded-md hover:bg-amber-50 cursor-pointer"
                >
                  Edit
                </button>
                <button
                  @click="handleDeleteExp(exp.id)"
                  class="text-xs text-red-500 hover:text-red-700 font-bold px-2 py-1 rounded-md hover:bg-red-50 cursor-pointer"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
          <div v-else class="p-8 text-center text-xs text-neutral-400 bg-[#faf8f5]/50 rounded-2xl border border-dashed border-[#f0e9dc]">
            No work experience recorded.
          </div>
        </div>

        <!-- 4. Education History -->
        <div class="bg-white rounded-3xl border border-[#f0e9dc] p-7 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-lg font-bold text-neutral-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                </svg>
                Education History
              </h2>
              <p class="text-xs text-[#7a6e5a] mt-0.5">Degrees and academic qualifications</p>
            </div>
            <button
              @click="openAddEdu"
              class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-[#db802d] hover:bg-[#c46f20] cursor-pointer"
            >
              Add Education
            </button>
          </div>

          <div v-if="profileStore.educations && profileStore.educations.length > 0" class="space-y-3">
            <div
              v-for="edu in profileStore.educations"
              :key="edu.id"
              class="p-4 rounded-2xl bg-[#faf8f5] border border-[#f0e9dc] flex items-start justify-between gap-4 hover:border-[#db802d]/40 transition-all"
            >
              <div class="min-w-0 flex-1">
                <h3 class="font-bold text-sm text-neutral-900">{{ edu.degree }} in {{ edu.field_of_study }}</h3>
                <div class="text-xs font-semibold text-[#db802d]">{{ edu.institution }}</div>
                <div class="text-xs text-[#7a6e5a] mt-1 font-medium">
                  {{ formatDateClean(edu.start_date) }} - {{ edu.is_current ? 'Present' : formatDateClean(edu.end_date) }}
                  <span v-if="edu.grade" class="ml-2 px-2 py-0.5 rounded-md bg-amber-100 text-amber-800 font-bold text-xs">GPA: {{ edu.grade }}</span>
                </div>
              </div>
              <div class="flex items-center gap-1 shrink-0">
                <button
                  @click="openEditEdu(edu)"
                  class="text-xs text-[#db802d] hover:text-[#c46f20] font-bold px-2 py-1 rounded-md hover:bg-amber-50 cursor-pointer"
                >
                  Edit
                </button>
                <button
                  @click="handleDeleteEdu(edu.id)"
                  class="text-xs text-red-500 hover:text-red-700 font-bold px-2 py-1 rounded-md hover:bg-red-50 cursor-pointer"
                >
                  Delete
                </button>
              </div>
            </div>
          </div>
          <div v-else class="p-8 text-center text-xs text-neutral-400 bg-[#faf8f5]/50 rounded-2xl border border-dashed border-[#f0e9dc]">
            No education entries added yet.
          </div>
        </div>

        <!-- 5. Supporting Documents -->
        <div class="bg-white rounded-3xl border border-[#f0e9dc] p-7 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-lg font-bold text-neutral-900 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#db802d]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                </svg>
                Supporting Credentials
              </h2>
              <p class="text-xs text-[#7a6e5a] mt-0.5">Certificates, transcripts, and credentials</p>
            </div>
            <button
              @click="showDocModal = true"
              class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-[#db802d] hover:bg-[#c46f20] cursor-pointer"
            >
              Upload
            </button>
          </div>

          <div v-if="supportingDocs && supportingDocs.length > 0" class="grid grid-cols-1 xl:grid-cols-2 gap-3">
            <div
              v-for="doc in supportingDocs"
              :key="doc.id"
              class="p-4 rounded-2xl bg-[#faf8f5] border border-[#f0e9dc] flex flex-row xl:flex-col 2xl:flex-row items-center justify-between hover:border-[#db802d]/40 transition-all"
            >
              <div class="flex items-center gap-3 min-w-0 flex-1 mr-2">
                <div class="w-9 h-9 rounded-xl bg-white border border-amber-200 flex items-center justify-center shrink-0 overflow-hidden shadow-2xs">
                  <img
                    v-if="isPdf(doc.original_filename)"
                    :src="pdfIcon"
                    alt="PDF"
                    class="w-6 h-6 object-contain"
                  />
                  <svg v-else-if="isImage(doc.original_filename)" class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <svg v-else class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 13h6m-6 4h6" />
                  </svg>
                </div>
                <div class="min-w-0 flex-1">
                  <span class="text-[10px] font-bold tracking-wider px-2 py-0.5 rounded-md bg-amber-100 text-amber-800">
                    {{ doc.type }}
                  </span>
                  <div class="text-xs font-bold text-neutral-800 mt-1 truncate">
                    {{ doc.original_filename.length > 10 ? doc.original_filename.substring(0, 10) + '...' : doc.original_filename }}
                  </div>
                  <div class="text-[11px] text-[#7a6e5a]">{{ formatBytes(doc.file_size) }}</div>
                </div>
              </div>
              <div class="flex items-center gap-1 shrink-0">
                <button
                  @click="openViewer(doc)"
                  class="p-2 text-[#db802d] hover:bg-amber-50 rounded-xl cursor-pointer"
                  title="Preview"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                </button>
                <button
                  @click="handleDownload(doc)"
                  class="p-2 text-[#db802d] hover:bg-amber-50 rounded-xl cursor-pointer"
                  title="Download"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                  </svg>
                </button>
                <button
                  @click="handleDeleteDoc(doc.id)"
                  class="p-2 text-red-500 hover:bg-red-50 rounded-xl cursor-pointer"
                  title="Delete"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                </button>
              </div>
            </div>
          </div>
          <div v-else class="p-8 text-center text-xs text-neutral-400 bg-[#faf8f5]/50 rounded-2xl border border-dashed border-[#f0e9dc]">
            No extra credentials uploaded.
          </div>
        </div>
      </div>
    </div>

    <!-- Modal: Add / Edit Education -->
    <div v-if="showEduModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
      <div class="bg-white rounded-3xl max-w-md w-full p-7 shadow-2xl border border-[#f0e9dc]">
        <div class="flex justify-between items-center pb-3 border-b border-neutral-100">
          <h3 class="font-bold text-lg text-neutral-900">{{ isEditingEdu ? 'Edit Education Record' : 'Add Education Record' }}</h3>
          <button @click="showEduModal = false" class="text-neutral-400 hover:text-neutral-600 font-bold text-2xl cursor-pointer">&times;</button>
        </div>
        <form @submit.prevent="handleSaveEdu" novalidate class="mt-4 space-y-3.5">
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Institution Name *</label>
            <input
              v-model="eduForm.institution"
              type="text"
              placeholder="Enter institution name"
              class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              :class="eduErrors.institution ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="eduErrors.institution" class="text-xs text-red-600 mt-1 font-medium">{{ eduErrors.institution }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Degree / Qualification *</label>
            <input
              v-model="eduForm.degree"
              type="text"
              placeholder="Enter degree or qualification"
              class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              :class="eduErrors.degree ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="eduErrors.degree" class="text-xs text-red-600 mt-1 font-medium">{{ eduErrors.degree }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Field Of Study *</label>
            <input
              v-model="eduForm.field_of_study"
              type="text"
              placeholder="Enter field of study"
              class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              :class="eduErrors.field_of_study ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="eduErrors.field_of_study" class="text-xs text-red-600 mt-1 font-medium">{{ eduErrors.field_of_study }}</p>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Start Date *</label>
              <input
                v-model="eduForm.start_date"
                type="date"
                class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
                :class="eduErrors.start_date ? 'border-red-400 bg-red-50/20' : ''"
              />
              <p v-if="eduErrors.start_date" class="text-xs text-red-600 mt-1 font-medium">{{ eduErrors.start_date }}</p>
            </div>
            <div>
              <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">End Date</label>
              <input
                v-model="eduForm.end_date"
                type="date"
                :disabled="eduForm.is_current"
                class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] disabled:bg-neutral-100"
              />
            </div>
          </div>
          <div class="flex items-center gap-2">
            <input v-model="eduForm.is_current" type="checkbox" id="currentEdu" class="rounded text-[#db802d] focus:ring-[#db802d] cursor-pointer" />
            <label for="currentEdu" class="text-xs text-neutral-700 font-medium cursor-pointer">Currently Studying Here</label>
          </div>
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Grade / GPA (Optional)</label>
            <input v-model="eduForm.grade" type="text" placeholder="Enter GPA or grade" class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]" />
          </div>
          <div class="pt-3 flex justify-end gap-2.5 border-t border-neutral-100">
            <button type="button" @click="showEduModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-neutral-600 hover:bg-neutral-100 cursor-pointer">Cancel</button>
            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-[#db802d] hover:bg-[#c46f20] cursor-pointer">
              {{ isEditingEdu ? 'Update Education' : 'Save Education' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Add / Edit Experience -->
    <div v-if="showExpModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
      <div class="bg-white rounded-3xl max-w-md w-full p-7 shadow-2xl border border-[#f0e9dc]">
        <div class="flex justify-between items-center pb-3 border-b border-neutral-100">
          <h3 class="font-bold text-lg text-neutral-900">{{ isEditingExp ? 'Edit Work Experience' : 'Add Work Experience' }}</h3>
          <button @click="showExpModal = false" class="text-neutral-400 hover:text-neutral-600 font-bold text-2xl cursor-pointer">&times;</button>
        </div>
        <form @submit.prevent="handleSaveExp" novalidate class="mt-4 space-y-3.5">
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Company Name *</label>
            <input
              v-model="expForm.company_name"
              type="text"
              placeholder="Enter company name"
              class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              :class="expErrors.company_name ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="expErrors.company_name" class="text-xs text-red-600 mt-1 font-medium">{{ expErrors.company_name }}</p>
          </div>
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Job Title *</label>
            <input
              v-model="expForm.job_title"
              type="text"
              placeholder="Enter job title"
              class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
              :class="expErrors.job_title ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="expErrors.job_title" class="text-xs text-red-600 mt-1 font-medium">{{ expErrors.job_title }}</p>
          </div>
          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Start Date *</label>
              <input
                v-model="expForm.start_date"
                type="date"
                class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"
                :class="expErrors.start_date ? 'border-red-400 bg-red-50/20' : ''"
              />
              <p v-if="expErrors.start_date" class="text-xs text-red-600 mt-1 font-medium">{{ expErrors.start_date }}</p>
            </div>
            <div>
              <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">End Date</label>
              <input
                v-model="expForm.end_date"
                type="date"
                :disabled="expForm.is_current"
                class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] disabled:bg-neutral-100"
              />
            </div>
          </div>
          <div class="flex items-center gap-2">
            <input v-model="expForm.is_current" type="checkbox" id="currentExp" class="rounded text-[#db802d] focus:ring-[#db802d] cursor-pointer" />
            <label for="currentExp" class="text-xs text-neutral-700 font-medium cursor-pointer">Currently Working Here</label>
          </div>
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Key Responsibilities / Impact</label>
            <textarea v-model="expForm.responsibilities" rows="3" placeholder="Enter key responsibilities" class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d]"></textarea>
          </div>
          <div class="pt-3 flex justify-end gap-2.5 border-t border-neutral-100">
            <button type="button" @click="showExpModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-neutral-600 hover:bg-neutral-100 cursor-pointer">Cancel</button>
            <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-[#db802d] hover:bg-[#c46f20] cursor-pointer">
              {{ isEditingExp ? 'Update Experience' : 'Save Experience' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: Upload Supporting Document -->
    <div v-if="showDocModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
      <div class="bg-white rounded-3xl max-w-md w-full p-7 shadow-2xl border border-[#f0e9dc]">
        <div class="flex justify-between items-center pb-3 border-b border-neutral-100">
          <h3 class="font-bold text-lg text-neutral-900">Upload Supporting Document</h3>
          <button @click="showDocModal = false" class="text-neutral-400 hover:text-neutral-600 font-bold text-2xl cursor-pointer">&times;</button>
        </div>
        <form @submit.prevent="handleSaveDoc" novalidate class="mt-4 space-y-4">
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Document Type *</label>
            <select v-model="docForm.type" class="w-full pl-3.5 pr-8 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] cursor-pointer">
              <option value="certificate">Professional Certificate</option>
              <option value="degree">Degree / Diploma Transcript</option>
              <option value="application_letter">Application Letter</option>
              <option value="other">Other Recommendation / Reference</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-semibold tracking-wider text-[#7a6e5a] mb-1">Select File (PDF, DOCX, PNG, JPG - max 15MB)</label>
            <div class="border border-dashed border-[#db802d]/40 rounded-2xl p-3 bg-[#faf8f5]">
              <input type="file" ref="supportDocInput" class="w-full text-xs text-neutral-600 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-[#db802d] file:text-white hover:file:bg-[#c46f20] cursor-pointer" />
            </div>
            <p v-if="docError" class="text-xs text-red-600 mt-1 font-medium">{{ docError }}</p>
          </div>
          <div class="pt-3 flex justify-end gap-2.5 border-t border-neutral-100">
            <button type="button" @click="showDocModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-neutral-600 hover:bg-neutral-100 cursor-pointer">Cancel</button>
            <button type="submit" :disabled="profileStore.uploadingDoc" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-[#db802d] hover:bg-[#c46f20] cursor-pointer disabled:opacity-50">
              {{ profileStore.uploadingDoc ? 'Uploading...' : 'Upload Document' }}
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal: In-App Document Viewer with docx-preview Support -->
    <div v-if="viewerModal.open" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-xs p-4">
      <div class="bg-white rounded-3xl max-w-5xl w-full h-[88vh] flex flex-col shadow-2xl border border-[#f0e9dc] overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-[#f0e9dc] flex items-center justify-between bg-[#faf8f5]">
          <div class="flex items-center gap-2.5 min-w-0">
            <svg class="w-5 h-5 text-[#db802d] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
            </svg>
            <span class="font-bold text-base text-neutral-900 truncate">{{ viewerModal.filename }}</span>
          </div>
          <div class="flex items-center gap-3">
            <button
              v-if="viewerModal.doc"
              @click="handleDownload(viewerModal.doc)"
              class="px-3.5 py-1.5 rounded-xl text-xs font-semibold text-[#db802d] bg-amber-50 hover:bg-amber-100 border border-amber-200 cursor-pointer flex items-center gap-1.5"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Download
            </button>
            <button @click="closeViewer" class="text-neutral-400 hover:text-neutral-600 font-bold text-2xl cursor-pointer">&times;</button>
          </div>
        </div>

        <div class="flex-1 bg-neutral-100 p-2 overflow-auto flex items-center justify-center relative">
          <!-- Loading state -->
          <div v-if="viewerModal.loading" class="flex flex-col items-center gap-3 text-neutral-600">
            <div class="w-8 h-8 border-3 border-[#db802d] border-t-transparent rounded-full animate-spin"></div>
            <span class="text-xs font-semibold">Rendering document preview...</span>
          </div>

          <!-- Error state (Preview failed - NO automatic download) -->
          <div v-else-if="viewerModal.error" class="text-center p-8 bg-white rounded-3xl border border-[#f0e9dc] max-w-md shadow-sm">
            <div class="w-14 h-14 rounded-2xl bg-amber-50 text-[#db802d] flex items-center justify-center mx-auto mb-3">
              <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
              </svg>
            </div>
            <h4 class="font-bold text-neutral-900 text-base mb-1">Preview Unavailable</h4>
            <p class="text-xs text-neutral-500 mb-5 leading-relaxed">{{ viewerModal.error }}</p>
            <button
              v-if="viewerModal.doc"
              @click="handleDownload(viewerModal.doc)"
              class="w-full py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-[#db802d] hover:bg-[#c46f20] transition-colors cursor-pointer flex items-center justify-center gap-2 shadow-sm"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
              </svg>
              Download Document
            </button>
          </div>

          <!-- DOCX Viewer Container rendered by docx-preview -->
          <div
            v-else-if="viewerModal.isWordDoc"
            ref="docxContainerRef"
            class="w-full h-full overflow-auto bg-white rounded-2xl p-6 sm:p-10 shadow-sm border border-neutral-200 docx-wrapper"
          ></div>

          <!-- Direct Image Preview with High-Res Fit/Contain -->
          <div
            v-else-if="viewerModal.isImage && viewerModal.url"
            class="w-full h-full flex items-center justify-center p-4 overflow-auto bg-neutral-900/5 rounded-2xl"
          >
            <img
              :src="viewerModal.url"
              :alt="viewerModal.filename"
              class="max-w-full max-h-full object-contain rounded-xl shadow-md border border-neutral-200 bg-white"
            />
          </div>

          <!-- Document / PDF Iframe Preview with Object Embed Fallback -->
          <div v-else-if="viewerModal.url" class="w-full h-full rounded-2xl overflow-hidden bg-white shadow-xs">
            <object
              :data="viewerModal.url"
              type="application/pdf"
              class="w-full h-full rounded-2xl border-0 bg-white"
            >
              <iframe
                :src="viewerModal.url"
                class="w-full h-full rounded-2xl border-0 bg-white"
              ></iframe>
            </object>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, nextTick, onMounted, reactive, ref } from 'vue'
import { renderAsync } from 'docx-preview'
import pdfIcon from '../../assets/pdf_icon.png'
import { useApplicantProfileStore } from '../../stores/applicantProfile'
import { useAuthStore } from '../../stores/auth'
import { useNotificationStore } from '../../stores/notification'

const authStore = useAuthStore()
const profileStore = useApplicantProfileStore()
const notificationStore = useNotificationStore()

const isEditingPersonal = ref(false)
const savingPersonal = ref(false)
const uploadingAppLetter = ref(false)
const showEduModal = ref(false)
const showExpModal = ref(false)
const showDocModal = ref(false)
const isEditingEdu = ref(false)
const editingEduId = ref(null)
const isEditingExp = ref(false)
const editingExpId = ref(null)

const cvFileInput = ref(null)
const appLetterInput = ref(null)
const supportDocInput = ref(null)
const docxContainerRef = ref(null)

const viewerModal = reactive({
  open: false,
  url: '',
  filename: '',
  doc: null,
  isWordDoc: false,
  isImage: false,
  loading: false,
  error: '',
})

const personalForm = reactive({
  phone: '',
  city: '',
  country: 'Ethiopia',
  date_of_birth: '',
  bio: '',
})

const personalErrors = reactive({
  phone: '',
})

const skillForm = reactive({
  name: '',
  proficiency: 'Intermediate',
})
const skillError = ref('')

const eduForm = reactive({
  institution: '',
  degree: '',
  field_of_study: '',
  start_date: '',
  end_date: '',
  is_current: false,
  grade: '',
})

const eduErrors = reactive({
  institution: '',
  degree: '',
  field_of_study: '',
  start_date: '',
})

const expForm = reactive({
  company_name: '',
  job_title: '',
  start_date: '',
  end_date: '',
  is_current: false,
  responsibilities: '',
})

const expErrors = reactive({
  company_name: '',
  job_title: '',
  start_date: '',
})

const docForm = reactive({
  type: 'certificate',
})
const docError = ref('')

const supportingDocs = computed(() => {
  return (profileStore.documents || []).filter((d) => !d.is_primary_cv && d.type !== 'application_letter')
})

const appLetterDoc = computed(() => {
  return (profileStore.documents || []).find((d) => d.type === 'application_letter')
})

const isPdf = (filename) => {
  return filename && filename.toLowerCase().endsWith('.pdf')
}

const isImage = (filename) => {
  if (!filename) return false
  const lower = filename.toLowerCase()
  return lower.endsWith('.png') || lower.endsWith('.jpg') || lower.endsWith('.jpeg') || lower.endsWith('.webp')
}

const isWord = (filename) => {
  if (!filename) return false
  const lower = filename.toLowerCase()
  return lower.endsWith('.docx') || lower.endsWith('.doc')
}

const formatDateClean = (dateStr) => {
  if (!dateStr) return ''
  return dateStr.substring(0, 10)
}

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
  personalErrors.phone = ''
  if (!personalForm.phone || !personalForm.phone.trim()) {
    personalErrors.phone = 'This field is required.'
    return
  }

  savingPersonal.value = true
  try {
    await profileStore.updatePersonalInfo(personalForm)
    notificationStore.success('Personal contact details updated successfully.', 'Success')
    isEditingPersonal.value = false
  } catch (err) {
    notificationStore.error(err.response?.data?.message || 'Error saving personal information.', 'Error')
  } finally {
    savingPersonal.value = false
  }
}

const handleCvSelect = async (e) => {
  const file = e.target.files[0]
  if (!file) return
  if (file.size > 15 * 1024 * 1024) {
    notificationStore.error('CV file size exceeds 15MB limit.', 'Error')
    return
  }
  try {
    await profileStore.uploadCv(file)
    notificationStore.success('CV uploaded and verified successfully.', 'Success')
  } catch (err) {
    notificationStore.error(err.response?.data?.message || 'Error uploading CV.', 'Error')
  }
}

const handleAppLetterSelect = async (e) => {
  const file = e.target.files[0]
  if (!file) return
  if (file.size > 15 * 1024 * 1024) {
    notificationStore.error('Application Letter exceeds 15MB limit.', 'Error')
    return
  }
  uploadingAppLetter.value = true
  try {
    await profileStore.uploadSupportingDocument('application_letter', file)
    notificationStore.success('Application letter uploaded successfully.', 'Success')
  } catch (err) {
    notificationStore.error(err.response?.data?.message || 'Error uploading application letter.', 'Error')
  } finally {
    uploadingAppLetter.value = false
  }
}

const handleAddSkill = async () => {
  skillError.value = ''
  if (!skillForm.name || !skillForm.name.trim()) {
    skillError.value = 'This field is required.'
    return
  }
  try {
    await profileStore.addSkill(skillForm)
    notificationStore.success(`Skill "${skillForm.name}" added.`, 'Success')
    skillForm.name = ''
  } catch (e) {
    notificationStore.error('Error adding skill.', 'Error')
  }
}

const handleDeleteSkill = async (id) => {
  try {
    await profileStore.deleteSkill(id)
    notificationStore.success('Skill removed successfully.', 'Success')
  } catch (e) {
    notificationStore.error('Failed to remove skill.', 'Error')
  }
}

// Education Management
const openAddEdu = () => {
  isEditingEdu.value = false
  editingEduId.value = null
  eduForm.institution = ''
  eduForm.degree = ''
  eduForm.field_of_study = ''
  eduForm.start_date = ''
  eduForm.end_date = ''
  eduForm.is_current = false
  eduForm.grade = ''
  eduErrors.institution = ''
  eduErrors.degree = ''
  eduErrors.field_of_study = ''
  eduErrors.start_date = ''
  showEduModal.value = true
}

const openEditEdu = (edu) => {
  isEditingEdu.value = true
  editingEduId.value = edu.id
  eduForm.institution = edu.institution || ''
  eduForm.degree = edu.degree || ''
  eduForm.field_of_study = edu.field_of_study || ''
  eduForm.start_date = edu.start_date ? edu.start_date.substring(0, 10) : ''
  eduForm.end_date = edu.end_date ? edu.end_date.substring(0, 10) : ''
  eduForm.is_current = Boolean(edu.is_current)
  eduForm.grade = edu.grade || ''
  eduErrors.institution = ''
  eduErrors.degree = ''
  eduErrors.field_of_study = ''
  eduErrors.start_date = ''
  showEduModal.value = true
}

const validateEduForm = () => {
  eduErrors.institution = ''
  eduErrors.degree = ''
  eduErrors.field_of_study = ''
  eduErrors.start_date = ''
  let valid = true

  if (!eduForm.institution || !eduForm.institution.trim()) {
    eduErrors.institution = 'This field is required.'
    valid = false
  }
  if (!eduForm.degree || !eduForm.degree.trim()) {
    eduErrors.degree = 'This field is required.'
    valid = false
  }
  if (!eduForm.field_of_study || !eduForm.field_of_study.trim()) {
    eduErrors.field_of_study = 'This field is required.'
    valid = false
  }
  if (!eduForm.start_date) {
    eduErrors.start_date = 'This field is required.'
    valid = false
  }
  return valid
}

const handleSaveEdu = async () => {
  if (!validateEduForm()) return
  try {
    if (isEditingEdu.value && editingEduId.value) {
      await profileStore.updateEducation(editingEduId.value, eduForm)
      notificationStore.success('Education record updated successfully.', 'Success')
    } else {
      await profileStore.addEducation(eduForm)
      notificationStore.success('Education record saved successfully.', 'Success')
    }
    showEduModal.value = false
  } catch (e) {
    notificationStore.error('Error saving education record.', 'Error')
  }
}

const handleDeleteEdu = async (id) => {
  try {
    await profileStore.deleteEducation(id)
    notificationStore.success('Education record removed.', 'Success')
  } catch (e) {
    notificationStore.error('Error deleting education record.', 'Error')
  }
}

// Work Experience Management
const openAddExp = () => {
  isEditingExp.value = false
  editingExpId.value = null
  expForm.company_name = ''
  expForm.job_title = ''
  expForm.start_date = ''
  expForm.end_date = ''
  expForm.is_current = false
  expForm.responsibilities = ''
  expErrors.company_name = ''
  expErrors.job_title = ''
  expErrors.start_date = ''
  showExpModal.value = true
}

const openEditExp = (exp) => {
  isEditingExp.value = true
  editingExpId.value = exp.id
  expForm.company_name = exp.company_name || ''
  expForm.job_title = exp.job_title || ''
  expForm.start_date = exp.start_date ? exp.start_date.substring(0, 10) : ''
  expForm.end_date = exp.end_date ? exp.end_date.substring(0, 10) : ''
  expForm.is_current = Boolean(exp.is_current)
  expForm.responsibilities = exp.responsibilities || ''
  expErrors.company_name = ''
  expErrors.job_title = ''
  expErrors.start_date = ''
  showExpModal.value = true
}

const validateExpForm = () => {
  expErrors.company_name = ''
  expErrors.job_title = ''
  expErrors.start_date = ''
  let valid = true

  if (!expForm.company_name || !expForm.company_name.trim()) {
    expErrors.company_name = 'This field is required.'
    valid = false
  }
  if (!expForm.job_title || !expForm.job_title.trim()) {
    expErrors.job_title = 'This field is required.'
    valid = false
  }
  if (!expForm.start_date) {
    expErrors.start_date = 'This field is required.'
    valid = false
  }
  return valid
}

const handleSaveExp = async () => {
  if (!validateExpForm()) return
  try {
    if (isEditingExp.value && editingExpId.value) {
      await profileStore.updateWorkExperience(editingExpId.value, expForm)
      notificationStore.success('Work experience updated successfully.', 'Success')
    } else {
      await profileStore.addWorkExperience(expForm)
      notificationStore.success('Work experience saved successfully.', 'Success')
    }
    showExpModal.value = false
  } catch (e) {
    notificationStore.error('Error saving work experience.', 'Error')
  }
}

const handleDeleteExp = async (id) => {
  try {
    await profileStore.deleteWorkExperience(id)
    notificationStore.success('Work experience removed.', 'Success')
  } catch (e) {
    notificationStore.error('Error deleting work experience.', 'Error')
  }
}

// Supporting Documents
const handleSaveDoc = async () => {
  docError.value = ''
  const file = supportDocInput.value?.files[0]
  if (!file) {
    docError.value = 'This field is required.'
    return
  }
  if (file.size > 15 * 1024 * 1024) {
    notificationStore.error('Document size exceeds 15MB limit.', 'Error')
    return
  }
  try {
    await profileStore.uploadSupportingDocument(docForm.type, file)
    showDocModal.value = false
    notificationStore.success('Document uploaded successfully.', 'Success')
  } catch (e) {
    notificationStore.error(e.response?.data?.message || 'Error uploading document.', 'Error')
  }
}

const handleDeleteDoc = async (id) => {
  try {
    await profileStore.deleteDocument(id)
    notificationStore.success('Document deleted successfully.', 'Success')
  } catch (e) {
    notificationStore.error('Error deleting document.', 'Error')
  }
}

// Document Preview & In-App Viewer
const openViewer = async (doc) => {
  viewerModal.doc = doc
  viewerModal.filename = doc.original_filename
  viewerModal.isWordDoc = isWord(doc.original_filename)
  viewerModal.isImage = isImage(doc.original_filename)
  viewerModal.loading = true
  viewerModal.error = ''
  viewerModal.open = true

  try {
    if (viewerModal.isWordDoc) {
      const blob = await profileStore.getDocumentBlob(doc)
      viewerModal.loading = false
      await nextTick()
      if (docxContainerRef.value) {
        docxContainerRef.value.innerHTML = ''
        await renderAsync(blob, docxContainerRef.value, null, {
          className: 'docx-preview-root',
          inWrapper: true,
          ignoreWidth: false,
          ignoreHeight: false,
        })
      }
    } else {
      const url = await profileStore.getDocumentBlobUrl(doc)
      viewerModal.url = url
      viewerModal.loading = false
    }
  } catch (err) {
    console.error('Document preview error:', err)
    viewerModal.loading = false
    viewerModal.error = 'Unable to render this document preview. You may still download it directly below.'
    // DO NOT trigger automatic download
  }
}

const closeViewer = () => {
  if (viewerModal.url) {
    window.URL.revokeObjectURL(viewerModal.url)
  }
  viewerModal.url = ''
  viewerModal.filename = ''
  viewerModal.doc = null
  viewerModal.isWordDoc = false
  viewerModal.isImage = false
  viewerModal.loading = false
  viewerModal.error = ''
  viewerModal.open = false
}

const handleDownload = async (doc) => {
  try {
    await profileStore.downloadDocument(doc)
    notificationStore.success(`Downloading ${doc.original_filename}`, 'Success')
  } catch (e) {
    const msg = e?.response?.data?.message || e?.message || 'Failed to download document.'
    notificationStore.error(msg, 'Download Error')
  }
}

onMounted(() => {
  loadProfile()
})
</script>

