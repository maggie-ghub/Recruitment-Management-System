<template>
  <div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
      <div>
        <h1 class="text-3xl font-extrabold text-neutral-900 tracking-tight">Audit Trail</h1>
        <p class="text-sm text-[#7a6e5a] mt-1">
          Immutable record of administrative, authentication, and recruitment events
        </p>
      </div>
      <!-- Total badge -->
      <div v-if="meta" class="px-4 py-2 rounded-2xl bg-amber-50 border border-amber-200 text-xs font-bold text-[#db802d] self-start sm:self-auto">
        {{ meta.total.toLocaleString() }} total events
      </div>
    </div>

    <!-- Filters row -->
    <div class="bg-white p-4 rounded-3xl border border-[#f0e9dc] shadow-xs flex flex-wrap items-center gap-3">
      <div class="flex-1 min-w-[200px]">
        <input
          v-model="search"
          @input="onSearchInput"
          type="text"
          placeholder="Search by user, action, or entity..."
          class="w-full px-4 py-2.5 rounded-2xl border border-neutral-200 text-sm focus:outline-none focus:ring-1 focus:ring-[#db802d] bg-neutral-50"
        />
      </div>
      <select
        v-model="filterAction"
        @change="loadLogs(1)"
        class="pl-3.5 pr-8 py-2.5 rounded-2xl border border-neutral-200 text-xs font-medium focus:outline-none focus:ring-1 focus:ring-[#db802d] bg-neutral-50 cursor-pointer"
      >
        <option value="">All Actions</option>
        <option value="login">Login</option>
        <option value="register">Register</option>
        <option value="created">Created</option>
        <option value="updated">Updated</option>
        <option value="deleted">Deleted</option>
        <option value="suspended">Suspended</option>
        <option value="application_submitted">Application Submitted</option>
        <option value="document_downloaded">Document Downloaded</option>
      </select>
      <select
        v-model="perPage"
        @change="loadLogs(1)"
        class="pl-3.5 pr-8 py-2.5 rounded-2xl border border-neutral-200 text-xs font-medium focus:outline-none focus:ring-1 focus:ring-[#db802d] bg-neutral-50 cursor-pointer"
      >
        <option :value="10">10 / page</option>
        <option :value="20">20 / page</option>
        <option :value="50">50 / page</option>
        <option :value="100">100 / page</option>
      </select>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-3xl border border-[#f0e9dc] shadow-xs overflow-hidden w-full">
      <div v-if="loading" class="p-16 text-center text-base text-[#7a6e5a]">
        Loading audit records...
      </div>

      <div v-else-if="logs.length === 0" class="p-16 text-center text-base text-neutral-500">
        No audit log events found.
      </div>

      <div v-else class="w-full overflow-x-auto">
        <table class="w-full text-left text-sm min-w-full">
          <thead class="bg-[#faf8f5] text-xs font-bold tracking-wider text-[#7a6e5a] border-b border-[#f0e9dc]">
            <tr>
              <th class="py-4 px-6 whitespace-nowrap">#</th>
              <th class="py-4 px-6 whitespace-nowrap">Timestamp</th>
              <th class="py-4 px-6 whitespace-nowrap">User</th>
              <th class="py-4 px-6 whitespace-nowrap">Action</th>
              <th class="py-4 px-6 whitespace-nowrap">Entity Type</th>
              <th class="py-4 px-6 whitespace-nowrap">Entity ID</th>
              <th class="py-4 px-6 whitespace-nowrap">IP Address</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-100">
            <tr v-for="(log, idx) in logs" :key="log.id" class="hover:bg-amber-50/20 transition-colors">
              <td class="py-3 px-6 text-xs text-neutral-400 font-mono">
                {{ (meta.from + idx) }}
              </td>
              <td class="py-3 px-6 text-xs text-neutral-600 font-mono whitespace-nowrap">
                {{ new Date(log.created_at).toLocaleString() }}
              </td>
              <td class="py-3 px-6">
                <div class="font-bold text-neutral-900 text-sm">{{ log.user?.name || 'System / Guest' }}</div>
                <div class="text-xs text-[#7a6e5a]">{{ log.user?.email || '-' }}</div>
              </td>
              <td class="py-3 px-6">
                <span
                  class="px-2.5 py-1 rounded-full text-xs font-semibold tracking-wider"
                  :class="getActionBadgeClass(log.action)"
                >
                  {{ log.action.replace(/_/g, ' ') }}
                </span>
              </td>
              <td class="py-3 px-6 font-mono text-xs text-neutral-700 font-semibold">{{ log.entity_type }}</td>
              <td class="py-3 px-6 font-mono text-xs text-[#db802d] font-bold">#{{ log.entity_id }}</td>
              <td class="py-3 px-6 font-mono text-xs text-neutral-500">{{ log.ip_address || '127.0.0.1' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Footer -->
      <div v-if="meta && meta.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-[#f0e9dc] bg-[#faf8f5]">
        <!-- Info -->
        <div class="text-xs text-[#7a6e5a] font-medium">
          Showing <strong class="text-neutral-800">{{ meta.from }}</strong>–<strong class="text-neutral-800">{{ meta.to }}</strong>
          of <strong class="text-neutral-800">{{ meta.total.toLocaleString() }}</strong> events
        </div>

        <!-- Page buttons -->
        <div class="flex items-center gap-1">
          <!-- Prev -->
          <button
            @click="loadLogs(currentPage - 1)"
            :disabled="currentPage === 1"
            class="px-3 py-1.5 rounded-xl text-xs font-semibold border border-neutral-200 text-neutral-600 hover:bg-white disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-all"
          >
            ← Prev
          </button>

          <!-- Page numbers (show up to 7 pages with ellipsis) -->
          <template v-for="page in visiblePages" :key="page">
            <span v-if="page === '...'" class="px-2 text-neutral-400 text-xs">…</span>
            <button
              v-else
              @click="loadLogs(page)"
              class="px-3 py-1.5 rounded-xl text-xs font-bold border transition-all cursor-pointer"
              :class="page === currentPage
                ? 'bg-[#db802d] text-white border-[#db802d] shadow-sm'
                : 'border-neutral-200 text-neutral-600 hover:bg-white hover:border-[#db802d]/40'"
            >
              {{ page }}
            </button>
          </template>

          <!-- Next -->
          <button
            @click="loadLogs(currentPage + 1)"
            :disabled="currentPage === meta.last_page"
            class="px-3 py-1.5 rounded-xl text-xs font-semibold border border-neutral-200 text-neutral-600 hover:bg-white disabled:opacity-40 disabled:cursor-not-allowed cursor-pointer transition-all"
          >
            Next →
          </button>
        </div>
      </div>

      <!-- Simple total count when only 1 page -->
      <div v-else-if="meta && meta.last_page === 1 && logs.length > 0" class="px-6 py-3 border-t border-[#f0e9dc] bg-[#faf8f5]">
        <div class="text-xs text-[#7a6e5a]">
          Showing all <strong class="text-neutral-800">{{ meta.total }}</strong> events
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, ref } from 'vue'
import api from '../../api/client'

const logs = ref([])
const loading = ref(false)
const currentPage = ref(1)
const perPage = ref(20)
const meta = ref(null)
const search = ref('')
const filterAction = ref('')
let searchTimeout = null

const loadLogs = async (page = 1) => {
  currentPage.value = page
  loading.value = true
  try {
    const params = {
      page,
      per_page: perPage.value,
    }
    if (filterAction.value) params.action = filterAction.value
    if (search.value.trim()) params.search = search.value.trim()

    const response = await api.get('/audit-logs', { params })
    logs.value = response.data.data
    meta.value = response.data.meta
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

// Debounce search input
const onSearchInput = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => loadLogs(1), 350)
}

// Generate visible page range with ellipsis
const visiblePages = computed(() => {
  if (!meta.value) return []
  const total = meta.value.last_page
  const current = currentPage.value
  const delta = 2
  const pages = []

  const left = Math.max(2, current - delta)
  const right = Math.min(total - 1, current + delta)

  pages.push(1)
  if (left > 2) pages.push('...')
  for (let i = left; i <= right; i++) pages.push(i)
  if (right < total - 1) pages.push('...')
  if (total > 1) pages.push(total)

  return pages
})

const getActionBadgeClass = (action) => {
  if (['created', 'register', 'application_submitted'].includes(action))
    return 'bg-emerald-50 text-emerald-700 border border-emerald-200'
  if (['updated', 'login', 'document_downloaded'].includes(action))
    return 'bg-blue-50 text-blue-700 border border-blue-200'
  if (['deleted', 'suspended'].includes(action))
    return 'bg-red-50 text-red-700 border border-red-200'
  return 'bg-neutral-100 text-neutral-700'
}

// Load on mount
loadLogs()
</script>
