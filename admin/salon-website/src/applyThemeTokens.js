import { themeTokens } from './themeTokens'

export function applyThemeTokens(themeId) {
  const tokens = themeTokens[themeId] || themeTokens['glow-rose']
  const root = document.documentElement

  root.style.setProperty('--color-primary', tokens.primary)
  root.style.setProperty('--color-primary-dark', tokens.primaryDark)
  root.style.setProperty('--color-deep-maroon', tokens.deepMaroon)
  root.style.setProperty('--color-burgundy', tokens.deepMaroon)
  root.style.setProperty('--color-salmon', tokens.salmon)
  root.style.setProperty('--color-icon-circle', tokens.iconCircle)
}
