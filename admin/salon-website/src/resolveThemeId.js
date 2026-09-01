const VALID = new Set(['glow-rose', 'beauty', 'nail', 'tattoo', 'mackup', 'pet-grooming', 'spa'])

const ALIASES = {
  mockup: 'mackup',
}

function normalizeTheme(id) {
  if (!id) return null
  if (VALID.has(id)) return id
  return ALIASES[id] ?? null
}

export function resolveThemeId(apiTheme) {
  const buildTheme = import.meta.env.VITE_BUILD_THEME
  const fromBuild = normalizeTheme(buildTheme)
  if (fromBuild) {
    return fromBuild
  }

  if (typeof window !== 'undefined') {
    const override = new URLSearchParams(window.location.search).get('theme')
    const fromQuery = normalizeTheme(override)
    if (fromQuery) {
      return fromQuery
    }
  }

  const fromApi = normalizeTheme(apiTheme)
  if (fromApi) {
    return fromApi
  }

  return 'glow-rose'
}
