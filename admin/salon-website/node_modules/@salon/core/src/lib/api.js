import { getApiBase } from './apiBase'

export async function fetchSalonWebsite(slug) {
  const url = `${getApiBase()}/api/v1/salon/${encodeURIComponent(slug)}/website`
  let res
  try {
    res = await fetch(url, { headers: { Accept: 'application/json' } })
  } catch {
    throw new Error(
      import.meta.env.DEV
        ? 'Cannot reach API. Ensure XAMPP Apache + MySQL are running, then restart salon-website dev server.'
        : 'Failed to fetch',
    )
  }
  if (!res.ok) {
    let detail = ''
    try {
      const body = await res.json()
      detail = body?.message ? `: ${body.message}` : ''
    } catch { /* non-JSON error page */ }
    const err = new Error(
      res.status === 404 ? 'Salon not found' : `Failed to load salon (${res.status})${detail}`,
    )
    err.status = res.status
    throw err
  }
  return res.json()
}
