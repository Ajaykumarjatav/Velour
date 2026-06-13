import { getApiBase } from './apiBase.js'

/**
 * Normalize API media URLs (storage paths, relative URLs) for the current storefront origin.
 */
export function resolveMediaUrl(url) {
  if (!url || typeof url !== 'string') return null

  const trimmed = url.trim()
  if (!trimmed) return null

  if (/^https?:\/\//i.test(trimmed) || trimmed.startsWith('data:')) {
    return trimmed
  }

  if (trimmed.startsWith('//')) {
    if (typeof window === 'undefined') return trimmed
    return `${window.location.protocol}${trimmed}`
  }

  const base = getApiBase() || (import.meta.env.VITE_API_PROXY_TARGET || '').replace(/\/$/, '')
  if (!base) return trimmed

  return `${base}/${trimmed.replace(/^\//, '')}`
}
