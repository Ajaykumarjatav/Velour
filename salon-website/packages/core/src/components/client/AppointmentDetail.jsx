import { useCallback, useEffect, useState } from 'react'
import { useClientAuth } from '../../context/ClientAuthContext'
import { useSalon } from '../../context/SalonContext'
import { fetchBookSlots } from '../../lib/bookingApi'
import {
  cancelClientAppointment,
  deleteClientReview,
  downloadClientInvoicePdf,
  fetchClientAppointment,
  fetchClientInvoice,
  rescheduleClientAppointment,
  submitClientReview,
  updateClientReview,
} from '../../lib/clientApi'
import PortalShell, { PortalButton, StatusBadge } from './PortalShell'

function todayYmd() {
  return new Date().toISOString().slice(0, 10)
}

export default function AppointmentDetail({ refCode, onBack, onUpdated }) {
  const { slug, salon } = useSalon()
  const { token } = useClientAuth()
  const [apt, setApt] = useState(null)
  const [invoice, setInvoice] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState('')
  const [actionError, setActionError] = useState('')
  const [showReschedule, setShowReschedule] = useState(false)
  const [rescheduleDate, setRescheduleDate] = useState(todayYmd())
  const [slots, setSlots] = useState([])
  const [selectedSlot, setSelectedSlot] = useState(null)
  const [rating, setRating] = useState(0)
  const [comment, setComment] = useState('')
  const [reviewMode, setReviewMode] = useState(false)

  const load = useCallback(async () => {
    setLoading(true)
    setError('')
    try {
      const data = await fetchClientAppointment(slug, token, refCode)
      setApt(data.appointment)
      if (data.appointment?.review) {
        setRating(data.appointment.review.rating)
        setComment(data.appointment.review.comment || '')
      }
      if (data.appointment?.status === 'completed' && data.appointment?.has_invoice) {
        try {
          const inv = await fetchClientInvoice(slug, token, refCode)
          setInvoice(inv.invoice)
        } catch {
          setInvoice(null)
        }
      }
    } catch (e) {
      setError(e.message || 'Could not load appointment')
    } finally {
      setLoading(false)
    }
  }, [slug, token, refCode])

  useEffect(() => { load() }, [load])

  const serviceIds = apt?.services?.map((s) => s.id).filter(Boolean) || []

  const loadRescheduleSlots = async () => {
    if (!serviceIds.length) return
    try {
      const data = await fetchBookSlots(slug, {
        date: rescheduleDate,
        serviceIds,
        staffId: apt?.staff?.id,
      })
      setSlots(data.slots ?? [])
      setSelectedSlot(null)
    } catch {
      setSlots([])
    }
  }

  useEffect(() => {
    if (showReschedule && rescheduleDate) loadRescheduleSlots()
  }, [showReschedule, rescheduleDate])

  const handleCancel = async () => {
    if (!window.confirm('Cancel this appointment?')) return
    setActionError('')
    try {
      await cancelClientAppointment(slug, token, refCode)
      onUpdated?.()
      onBack()
    } catch (e) {
      setActionError(e.message)
    }
  }

  const handleReschedule = async () => {
    if (!selectedSlot) return
    setActionError('')
    try {
      await rescheduleClientAppointment(slug, token, refCode, {
        starts_at: `${rescheduleDate} ${selectedSlot.time}:00`,
        staff_id: apt?.staff?.id,
      })
      setShowReschedule(false)
      onUpdated?.()
      load()
    } catch (e) {
      setActionError(e.message)
    }
  }

  const handleReview = async () => {
    setActionError('')
    try {
      if (apt?.review) {
        await updateClientReview(slug, token, apt.review.id, { rating, comment })
      } else {
        await submitClientReview(slug, token, refCode, { rating, comment })
      }
      setReviewMode(false)
      load()
    } catch (e) {
      setActionError(e.message)
    }
  }

  const handleDeleteReview = async () => {
    if (!apt?.review || !window.confirm('Remove your review?')) return
    try {
      await deleteClientReview(slug, token, apt.review.id)
      setRating(0)
      setComment('')
      load()
    } catch (e) {
      setActionError(e.message)
    }
  }

  const canModify = apt && ['confirmed', 'pending'].includes(apt.status)

  return (
    <PortalShell title="Appointment" onBack={onBack} backLabel="← Back">
      {loading ? (
        <div className="h-40 bg-white/5 rounded-2xl animate-pulse" />
      ) : error ? (
        <p className="text-red-400 text-sm">{error}</p>
      ) : apt ? (
        <div className="space-y-4">
          <div className="flex items-center justify-between">
            <span className="font-mono text-sm text-white/60">{apt.reference}</span>
            <StatusBadge status={apt.status} />
          </div>

          <div className="grid lg:grid-cols-3 gap-4">
            <div className="lg:col-span-2 space-y-4">
              <div className="bg-white/5 border border-white/10 rounded-2xl p-5 space-y-3 text-sm">
                <h2 className="font-manrope font-bold">Services</h2>
                {apt.services?.map((s, i) => (
                  <div key={i} className="flex justify-between gap-4">
                    <span>{s.name}</span>
                    <span className="text-white/60">{s.duration_minutes} min</span>
                  </div>
                ))}
                <div className="pt-2 border-t border-white/10">
                  <p className="text-white/50 text-xs uppercase">Appointment time</p>
                  <p className="font-semibold">{apt.display?.date_long}</p>
                  <p>{apt.display?.time_range || apt.display?.time}</p>
                </div>
                {apt.client_notes ? (
                  <div>
                    <p className="text-white/50 text-xs uppercase">Notes</p>
                    <p className="text-white/80">{apt.client_notes}</p>
                  </div>
                ) : null}
              </div>

              {apt.staff ? (
                <div className="bg-white/5 border border-white/10 rounded-2xl p-5 text-sm">
                  <h2 className="font-manrope font-bold mb-3">Your stylist</h2>
                  <p className="font-semibold">{apt.staff.full_name}</p>
                  {apt.staff.role ? <p className="text-white/60 capitalize">{apt.staff.role}</p> : null}
                  {apt.staff.bio ? <p className="text-white/70 mt-2 text-xs">{apt.staff.bio}</p> : null}
                </div>
              ) : null}

              {apt.status === 'completed' ? (
                <div className="bg-white/5 border border-white/10 rounded-2xl p-5 text-sm space-y-3">
                  <h2 className="font-manrope font-bold">Your review</h2>
                  {apt.review && !reviewMode ? (
                    <>
                      <p>{[1, 2, 3, 4, 5].map((n) => (n <= apt.review.rating ? '★' : '☆')).join('')}</p>
                      {apt.review.comment ? <p className="text-white/70">{apt.review.comment}</p> : null}
                      <div className="flex gap-2">
                        <button type="button" onClick={() => setReviewMode(true)} className="text-xs text-primary">Edit</button>
                        <button type="button" onClick={handleDeleteReview} className="text-xs text-white/50">Delete</button>
                      </div>
                    </>
                  ) : (
                    <>
                      <div className="flex gap-1">
                        {[1, 2, 3, 4, 5].map((n) => (
                          <button key={n} type="button" onClick={() => setRating(n)} className="text-xl text-primary">
                            {n <= rating ? '★' : '☆'}
                          </button>
                        ))}
                      </div>
                      <textarea
                        value={comment}
                        onChange={(e) => setComment(e.target.value)}
                        rows={3}
                        placeholder="Share your experience (optional)"
                        className="w-full bg-[#1a1f2e] border border-white/10 rounded-xl px-3 py-2 text-white text-sm"
                      />
                      <PortalButton onClick={handleReview} disabled={!rating}>Submit review</PortalButton>
                    </>
                  )}
                </div>
              ) : null}
            </div>

            <div className="space-y-4">
              <div className="bg-white/5 border border-white/10 rounded-2xl p-5 text-sm space-y-2">
                <h2 className="font-manrope font-bold mb-2">Payment</h2>
                <div className="flex justify-between"><span className="text-white/50">Total</span><span>{salon?.currency_symbol || '£'}{apt.total_price}</span></div>
                <div className="flex justify-between"><span className="text-white/50">Paid</span><span>{salon?.currency_symbol || '£'}{apt.amount_paid}</span></div>
                <div className="flex justify-between font-semibold"><span className="text-white/50">Balance</span><span>{salon?.currency_symbol || '£'}{apt.balance_due}</span></div>
                <p className="text-xs text-white/40 capitalize pt-1">{apt.payment_status}</p>
              </div>

              {invoice ? (
                <div className="bg-white/5 border border-white/10 rounded-2xl p-5 text-sm space-y-2">
                  <h2 className="font-manrope font-bold">Invoice</h2>
                  <p className="text-white/60">#{invoice.number}</p>
                  <p className="font-semibold">{salon?.currency_symbol || '£'}{invoice.total}</p>
                  <PortalButton onClick={() => downloadClientInvoicePdf(slug, token, refCode)}>Download PDF</PortalButton>
                </div>
              ) : null}

              {canModify ? (
                <div className="space-y-2">
                  <PortalButton onClick={() => setShowReschedule((v) => !v)} variant="secondary">Reschedule</PortalButton>
                  <PortalButton onClick={handleCancel} variant="secondary">Cancel appointment</PortalButton>
                </div>
              ) : null}
            </div>
          </div>

          {showReschedule ? (
            <div className="bg-white/5 border border-white/10 rounded-2xl p-5 space-y-3">
              <h3 className="font-semibold text-sm">Pick a new time</h3>
              <input
                type="date"
                value={rescheduleDate}
                min={todayYmd()}
                onChange={(e) => setRescheduleDate(e.target.value)}
                className="w-full bg-[#1a1f2e] border border-white/10 rounded-xl px-4 py-2 text-white"
              />
              <div className="grid grid-cols-3 gap-2">
                {slots.map((slot) => (
                  <button
                    key={slot.time}
                    type="button"
                    onClick={() => setSelectedSlot(slot)}
                    className={`py-2 rounded-lg text-sm border ${
                      selectedSlot?.time === slot.time ? 'border-primary bg-primary/20' : 'border-white/10'
                    }`}
                  >
                    {slot.time}
                  </button>
                ))}
              </div>
              <PortalButton onClick={handleReschedule} disabled={!selectedSlot}>Confirm reschedule</PortalButton>
            </div>
          ) : null}

          {actionError ? <p className="text-sm text-red-400">{actionError}</p> : null}
        </div>
      ) : null}
    </PortalShell>
  )
}
