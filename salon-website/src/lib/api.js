import { getApiBase } from './apiBase'

export async function fetchSalonWebsite(slug) {
  const res = await fetch(`${getApiBase()}/api/v1/salon/${encodeURIComponent(slug)}/website`, {
    headers: { Accept: 'application/json' },
  })
  if (!res.ok) {
    const err = new Error(res.status === 404 ? 'Salon not found' : 'Failed to load salon')
    err.status = res.status
    throw err
  }
  return res.json()
}
