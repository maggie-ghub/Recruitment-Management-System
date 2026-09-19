<template>
  <div class="min-h-screen bg-[#f4f6f3] flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden">
    <div class="absolute -left-24 -top-24 h-72 w-72 rounded-full border-[32px] border-[#e9b949]/20"></div>
    <div class="absolute -right-20 bottom-0 h-80 w-80 rounded-full border-[40px] border-[#c96b3b]/10"></div>
    <div class="sm:mx-auto sm:w-full sm:max-w-lg">
      <!-- Brand Header -->
      <div class="flex justify-center mb-3">
        <img src="../../assets/TORA_Logo.png" alt="Tora Holding Company Logo" class="h-16 w-auto object-contain drop-shadow-sm" />
      </div>
      <h2 class="text-center text-3xl sm:text-4xl font-extrabold text-[#29332f] tracking-tight relative">
        Tora Holding Company
      </h2>
      <p class="mt-2 text-center text-base text-[#6d716d] font-medium relative">
        Vacancy & Recruitment Management Portal
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg px-4 sm:px-0">
      <div class="bg-white py-9 px-7 sm:px-10 shadow-[0_22px_55px_rgba(41,51,47,0.1)] rounded-[26px] border border-[#e4e9e4] relative">
        <div class="mb-6 border-b border-neutral-100 pb-4">
          <h3 class="text-xl font-bold text-[#29332f]">Sign In</h3>
        </div>

        <!-- Alert messages -->
        <div v-if="errorMessage" class="mb-5 p-4 rounded-2xl bg-red-50 border border-red-200 text-sm text-red-700 flex items-start gap-3">
          <svg class="w-5 h-5 shrink-0 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="flex-1 font-medium">{{ errorMessage }}</div>
        </div>

        <form @submit.prevent="handleLogin" novalidate class="space-y-5">
          <div>
            <label class="block text-sm font-semibold tracking-wider text-[#7a6e5a] mb-1.5">
              Email Address *
            </label>
            <input
              v-model="email"
              type="email"
              autocomplete="email"
              placeholder="Enter email address"
              class="w-full px-4 py-3 rounded-2xl border border-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#db802d]/20 text-base bg-neutral-50/50 transition-all"
              :class="errors.email ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="errors.email" class="text-xs text-red-600 mt-1.5 font-medium">{{ errors.email }}</p>
          </div>

          <div>
            <div class="flex justify-between items-center mb-1.5">
              <label class="block text-sm font-semibold tracking-wider text-[#7a6e5a]">
                Password *
              </label>
            </div>
            <div class="relative">
              <input
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                autocomplete="current-password"
                placeholder="Enter password"
                class="w-full pl-4 pr-12 py-3 rounded-2xl border border-neutral-200 text-base bg-neutral-50/50 transition-all"
                :class="errors.password ? 'border-red-400 bg-red-50/20' : ''"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-700 p-1 cursor-pointer transition-colors"
                title="Toggle password visibility"
              >
                <!-- Eye Open -->
                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <!-- Eye Slash -->
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                </svg>
              </button>
            </div>
            <p v-if="errors.password" class="text-xs text-red-600 mt-1.5 font-medium">{{ errors.password }}</p>
          </div>

          <div class="pt-2">
            <button
              type="submit"
              :disabled="loading"
              class="w-full flex justify-center items-center py-3 px-4 rounded-xl text-base font-semibold text-white bg-[#29332f] hover:bg-[#1e2723] active:scale-[0.99] transition-all shadow-md shadow-[#29332f]/15 disabled:opacity-50 cursor-pointer"
            >
              <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ loading ? 'Signing In...' : 'Sign In' }}</span>
            </button>
          </div>
        </form>

        <div class="mt-6 border-t border-neutral-100 pt-5 text-center">
          <p class="text-sm text-neutral-600">
            Applying for a position?
            <router-link to="/register" class="font-semibold text-[#db802d] hover:underline ml-1">
              Create An Applicant Account
            </router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const email = ref('')
const password = ref('')
const showPassword = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const errors = reactive({
  email: '',
  password: '',
})

const validate = () => {
  errors.email = ''
  errors.password = ''
  let valid = true

  if (!email.value) {
    errors.email = 'This field is required.'
    valid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
    errors.email = 'Please enter a valid email address.'
    valid = false
  }

  if (!password.value) {
    errors.password = 'This field is required.'
    valid = false
  }

  return valid
}

const handleLogin = async () => {
  if (!validate()) return

  loading.value = true
  errorMessage.value = ''
  try {
    await authStore.login({
      email: email.value,
      password: password.value,
    })
    // Redirect to originally intended page (set by router guard on session expiry)
    const redirectTo = route.query.redirect && typeof route.query.redirect === 'string'
      ? route.query.redirect
      : '/'
    router.push(redirectTo)
  } catch (err) {
    if (err.response?.status === 423) {
      errorMessage.value = err.response.data.message || 'Account locked due to consecutive failed attempts. Please try again later.'
    } else if (err.response?.data?.errors) {
      const respErrors = err.response.data.errors
      if (respErrors.email) errors.email = respErrors.email[0]
      if (respErrors.password) errors.password = respErrors.password[0]
      errorMessage.value = err.response.data.message || 'Invalid credentials provided.'
    } else {
      errorMessage.value = err.response?.data?.message || 'Invalid email or password.'
    }
  } finally {
    loading.value = false
  }
}
</script>
