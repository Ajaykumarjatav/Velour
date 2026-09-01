import { useMemo, useState } from 'react'
import { useSalon } from '../../context/SalonContext'
import { clientResetPassword } from '../../lib/clientApi'
import PortalShell, { PortalButton, PortalInput } from './PortalShell'

export default function ClientResetPassword() {
  const { slug, salon, openLogin, closePortal } = useSalon()
  const params = useMemo(() => new URLSearchParams(window.location.search), [])
  const [password, setPassword] = useState('')
  const [passwordConfirmation, setPasswordConfirmation] = useState('')
  const [error, setError] = useState('')
  const [done, setDone] = useState(false)
  const [loading, setLoading] = useState(false)

  const token = params.get('token') || ''
  const email = params.get('email') || ''

  const handleSubmit = async (e) => {
    e.preventDefault()
    setError('')
    if (password !== passwordConfirmation) {
      setError('Passwords do not match.')
      return
    }
    setLoading(true)
    try {
      await clientResetPassword(slug, { token, email, password, password_confirmation: passwordConfirmation })
      setDone(true)
    } catch (err) {
      setError(err.message || 'Reset failed')
    } finally {
      setLoading(false)
    }
  }

  return (
    <PortalShell title="Reset password" onBack={closePortal}>
      <div className="bg-white/5 border border-white/10 rounded-2xl p-6 space-y-4">
        {done ? (
          <>
            <p className="text-white/70 text-sm text-center">Your password has been reset.</p>
            <PortalButton onClick={openLogin}>Sign in</PortalButton>
          </>
        ) : (
          <form onSubmit={handleSubmit} className="space-y-4">
            <p className="text-white/70 text-sm text-center">
              Choose a new password for your {salon?.name} account.
            </p>
            <PortalInput label="New password" type="password" value={password} onChange={(e) => setPassword(e.target.value)} required />
            <PortalInput label="Confirm password" type="password" value={passwordConfirmation} onChange={(e) => setPasswordConfirmation(e.target.value)} required />
            {error ? <p className="text-sm text-red-400">{error}</p> : null}
            <PortalButton type="submit" disabled={loading || !token || !email}>
              {loading ? 'Saving…' : 'Reset password'}
            </PortalButton>
          </form>
        )}
      </div>
    </PortalShell>
  )
}
