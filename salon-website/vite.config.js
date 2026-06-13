import path from 'path'
import { fileURLToPath } from 'url'
import { defineConfig, loadEnv } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const buildTheme = env.VITE_BUILD_THEME || 'glow-rose'
  const isProdBuild = mode === 'production' && env.VITE_BUILD_THEME

  const base = mode === 'production'
    ? (env.VITE_STOREFRONT_BASE || `/vellor/admin/website/${buildTheme}/`)
    : '/'

  const apiTarget = env.VITE_API_PROXY_TARGET || 'http://localhost/vellor/admin'

  return {
    base,
    plugins: [react(), tailwindcss()],
    resolve: {
      alias: {
        '@salon/core': path.resolve(__dirname, 'packages/core/src'),
      },
    },
    build: {
      outDir: path.resolve(__dirname, `../public/website/${buildTheme}`),
      emptyOutDir: true,
    },
    publicDir: isProdBuild
      ? path.resolve(__dirname, `themes/${buildTheme}/public`)
      : path.resolve(__dirname, 'public'),
    server: {
      port: 5173,
      strictPort: true,
      proxy: {
        '/api': {
          target: apiTarget,
          changeOrigin: true,
        },
        '/storage': {
          target: apiTarget,
          changeOrigin: true,
        },
      },
    },
  }
})
