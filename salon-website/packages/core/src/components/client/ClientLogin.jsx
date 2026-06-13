import { useState } from 'react'
import { useClientAuth } from '../../context/ClientAuthContext'
import { useSalon } from '../../context/SalonContext'
import { fieldError } from '../../lib/apiErrors'
import { clientForgotPassword } from '../../lib/clientApi'
import AuthModal from './AuthModal'
import { FormErrorList, PortalButton, PortalInput } from './PortalShell'

export default function ClientLogin() {
  const { salon, openRegister, openBooking, openAccount, closeAuthModal, authModal } = useSalon()
  const { login, returnTo, setReturnTo } = useClientAuth()
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [errors, setErrors] = useState([])
  const [fieldErrors, setFieldErrors] = useState({})
  const [loading, setLoading] = useState(false)
  const [forgotSent, setForgotSent] = useState(false)

  const afterAuth = () => {
    const dest = returnTo
    setReturnTo(null)
    closeAuthModal()
    if (dest === 'booking') openBooking()
    else if (dest === 'account') openAccount()
    // default: stay on current page; TopBar updates to show client name
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    setErrors([])
    setFieldErrors({})
    setLoading(true)
    try {
      await login(email.trim(), password)
      afterAuth()
    } catch (err) {
      setFieldErrors(err.fieldErrors || {})
      setErrors(err.messages || [err.message || 'Login failed'])
    } finally {
      setLoading(false)
    }
  }

  const handleForgot = async () => {
    if (!email.trim()) {
      setErrors(['Enter your email above to reset your password.'])
      return
    }
    setErrors([])
    setFieldErrors({})
    try {
      await clientForgotPassword(salon?.slug || '', email.trim())
      setForgotSent(true)
    } catch (err) {
      setErrors(err.messages || [err.message || 'Could not send reset link'])
    }
  }

  return (
    <AuthModal open={authModal === 'login'} onClose={closeAuthModal} title="Client login">
      <form onSubmit={handleSubmit} className="space-y-4">
        <PortalInput
          label="Email address"
          type="email"
          value={email}
          onChange={(e) => setEmail(e.target.value)}
          required
          autoComplete="email"
          error={fieldError(fieldErrors, 'login') || fieldError(fieldErrors, 'email')}
        />
        <PortalInput
          label="Password"
          type="password"
          value={password}
          onChange={(e) => setPassword(e.target.value)}
          required
          autoComplete="current-password"
          error={fieldError(fieldErrors, 'password')}
        />

        <FormErrorList messages={errors.filter((msg) => !Object.values(fieldErrors).flat().includes(msg))} />
        {forgotSent ? (
          <p className="text-sm text-emerald-400">If an account exists, a reset link has been sent.</p>
        ) : null}

        <PortalButton type="submit" disabled={loading}>
          {loading ? 'Signing in…' : 'Sign in'}
        </PortalButton>
      </form>

      <button
        type="button"
        onClick={handleForgot}
        className="text-sm text-white/50 hover:text-primary w-full text-center mt-4"
      >
        Forgot password?
      </button>

      <p className="text-center text-sm text-white/60 mt-5">
        Don&apos;t have an account?{' '}
        <button type="button" onClick={openRegister} className="text-primary hover:underline font-semibold">
          Register
        </button>
      </p>
    </AuthModal>
  )
}
