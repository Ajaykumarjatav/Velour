import { useSalon } from '../context/SalonContext'

export default function SalonSiteShell({ children }) {
  const { loading, error } = useSalon()

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center bg-black text-white">
        <p className="text-sm text-white/70 animate-pulse">Loading your salon…</p>
      </div>
    )
  }

  if (error) {
    return (
      <div className="min-h-screen flex flex-col items-center justify-center bg-black text-white px-6 text-center">
        <p className="text-lg font-semibold mb-2">Salon unavailable</p>
        <p className="text-sm text-white/60 max-w-md">{error}</p>
      </div>
    )
  }

  return children
}
