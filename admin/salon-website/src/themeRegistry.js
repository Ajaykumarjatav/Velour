import { lazy } from 'react'

const loaders = {
  'glow-rose': () => import('../themes/glow-rose/MarketingSite.jsx'),
  beauty: () => import('../themes/beauty/MarketingSite.jsx'),
  nail: () => import('../themes/nail/MarketingSite.jsx'),
  tattoo: () => import('../themes/tattoo/MarketingSite.jsx'),
  mackup: () => import('../themes/mackup/MarketingSite.jsx'),
  'pet-grooming': () => import('../themes/pet-grooming/MarketingSite.jsx'),
  spa: () => import('../themes/spa/MarketingSite.jsx'),
}

export function lazyTheme(themeId) {
  const load = loaders[themeId] || loaders['glow-rose']
  return lazy(load)
}
