import { defineStore } from 'pinia'
import api from '../api/client'

const API_BASE = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace(/\/$/, '')

/**
 * Multipart file upload via native fetch().
 * Axios merges instance-level Content-Type headers which can corrupt
 * the multipart boundary on FormData. fetch() lets the browser set
 * Content-Type: multipart/form-data; boundary=... automatically.
 */
async function multipartPost(endpoint, formData, method = 'POST') {
  const token = localStorage.getItem('vms_auth_token')
  const response = await fetch(`${API_BASE}/${endpoint}`, {
    method: method,
    headers: {
      Authorization: `Bearer ${token}`,
      // Do NOT set Content-Type – browser sets it with the correct boundary
    },
    body: formData,
  })
  const json = await response.json()
  if (!response.ok) {
    const err = new Error(json.message || 'Request failed')
    err.response = { data: json, status: response.status }
    throw err
  }
  return json
}

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
      // Always use native fetch() for multipart – avoids Axios Content-Type/boundary issue
      const result = await multipartPost('subsidiaries', data)
      await this.fetchCompanies()
      return result
    },

    async updateCompany(id, data) {
      if (data instanceof FormData) {
        // Laravel method spoofing: POST with _method=PUT in FormData
        // Use native fetch() to preserve multipart boundary
        const result = await multipartPost(`subsidiaries/${id}`, data)
        await this.fetchCompanies()
        return result
      }
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
