import { defineStore } from 'pinia'
import api from '../api/client'

export const useVacancyStore = defineStore('vacancy', {
  state: () => ({
    vacancies: [],
    activeVacancy: null,
    loading: false,
    actionLoading: false,
    filters: {
      search: '',
      subsidiary_id: '',
      department_id: '',
      status: '',
      employment_type: '',
    },
  }),

  getters: {
    // Alias so both vacancyStore.currentVacancy and vacancyStore.activeVacancy work
    currentVacancy: (state) => state.activeVacancy,
  },

  actions: {
    async fetchVacancies(customParams = {}) {
      this.loading = true
      try {
        const params = { ...this.filters, ...customParams }
        // Clean empty keys
        Object.keys(params).forEach((key) => {
          if (!params[key]) delete params[key]
        })
        const response = await api.get('/vacancies', { params })
        this.vacancies = response.data.data
        return this.vacancies
      } finally {
        this.loading = false
      }
    },

    async fetchVacancy(id) {
      this.loading = true
      this.activeVacancy = null   // clear stale data so the loading state shows
      try {
        const response = await api.get(`/vacancies/${id}`)
        this.activeVacancy = response.data.data
        return this.activeVacancy
      } catch (err) {
        this.activeVacancy = null
        throw err
      } finally {
        this.loading = false
      }
    },

    async createVacancy(payload) {
      this.actionLoading = true
      try {
        const response = await api.post('/vacancies', payload)
        return response.data
      } finally {
        this.actionLoading = false
      }
    },

    async updateVacancy(id, payload) {
      this.actionLoading = true
      try {
        const response = await api.put(`/vacancies/${id}`, payload)
        return response.data
      } finally {
        this.actionLoading = false
      }
    },

    async submitForApproval(id, notes = '') {
      const response = await api.post(`/vacancies/${id}/submit-approval`, { notes })
      await this.fetchVacancy(id)
      return response.data
    },

    async approveVacancy(id, decision, notes = '') {
      const response = await api.post(`/vacancies/${id}/approve`, { decision, notes })
      await this.fetchVacancy(id)
      return response.data
    },

    async publishVacancy(id) {
      const response = await api.post(`/vacancies/${id}/publish`)
      await this.fetchVacancy(id)
      return response.data
    },

    async closeVacancy(id, notes = '') {
      const response = await api.post(`/vacancies/${id}/close`, { notes })
      await this.fetchVacancy(id)
      return response.data
    },

    async reopenVacancy(id, notes = '') {
      const response = await api.post(`/vacancies/${id}/reopen`, { notes })
      await this.fetchVacancy(id)
      return response.data
    },

    async archiveVacancy(id) {
      const response = await api.post(`/vacancies/${id}/archive`)
      await this.fetchVacancy(id)
      return response.data
    },

    async saveQuestions(id, questions) {
      const response = await api.post(`/vacancies/${id}/questions`, { questions })
      if (this.activeVacancy) {
        this.activeVacancy.questions = response.data.questions
      }
      return response.data
    },
  },
})

