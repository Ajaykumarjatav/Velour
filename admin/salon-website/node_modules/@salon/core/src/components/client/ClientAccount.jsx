import { useCallback, useEffect, useState } from 'react'
import { useClientAuth } from '../../context/ClientAuthContext'
import { useSalon } from '../../context/SalonContext'
import { fetchClientAppointments } from '../../lib/clientApi'
import AppointmentDetail from './AppointmentDetail'
import ClientProfile from './ClientProfile'
import PortalShell, { StatusBadge } from './PortalShell'

const TABS = [
  { id: 'upcoming', label: 'Upcoming' },
  { id: 'completed', label: 'Completed' },
  { id: 'cancelled', label: 'Cancelled' },
  { id: 'profile', label: 'Profile' },
]

export default function ClientAccount() {
  const { slug, accountRef, openAppointmentDetail, closePortal, openLogin } = useSalon()
  const { token, client, logout, isAuthenticated, loading: authLoading, setReturnTo } = useClientAuth()
  const [tab, setTab] = useState('upcoming')
  const [search, setSearch] = useState('')
  const [appointments, setAppointments] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')

  useEffect(() => {
    if (authLoading) return
    if (!isAuthenticated) {
      setReturnTo('account')
      openLogin()
    }
  }, [authLoading, isAuthenticated, openLogin, setReturnTo])

  const loadAppointments = useCallback(async () => {
    if (!token || tab === 'profile') return
    setLoading(true)
    setError('')
    try {
      const data = await fetchClientAppointments(slug, token, { status: tab, search })
      setAppointments(data.appointments?.data ?? data.appointments ?? [])
    } catch (e) {
      setError(e.message || 'Could not load appointments')
      setAppointments([])
    } finally {
      setLoading(false)
    }
  }, [slug, token, tab, search])

  useEffect(() => {
    if (tab !== 'profile') loadAppointments()
  }, [loadAppointments, tab])

  if (authLoading || !isAuthenticated) {
    return (
      <PortalShell title="My account" onBack={closePortal}>
        <p className="text-white/60 text-sm text-center">Loading your account…</p>
      </PortalShell>
    )
  }

  if (accountRef) {
    return (
      <AppointmentDetail
        refCode={accountRef}
        onBack={() => openAppointmentDetail(null)}
        onUpdated={loadAppointments}
      />
    )
  }

  return (
    <PortalShell
      title="My account"
      onBack={closePortal}
      backLabel="← Back to site"
    >
      <div className="flex items-center justify-between mb-6">
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-full bg-primary/30 flex items-center justify-center text-sm font-bold">
            {client?.avatar_url ? (
              <img src={client.avatar_url} alt="" className="w-full h-full rounded-full object-cover" />
            ) : (
              (client?.first_name?.[0] || '?').toUpperCase()
            )}
          </div>
          <div>
            <p className="font-semibold">{client?.full_name}</p>
            <p className="text-xs text-white/50">{client?.email}</p>
          </div>
        </div>
        <button type="button" onClick={logout} className="text-xs text-white/50 hover:text-white">
          Logout
        </button>
      </div>

      <div className="flex gap-1 overflow-x-auto pb-2 mb-4">
        {TABS.map((t) => (
          <button
            key={t.id}
            type="button"
            onClick={() => setTab(t.id)}
            className={`text-xs uppercase tracking-wide px-3 py-2 rounded-full whitespace-nowrap transition-colors ${
              tab === t.id ? 'bg-primary text-white' : 'bg-white/5 text-white/50 hover:text-white'
            }`}
          >
            {t.label}
          </button>
        ))}
      </div>

      {tab === 'profile' ? (
        <ClientProfile />
      ) : (
        <>
          <input
            type="search"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
            onKeyDown={(e) => e.key === 'Enter' && loadAppointments()}
            placeholder="Search by ref or service…"
            className="w-full bg-[#1a1f2e] border border-white/10 rounded-xl px-4 py-2.5 text-sm text-white mb-4 placeholder:text-white/30"
          />

          {loading ? (
            <div className="space-y-3">
              {[1, 2, 3].map((i) => (
                <div key={i} className="h-24 bg-white/5 rounded-2xl animate-pulse" />
              ))}
            </div>
          ) : error ? (
            <p className="text-sm text-red-400">{error}</p>
          ) : appointments.length === 0 ? (
            <p className="text-sm text-white/50 text-center py-8">No {tab} appointments.</p>
          ) : (
            <div className="space-y-3">
              {appointments.map((apt) => (
                <button
                  key={apt.reference}
                  type="button"
                  onClick={() => openAppointmentDetail(apt.reference)}
                  className="w-full text-left bg-white/5 border border-white/10 rounded-2xl p-4 hover:border-primary/40 transition-colors"
                >
                  <div className="flex items-start justify-between gap-2 mb-2">
                    <span className="font-mono text-xs text-white/50">{apt.reference}</span>
                    <StatusBadge status={apt.status} />
                  </div>
                  <p className="font-semibold text-sm mb-1">
                    {apt.services?.map((s) => s.name).join(', ') || 'Appointment'}
                  </p>
                  <p className="text-xs text-white/60">
                    {apt.display?.date_long || apt.display?.date} · {apt.display?.time}
                  </p>
                  {apt.staff?.full_name ? (
                    <p className="text-xs text-white/50 mt-1">With {apt.staff.full_name}</p>
                  ) : null}
                </button>
              ))}
            </div>
          )}
        </>
      )}
    </PortalShell>
  )
}
