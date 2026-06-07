/**
 * Public files live in salon-website/public/ (copied to public/website/ on build).
 * Use this instead of hard-coded "/assets/..." so paths work in dev (base /)
 * and production (e.g. base /vellor/public/website/).
 */
export function assetUrl(path) {
  if (!path) return path
  if (/^https?:\/\//i.test(path) || path.startsWith('data:')) {
    return path
  }

  const base = import.meta.env.BASE_URL || '/'
  const normalized = String(path).replace(/^\//, '')
  const encoded = normalized
    .split('/')
    .map((segment) => encodeURIComponent(segment))
    .join('/')

  return `${base}${encoded}`
}
