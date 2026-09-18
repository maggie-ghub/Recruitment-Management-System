<template>
  <div class="fixed top-5 right-5 z-[9999] flex flex-col gap-2.5 max-w-sm w-full pointer-events-none">
    <transition-group
      enter-active-class="transform ease-out duration-300 transition"
      enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
      enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
      leave-active-class="transition ease-in duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-for="toast in store.notifications"
        :key="toast.id"
        class="pointer-events-auto flex items-start gap-3 p-4 rounded-2xl shadow-xl border backdrop-blur-md bg-white/95"
        :class="getToastBorder(toast.type)"
      >
        <!-- Icon -->
        <div class="shrink-0 mt-0.5">
          <span
            class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold"
            :class="getIconClass(toast.type)"
          >
            {{ getIconSymbol(toast.type) }}
          </span>
        </div>

        <!-- Text -->
        <div class="flex-1 min-w-0">
          <div v-if="toast.title" class="text-xs font-bold text-neutral-900 tracking-tight">
            {{ toast.title }}
          </div>
          <div class="text-xs text-neutral-600 mt-0.5 leading-relaxed break-words">
            {{ toast.message }}
          </div>
        </div>

        <!-- Close Button -->
        <button
          @click="store.remove(toast.id)"
          class="shrink-0 text-neutral-400 hover:text-neutral-700 transition-colors p-1"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { useNotificationStore } from '../stores/notification'

const store = useNotificationStore()

const getToastBorder = (type) => {
  switch (type) {
    case 'success': return 'border-emerald-200 shadow-emerald-950/5'
    case 'error': return 'border-rose-200 shadow-rose-950/5'
    case 'warning': return 'border-amber-200 shadow-amber-950/5'
    default: return 'border-blue-200 shadow-neutral-950/5'
  }
}

const getIconClass = (type) => {
  switch (type) {
    case 'success': return 'bg-emerald-100 text-emerald-700'
    case 'error': return 'bg-rose-100 text-rose-700'
    case 'warning': return 'bg-amber-100 text-amber-700'
    default: return 'bg-blue-100 text-blue-700'
  }
}

const getIconSymbol = (type) => {
  switch (type) {
    case 'success': return '✓'
    case 'error': return '✕'
    case 'warning': return '!'
    default: return 'i'
  }
}
</script>
