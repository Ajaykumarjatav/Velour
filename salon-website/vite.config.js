import { defineConfig, loadEnv } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  // Dev (port 5173): base must be "/" so /s/ak-salon works.
  // Production build: Laravel serves assets under /vellor/public/website/
  const base =
    mode === 'production'
      ? (env.VITE_STOREFRONT_BASE || '/website/')
      : '/'
  const apiTarget = env.VITE_API_PROXY_TARGET || 'http://localhost/vellor/public'

  return {
    base,
    plugins: [react(), tailwindcss()],
    build: {
      outDir: '../public/website',
      emptyOutDir: true,
    },
    server: {
      proxy: {
        '/api': {
          target: apiTarget,
          changeOrigin: true,
        },
      },
    },
  }
})
