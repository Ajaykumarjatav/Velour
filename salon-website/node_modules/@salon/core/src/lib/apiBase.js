/**
 * Laravel API root (no trailing slash).
 * - Dev (Vite :5173): empty → /api is proxied to Laravel in vite.config.js
 * - Production on /vellor/public/s/{slug}: derived from URL path
 * - Override anytime with VITE_API_BASE in salon-website/.env
 */
function apiBaseFromMeta() {
  if (typeof document === 'undefined') return ''
  const content = document.querySelector('meta[name="api-base"]')?.getAttribute('content')?.trim()
  return content ? content.replace(/\/$/, '') : ''
}

export function getApiBase() {
  let fromEnv = (import.meta.env.VITE_API_BASE || '').replace(/\/$/, '')

  if (typeof window === 'undefined') {
    return fromEnv
  }

  const host = window.location.hostname
  const onLocalhost = host === 'localhost' || host === '127.0.0.1'

  // Build was made with localhost API but app runs on live — ignore baked localhost
  if (fromEnv && /localhost|127\.0\.0\.1/i.test(fromEnv) && !onLocalhost) {
    fromEnv = ''
  }

  if (fromEnv) {
    return fromEnv
  }

  if (import.meta.env.DEV) {
    return ''
  }

  const fromMeta = apiBaseFromMeta()
  if (fromMeta) {
    return fromMeta
  }

  const m = window.location.pathname.match(/^(.*)\/s\/[^/]+/)
  if (m?.[1]) {
    return window.location.origin + m[1]
  }

  return ''
}
