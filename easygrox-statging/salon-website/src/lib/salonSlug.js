/** Resolve salon slug from /s/{slug} or ?slug= */
export function getSalonSlug() {
  const params = new URLSearchParams(window.location.search)
  const fromQuery = params.get('slug')
  if (fromQuery) return fromQuery

  const parts = window.location.pathname.split('/').filter(Boolean)
  const idx = parts.indexOf('s')
  if (idx >= 0 && parts[idx + 1]) {
    return parts[idx + 1]
  }

  return import.meta.env.VITE_DEFAULT_SALON_SLUG || ''
}
