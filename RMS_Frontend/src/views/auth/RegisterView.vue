<template>
  <div class="min-h-screen bg-[#faf8f5] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-lg">
      <div class="flex justify-center mb-3">
        <img src="../../assets/TORA_Logo.png" alt="Tora Holding Company Logo" class="h-16 w-auto object-contain drop-shadow-sm" />
      </div>
      <h2 class="text-center text-3xl sm:text-4xl font-extrabold text-neutral-900 tracking-tight">
        Applicant Registration
      </h2>
      <p class="mt-2 text-center text-base text-[#7a6e5a] font-medium">
        Candidate Career Portal
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-lg px-4 sm:px-0">
      <div class="bg-white py-9 px-7 sm:px-10 shadow-xl shadow-amber-900/5 rounded-3xl border border-[#f0e9dc]">
        <div class="mb-6 border-b border-neutral-100 pb-4">
          <h3 class="text-xl font-bold text-neutral-900">Sign Up</h3>
        </div>

        <div v-if="errorMessage" class="mb-5 p-4 rounded-2xl bg-red-50 border border-red-200 text-sm text-red-700 flex items-start gap-3">
          <svg class="w-5 h-5 shrink-0 text-red-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="flex-1 font-medium">{{ errorMessage }}</div>
        </div>

        <form @submit.prevent="handleRegister" novalidate class="space-y-5">
          <div>
            <label class="block text-sm font-semibold tracking-wider text-[#7a6e5a] mb-1.5">
              Full Name *
            </label>
            <input
              v-model="form.name"
              type="text"
              placeholder="Enter full name"
              class="w-full px-4 py-3 rounded-2xl border border-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#db802d]/20 text-base bg-neutral-50/50"
              :class="errors.name ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="errors.name" class="text-xs text-red-600 mt-1.5 font-medium">{{ errors.name }}</p>
          </div>

          <div>
            <label class="block text-sm font-semibold tracking-wider text-[#7a6e5a] mb-1.5">
              Email Address *
            </label>
            <input
              v-model="form.email"
              type="email"
              placeholder="Enter email address"
              class="w-full px-4 py-3 rounded-2xl border border-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#db802d]/20 text-base bg-neutral-50/50"
              :class="errors.email ? 'border-red-400 bg-red-50/20' : ''"
            />
            <p v-if="errors.email" class="text-xs text-red-600 mt-1.5 font-medium">{{ errors.email }}</p>
          </div>

          <div>
            <label class="block text-sm font-semibold tracking-wider text-[#7a6e5a] mb-1.5">
              Password *
            </label>
            <div class="relative">
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Enter password"
                class="w-full pl-4 pr-12 py-3 rounded-2xl border border-neutral-200 text-base bg-neutral-50/50"
                :class="errors.password ? 'border-red-400 bg-red-50/20' : ''"
              />
              <button
                type="button"
                @click="showPassword = !showPassword"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-700 p-1 cursor-pointer transition-colors"
                title="Toggle password visibility"
              >
                <svg v-if="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                </svg>
              </button>
            </div>
            <p v-if="errors.password" class="text-xs text-red-600 mt-1.5 font-medium">{{ errors.password }}</p>
          </div>

          <div>
            <label class="block text-sm font-semibold tracking-wider text-[#7a6e5a] mb-1.5">
              Confirm Password *
            </label>
            <div class="relative">
              <input
                v-model="form.password_confirmation"
                :type="showConfirmPassword ? 'text' : 'password'"
                placeholder="Enter password confirmation"
                class="w-full pl-4 pr-12 py-3 rounded-2xl border border-neutral-200 text-base bg-neutral-50/50"
                :class="errors.password_confirmation ? 'border-red-400 bg-red-50/20' : ''"
              />
              <button
                type="button"
                @click="showConfirmPassword = !showConfirmPassword"
                class="absolute right-3.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-700 p-1 cursor-pointer transition-colors"
                title="Toggle confirm password visibility"
              >
                <svg v-if="!showConfirmPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                </svg>
              </button>
            </div>
            <p v-if="errors.password_confirmation" class="text-xs text-red-600 mt-1.5 font-medium">{{ errors.password_confirmation }}</p>

            <!-- Real-time Password Rules Checklist (Positioned AFTER Confirm Password) -->
            <div class="mt-4 p-4 rounded-2xl bg-[#faf8f5] border border-[#f0e9dc] text-xs space-y-1.5">
              <div class="font-bold text-[#7a6e5a] mb-1.5 text-xs tracking-wider">Password Must Include:</div>
              <div class="grid grid-cols-2 gap-2 text-neutral-600">
                <div class="flex items-center gap-1.5" :class="passRules.length ? 'text-emerald-700 font-semibold' : ''">
                  <span>{{ passRules.length ? '✓' : '○' }}</span> Min 8 Characters
                </div>
                <div class="flex items-center gap-1.5" :class="passRules.upper ? 'text-emerald-700 font-semibold' : ''">
                  <span>{{ passRules.upper ? '✓' : '○' }}</span> Uppercase Letter
                </div>
                <div class="flex items-center gap-1.5" :class="passRules.lower ? 'text-emerald-700 font-semibold' : ''">
                  <span>{{ passRules.lower ? '✓' : '○' }}</span> Lowercase Letter
                </div>
                <div class="flex items-center gap-1.5" :class="passRules.number ? 'text-emerald-700 font-semibold' : ''">
                  <span>{{ passRules.number ? '✓' : '○' }}</span> Numeric Digit
                </div>
                <div class="flex items-center gap-1.5 col-span-2" :class="passRules.symbol ? 'text-emerald-700 font-semibold' : ''">
                  <span>{{ passRules.symbol ? '✓' : '○' }}</span> Special Character (!@#$%^&*...)
                </div>
              </div>
            </div>
          </div>

          <div class="pt-2">
            <button
              type="submit"
              :disabled="loading"
              class="w-full flex justify-center items-center py-3 px-4 rounded-2xl text-base font-semibold text-white bg-[#db802d] hover:bg-[#c46f20] active:scale-[0.99] transition-all shadow-md shadow-orange-900/10 disabled:opacity-50 cursor-pointer"
            >
              <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span>{{ loading ? 'Creating Account...' : 'Create Account' }}</span>
            </button>
          </div>
        </form>

        <div class="mt-6 border-t border-neutral-100 pt-5 text-center">
          <p class="text-sm text-neutral-600">
            Already have an account?
            <router-link to="/login" class="font-semibold text-[#db802d] hover:underline ml-1">
              Sign In
            </router-link>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const showPassword = ref(false)
const showConfirmPassword = ref(false)
const loading = ref(false)
const errorMessage = ref('')
const errors = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
})

const passRules = computed(() => {
  const p = form.password || ''
  return {
    length: p.length >= 8,
    upper: /[A-Z]/.test(p),
    lower: /[a-z]/.test(p),
    number: /[0-9]/.test(p),
    symbol: /[^A-Za-z0-9]/.test(p),
  }
})

const validate = () => {
  errors.name = ''
  errors.email = ''
  errors.password = ''
  errors.password_confirmation = ''
  errorMessage.value = ''
  let valid = true

  if (!form.name.trim()) {
    errors.name = 'This field is required.'
    valid = false
  }

  if (!form.email.trim()) {
    errors.email = 'This field is required.'
    valid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Please enter a valid email address.'
    valid = false
  }

  if (!form.password) {
    errors.password = 'This field is required.'
    valid = false
  } else if (!passRules.value.length || !passRules.value.upper || !passRules.value.lower || !passRules.value.number || !passRules.value.symbol) {
    errors.password = 'Password does not meet the complexity requirements.'
    valid = false
  }

  if (!form.password_confirmation) {
    errors.password_confirmation = 'This field is required.'
    valid = false
  } else if (form.password !== form.password_confirmation) {
    errorMessage.value = 'Passwords do not match.'
    errors.password_confirmation = 'Passwords do not match.'
    valid = false
  }

  return valid
}

const handleRegister = async () => {
  if (!validate()) return

  loading.value = true
  errorMessage.value = ''
  try {
    await authStore.register(form)
    router.push('/profile')
  } catch (err) {
    if (err.response?.data?.errors) {
      const respErrors = err.response.data.errors
      if (respErrors.name) errors.name = respErrors.name[0]
      if (respErrors.email) errors.email = respErrors.email[0]
      if (respErrors.password) errors.password = respErrors.password[0]
      if (respErrors.password_confirmation) errors.password_confirmation = respErrors.password_confirmation[0]
      errorMessage.value = err.response.data.message || 'Validation error. Please review the highlighted fields.'
    } else {
      errorMessage.value = err.response?.data?.message || 'Registration failed. Please try again.'
    }
  } finally {
    loading.value = false
  }
}
</script>
