import { defineStore } from 'pinia'
import api from '../api/client'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: JSON.parse(localStorage.getItem('vms_user') || 'null'),
    token: localStorage.getItem('vms_auth_token') || null,
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token && !!state.user,
    role: (state) => state.user?.role || 'Applicant',
    isAdmin: (state) => state.user?.role === 'Admin',
    isHRManager: (state) => state.user?.role === 'HR Manager',
    isRecruiter: (state) => state.user?.role === 'Recruiter',
    isHiringManager: (state) => state.user?.role === 'Hiring Manager',
    isApplicant: (state) => state.user?.role === 'Applicant',
    isHoldingCompanyUser: (state) => !state.user?.subsidiary_id,
    userCompany: (state) => state.user?.subsidiary || null,
  },

  actions: {
    async login(credentials) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/auth/login', credentials)
        this.token = response.data.token
        this.user = response.data.user
        localStorage.setItem('vms_auth_token', this.token)
        localStorage.setItem('vms_user', JSON.stringify(this.user))
        return response.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Login failed. Please check your credentials.'
        throw err
      } finally {
        this.loading = false
      }
    },

    async register(payload) {
      this.loading = true
      this.error = null
      try {
        const response = await api.post('/auth/register', payload)
        this.token = response.data.token
        this.user = response.data.user
        localStorage.setItem('vms_auth_token', this.token)
        localStorage.setItem('vms_user', JSON.stringify(this.user))
        return response.data
      } catch (err) {
        this.error = err.response?.data?.message || 'Registration failed.'
        throw err
      } finally {
        this.loading = false
      }
    },

    async fetchCurrentUser() {
      if (!this.token) return null
      try {
        const response = await api.get('/auth/me')
        this.user = response.data.user
        localStorage.setItem('vms_user', JSON.stringify(this.user))
        return this.user
      } catch (err) {
        this.logout()
        return null
      }
    },

    async logout() {
      try {
        if (this.token) {
          await api.post('/auth/logout')
        }
      } catch (e) {
        // silent fail on network/expired token
      } finally {
        this.token = null
        this.user = null
        this.error = null
        localStorage.removeItem('vms_auth_token')
        localStorage.removeItem('vms_user')
      }
    },
  },
})
