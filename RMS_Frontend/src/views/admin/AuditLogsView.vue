<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-neutral-900 tracking-tight">Audit Trail</h1>
      <p class="text-xs text-[#7a6e5a] mt-0.5">
        Immutable record of administrative and recruitment activities (FR-ADM-003, FR-ADM-004, NFR-SEC-006)
      </p>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white rounded-3xl border border-[#f0e9dc] shadow-xs overflow-hidden">
      <div v-if="loading" class="p-12 text-center text-sm text-[#7a6e5a]">
        Loading audit records...
      </div>

      <div v-else-if="logs.length === 0" class="p-12 text-center text-sm text-neutral-500">
        No audit log events recorded yet.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-[#faf8f5] text-xs font-bold uppercase tracking-wider text-[#7a6e5a] border-b border-[#f0e9dc]">
            <tr>
              <th class="py-3.5 px-5">Timestamp</th>
              <th class="py-3.5 px-5">User</th>
              <th class="py-3.5 px-5">Action</th>
              <th class="py-3.5 px-5">Entity</th>
              <th class="py-3.5 px-5">Entity ID</th>
              <th class="py-3.5 px-5">IP Address</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-neutral-100">
            <tr v-for="log in logs" :key="log.id" class="hover:bg-neutral-50/50">
              <td class="py-3.5 px-5 text-xs text-neutral-600 font-mono">
                {{ new Date(log.created_at).toLocaleString() }}
              </td>
              <td class="py-3.5 px-5">
                <div class="font-bold text-neutral-800 text-xs">{{ log.user?.name || 'System / Guest' }}</div>
                <div class="text-[11px] text-[#7a6e5a]">{{ log.user?.email || '-' }}</div>
              </td>
              <td class="py-3.5 px-5">
                <span
                  class="px-2 py-0.5 rounded-md text-xs font-semibold uppercase"
                  :class="getActionBadgeClass(log.action)"
                >
                  {{ log.action }}
                </span>
              </td>
              <td class="py-3.5 px-5 font-mono text-xs text-neutral-700">{{ log.entity_type }}</td>
              <td class="py-3.5 px-5 font-mono text-xs text-[#7a6e5a]">#{{ log.entity_id }}</td>
              <td class="py-3.5 px-5 font-mono text-xs text-neutral-500">{{ log.ip_address || '127.0.0.1' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../../api/client'

const logs = ref([])
const loading = ref(false)

const loadLogs = async () => {
  loading.value = true
  try {
    const response = await api.get('/audit-logs')
    logs.value = response.data.data
  } catch (err) {
    console.error(err)
  } finally {
    loading.value = false
  }
}

const getActionBadgeClass = (action) => {
  switch (action) {
    case 'created':
    case 'register':
      return 'bg-emerald-50 text-emerald-700 border border-emerald-200'
    case 'updated':
    case 'login':
      return 'bg-blue-50 text-blue-700 border border-blue-200'
    case 'deleted':
    case 'suspended':
      return 'bg-red-50 text-red-700 border border-red-200'
    default:
      return 'bg-neutral-100 text-neutral-700'
  }
}

onMounted(() => {
  loadLogs()
})
</script>
