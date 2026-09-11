import config from './phoneCountryConfig.json'

export const DEFAULT_ISO = config.default_iso || 'IN'

export function countryList() {
  const all = config.countries || {}
  const india = all.IN ? { IN: all.IN } : {}
  const rest = Object.keys(all)
    .filter((iso) => iso !== 'IN')
    .sort((a, b) => all[a].name.localeCompare(all[b].name))
  const ordered = { ...india }
  rest.forEach((iso) => {
    ordered[iso] = all[iso]
  })
  return ordered
}

export function digitsOnly(value) {
  return String(value || '').replace(/\D+/g, '')
}

function ruleFor(iso) {
  return (config.countries && config.countries[iso]) || config.countries[DEFAULT_ISO]
}

export function isValidNational(iso, digits) {
  if (!digits) return true
  const rule = ruleFor(iso)
  try {
    return new RegExp(rule.pattern).test(digits)
  } catch (e) {
    return digits.length >= rule.min && digits.length <= rule.max
  }
}

export function normalizeNational(iso, value) {
  const rule = ruleFor(iso)
  let digits = digitsOnly(value)
  if (digits.indexOf(rule.dial) === 0) {
    const rest = digits.slice(rule.dial.length)
    if (rest.length >= rule.min && rest.length <= rule.max) digits = rest
  }
  if (digits.charAt(0) === '0') {
    const stripped = digits.replace(/^0+/, '')
    if (stripped.length >= rule.min && stripped.length <= rule.max) digits = stripped
  }
  if (digits.length > rule.max) digits = digits.slice(0, rule.max)
  return digits
}

export function splitStored(stored, preferredIso) {
  const digits = digitsOnly(stored)
  if (!digits) return { iso: preferredIso || DEFAULT_ISO, national: '' }
  if (preferredIso && config.countries?.[preferredIso] && String(stored || '').startsWith('+')) {
    const dial = config.countries[preferredIso].dial
    if (digits.startsWith(dial)) {
      return { iso: preferredIso, national: digits.slice(dial.length) }
    }
  }
    const dial = config.countries[preferredIso].dial
    if (digits.startsWith(dial)) {
      return { iso: preferredIso, national: digits.slice(dial.length) }
    }
  }
  const ranked = Object.keys(config.countries || {}).map((iso) => ({
    iso,
    dial: config.countries[iso].dial,
  })).sort((a, b) => b.dial.length - a.dial.length)
  for (const row of ranked) {
    const rule = config.countries[row.iso]
    if (!digits.startsWith(row.dial)) continue
    const national = digits.slice(row.dial.length)
    if (national.length >= rule.min && national.length <= rule.max && isValidNational(row.iso, national)) {
      return { iso: row.iso, national }
    }
  }
  return { iso: DEFAULT_ISO, national: digits }
}

export function toE164(iso, national) {
  const rule = ruleFor(iso)
  const digits = normalizeNational(iso, national)
  return digits ? `+${rule.dial}${digits}` : ''
}

export function errorForValue(stored, required = false) {
  const parts = splitStored(stored)
  const digits = digitsOnly(parts.national)
  if (!digits) return required ? (ruleFor(parts.iso).message || 'Enter a valid phone number.') : null
  if (!isValidNational(parts.iso, digits)) return ruleFor(parts.iso).message
  return null
}
