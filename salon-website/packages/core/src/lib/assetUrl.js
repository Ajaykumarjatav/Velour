/**
 * Theme static assets:
 * - Dev (single server): /themes/{themeId}/assets/...
 * - Prod (Laravel /s/{slug}): meta storefront-asset-base + assets/...
 * - Prod (Vite build): BASE_URL + assets/...
 */
let currentThemeId = 'glow-rose'

export function setAssetTheme(themeId) {
  currentThemeId = themeId || 'glow-rose'
}

function runtimeAssetBase() {
  if (typeof document === 'undefined') return null
  const content = document.querySelector('meta[name="storefront-asset-base"]')?.getAttribute('content')?.trim()
  if (!content) return null
  return content.endsWith('/') ? content : `${content}/`
}

function resolveAssetThemeId() {
  const buildTheme = import.meta.env.VITE_BUILD_THEME
  if (buildTheme) return buildTheme

  if (typeof window !== 'undefined') {
    const override = new URLSearchParams(window.location.search).get('theme')
    if (override === 'mockup') return 'mackup'
    if (override) return override
  }

  return currentThemeId || 'glow-rose'
}

export function assetUrl(path) {
  if (!path) return path
  if (/^https?:\/\//i.test(path) || path.startsWith('data:')) {
    return path
  }

  const normalized = String(path).replace(/^\//, '')
  const encoded = normalized
    .split('/')
    .map((segment) => encodeURIComponent(segment))
    .join('/')

  const metaBase = runtimeAssetBase()
  if (metaBase) {
    return `${metaBase}${encoded}`
  }

  const base = import.meta.env.BASE_URL || '/'
  const buildTheme = import.meta.env.VITE_BUILD_THEME

  if (buildTheme) {
    return `${base}${encoded}`
  }

  const themeId = resolveAssetThemeId()
  return `${base}themes/${themeId}/${encoded}`
}
