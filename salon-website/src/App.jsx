import { Suspense, useEffect, useMemo } from 'react'
import { SalonProvider, useSalon } from '@salon/core/context/SalonContext'
import SalonSiteShell from '@salon/core/components/SalonSiteShell'
import BookingFlow from '@salon/core/components/BookingFlow'
import { setAssetTheme } from '@salon/core/lib/assetUrl'
import { resolveThemeId } from './resolveThemeId'
import { lazyTheme } from './themeRegistry'
import { applyThemeTokens } from './applyThemeTokens'

function SalonApp() {
  const { view, salon } = useSalon()
  const themeId = useMemo(
    () => resolveThemeId(salon?.website_theme),
    [salon?.website_theme],
  )
  const MarketingSite = useMemo(() => lazyTheme(themeId), [themeId])

  useEffect(() => {
    setAssetTheme(themeId)
    applyThemeTokens(themeId)
  }, [themeId])

  if (view === 'booking') {
    return (
      <SalonSiteShell>
        <BookingFlow />
      </SalonSiteShell>
    )
  }

  return (
    <SalonSiteShell>
      <Suspense
        fallback={
          <div className="min-h-screen flex items-center justify-center bg-white text-gray-500 text-sm">
            Loading theme…
          </div>
        }
      >
        <MarketingSite />
      </Suspense>
    </SalonSiteShell>
  )
}

export default function App() {
  return (
    <SalonProvider>
      <SalonApp />
    </SalonProvider>
  )
}
