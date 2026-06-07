import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import BookingDateCalendar from './BookingDateCalendar'
import { useSalon } from '../context/SalonContext'
import {
  confirmBooking,
  fetchBookServices,
  fetchBookSlots,
  holdSlot,
} from '../lib/bookingApi'

const STEPS = ['Services', 'Date & time', 'Stylist', 'Your details', 'Confirm']

function todayYmd() {
  const d = new Date()
  return d.toISOString().slice(0, 10)
}

function maxDateYmd() {
  const d = new Date()
  d.setDate(d.getDate() + 60)
  return d.toISOString().slice(0, 10)
}

function slotPeriod(time) {
  const hour = parseInt((time || '0').split(':')[0], 10)
  if (hour < 12) return 'Morning'
  if (hour < 17) return 'Afternoon'
  return 'Evening'
}

function groupSlotsByPeriod(slots) {
  const groups = { Morning: [], Afternoon: [], Evening: [] }
  for (const slot of slots) {
    const period = slotPeriod(slot.time)
    groups[period].push(slot)
  }
  return Object.entries(groups).filter(([, list]) => list.length > 0)
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

function formatServicePrice(value, symbol) {
  const n = parseFloat(value || 0)
  if (Number.isNaN(n)) return `${symbol}0`
  if (symbol === '₹') {
    return `₹${n.toLocaleString('en-IN', { maximumFractionDigits: 2 })}`
  }
  return `${symbol}${n.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`
}

function ServiceScissorsIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
      <circle cx="6" cy="6" r="3" />
      <circle cx="6" cy="18" r="3" />
      <line x1="20" y1="4" x2="8.12" y2="15.88" />
      <line x1="14.47" y1="14.48" x2="20" y2="20" />
      <line x1="8.12" y1="8.12" x2="12" y2="12" />
    </svg>
  )
}

function ServiceClockIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="opacity-70 shrink-0" aria-hidden>
      <circle cx="12" cy="12" r="10" />
      <polyline points="12 6 12 12 16 14" />
    </svg>
  )
}

function BookingCategorySlider({ categories, activeCategoryId, onSelect }) {
  const trackRef = useRef(null)
  const isDragging = useRef(false)
  const didDrag = useRef(false)
  const startX = useRef(0)
  const scrollStart = useRef(0)
  const [grabbing, setGrabbing] = useState(false)

  const endDrag = () => {
    isDragging.current = false
    setGrabbing(false)
  }

  const onMouseDown = (e) => {
    if (e.button !== 0 || !trackRef.current) return
    isDragging.current = true
    didDrag.current = false
    startX.current = e.pageX
    scrollStart.current = trackRef.current.scrollLeft
    setGrabbing(true)
  }

  const onMouseMove = (e) => {
    if (!isDragging.current || !trackRef.current) return
    const delta = e.pageX - startX.current
    if (Math.abs(delta) > 4) didDrag.current = true
    trackRef.current.scrollLeft = scrollStart.current - delta
  }

  const onWheel = (e) => {
    const el = trackRef.current
    if (!el || el.scrollWidth <= el.clientWidth) return
    if (Math.abs(e.deltaY) <= Math.abs(e.deltaX)) return
    el.scrollLeft += e.deltaY
    e.preventDefault()
  }

  const handleSelect = (id) => {
    if (didDrag.current) return
    onSelect(id)
  }

  useEffect(() => {
    if (!grabbing) return
    const stop = () => endDrag()
    document.addEventListener('mouseup', stop)
    return () => document.removeEventListener('mouseup', stop)
  }, [grabbing])

  return (
    <div className="w-full min-w-0 pb-4 mb-4 border-b border-white/10">
      <div
        ref={trackRef}
        role="tablist"
        aria-label="Service categories"
        onMouseDown={onMouseDown}
        onMouseLeave={endDrag}
        onMouseUp={endDrag}
        onMouseMove={onMouseMove}
        onWheel={onWheel}
        className={`flex items-center gap-2 overflow-x-auto scrollbar-none scroll-smooth snap-x snap-mandatory py-1 w-full min-w-0 touch-pan-x select-none
          ${grabbing ? 'cursor-grabbing snap-none' : 'cursor-grab'}`}
      >
        {categories.map((cat) => (
          <button
            key={cat.id}
            type="button"
            role="tab"
            aria-selected={activeCategoryId === cat.id}
            onClick={() => handleSelect(cat.id)}
            className={`shrink-0 snap-start px-4 py-2 rounded-full font-manrope font-semibold text-xs uppercase tracking-wider transition-all duration-200 outline-none focus-visible:ring-2 focus-visible:ring-primary whitespace-nowrap max-w-[min(100%,18rem)] truncate
              ${activeCategoryId === cat.id
                ? 'bg-primary text-white shadow-md shadow-primary/25'
                : 'bg-white/10 text-white/70 hover:bg-white/15 hover:text-white'
              }`}
            title={cat.name}
          >
            {cat.name}
          </button>
        ))}
      </div>
    </div>
  )
}

export default function BookingFlow() {
  const { slug, salon, closeBooking } = useSalon()
  const currency = salon?.currency_symbol ?? '£'

  const [step, setStep] = useState(0)
  const [loading, setLoading] = useState(true)
  const [globalError, setGlobalError] = useState('')
  const [allServices, setAllServices] = useState([])
  const [activeCategoryId, setActiveCategoryId] = useState(null)
  const [slotsLoading, setSlotsLoading] = useState(false)
  const [slots, setSlots] = useState([])
  const [combinedInfo, setCombinedInfo] = useState(null)
  const [slotsError, setSlotsError] = useState('')
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
  const [bookingStatus, setBookingStatus] = useState('')
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

  const bookCategories = useMemo(
    () => allServices.filter((cat) => (cat.services?.length ?? 0) > 0),
    [allServices],
  )

  useEffect(() => {
    if (bookCategories.length === 0) {
      setActiveCategoryId(null)
      return
    }
    if (activeCategoryId === null || !bookCategories.some((c) => c.id === activeCategoryId)) {
      setActiveCategoryId(bookCategories[0].id)
    }
  }, [bookCategories, activeCategoryId])

  const activeCategory = useMemo(
    () => bookCategories.find((c) => c.id === activeCategoryId) ?? null,
    [bookCategories, activeCategoryId],
  )

  const activeCategoryServices = useMemo(
    () => activeCategory?.services ?? [],
    [activeCategory],
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

  const availableStaffForSlot = useMemo(() => {
    const list = selected.slot?.available_staff ?? []
    return [...list].sort((a, b) => {
      const nameA = `${a.first_name || ''} ${a.last_name || ''}`.trim()
      const nameB = `${b.first_name || ''} ${b.last_name || ''}`.trim()
      return nameA.localeCompare(nameB)
    })
  }, [selected.slot])

  const loadSlots = useCallback(async () => {
    if (!slug || !selected.date || !serviceIds.length) return
    let date = selected.date
    if (date < today) date = today
    setSlotsLoading(true)
    setSlots([])
    setCombinedInfo(null)
    setSlotsError('')
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
    } catch (e) {
      setSlots([])
      setSlotsError(e.message || 'Could not load available times. Please try again.')
    } finally {
      setSlotsLoading(false)
    }
  }, [slug, selected.date, selected.staff, serviceIds, today])

  useEffect(() => {
    if (step === 1 && selected.date && serviceIds.length) {
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
      setBookingStatus(confirmData.status ?? confirmData.appointment?.status ?? 'pending')
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

  if (step === 5 && bookingRef) {
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
          <h1 className="text-2xl font-bold mb-2">
            {bookingStatus === 'pending' ? 'Request received!' : "You're all booked!"}
          </h1>
          <p className="text-white/70 text-sm mb-4">
            {bookingStatus === 'pending' ? (
              <>
                We&apos;ve received your booking request. You&apos;ll get a confirmation at{' '}
                <strong className="text-white">{client.email}</strong> once the salon approves it.
              </>
            ) : (
              <>
                Confirmation sent to <strong className="text-white">{client.email}</strong>
              </>
            )}
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
              setClient({
                first_name: '',
                last_name: '',
                email: '',
                phone: '',
                notes: '',
                marketing_consent: false,
              })
              setBookingRef('')
              setBookingStatus('')
              setConfirmDisplay(null)
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
              {bookCategories.length === 0 ? (
                <p className="text-white/50 text-sm py-8 text-center">No services available for booking.</p>
              ) : (
                <>
                  <BookingCategorySlider
                    categories={bookCategories}
                    activeCategoryId={activeCategoryId}
                    onSelect={setActiveCategoryId}
                  />

                  {activeCategory ? (
                    <div className="flex items-center justify-between gap-3 mb-4 pb-3 border-b border-white/10">
                      <div className="min-w-0">
                        <h3 className="font-manrope font-semibold text-base text-white leading-snug">
                          {activeCategory.name}
                        </h3>
                        {activeCategory.business_type ? (
                          <p className="text-xs text-white/50 mt-0.5">{activeCategory.business_type}</p>
                        ) : null}
                      </div>
                      <span className="shrink-0 inline-flex items-center rounded-full bg-white/10 border border-white/10 px-2.5 py-0.5 text-[11px] font-medium text-white/70 tabular-nums">
                        {activeCategoryServices.length} {activeCategoryServices.length === 1 ? 'service' : 'services'}
                      </span>
                    </div>
                  ) : null}

                  {activeCategoryServices.length === 0 ? (
                    <p className="text-white/50 text-sm py-6 text-center">No services in this category.</p>
                  ) : (
                    <div
                      className="divide-y divide-white/10 overflow-y-auto scrollbar-none -mx-1 px-1"
                      style={{ maxHeight: 'min(55vh, 24rem)' }}
                      role="tabpanel"
                    >
                      {activeCategoryServices.map((svc) => {
                        const on = selected.services.some((s) => s.id === svc.id)
                        return (
                          <label
                            key={svc.id}
                            className={`flex items-center gap-3 py-3.5 cursor-pointer group transition-colors rounded-lg px-1 -mx-1
                              ${on ? 'bg-white/5' : 'hover:bg-white/[0.03]'}`}
                          >
                            <input
                              type="checkbox"
                              checked={on}
                              onChange={() => toggleService(svc)}
                              className="h-4 w-4 shrink-0 rounded border-white/30 bg-transparent text-teal-500 focus:ring-teal-500/40 focus:ring-offset-0 accent-teal-500"
                            />
                            <span className="w-11 h-11 shrink-0 rounded-xl bg-gradient-to-br from-violet-500/90 to-purple-800/90 flex items-center justify-center text-white shadow-sm">
                              <ServiceScissorsIcon />
                            </span>
                            <span className="flex-1 min-w-0">
                              <span className="block font-semibold text-sm text-white leading-snug group-hover:text-white">
                                {svc.name}
                              </span>
                              <span className="mt-0.5 flex items-center gap-1.5 text-xs text-white/50">
                                <ServiceClockIcon />
                                {svc.duration_minutes} min
                              </span>
                            </span>
                            <span className="shrink-0 text-sm font-semibold text-white tabular-nums">
                              {formatServicePrice(svc.price, currency)}
                            </span>
                          </label>
                        )
                      })}
                    </div>
                  )}
                </>
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
                  onClick={() => setStep(1)}
                  className="bg-primary hover:bg-primary-dark text-white font-semibold rounded-full px-6 py-2.5 text-sm"
                >
                  Continue
                </button>
              </div>
            ) : null}
          </div>
        ) : null}

        {step === 1 ? (
          <div className="space-y-4">
            <div className="text-center sm:text-left">
              <h2 className="font-manrope font-bold text-base sm:text-lg">When would you like to visit?</h2>
              <p className="text-xs sm:text-sm text-white/50 mt-0.5">Pick a date, then choose a time.</p>
            </div>

            <BookingDateCalendar
              value={selected.date}
              minDate={today}
              maxDate={maxDate}
              onChange={(ymd) => {
                setSelected((p) => ({ ...p, date: ymd, slot: null }))
              }}
            />

            {selected.date ? (
              <div className="rounded-xl border border-white/10 bg-[#1a1f2e]/80 p-3 sm:p-4">
                <div className="flex items-center justify-between gap-2 mb-3">
                  <h3 className="font-manrope font-semibold text-sm text-white">Available times</h3>
                  <button
                    type="button"
                    onClick={loadSlots}
                    disabled={slotsLoading}
                    className="text-[11px] font-semibold text-primary hover:text-primary-dark disabled:opacity-40 transition-colors"
                  >
                    {slotsLoading ? 'Loading…' : 'Refresh'}
                  </button>
                </div>

                {combinedInfo ? (
                  <p className="text-[11px] text-white/50 mb-2.5 px-2.5 py-1.5 rounded-lg bg-white/5 border border-white/5">
                    {combinedInfo.message || combinedInfo.label}
                  </p>
                ) : null}
                {slotsError ? (
                  <p className="text-red-300 text-xs sm:text-sm bg-red-500/20 border border-red-500/40 rounded-lg p-2.5 mb-3">{slotsError}</p>
                ) : null}
                {slotsLoading ? (
                  <div className="flex items-center justify-center gap-2 py-6 text-white/50 text-xs sm:text-sm">
                    <span className="inline-block w-3.5 h-3.5 border-2 border-white/20 border-t-primary rounded-full animate-spin" />
                    Finding open slots…
                  </div>
                ) : null}
                {!slotsLoading && !slotsError && slots.length === 0 ? (
                  <p className="text-white/50 text-xs sm:text-sm text-center py-5">No slots this day. Try another date.</p>
                ) : null}

                {!slotsLoading && slots.length > 0 ? (
                  <div className="space-y-3 sm:space-y-4">
                    {groupSlotsByPeriod(slots).map(([period, periodSlots]) => (
                      <div key={period}>
                        <p className="text-[9px] sm:text-[10px] font-semibold uppercase tracking-widest text-white/35 mb-1.5">{period}</p>
                        <div className="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-1.5 sm:gap-2">
                          {periodSlots.map((slot) => (
                            <button
                              key={slot.time}
                              type="button"
                              disabled={!slot.available}
                              onClick={() => {
                                setSelected((p) => {
                                  const next = { ...p, slot }
                                  if (p.staff && !slot.available_staff?.some((s) => s.id === p.staff.id)) {
                                    next.staff = null
                                  }
                                  return next
                                })
                                setStep(2)
                              }}
                              className={`rounded-lg py-2 sm:py-2.5 text-xs sm:text-sm font-semibold border transition-colors duration-150 ${
                                slot.available
                                  ? 'border-white/15 bg-white/5 text-white hover:border-primary hover:bg-primary/15 active:scale-[0.98]'
                                  : 'border-white/5 text-white/25 cursor-not-allowed'
                              }`}
                            >
                              {slot.time}
                            </button>
                          ))}
                        </div>
                      </div>
                    ))}
                  </div>
                ) : null}
              </div>
            ) : (
              <p className="text-center text-xs sm:text-sm text-white/40 py-2">Tap the date field above to choose a day.</p>
            )}

            <button type="button" onClick={() => setStep(0)} className="text-sm text-white/50 hover:text-white">
              ← Back
            </button>
          </div>
        ) : null}

        {step === 2 ? (
          <div>
            <p className="text-white/70 text-sm mb-4">Choose your stylist (or any available)</p>
            {selected.slot ? (
              <p className="text-xs text-white/50 mb-4">
                Available for {formatDate(selected.date)} at {selected.slot.time}
              </p>
            ) : null}
            <div className="space-y-2">
              <button
                type="button"
                onClick={() => {
                  setSelected((p) => ({ ...p, staff: null }))
                  setStep(3)
                }}
                className="w-full rounded-xl border-2 border-white/10 bg-white/5 p-4 text-left hover:border-primary"
              >
                <span className="font-semibold">Any available stylist</span>
              </button>
              {availableStaffForSlot.length === 0 ? (
                <p className="text-white/50 text-sm py-2">No specific stylists are free at this time.</p>
              ) : null}
              {availableStaffForSlot.map((member) => (
                <button
                  key={member.id}
                  type="button"
                  onClick={() => {
                    setSelected((p) => ({ ...p, staff: member }))
                    setStep(3)
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
                className="rounded accent-primary"
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
