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
  for (const [categoryId, svcs] of Object.entries(raw)) {
    if (!Array.isArray(svcs) || svcs.length === 0) continue
    const parsedId = categoryId === '' || categoryId === 'null' ? 0 : Number(categoryId)
    const first = svcs[0]
    cats.push({
      id: Number.isFinite(parsedId) ? parsedId : 0,
      name: first?.category?.name ?? 'Services',
      business_type: first?.category?.business_type?.name ?? null,
      sort_order: first?.category?.sort_order ?? 999,
      services: svcs,
    })
  }
  cats.sort((a, b) => a.sort_order - b.sort_order || a.name.localeCompare(b.name))
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
  if (!res.ok) {
    let message = 'Failed to load availability'
    try {
      const err = await res.json()
      message = err.message || err.error || message
    } catch {
      // ignore
    }
    throw new Error(message)
  }
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
