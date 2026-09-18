import { defineStore } from 'pinia'
import api from '../api/client'

export const useOrganizationStore = defineStore('organization', {
  state: () => ({
    companies: [], // Renamed from subsidiaries for user-facing UI
    departments: [],
    loading: false,
    selectedCompanyId: null,
  }),

  actions: {
    async fetchCompanies(status = null) {
      this.loading = true
      try {
        const params = status ? { status } : {}
        const response = await api.get('/subsidiaries', { params })
        this.companies = response.data.data
        return this.companies
      } finally {
        this.loading = false
      }
    },

    async createCompany(data) {
      const response = await api.post('/subsidiaries', data)
      await this.fetchCompanies()
      return response.data
    },

    async updateCompany(id, data) {
      const response = await api.put(`/subsidiaries/${id}`, data)
      await this.fetchCompanies()
      return response.data
    },

    async deleteCompany(id) {
      await api.delete(`/subsidiaries/${id}`)
      await this.fetchCompanies()
    },

    async fetchDepartments(companyId = null) {
      this.loading = true
      try {
        const params = companyId ? { subsidiary_id: companyId } : {}
        const response = await api.get('/departments', { params })
        this.departments = response.data.data
        return this.departments
      } finally {
        this.loading = false
      }
    },

    async createDepartment(data) {
      const response = await api.post('/departments', data)
      await this.fetchDepartments(this.selectedCompanyId)
      return response.data
    },

    async updateDepartment(id, data) {
      const response = await api.put(`/departments/${id}`, data)
      await this.fetchDepartments(this.selectedCompanyId)
      return response.data
    },

    async deleteDepartment(id) {
      await api.delete(`/departments/${id}`)
      await this.fetchDepartments(this.selectedCompanyId)
    },
  },
})
