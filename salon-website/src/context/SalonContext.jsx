import { createContext, useContext, useEffect, useMemo, useState } from 'react'
import { fetchSalonWebsite } from '../lib/api'
import { getSalonSlug } from '../lib/salonSlug'

const SalonContext = createContext(null)

function initialView() {
  if (typeof window === 'undefined') return 'site'
  return window.location.hash === '#book' ? 'booking' : 'site'
}

export function SalonProvider({ children }) {
  const slug = useMemo(() => getSalonSlug(), [])
  const [data, setData] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [view, setView] = useState(initialView)

  useEffect(() => {
    const onHash = () => setView(window.location.hash === '#book' ? 'booking' : 'site')
    window.addEventListener('hashchange', onHash)
    return () => window.removeEventListener('hashchange', onHash)
  }, [])

  const openBooking = () => {
    window.location.hash = 'book'
    setView('booking')
    window.scrollTo(0, 0)
  }

  const closeBooking = () => {
    const base = window.location.pathname + window.location.search
    window.history.replaceState(null, '', base)
    setView('site')
  }

  useEffect(() => {
    if (!slug) {
      setError('No salon specified in URL (expected /s/your-salon-slug)')
      setLoading(false)
      return
    }

    let cancelled = false
    setLoading(true)
    setError(null)

    fetchSalonWebsite(slug)
      .then((json) => {
        if (!cancelled) {
          setData(json)
          document.title = `${json.salon?.name || 'Salon'} — Book Online`
        }
      })
      .catch((e) => {
        if (!cancelled) setError(e.message || 'Unable to load salon')
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })

    return () => {
      cancelled = true
    }
  }, [slug])

  const value = useMemo(
    () => ({
      slug,
      salon: data?.salon ?? null,
      serviceCategories: data?.service_categories ?? [],
      staff: data?.staff ?? [],
      packages: data?.packages ?? [],
      reviews: data?.reviews ?? [],
      photos: data?.photos ?? [],
      loading,
      error,
      view,
      openBooking,
      closeBooking,
    }),
    [slug, data, loading, error, view],
  )

  return <SalonContext.Provider value={value}>{children}</SalonContext.Provider>
}

export function useSalon() {
  const ctx = useContext(SalonContext)
  if (!ctx) throw new Error('useSalon must be used within SalonProvider')
  return ctx
}
