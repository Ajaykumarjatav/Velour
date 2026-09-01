import { useSalon } from '../../context/SalonContext'

export default function PortalShell({ title, children, onBack, backLabel = '← Back to site' }) {
  const { salon } = useSalon()

  return (
    <div className="min-h-screen bg-black text-white">
      <header className="border-b border-white/10 px-4 py-4 max-w-2xl mx-auto flex items-center justify-between">
        <div>
          <p className="text-xs text-white/50 uppercase tracking-wider">{salon?.name}</p>
          {title ? <h1 className="font-manrope font-bold text-lg">{title}</h1> : null}
        </div>
        {onBack ? (
          <button type="button" onClick={onBack} className="text-sm text-white/60 hover:text-white shrink-0">
            {backLabel}
          </button>
        ) : null}
      </header>
      <main className="max-w-lg mx-auto px-4 py-8">{children}</main>
    </div>
  )
}

export function PortalInput({ label, type = 'text', value, onChange, required, autoComplete, placeholder, error }) {
  return (
    <label className="block text-sm">
      <span className="text-white/70 mb-1 block">{label}</span>
      <input
        type={type}
        value={value}
        onChange={onChange}
        required={required}
        autoComplete={autoComplete}
        placeholder={placeholder}
        className={`w-full bg-[#1a1f2e] border rounded-xl px-4 py-3 text-white placeholder:text-white/30 focus:outline-none focus:ring-2 ${
          error ? 'border-red-500/60 focus:ring-red-500/40' : 'border-white/10 focus:ring-primary'
        }`}
      />
      {error ? <span className="text-xs text-red-400 mt-1 block">{error}</span> : null}
    </label>
  )
}

export function FormErrorList({ messages }) {
  if (!messages?.length) return null
  if (messages.length === 1) {
    return <p className="text-sm text-red-400">{messages[0]}</p>
  }
  return (
    <ul className="text-sm text-red-400 space-y-1 list-disc pl-4">
      {messages.map((msg) => (
        <li key={msg}>{msg}</li>
      ))}
    </ul>
  )
}

export function PortalButton({ children, type = 'button', onClick, disabled, variant = 'primary' }) {
  const cls = variant === 'primary'
    ? 'bg-primary hover:bg-primary-dark text-white'
    : 'border border-white/20 text-white/80 hover:bg-white/5'
  return (
    <button
      type={type}
      onClick={onClick}
      disabled={disabled}
      className={`w-full font-semibold rounded-full px-6 py-3 transition-colors disabled:opacity-50 ${cls}`}
    >
      {children}
    </button>
  )
}

export function StatusBadge({ status }) {
  const map = {
    confirmed: 'bg-primary/20 text-primary',
    pending: 'bg-primary/20 text-primary',
    completed: 'bg-emerald-500/20 text-emerald-400',
    cancelled: 'bg-white/10 text-white/50',
    no_show: 'bg-white/10 text-white/50',
  }
  const cls = map[status] || 'bg-white/10 text-white/60'
  return (
    <span className={`text-[10px] uppercase tracking-wide px-2 py-1 rounded-full ${cls}`}>
      {status?.replace('_', ' ')}
    </span>
  )
}
