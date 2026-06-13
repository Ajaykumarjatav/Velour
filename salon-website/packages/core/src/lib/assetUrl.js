/**
 * Theme static assets:
 * - Dev (single server): /themes/{themeId}/assets/...
 * - Prod build (per theme): /assets/... under that theme's Vite base
 */
let currentThemeId = 'glow-rose'

export function setAssetTheme(themeId) {
  currentThemeId = themeId || 'glow-rose'
}

export function assetUrl(path) {
  if (!path) return path
  if (/^https?:\/\//i.test(path) || path.startsWith('data:')) {
    return path
  }

  const base = import.meta.env.BASE_URL || '/'
  const normalized = String(path).replace(/^\//, '')
  const buildTheme = import.meta.env.VITE_BUILD_THEME

  const withThemePrefix = buildTheme
    ? normalized
    : `themes/${currentThemeId}/${normalized}`

  const encoded = withThemePrefix
    .split('/')
    .map((segment) => encodeURIComponent(segment))
    .join('/')

  return `${base}${encoded}`
}
