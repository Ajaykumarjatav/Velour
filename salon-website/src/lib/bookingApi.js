import { getApiBase } from './apiBase'

function bookBase(slug) {
  return `${getApiBase()}/api/v1/book/${encodeURIComponent(slug)}`
}

export async function fetchBookServices(slug) {
  const res = await fetch(`${bookBase(slug)}/services`, { headers: { Accept: 'application/json' } })
  if (!res.ok) throw new Error('Failed to load services')
  const data = await res.json()
  const raw = data.services ?? {}
  const cats = []
  for (const svcs of Object.values(raw)) {
    if (!Array.isArray(svcs) || svcs.length === 0) continue
    cats.push({ name: svcs[0]?.category?.name ?? 'Services', services: svcs })
  }
  return cats
}

export async function fetchBookStaff(slug, serviceIds) {
  const params = new URLSearchParams()
  serviceIds.forEach((id) => params.append('service_ids[]', id))
  const res = await fetch(`${bookBase(slug)}/staff?${params}`, { headers: { Accept: 'application/json' } })
  if (!res.ok) throw new Error('Failed to load staff')
  const data = await res.json()
  return data.staff ?? []
}

export async function fetchBookSlots(slug, { date, serviceIds, staffId }) {
  const params = new URLSearchParams({ date })
  serviceIds.forEach((id) => params.append('service_ids[]', id))
  if (staffId) params.set('staff_id', String(staffId))
  const res = await fetch(`${bookBase(slug)}/availability?${params}`, { headers: { Accept: 'application/json' } })
  if (!res.ok) throw new Error('Failed to load availability')
  return res.json()
}

export async function holdSlot(slug, body) {
  const res = await fetch(`${bookBase(slug)}/hold`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify(body),
  })
  const data = await res.json()
  if (!res.ok) throw new Error(data.message || 'Slot unavailable')
  return data
}

export async function confirmBooking(slug, body) {
  const res = await fetch(`${bookBase(slug)}/confirm`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify(body),
  })
  const data = await res.json()
  if (!res.ok) throw new Error(data.message || 'Booking failed')
  return data
}
