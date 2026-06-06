import { useState } from 'react'
import { useClientAuth } from '../../context/ClientAuthContext'
import { useSalon } from '../../context/SalonContext'
import { clientUpdatePassword, clientUpdateProfile, clientUploadAvatar } from '../../lib/clientApi'
import { PortalButton, PortalInput } from './PortalShell'

export default function ClientProfile() {
  const { slug } = useSalon()
  const { token, client, refreshMe } = useClientAuth()
  const [section, setSection] = useState('profile')
  const [form, setForm] = useState({
    first_name: client?.first_name || '',
    last_name: client?.last_name || '',
    email: client?.email || '',
    phone: client?.phone || '',
    address: client?.address || '',
    date_of_birth: client?.date_of_birth || '',
    gender: client?.gender || '',
    marketing_consent: client?.marketing_consent || false,
  })
  const [passwordForm, setPasswordForm] = useState({
    current_password: '',
    password: '',
    password_confirmation: '',
  })
  const [message, setMessage] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  const set = (key) => (e) => setForm((p) => ({
    ...p,
    [key]: e.target.type === 'checkbox' ? e.target.checked : e.target.value,
  }))

  const saveProfile = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError('')
    setMessage('')
    try {
      await clientUpdateProfile(slug, token, form)
      await refreshMe()
      setMessage('Profile updated.')
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  const savePassword = async (e) => {
    e.preventDefault()
    setLoading(true)
    setError('')
    setMessage('')
    if (passwordForm.password !== passwordForm.password_confirmation) {
      setError('Passwords do not match.')
      setLoading(false)
      return
    }
    try {
      await clientUpdatePassword(slug, token, passwordForm)
      setMessage('Password updated.')
      setPasswordForm({ current_password: '', password: '', password_confirmation: '' })
    } catch (err) {
      setError(err.message)
    } finally {
      setLoading(false)
    }
  }

  const onAvatar = async (e) => {
    const file = e.target.files?.[0]
    if (!file) return
    try {
      await clientUploadAvatar(slug, token, file)
      await refreshMe()
      setMessage('Photo updated.')
    } catch (err) {
      setError(err.message)
    }
  }

  return (
    <div className="space-y-4">
      <div className="flex gap-2">
        {['profile', 'security'].map((s) => (
          <button
            key={s}
            type="button"
            onClick={() => setSection(s)}
            className={`text-xs uppercase px-3 py-2 rounded-full ${
              section === s ? 'bg-primary text-white' : 'bg-white/5 text-white/50'
            }`}
          >
            {s}
          </button>
        ))}
      </div>

      {message ? <p className="text-sm text-emerald-400">{message}</p> : null}
      {error ? <p className="text-sm text-red-400">{error}</p> : null}

      {section === 'profile' ? (
        <form onSubmit={saveProfile} className="bg-white/5 border border-white/10 rounded-2xl p-5 space-y-4">
          <label className="block">
            <span className="text-white/70 text-sm mb-2 block">Profile photo</span>
            <div className="flex items-center gap-4">
              <div className="w-16 h-16 rounded-full bg-primary/20 overflow-hidden flex items-center justify-center">
                {client?.avatar_url ? (
                  <img src={client.avatar_url} alt="" className="w-full h-full object-cover" />
                ) : (
                  <span className="text-xl font-bold">{(client?.first_name?.[0] || '?').toUpperCase()}</span>
                )}
              </div>
              <input type="file" accept="image/*" onChange={onAvatar} className="text-xs text-white/60" />
            </div>
          </label>
          <div className="grid grid-cols-2 gap-3">
            <PortalInput label="First name" value={form.first_name} onChange={set('first_name')} required />
            <PortalInput label="Last name" value={form.last_name} onChange={set('last_name')} required />
          </div>
          <PortalInput label="Email" type="email" value={form.email} onChange={set('email')} required />
          <PortalInput label="Phone" type="tel" value={form.phone} onChange={set('phone')} required />
          <PortalInput label="Address" value={form.address} onChange={set('address')} />
          <PortalInput label="Date of birth" type="date" value={form.date_of_birth} onChange={set('date_of_birth')} />
          <label className="flex items-center gap-2 text-sm text-white/70">
            <input type="checkbox" checked={form.marketing_consent} onChange={set('marketing_consent')} className="accent-primary" />
            Marketing updates
          </label>
          <PortalButton type="submit" disabled={loading}>Save profile</PortalButton>
        </form>
      ) : (
        <form onSubmit={savePassword} className="bg-white/5 border border-white/10 rounded-2xl p-5 space-y-4">
          <PortalInput label="Current password" type="password" value={passwordForm.current_password} onChange={(e) => setPasswordForm((p) => ({ ...p, current_password: e.target.value }))} required />
          <PortalInput label="New password" type="password" value={passwordForm.password} onChange={(e) => setPasswordForm((p) => ({ ...p, password: e.target.value }))} required />
          <PortalInput label="Confirm new password" type="password" value={passwordForm.password_confirmation} onChange={(e) => setPasswordForm((p) => ({ ...p, password_confirmation: e.target.value }))} required />
          <PortalButton type="submit" disabled={loading}>Change password</PortalButton>
        </form>
      )}
    </div>
  )
}
