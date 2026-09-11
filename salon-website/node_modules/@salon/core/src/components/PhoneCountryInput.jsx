import { useEffect, useMemo, useState } from 'react'
import {
  countryList,
  DEFAULT_ISO,
  errorForValue,
  isValidNational,
  normalizeNational,
  splitStored,
  toE164,
} from '../lib/phoneCountry'

export default function PhoneCountryInput({
  value = '',
  onChange,
  required = false,
  error,
  label,
  dark = true,
  className = '',
  inputClassName = '',
  selectClassName = '',
}) {
  const countries = useMemo(() => countryList(), [])
  const [iso, setIso] = useState(DEFAULT_ISO)
  const [national, setNational] = useState('')

  useEffect(() => {
    const parts = splitStored(value, iso)
    setIso(parts.iso)
    setNational(parts.national)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [value])

  const rule = countries[iso] || countries[DEFAULT_ISO]
  const showFormatError = national.length >= (rule?.min || 0) && !isValidNational(iso, national)
  const localError = error || (showFormatError ? errorForValue(toE164(iso, national), false) : null)

  function emit(nextIso, nextNational) {
    const digits = normalizeNational(nextIso, nextNational)
    setIso(nextIso)
    setNational(digits)
    onChange?.(toE164(nextIso, digits))
  }

  const inputCls = inputClassName || (dark
    ? `flex-1 min-w-0 bg-white/10 border rounded-xl px-4 py-3 text-white placeholder:text-white/40 ${localError ? 'border-red-500/60' : 'border-white/20'}`
    : 'form-input flex-1 min-w-0')
  const selectCls = selectClassName || (dark
    ? 'w-[5.25rem] shrink-0 bg-white/10 border border-white/20 rounded-xl px-2 py-3 text-white text-sm'
    : 'form-select w-[5.25rem] shrink-0')

  return (
    <div className={className}>
      {label ? <span className={`mb-1 block text-sm ${dark ? 'text-white/70' : ''}`}>{label}{required ? ' *' : ''}</span> : null}
      <div className="flex gap-2 min-w-0 items-stretch">
        <select
          value={iso}
          onChange={(e) => emit(e.target.value, national)}
          className={selectCls}
          aria-label="Country code"
        >
          {Object.entries(countries).map(([code, row]) => (
            <option key={code} value={code} className="text-black">
              +{row.dial}
            </option>
          ))}
        </select>
        <input
          type="tel"
          inputMode="numeric"
          autoComplete="tel-national"
          value={national}
          maxLength={rule?.max || 15}
          required={required}
          placeholder={rule?.min === rule?.max ? `${rule.max} digits` : `${rule.min}–${rule.max} digits`}
          onChange={(e) => emit(iso, e.target.value)}
          className={inputCls}
        />
      </div>
      {localError ? <p className="text-xs text-red-400 mt-1">{localError}</p> : null}
    </div>
  )
}
