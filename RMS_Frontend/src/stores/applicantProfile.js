import { defineStore } from 'pinia'
import api from '../api/client'

const API_BASE = (import.meta.env.VITE_API_URL || 'http://localhost:8000/api').replace(/\/$/, '')

// ─── Helper: fetch a private document using native fetch() (not Axios/XHR) ───
// IDM Advanced Integration only patches XMLHttpRequest. Native fetch() bypasses
// IDM interception entirely - this is why we use it for preview (view) requests.
async function fetchDocumentBlob(endpoint) {
  const token = localStorage.getItem('vms_auth_token')

  const response = await fetch(`${API_BASE}/${endpoint}`, {
    method: 'GET',
    headers: {
      Authorization: `Bearer ${token}`,
      Accept: '*/*',
    },
  })

  if (!response.ok) {
    let message = `Request failed with status ${response.status}.`
    try {
      const text = await response.text()
      const json = JSON.parse(text)
      if (json.message) message = json.message
    } catch {
      // ignore JSON parse failure
    }
    throw new Error(message)
  }

  const contentType = response.headers.get('content-type') || 'application/octet-stream'
  const arrayBuffer = await response.arrayBuffer()
  return { blob: new Blob([arrayBuffer], { type: contentType }), contentType }
}

export const useApplicantProfileStore = defineStore('applicantProfile', {
  state: () => ({
    profile: null,
    educations: [],
    workExperiences: [],
    skills: [],
    documents: [],
    primaryCv: null,
    completenessPercentage: 0,
    missingItems: [],
    isReadyToApply: false,
    loading: false,
    uploadingCv: false,
    uploadingDoc: false,
  }),

  actions: {
    async fetchProfile() {
      this.loading = true
      try {
        const response = await api.get('/applicant/profile')
        const data = response.data
        this.profile = data.profile
        this.educations = data.educations
        this.workExperiences = data.work_experiences
        this.skills = data.skills
        this.documents = data.documents
        this.primaryCv = data.primary_cv
        this.completenessPercentage = data.completeness_percentage
        this.missingItems = data.missing_items
        this.isReadyToApply = data.is_ready_to_apply
        return data
      } finally {
        this.loading = false
      }
    },

    async updatePersonalInfo(formData) {
      const response = await api.post('/applicant/profile', formData)
      await this.fetchProfile()
      return response.data
    },

    async addEducation(eduData) {
      const response = await api.post('/applicant/education', eduData)
      await this.fetchProfile()
      return response.data
    },

    async updateEducation(id, eduData) {
      const response = await api.put(`/applicant/education/${id}`, eduData)
      await this.fetchProfile()
      return response.data
    },

    async deleteEducation(id) {
      await api.delete(`/applicant/education/${id}`)
      await this.fetchProfile()
    },

    async addWorkExperience(expData) {
      const response = await api.post('/applicant/experience', expData)
      await this.fetchProfile()
      return response.data
    },

    async updateWorkExperience(id, expData) {
      const response = await api.put(`/applicant/experience/${id}`, expData)
      await this.fetchProfile()
      return response.data
    },

    async deleteWorkExperience(id) {
      await api.delete(`/applicant/experience/${id}`)
      await this.fetchProfile()
    },

    async addSkill(skillData) {
      const response = await api.post('/applicant/skills', skillData)
      await this.fetchProfile()
      return response.data
    },

    async deleteSkill(id) {
      await api.delete(`/applicant/skills/${id}`)
      await this.fetchProfile()
    },

    async uploadCv(file) {
      this.uploadingCv = true
      try {
        const formData = new FormData()
        formData.append('cv', file)
        const response = await api.post('/applicant/documents/cv', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })
        await this.fetchProfile()
        return response.data
      } finally {
        this.uploadingCv = false
      }
    },

    async uploadSupportingDocument(type, file) {
      this.uploadingDoc = true
      try {
        const formData = new FormData()
        formData.append('type', type)
        formData.append('document', file)
        const response = await api.post('/applicant/documents', formData, {
          headers: { 'Content-Type': 'multipart/form-data' },
        })
        await this.fetchProfile()
        return response.data
      } finally {
        this.uploadingDoc = false
      }
    },

    async deleteDocument(id) {
      await api.delete(`/applicant/documents/${id}`)
      await this.fetchProfile()
    },

    // Token-based download: request a short-lived one-time token then open the
    // stream URL directly in the browser. IDM intercepts this as a normal browser
    // download (its own HTTP request) - no XHR/fetch needed, so it works for PDFs.
    async downloadDocument(doc) {
      // Step 1: get a 90-second one-time token via authenticated Axios call
      const { data } = await api.post(`/applicant/documents/${doc.id}/request-token`)
      const token = data.token

      // Step 2: open the public stream URL directly in the browser.
      // The browser (or IDM) makes its own plain HTTP GET - no auth header needed.
      const streamUrl = `${API_BASE}/documents/stream/${token}`
      const link = document.createElement('a')
      link.href = streamUrl
      link.setAttribute('download', doc.original_filename)
      link.target = '_blank'
      document.body.appendChild(link)
      link.click()
      link.remove()
    },

    // Uses native fetch() to bypass IDM Advanced Integration XHR interception
    async getDocumentBlob(doc) {
      const { blob } = await fetchDocumentBlob(`applicant/documents/${doc.id}/view`)
      return blob
    },

    async getDocumentBlobUrl(doc) {
      const blob = await this.getDocumentBlob(doc)
      return window.URL.createObjectURL(blob)
    },
  },
})
