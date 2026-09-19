import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import { defineConfig } from 'vite'

// https://vite.dev/config/
export default defineConfig({
  plugins: [
    vue(),
    tailwindcss(),
  ],
  server: {
    port: 5173,
    host: true,
    headers: {
      'Content-Security-Policy': "default-src 'self'; connect-src 'self' http://localhost:8000 http://127.0.0.1:8000 ws://localhost:5173; img-src 'self' http://localhost:8000 http://127.0.0.1:8000 data: blob:; style-src 'self' 'unsafe-inline'; script-src 'self' 'unsafe-inline'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'",
      'X-Frame-Options': 'DENY',
      'X-Content-Type-Options': 'nosniff',
      'Referrer-Policy': 'strict-origin-when-cross-origin',
    },
    watch: {
      usePolling: true,
      interval: 1000,
    },
  },
  preview: {
    headers: {
      'Content-Security-Policy': "default-src 'self'; connect-src 'self' http://localhost:8000 http://127.0.0.1:8000; img-src 'self' http://localhost:8000 http://127.0.0.1:8000 data: blob:; style-src 'self' 'unsafe-inline'; script-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self'",
      'X-Frame-Options': 'DENY',
      'X-Content-Type-Options': 'nosniff',
      'Referrer-Policy': 'strict-origin-when-cross-origin',
    },
  },
})
