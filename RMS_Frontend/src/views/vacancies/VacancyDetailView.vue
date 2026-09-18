<template>
  <div v-if="vacancyStore.loading" class="bg-white rounded-3xl border border-[#f0e9dc] p-12 text-center text-sm text-[#7a6e5a]">
    Loading vacancy details...
  </div>

  <div v-else-if="!vacancy" class="bg-white rounded-3xl border border-[#f0e9dc] p-12 text-center text-red-600">
    Vacancy not found or access restricted.
  </div>

  <div v-else class="max-w-5xl mx-auto space-y-6 pb-12">
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
            class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 cursor-pointer shadow-sm"
          >
            Submit for Approval &rarr;
          </button>

          <!-- Pending Approval -> Approve or Return (HR Manager / Admin per UC-06) -->
          <template v-if="vacancy.status === 'Pending Approval' && canApprove">
            <button
              @click="handleApprove('Approved')"
              class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 cursor-pointer shadow-sm"
            >
              ✓ Approve Vacancy
            </button>
            <button
              @click="handleApprove('Draft')"
              class="px-3 py-2 rounded-xl text-xs font-semibold text-neutral-700 bg-neutral-100 hover:bg-neutral-200 cursor-pointer"
            >
              Return for Revision
            </button>
          </template>

          <!-- Approved -> Publish (FR-VAC-006, UC-07) -->
          <button
            v-if="vacancy.status === 'Approved'"
            @click="handlePublish"
            class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 cursor-pointer shadow-sm"
          >
            Publish to Job Board
          </button>

          <!-- Published -> Close (FR-VAC-007, UC-11) -->
          <button
            v-if="vacancy.status === 'Published'"
            @click="handleClose"
            class="px-3 py-2 rounded-xl text-xs font-semibold text-rose-700 bg-rose-50 border border-rose-200 hover:bg-rose-100 cursor-pointer"
          >
            Close Applications
          </button>

          <!-- Closed -> Archive (FR-VAC-008) -->
          <button
            v-if="vacancy.status === 'Closed'"
            @click="handleArchive"
            class="px-3 py-2 rounded-xl text-xs font-semibold text-neutral-600 bg-neutral-100 hover:bg-neutral-200 cursor-pointer"
          >
            Archive
          </button>
        </div>

        <!-- Applicant Action: Apply Now (Module 4 Readiness) -->
        <div v-else>
          <button
            v-if="vacancy.is_open"
            @click="alert('Module 4 application submission workflow connects here!')"
            class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] shadow-md cursor-pointer"
          >
            Apply for this Vacancy
          </button>
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
            <h2 class="text-sm font-bold uppercase tracking-wider text-[#db802d] mb-2">Job Description</h2>
            <p class="text-sm text-neutral-700 leading-relaxed whitespace-pre-line">{{ vacancy.description }}</p>
          </div>

          <div>
            <h2 class="text-sm font-bold uppercase tracking-wider text-[#db802d] mb-2">Key Responsibilities</h2>
            <div class="text-sm text-neutral-700 leading-relaxed whitespace-pre-line">{{ vacancy.responsibilities }}</div>
          </div>

          <div>
            <h2 class="text-sm font-bold uppercase tracking-wider text-[#db802d] mb-2">Qualifications & Background</h2>
            <div class="text-sm text-neutral-700 leading-relaxed whitespace-pre-line">{{ vacancy.qualifications }}</div>
          </div>

          <div v-if="vacancy.application_requirements">
            <h2 class="text-sm font-bold uppercase tracking-wider text-[#db802d] mb-2">Application Requirements</h2>
            <div class="text-sm text-neutral-700 leading-relaxed whitespace-pre-line">{{ vacancy.application_requirements }}</div>
          </div>
        </div>

        <!-- Custom Screening Questions Manager (BR-009) -->
        <div v-if="!authStore.isApplicant" class="bg-white rounded-3xl border border-[#f0e9dc] p-6 shadow-xs">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-sm font-bold uppercase tracking-wider text-[#db802d]">
                Custom Screening Questions (BR-009)
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
                    placeholder="Question prompt..."
                    class="w-full px-3 py-1.5 rounded-xl border border-neutral-200 text-xs focus:ring-2 focus:ring-[#ebb630] bg-white"
                  />
                </div>
                <div>
                  <select
                    v-model="q.question_type"
                    class="w-full px-3 py-1.5 rounded-xl border border-neutral-200 text-xs focus:ring-2 focus:ring-[#ebb630] bg-white"
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
          <h2 class="text-sm font-bold uppercase tracking-wider text-neutral-900 pb-2 border-b border-neutral-100">
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

          <!-- Internal Salary (Confidential BR-005) -->
          <div v-if="!authStore.isApplicant && vacancy.salary_amount" class="p-3 rounded-xl bg-amber-50 border border-amber-200">
            <div class="text-[10px] uppercase font-bold text-amber-900 flex items-center gap-1">
              <span>🔒 Internal Salary (Confidential)</span>
            </div>
            <div class="text-sm font-extrabold text-neutral-900 mt-1">
              {{ vacancy.salary_amount }} {{ vacancy.salary_currency }}
            </div>
            <div class="text-[10px] text-[#7a6e5a] mt-0.5">Masked from applicant portal</div>
          </div>
        </div>

        <!-- Status Audit Trail (FR-VAC-003, Section 6) -->
        <div v-if="!authStore.isApplicant" class="bg-white rounded-3xl border border-[#f0e9dc] p-6 shadow-xs">
          <h2 class="text-sm font-bold uppercase tracking-wider text-neutral-900 pb-2 border-b border-neutral-100 mb-4">
            Approval & Status Trail
          </h2>

          <div v-if="vacancy.status_histories && vacancy.status_histories.length > 0" class="space-y-3">
            <div
              v-for="h in vacancy.status_histories"
              :key="h.id"
              class="text-xs border-l-2 border-[#db802d] pl-3 py-0.5"
            >
              <div class="font-bold text-neutral-800 flex items-center justify-between">
                <span>{{ h.to_status }}</span>
                <span class="text-[10px] text-neutral-400">{{ new Date(h.created_at).toLocaleDateString() }}</span>
              </div>
              <div class="text-[11px] text-[#7a6e5a]">By: {{ h.user?.name || 'Staff' }}</div>
              <p v-if="h.notes" class="text-[11px] text-neutral-600 mt-1 italic">"{{ h.notes }}"</p>
            </div>
          </div>
          <div v-else class="text-xs text-neutral-400 italic">
            No history recorded.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'
import { useVacancyStore } from '../../stores/vacancy'

const route = useRoute()
const authStore = useAuthStore()
const vacancyStore = useVacancyStore()

const questions = ref([])

const vacancy = computed(() => vacancyStore.activeVacancy)

const canApprove = computed(() => {
  return ['Admin', 'HR Manager'].includes(authStore.role)
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

const handleSubmitForApproval = async () => {
  try {
    await vacancyStore.submitForApproval(route.params.id)
    alert('Vacancy submitted for approval.')
  } catch (err) {
    alert(err.response?.data?.message || 'Error submitting.')
  }
}

const handleApprove = async (decision) => {
  const notes = prompt(`Enter ${decision === 'Approved' ? 'approval' : 'revision'} notes:`)
  try {
    await vacancyStore.approveVacancy(route.params.id, decision, notes || '')
    alert(`Vacancy updated to ${decision}.`)
  } catch (err) {
    alert(err.response?.data?.message || 'Error updating approval.')
  }
}

const handlePublish = async () => {
  try {
    await vacancyStore.publishVacancy(route.params.id)
    alert('Vacancy published successfully to job board.')
  } catch (err) {
    alert(err.response?.data?.message || 'Error publishing.')
  }
}

const handleClose = async () => {
  if (!confirm('Are you sure you want to close this vacancy? Applications will be blocked.')) return
  try {
    await vacancyStore.closeVacancy(route.params.id)
  } catch (err) {
    alert('Error closing vacancy.')
  }
}

const handleArchive = async () => {
  if (!confirm('Archive this vacancy?')) return
  try {
    await vacancyStore.archiveVacancy(route.params.id)
  } catch (err) {
    alert('Error archiving vacancy.')
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
    alert('Screening questions saved.')
  } catch (err) {
    alert('Error saving questions.')
  }
}

onMounted(() => {
  loadVacancy()
})
</script>
