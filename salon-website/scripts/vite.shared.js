import path from 'path'
import { fileURLToPath } from 'url'
import { defineConfig, loadEnv } from 'vite'
import react from '@vitejs/plugin-react'
import tailwindcss from '@tailwindcss/vite'

const __dirname = path.dirname(fileURLToPath(import.meta.url))

export function createThemeConfig(themeId, devPort) {
  return defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '')
    const base =
      mode === 'production'
        ? (env.VITE_STOREFRONT_BASE || `/vellor/admin/website/${themeId}/`)
        : '/'
    const apiTarget = env.VITE_API_PROXY_TARGET || 'http://localhost/vellor/admin'

    return {
      base,
      plugins: [react(), tailwindcss()],
      resolve: {
        alias: {
          '@salon/core': path.resolve(__dirname, '../packages/core/src'),
        },
      },
      build: {
        outDir: path.resolve(__dirname, `../../public/website/${themeId}`),
        emptyOutDir: true,
      },
      server: {
        port: devPort,
        strictPort: true,
        proxy: {
          '/api': {
            target: apiTarget,
            changeOrigin: true,
          },
        },
      },
    }
  })
}
