import { defineStore } from 'pinia'
import api from '../api/client'

export const useApplicationStore = defineStore('application', {
  state: () => ({
    myApplications: [],
    loading: false,
    applying: false,
  }),

  getters: {
    // Check if user has already applied to a specific vacancy
    hasApplied: (state) => (vacancyId) => {
      return state.myApplications.some((a) => a.vacancy_id === vacancyId && a.status !== 'Withdrawn')
    },
    getApplication: (state) => (vacancyId) => {
      return state.myApplications.find((a) => a.vacancy_id === vacancyId)
    },
  },

  actions: {
    async fetchMyApplications() {
      this.loading = true
      try {
        const response = await api.get('/applicant/applications')
        this.myApplications = response.data.data
        return this.myApplications
      } finally {
        this.loading = false
      }
    },

    async apply(vacancyId, payload = {}) {
      this.applying = true
      try {
        const response = await api.post(`/applicant/applications/${vacancyId}`, payload)
        // Add to local list immediately
        if (response.data.application) {
          this.myApplications.push(response.data.application)
        }
        return response.data
      } finally {
        this.applying = false
      }
    },

    async withdraw(applicationId, reason = '') {
      const response = await api.patch(`/applicant/applications/${applicationId}/withdraw`, { reason })
      // Update local state
      const idx = this.myApplications.findIndex((a) => a.id === applicationId)
      if (idx !== -1) {
        this.myApplications[idx].status = 'Withdrawn'
      }
      return response.data
    },
  },
})
