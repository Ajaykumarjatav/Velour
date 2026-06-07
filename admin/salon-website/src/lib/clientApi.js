import { getApiBase } from './apiBase'
import { ClientApiError } from './apiErrors'

function clientBase(slug) {
  return `${getApiBase()}/api/v1/client/${encodeURIComponent(slug)}`
}

function authHeaders(token) {
  const headers = { Accept: 'application/json' }
  if (token) headers.Authorization = `Bearer ${token}`
  return headers
}

async function parseJson(res) {
  const data = await res.json().catch(() => ({}))
  if (!res.ok) throw new ClientApiError(data.message, data.errors)
  return data
}

export function storageKey(slug) {
  return `client_portal_token_${slug}`
}

export async function clientRegister(slug, body) {
  const res = await fetch(`${clientBase(slug)}/auth/register`, {
    method: 'POST',
    headers: { ...authHeaders(), 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  })
  return parseJson(res)
}

export async function clientLogin(slug, body) {
  const res = await fetch(`${clientBase(slug)}/auth/login`, {
    method: 'POST',
    headers: { ...authHeaders(), 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  })
  return parseJson(res)
}

export async function clientLogout(slug, token) {
  const res = await fetch(`${clientBase(slug)}/auth/logout`, {
    method: 'POST',
    headers: authHeaders(token),
  })
  return parseJson(res)
}

export async function clientMe(slug, token) {
  const res = await fetch(`${clientBase(slug)}/auth/me`, { headers: authHeaders(token) })
  return parseJson(res)
}

export async function clientUpdateProfile(slug, token, body) {
  const res = await fetch(`${clientBase(slug)}/auth/me`, {
    method: 'PUT',
    headers: { ...authHeaders(token), 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  })
  return parseJson(res)
}

export async function clientUpdatePassword(slug, token, body) {
  const res = await fetch(`${clientBase(slug)}/auth/me/password`, {
    method: 'PUT',
    headers: { ...authHeaders(token), 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  })
  return parseJson(res)
}

export async function clientUploadAvatar(slug, token, file) {
  const form = new FormData()
  form.append('avatar', file)
  const res = await fetch(`${clientBase(slug)}/auth/me/avatar`, {
    method: 'POST',
    headers: authHeaders(token),
    body: form,
  })
  return parseJson(res)
}

export async function clientForgotPassword(slug, email) {
  const res = await fetch(`${clientBase(slug)}/auth/forgot`, {
    method: 'POST',
    headers: { ...authHeaders(), 'Content-Type': 'application/json' },
    body: JSON.stringify({ email }),
  })
  return parseJson(res)
}

export async function clientResetPassword(slug, body) {
  const res = await fetch(`${clientBase(slug)}/auth/reset`, {
    method: 'POST',
    headers: { ...authHeaders(), 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  })
  return parseJson(res)
}

export async function fetchClientAppointments(slug, token, { status = 'upcoming', search = '' } = {}) {
  const params = new URLSearchParams({ status })
  if (search) params.set('search', search)
  const res = await fetch(`${clientBase(slug)}/appointments?${params}`, { headers: authHeaders(token) })
  return parseJson(res)
}

export async function fetchClientAppointment(slug, token, ref) {
  const res = await fetch(`${clientBase(slug)}/appointments/${encodeURIComponent(ref)}`, {
    headers: authHeaders(token),
  })
  return parseJson(res)
}

export async function cancelClientAppointment(slug, token, ref, reason = '') {
  const res = await fetch(`${clientBase(slug)}/appointments/${encodeURIComponent(ref)}/cancel`, {
    method: 'POST',
    headers: { ...authHeaders(token), 'Content-Type': 'application/json' },
    body: JSON.stringify({ reason }),
  })
  return parseJson(res)
}

export async function rescheduleClientAppointment(slug, token, ref, body) {
  const res = await fetch(`${clientBase(slug)}/appointments/${encodeURIComponent(ref)}/reschedule`, {
    method: 'POST',
    headers: { ...authHeaders(token), 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  })
  return parseJson(res)
}

export async function fetchClientInvoice(slug, token, ref) {
  const res = await fetch(`${clientBase(slug)}/appointments/${encodeURIComponent(ref)}/invoice`, {
    headers: authHeaders(token),
  })
  return parseJson(res)
}

export function clientInvoicePdfUrl(slug, ref, token) {
  return `${clientBase(slug)}/appointments/${encodeURIComponent(ref)}/invoice.pdf?token=${encodeURIComponent(token)}`
}

export async function downloadClientInvoicePdf(slug, token, ref) {
  const res = await fetch(`${clientBase(slug)}/appointments/${encodeURIComponent(ref)}/invoice.pdf`, {
    headers: authHeaders(token),
  })
  if (!res.ok) {
    const data = await res.json().catch(() => ({}))
    throw new Error(data.message || 'Could not download invoice')
  }
  const blob = await res.blob()
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `invoice-${ref}.pdf`
  a.click()
  URL.revokeObjectURL(url)
}

export async function submitClientReview(slug, token, ref, body) {
  const res = await fetch(`${clientBase(slug)}/appointments/${encodeURIComponent(ref)}/review`, {
    method: 'POST',
    headers: { ...authHeaders(token), 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  })
  return parseJson(res)
}

export async function updateClientReview(slug, token, id, body) {
  const res = await fetch(`${clientBase(slug)}/reviews/${id}`, {
    method: 'PUT',
    headers: { ...authHeaders(token), 'Content-Type': 'application/json' },
    body: JSON.stringify(body),
  })
  return parseJson(res)
}

export async function deleteClientReview(slug, token, id) {
  const res = await fetch(`${clientBase(slug)}/reviews/${id}`, {
    method: 'DELETE',
    headers: authHeaders(token),
  })
  return parseJson(res)
}
