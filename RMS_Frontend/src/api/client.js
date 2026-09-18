import axios from 'axios'

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: false,
})

// Request interceptor: attach auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('vms_auth_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => Promise.reject(error)
)

// Response interceptor: handle 401 and blob error responses
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response) {
      // If the response data is a Blob (binary request that errored), parse it back to JSON
      if (error.response.data instanceof Blob) {
        try {
          const text = await error.response.data.text()
          const json = JSON.parse(text)
          // Reconstruct the error with parsed data so callers can read error.response.data.message
          error.response.data = json
        } catch {
          // If parsing fails, leave the blob as-is
        }
      }

      if (error.response.status === 401) {
        localStorage.removeItem('vms_auth_token')
        localStorage.removeItem('vms_user')
        if (window.location.pathname !== '/login') {
          window.location.href = '/login'
        }
      }
    }
    return Promise.reject(error)
  }
)

export default api
