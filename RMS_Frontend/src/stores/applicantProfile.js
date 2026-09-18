import { defineStore } from 'pinia'
import api from '../api/client'

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

    async deleteEducation(id) {
      await api.delete(`/applicant/education/${id}`)
      await this.fetchProfile()
    },

    async addWorkExperience(expData) {
      const response = await api.post('/applicant/experience', expData)
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

    async downloadDocument(doc) {
      try {
        const response = await api.get(`/applicant/documents/${doc.id}/download`, {
          responseType: 'blob',
        })
        const blobUrl = window.URL.createObjectURL(new Blob([response.data]))
        const link = document.createElement('a')
        link.href = blobUrl
        link.setAttribute('download', doc.original_filename)
        document.body.appendChild(link)
        link.click()
        link.remove()
        window.URL.revokeObjectURL(blobUrl)
      } catch (err) {
        alert('Failed to download document.')
      }
    },
  },
})
