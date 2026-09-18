<template>
  <div class="min-h-screen bg-[#faf8f5] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <!-- Brand Header -->
      <div class="flex justify-center mb-3">
        <img src="../../assets/TORA_Logo.png" alt="Tora Holding Company Logo" class="h-16 w-auto object-contain drop-shadow-sm" />
      </div>
      <h2 class="text-center text-2xl sm:text-3xl font-extrabold text-neutral-900 tracking-tight">
        Tora Holding Company
      </h2>
      <p class="mt-1.5 text-center text-sm text-[#7a6e5a]">
        Vacancy & Recruitment Management Portal
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
      <div class="bg-white py-8 px-6 shadow-xl shadow-amber-900/5 rounded-3xl border border-[#f0e9dc] sm:px-10">
        <div class="mb-6 border-b border-neutral-100 pb-4">
          <h3 class="text-lg font-bold text-neutral-800">Sign in to your account</h3>
          <p class="text-xs text-neutral-500 mt-1">Access applicant tracking or company administration</p>
        </div>

        <!-- Alert messages -->
        <div v-if="errorMessage" class="mb-5 p-3.5 rounded-xl bg-red-50 border border-red-200 text-sm text-red-700 flex items-start gap-2.5">
          <svg class="w-5 h-5 shrink-0 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="flex-1">{{ errorMessage }}</div>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a] mb-1.5">
              Email Address
            </label>
            <input
              v-model="email"
              type="email"
              required
              autocomplete="email"
              placeholder="name@company.com"
              class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#ebb630] focus:border-transparent text-sm bg-neutral-50/50 transition-all"
            />
          </div>

          <div>
            <div class="flex justify-between items-center mb-1.5">
              <label class="block text-xs font-semibold uppercase tracking-wider text-[#7a6e5a]">
                Password
              </label>
            </div>
            <input
              v-model="password"
              type="password"
              required
              autocomplete="current-password"
              placeholder="••••••••"
              class="w-full px-3.5 py-2.5 rounded-xl border border-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#ebb630] focus:border-transparent text-sm bg-neutral-50/50 transition-all"
            />
          </div>

          <div class="pt-2">
            <button
              type="submit"
              :disabled="loading"
              class="w-full flex justify-center items-center py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] active:scale-[0.99] transition-all shadow-md shadow-orange-900/10 disabled:opacity-50 cursor-pointer"
            >
              <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ loading ? 'Signing in...' : 'Sign In' }}</span>
            </button>
          </div>
        </form>

        <div class="mt-6 border-t border-neutral-100 pt-5 text-center">
          <p class="text-xs text-neutral-600">
            Applying for a position?
            <router-link to="/register" class="font-semibold text-[#db802d] hover:underline ml-1">
              Create an Applicant Account
            </router-link>
          </p>
        </div>

        <!-- Quick Demo Credentials Box -->
        <div class="mt-6 p-3.5 rounded-xl bg-amber-50/70 border border-amber-200 text-xs text-[#7a6e5a]">
          <div class="font-bold text-[#8f5a11] mb-1">Demo Credentials:</div>
          <div class="grid grid-cols-2 gap-1 text-[11px]">
            <div><strong>Admin:</strong> admin@tora.com</div>
            <div><strong>HR Manager:</strong> hrmanager@tora.com</div>
            <div><strong>Recruiter:</strong> recruiter@tora.com</div>
            <div><strong>Applicant:</strong> applicant@gmail.com</div>
          </div>
          <div class="mt-1 text-[10px] text-neutral-500">Password for all seeded accounts: <code>password123</code></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const loading = ref(false)
const errorMessage = ref('')

const handleLogin = async () => {
  loading.value = true
  errorMessage.value = ''
  try {
    await authStore.login({
      email: email.value,
      password: password.value,
    })
    router.push('/')
  } catch (err) {
    if (err.response?.status === 423) {
      errorMessage.value = err.response.data.message || 'Account locked due to consecutive failed attempts. Please try again later.'
    } else {
      errorMessage.value = err.response?.data?.message || 'Invalid email or password.'
    }
  } finally {
    loading.value = false
  }
}
</script>
