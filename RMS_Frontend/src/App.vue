<template>
  <ToastContainer />
  <router-view />
</template>

<script setup>
import { onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'
import ToastContainer from './components/ToastContainer.vue'

const router = useRouter()
const authStore = useAuthStore()

onMounted(() => {
  /**
   * bfcache (Back-Forward Cache) Protection
   * When the browser restores a tab from its memory snapshot it does NOT make
   * a network request, so our router guard never runs. The `pageshow` event
   * fires after every restore; persisted=true means it came from bfcache.
   * If the token is now missing or revoked, we redirect to login immediately.
   */
  window.addEventListener('pageshow', async (event) => {
    if (event.persisted) {
      // Page was restored from bfcache — verify session is still valid
      if (!authStore.token) {
        router.push('/login')
        return
      }
      try {
        await authStore.fetchCurrentUser()
      } catch {
        // fetchCurrentUser calls logout() on 401 which clears state;
        // the 401 interceptor in client.js will redirect to /login
      }
    }
  })
})
</script>
