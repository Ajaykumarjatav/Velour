import { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react'
import {
  clientLogin,
  clientLogout,
  clientMe,
  clientRegister,
  storageKey,
} from '../lib/clientApi'
import { useSalon } from './SalonContext'

const ClientAuthContext = createContext(null)

export function ClientAuthProvider({ children }) {
  const { slug } = useSalon()
  const [token, setToken] = useState(() => (slug ? localStorage.getItem(storageKey(slug)) : null))
  const [client, setClient] = useState(null)
  const [loading, setLoading] = useState(!!token)
  const [returnTo, setReturnTo] = useState(null)

  useEffect(() => {
    if (!slug) return
    const stored = localStorage.getItem(storageKey(slug))
    setToken(stored)
    if (!stored) {
      setClient(null)
      setLoading(false)
    }
  }, [slug])

  useEffect(() => {
    if (!slug || !token) {
      setLoading(false)
      return
    }

    let cancelled = false
    setLoading(true)
    clientMe(slug, token)
      .then((data) => {
        if (!cancelled) setClient(data.client ?? null)
      })
      .catch(() => {
        if (!cancelled) {
          const stored = localStorage.getItem(storageKey(slug))
          if (stored === token) {
            localStorage.removeItem(storageKey(slug))
            setToken(null)
            setClient(null)
          }
        }
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })

    return () => { cancelled = true }
  }, [slug, token])

  const persistToken = useCallback((newToken) => {
    if (!slug) return
    if (newToken) {
      localStorage.setItem(storageKey(slug), newToken)
    } else {
      localStorage.removeItem(storageKey(slug))
    }
    setToken(newToken)
  }, [slug])

  const login = useCallback(async (loginValue, password) => {
    const data = await clientLogin(slug, { login: loginValue, password })
    persistToken(data.token)
    setClient(data.client ?? null)
    return data
  }, [slug, persistToken])

  const register = useCallback(async (body) => {
    const data = await clientRegister(slug, body)
    persistToken(data.token)
    setClient(data.client ?? null)
    return data
  }, [slug, persistToken])

  const logout = useCallback(async () => {
    if (token) {
      try { await clientLogout(slug, token) } catch { /* ignore */ }
    }
    persistToken(null)
    setClient(null)
  }, [slug, token, persistToken])

  const refreshMe = useCallback(async () => {
    if (!token) return null
    const data = await clientMe(slug, token)
    setClient(data.client ?? null)
    return data.client
  }, [slug, token])

  const value = useMemo(() => ({
    token,
    client,
    loading,
    isAuthenticated: !!token && !!client,
    login,
    register,
    logout,
    refreshMe,
    returnTo,
    setReturnTo,
  }), [token, client, loading, login, register, logout, refreshMe, returnTo])

  return <ClientAuthContext.Provider value={value}>{children}</ClientAuthContext.Provider>
}

export function useClientAuth() {
  const ctx = useContext(ClientAuthContext)
  if (!ctx) throw new Error('useClientAuth must be used within ClientAuthProvider')
  return ctx
}
