import { useState } from 'react'
import { useClientAuth } from '../../context/ClientAuthContext'
import { useSalon } from '../../context/SalonContext'
import AuthModal from './AuthModal'
import { fieldError } from '../../lib/apiErrors'
import { FormErrorList, PortalButton, PortalInput } from './PortalShell'

export default function ClientRegister() {
  const { salon, openLogin, openBooking, closeAuthModal, authModal } = useSalon()
  const { register, returnTo, setReturnTo } = useClientAuth()
  const [form, setForm] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    address: '',
    date_of_birth: '',
    gender: '',
    marketing_consent: false,
  })
  const [showOptional, setShowOptional] = useState(false)
  const [errors, setErrors] = useState([])
  const [fieldErrors, setFieldErrors] = useState({})
  const [loading, setLoading] = useState(false)

  const set = (key) => (e) => setForm((p) => ({ ...p, [key]: e.target.type === 'checkbox' ? e.target.checked : e.target.value }))

  const afterAuth = () => {
    const dest = returnTo
    setReturnTo(null)
    closeAuthModal()
    if (dest === 'booking') openBooking()
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setErrors([])
    setFieldErrors({})
    if (form.password !== form.password_confirmation) {
      setErrors(['Passwords do not match.'])
      setFieldErrors({ password_confirmation: ['Passwords do not match.'] })
      return
    }
    setLoading(true)
    try {
      await register({
        first_name: form.first_name,
        last_name: form.last_name,
        email: form.email,
        phone: form.phone,
        password: form.password,
        password_confirmation: form.password_confirmation,
        address: form.address || undefined,
        date_of_birth: form.date_of_birth || undefined,
        gender: form.gender || undefined,
        marketing_consent: form.marketing_consent,
      })
      afterAuth()
    } catch (err) {
      setFieldErrors(err.fieldErrors || {})
      setErrors(err.messages || [err.message || 'Registration failed'])
    } finally {
      setLoading(false)
    }
  }

  return (
    <AuthModal open={authModal === 'register'} onClose={closeAuthModal} title="Create account" wide>
      <form onSubmit={handleSubmit} className="space-y-4">
        <div className="grid grid-cols-2 gap-3">
          <PortalInput label="First name" value={form.first_name} onChange={set('first_name')} required error={fieldError(fieldErrors, 'first_name')} />
          <PortalInput label="Last name" value={form.last_name} onChange={set('last_name')} required error={fieldError(fieldErrors, 'last_name')} />
        </div>
        <PortalInput label="Email address" type="email" value={form.email} onChange={set('email')} required autoComplete="email" error={fieldError(fieldErrors, 'email')} />
        <PortalInput label="Mobile number" type="tel" value={form.phone} onChange={set('phone')} required autoComplete="tel" error={fieldError(fieldErrors, 'phone')} />
        <PortalInput label="Password" type="password" value={form.password} onChange={set('password')} required autoComplete="new-password" error={fieldError(fieldErrors, 'password')} />
        <PortalInput label="Confirm password" type="password" value={form.password_confirmation} onChange={set('password_confirmation')} required autoComplete="new-password" error={fieldError(fieldErrors, 'password_confirmation')} />

        <button
          type="button"
          onClick={() => setShowOptional((v) => !v)}
          className="text-sm text-white/50 hover:text-white"
        >
          {showOptional ? 'Hide' : 'Show'} optional details
        </button>

        {showOptional ? (
          <>
            <PortalInput label="Address" value={form.address} onChange={set('address')} />
            <PortalInput label="Date of birth" type="date" value={form.date_of_birth} onChange={set('date_of_birth')} />
            <label className="block text-sm">
              <span className="text-white/70 mb-1 block">Gender</span>
              <select
                value={form.gender}
                onChange={set('gender')}
                className="w-full bg-[#1a1f2e] border border-white/10 rounded-xl px-4 py-3 text-white focus:outline-none focus:ring-2 focus:ring-primary"
              >
                <option value="">Prefer not to say</option>
                <option value="female">Female</option>
                <option value="male">Male</option>
                <option value="other">Other</option>
              </select>
            </label>
          </>
        ) : null}

        <label className="flex items-start gap-3 text-sm text-white/70">
          <input type="checkbox" checked={form.marketing_consent} onChange={set('marketing_consent')} className="mt-1 accent-primary" />
          <span>Send me offers and updates from {salon?.name}</span>
        </label>

        <FormErrorList messages={errors.filter((msg) => !Object.values(fieldErrors).flat().includes(msg))} />

        <PortalButton type="submit" disabled={loading}>
          {loading ? 'Creating account…' : 'Create account'}
        </PortalButton>
      </form>

      <p className="text-center text-sm text-white/60 mt-5">
        Already have an account?{' '}
        <button type="button" onClick={openLogin} className="text-primary hover:underline font-semibold">
          Sign in
        </button>
      </p>
    </AuthModal>
  )
}
