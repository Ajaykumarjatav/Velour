import { useCallback, useEffect, useMemo, useState } from 'react'
import { useSalon } from '../context/SalonContext'
import {
  confirmBooking,
  fetchBookServices,
  fetchBookSlots,
  fetchBookStaff,
  holdSlot,
} from '../lib/bookingApi'

const STEPS = ['Services', 'Stylist', 'Date & time', 'Your details', 'Confirm']

function todayYmd() {
  const d = new Date()
  return d.toISOString().slice(0, 10)
}

function maxDateYmd() {
  const d = new Date()
  d.setDate(d.getDate() + 60)
  return d.toISOString().slice(0, 10)
}

function formatDate(dateStr) {
  if (!dateStr) return ''
  try {
    return new Date(`${dateStr}T00:00:00`).toLocaleDateString('en-GB', {
      weekday: 'long',
      day: 'numeric',
      month: 'long',
      year: 'numeric',
    })
  } catch {
    return dateStr
  }
}

export default function BookingFlow() {
  const { slug, salon, closeBooking } = useSalon()
  const currency = salon?.currency_symbol ?? '£'

  const [step, setStep] = useState(0)
  const [loading, setLoading] = useState(true)
  const [globalError, setGlobalError] = useState('')
  const [allServices, setAllServices] = useState([])
  const [staffList, setStaffList] = useState([])
  const [staffLoading, setStaffLoading] = useState(false)
  const [slotsLoading, setSlotsLoading] = useState(false)
  const [slots, setSlots] = useState([])
  const [combinedInfo, setCombinedInfo] = useState(null)
  const [selected, setSelected] = useState({ services: [], staff: null, date: '', slot: null })
  const [client, setClient] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    notes: '',
    marketing_consent: false,
  })
  const [detailsError, setDetailsError] = useState('')
  const [bookingError, setBookingError] = useState('')
  const [confirming, setConfirming] = useState(false)
  const [bookingRef, setBookingRef] = useState('')
  const [confirmedStaff, setConfirmedStaff] = useState(null)
  const [confirmDisplay, setConfirmDisplay] = useState(null)

  const today = todayYmd()
  const maxDate = maxDateYmd()

  useEffect(() => {
    if (!slug) return
    setLoading(true)
    fetchBookServices(slug)
      .then(setAllServices)
      .catch(() => setGlobalError('Failed to load services. Please refresh the page.'))
      .finally(() => setLoading(false))
  }, [slug])

  const serviceIds = useMemo(() => selected.services.map((s) => s.id), [selected.services])

  const flatServices = useMemo(
    () => allServices.flatMap((cat) => cat.services ?? []),
    [allServices],
  )

  const totalPrice = useCallback(
    () => selected.services.reduce((a, s) => a + parseFloat(s.price || 0), 0),
    [selected.services],
  )

  const toggleService = (svc) => {
    setSelected((prev) => {
      const idx = prev.services.findIndex((s) => s.id === svc.id)
      let services
      if (idx >= 0) {
        if (prev.services.length <= 1) return prev
        services = prev.services.filter((s) => s.id !== svc.id)
      } else {
        services = [...prev.services, svc]
      }
      return { ...prev, services, staff: null, date: '', slot: null }
    })
    setSlots([])
    setCombinedInfo(null)
  }

  const loadStaff = useCallback(async () => {
    if (!slug || !serviceIds.length) {
      setStaffList([])
      return
    }
    setStaffLoading(true)
    try {
      setStaffList(await fetchBookStaff(slug, serviceIds))
    } catch {
      setStaffList([])
    } finally {
      setStaffLoading(false)
    }
  }, [slug, serviceIds])

  const loadSlots = useCallback(async () => {
    if (!slug || !selected.date || !serviceIds.length) return
    let date = selected.date
    if (date < today) date = today
    setSlotsLoading(true)
    setSlots([])
    setCombinedInfo(null)
    try {
      const data = await fetchBookSlots(slug, {
        date,
        serviceIds,
        staffId: selected.staff?.id,
      })
      setSlots(data.slots ?? [])
      setCombinedInfo(data.combined ?? null)
      if (date !== selected.date) {
        setSelected((p) => ({ ...p, date }))
      }
    } catch {
      setSlots([])
    } finally {
      setSlotsLoading(false)
    }
  }, [slug, selected.date, selected.staff, serviceIds, today])

  useEffect(() => {
    if (step === 2 && selected.date && serviceIds.length) {
      loadSlots()
    }
  }, [step, selected.date, selected.staff?.id, serviceIds.join(','), loadSlots])

  const resolveHoldStaffId = () => {
    if (selected.staff?.id != null) return selected.staff.id
    const first = selected.slot?.available_staff?.[0]
    return first?.id != null ? first.id : null
  }

  const staffDisplayName = () => {
    if (confirmedStaff) {
      return `${confirmedStaff.first_name || ''} ${confirmedStaff.last_name || ''}`.trim()
    }
    if (selected.staff) {
      return `${selected.staff.first_name || ''} ${selected.staff.last_name || ''}`.trim()
    }
    const fb = selected.slot?.available_staff?.[0]
    if (fb) return `${fb.first_name || ''} ${fb.last_name || ''}`.trim()
    return 'Any available'
  }

  const handleConfirm = async () => {
    setConfirming(true)
    setBookingError('')
    try {
      const holdData = await holdSlot(slug, {
        service_ids: serviceIds,
        staff_id: resolveHoldStaffId(),
        starts_at: `${selected.date} ${selected.slot.time}:00`,
      })
      const confirmData = await confirmBooking(slug, {
        hold_token: holdData.hold_token,
        first_name: client.first_name,
        last_name: client.last_name,
        email: client.email,
        phone: client.phone,
        notes: client.notes,
        marketing_consent: client.marketing_consent,
      })
      setBookingRef(confirmData.reference ?? confirmData.appointment?.reference ?? '')
      setConfirmedStaff(confirmData.appointment?.staff ?? null)
      setConfirmDisplay(confirmData.display ?? null)
      setStep(5)
    } catch (e) {
      setBookingError(e.message || 'Something went wrong. Please try again.')
    } finally {
      setConfirming(false)
    }
  }

  const goToConfirm = () => {
    setDetailsError('')
    if (!client.first_name || !client.last_name) {
      setDetailsError('Please enter your full name.')
      return
    }
    if (!client.email) {
      setDetailsError('Please enter your email address.')
      return
    }
    if (!client.phone) {
      setDetailsError('Please enter your phone number.')
      return
    }
    setStep(4)
  }

  if (step === 5) {
    return (
      <div className="min-h-screen bg-black text-white">
        <header className="border-b border-white/10 px-4 py-4 max-w-2xl mx-auto flex items-center justify-between">
          <span className="font-manrope font-bold text-lg">{salon?.name}</span>
          <button type="button" onClick={closeBooking} className="text-sm text-white/60 hover:text-white">
            ← Back to site
          </button>
        </header>
        <main className="max-w-lg mx-auto px-4 py-12 text-center">
          <div className="w-20 h-20 rounded-full bg-primary flex items-center justify-center mx-auto mb-6 text-3xl shadow-lg shadow-primary/30">
            ✓
          </div>
          <h1 className="text-2xl font-bold mb-2">You&apos;re all booked!</h1>
          <p className="text-white/70 text-sm mb-4">
            Confirmation sent to <strong className="text-white">{client.email}</strong>
          </p>
          {bookingRef ? (
            <p className="inline-block bg-white/10 rounded-full px-4 py-1 text-xs font-mono mb-8">
              Ref: {bookingRef}
            </p>
          ) : null}
          <div className="bg-white/5 border border-white/10 rounded-2xl p-5 text-left text-sm space-y-3 mb-8">
            <div className="flex justify-between gap-4">
              <span className="text-white/50">Services</span>
              <span className="font-semibold text-right">{selected.services.map((s) => s.name).join(', ')}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-white/50">Date</span>
              <span className="font-semibold">{confirmDisplay?.date_long ?? formatDate(selected.date)}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-white/50">Time</span>
              <span className="font-semibold">{confirmDisplay?.time ?? selected.slot?.time}</span>
            </div>
            <div className="flex justify-between">
              <span className="text-white/50">With</span>
              <span className="font-semibold">{staffDisplayName()}</span>
            </div>
          </div>
          <button
            type="button"
            onClick={() => {
              setStep(0)
              setSelected({ services: [], staff: null, date: '', slot: null })
              setBookingRef('')
            }}
            className="bg-primary hover:bg-primary-dark text-white font-semibold rounded-full px-8 py-3"
          >
            Book another appointment
          </button>
        </main>
      </div>
    )
  }

  return (
    <div className="min-h-screen bg-black text-white">
      <header className="sticky top-0 z-50 bg-black/95 backdrop-blur border-b border-white/10 px-4 py-4">
        <div className="max-w-2xl mx-auto flex items-center justify-between gap-4">
          <div>
            <p className="text-xs text-white/50 uppercase tracking-wider">Book online</p>
            <h1 className="font-manrope font-bold text-lg">{salon?.name}</h1>
          </div>
          <button type="button" onClick={closeBooking} className="text-sm text-white/60 hover:text-white shrink-0">
            ← Back
          </button>
        </div>
        <div className="max-w-2xl mx-auto mt-4 flex gap-1 overflow-x-auto pb-1">
          {STEPS.map((label, i) => (
            <span
              key={label}
              className={`text-[10px] uppercase tracking-wide px-2 py-1 rounded-full whitespace-nowrap ${
                i === step ? 'bg-primary text-white' : i < step ? 'bg-white/20 text-white/80' : 'bg-white/5 text-white/40'
              }`}
            >
              {label}
            </span>
          ))}
        </div>
      </header>

      <main className="max-w-2xl mx-auto px-4 py-8 pb-16">
        {globalError ? (
          <p className="bg-red-500/20 border border-red-500/40 text-red-200 rounded-xl p-4 mb-6 text-sm">{globalError}</p>
        ) : null}

        {loading && step === 0 ? (
          <p className="text-white/60 text-center py-12">Loading services…</p>
        ) : null}

        {step === 0 && !loading ? (
          <div className="space-y-6">
            <div className="rounded-2xl border border-white/10 bg-[#1a1f2e] p-5 sm:p-6">
              <h2 className="font-manrope font-bold text-base text-white mb-4">Select Services</h2>
              {flatServices.length === 0 ? (
                <p className="text-white/50 text-sm py-8 text-center">No services available for booking.</p>
              ) : (
                <div
                  className="grid grid-cols-2 gap-x-5 gap-y-3.5 overflow-y-auto scrollbar-none pr-1"
                  style={{ minHeight: '9.5rem', maxHeight: 'min(50vh, 22rem)' }}
                >
                  {flatServices.map((svc) => {
                    const on = selected.services.some((s) => s.id === svc.id)
                    return (
                      <label
                        key={svc.id}
                        className="flex items-start gap-2.5 cursor-pointer group"
                      >
                        <input
                          type="checkbox"
                          checked={on}
                          onChange={() => toggleService(svc)}
                          className="mt-0.5 h-4 w-4 shrink-0 rounded border-white/30 bg-transparent text-teal-500 focus:ring-teal-500/40 focus:ring-offset-0 accent-teal-500"
                        />
                        <span className="text-sm text-white/90 leading-snug group-hover:text-white">
                          {svc.name}
                        </span>
                      </label>
                    )
                  })}
                </div>
              )}
            </div>
            {selected.services.length > 0 ? (
              <div className="sticky bottom-4 bg-black/90 backdrop-blur border border-white/10 rounded-2xl p-4 flex items-center justify-between gap-4">
                <span className="text-sm">
                  {selected.services.length} selected · {currency}
                  {totalPrice().toFixed(2)}
                </span>
                <button
                  type="button"
                  onClick={() => {
                    loadStaff()
                    setStep(1)
                  }}
                  className="bg-primary hover:bg-primary-dark text-white font-semibold rounded-full px-6 py-2.5 text-sm"
                >
                  Continue
                </button>
              </div>
            ) : null}
          </div>
        ) : null}

        {step === 1 ? (
          <div>
            <p className="text-white/70 text-sm mb-4">Choose your stylist (or any available)</p>
            {staffLoading ? <p className="text-white/50">Loading stylists…</p> : null}
            <div className="space-y-2">
              <button
                type="button"
                onClick={() => {
                  setSelected((p) => ({ ...p, staff: null, date: today, slot: null }))
                  setStep(2)
                  loadSlots()
                }}
                className="w-full rounded-xl border-2 border-white/10 bg-white/5 p-4 text-left hover:border-primary"
              >
                <span className="font-semibold">Any available stylist</span>
              </button>
              {staffList.map((member) => (
                <button
                  key={member.id}
                  type="button"
                  onClick={() => {
                    setSelected((p) => ({ ...p, staff: member, date: p.date || today, slot: null }))
                    setStep(2)
                    loadSlots()
                  }}
                  className="w-full rounded-xl border-2 border-white/10 bg-white/5 p-4 text-left hover:border-primary flex items-center gap-3"
                >
                  <span className="w-10 h-10 rounded-full bg-primary/30 flex items-center justify-center text-sm font-bold">
                    {(member.first_name?.[0] || '') + (member.last_name?.[0] || '')}
                  </span>
                  <span className="font-semibold">
                    {member.first_name} {member.last_name}
                  </span>
                </button>
              ))}
            </div>
            <button type="button" onClick={() => setStep(0)} className="mt-6 text-sm text-white/50 hover:text-white">
              ← Back
            </button>
          </div>
        ) : null}

        {step === 2 ? (
          <div>
            <label className="block text-sm text-white/70 mb-2">Date</label>
            <input
              type="date"
              min={today}
              max={maxDate}
              value={selected.date || today}
              onChange={(e) => {
                setSelected((p) => ({ ...p, date: e.target.value, slot: null }))
              }}
              onBlur={loadSlots}
              className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white mb-4 [color-scheme:dark]"
            />
            <button type="button" onClick={loadSlots} className="text-sm text-primary mb-4">
              Refresh times
            </button>
            {combinedInfo ? (
              <p className="text-xs text-white/50 mb-3">{combinedInfo.message || combinedInfo.label}</p>
            ) : null}
            {slotsLoading ? <p className="text-white/50">Loading times…</p> : null}
            {!slotsLoading && slots.length === 0 && selected.date ? (
              <p className="text-white/50 text-sm">No slots available this day. Try another date.</p>
            ) : null}
            <div className="grid grid-cols-3 sm:grid-cols-4 gap-2">
              {slots.map((slot) => (
                <button
                  key={slot.time}
                  type="button"
                  disabled={!slot.available}
                  onClick={() => {
                    setSelected((p) => ({ ...p, slot }))
                    setStep(3)
                  }}
                  className={`rounded-xl py-3 text-sm font-semibold border-2 ${
                    slot.available
                      ? 'border-white/20 hover:border-primary bg-white/5'
                      : 'border-white/5 text-white/30 cursor-not-allowed'
                  }`}
                >
                  {slot.time}
                </button>
              ))}
            </div>
            <button type="button" onClick={() => setStep(1)} className="mt-6 text-sm text-white/50 hover:text-white">
              ← Back
            </button>
          </div>
        ) : null}

        {step === 3 ? (
          <div className="space-y-4">
            <h2 className="font-bold text-lg">Your details</h2>
            {detailsError ? (
              <p className="text-red-300 text-sm bg-red-500/20 border border-red-500/40 rounded-lg p-3">{detailsError}</p>
            ) : null}
            <div className="grid grid-cols-2 gap-3">
              <input
                placeholder="First name"
                value={client.first_name}
                onChange={(e) => setClient((c) => ({ ...c, first_name: e.target.value }))}
                className="bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder:text-white/40"
              />
              <input
                placeholder="Last name"
                value={client.last_name}
                onChange={(e) => setClient((c) => ({ ...c, last_name: e.target.value }))}
                className="bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder:text-white/40"
              />
            </div>
            <input
              type="email"
              placeholder="Email"
              value={client.email}
              onChange={(e) => setClient((c) => ({ ...c, email: e.target.value }))}
              className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder:text-white/40"
            />
            <input
              type="tel"
              placeholder="Phone"
              value={client.phone}
              onChange={(e) => setClient((c) => ({ ...c, phone: e.target.value }))}
              className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder:text-white/40"
            />
            <textarea
              placeholder="Notes (optional)"
              value={client.notes}
              onChange={(e) => setClient((c) => ({ ...c, notes: e.target.value }))}
              rows={3}
              className="w-full bg-white/10 border border-white/20 rounded-xl px-4 py-3 text-white placeholder:text-white/40"
            />
            <label className="flex items-center gap-2 text-sm text-white/70">
              <input
                type="checkbox"
                checked={client.marketing_consent}
                onChange={(e) => setClient((c) => ({ ...c, marketing_consent: e.target.checked }))}
                className="rounded"
              />
              Keep me updated with offers and news
            </label>
            <button
              type="button"
              onClick={goToConfirm}
              className="w-full bg-primary hover:bg-primary-dark text-white font-semibold rounded-full py-4"
            >
              Review booking
            </button>
            <button type="button" onClick={() => setStep(2)} className="text-sm text-white/50 hover:text-white">
              ← Back
            </button>
          </div>
        ) : null}

        {step === 4 ? (
          <div>
            <h2 className="font-bold text-lg mb-4">Confirm your booking</h2>
            <div className="bg-white/5 border border-white/10 rounded-2xl p-5 text-sm space-y-3 mb-6">
              <div className="flex justify-between gap-4">
                <span className="text-white/50">Services</span>
                <span className="font-semibold text-right">{selected.services.map((s) => s.name).join(', ')}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-white/50">Total</span>
                <span className="font-bold text-primary">
                  {currency}
                  {totalPrice().toFixed(2)}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-white/50">When</span>
                <span className="font-semibold">
                  {formatDate(selected.date)} at {selected.slot?.time}
                </span>
              </div>
              <div className="flex justify-between">
                <span className="text-white/50">With</span>
                <span className="font-semibold">{staffDisplayName()}</span>
              </div>
            </div>
            {bookingError ? (
              <p className="text-red-300 text-sm bg-red-500/20 border border-red-500/40 rounded-lg p-3 mb-4">{bookingError}</p>
            ) : null}
            <button
              type="button"
              disabled={confirming}
              onClick={handleConfirm}
              className="w-full bg-primary hover:bg-primary-dark disabled:opacity-60 text-white font-semibold rounded-full py-4"
            >
              {confirming ? 'Confirming…' : 'Confirm booking'}
            </button>
            <button type="button" onClick={() => setStep(3)} className="mt-4 text-sm text-white/50 hover:text-white w-full text-center">
              ← Edit details
            </button>
          </div>
        ) : null}
      </main>
    </div>
  )
}
