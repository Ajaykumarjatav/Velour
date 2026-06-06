import { useEffect, useMemo, useRef, useState } from 'react'

const WEEKDAYS = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su']
const MONTHS = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
]

function ymdFromDate(date) {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

function parseYmd(ymd) {
  const [y, m, d] = ymd.split('-').map(Number)
  return new Date(y, m - 1, d)
}

function startOfMonth(date) {
  return new Date(date.getFullYear(), date.getMonth(), 1)
}

function addMonths(date, delta) {
  return new Date(date.getFullYear(), date.getMonth() + delta, 1)
}

function formatDisplayDate(ymd) {
  if (!ymd) return ''
  return parseYmd(ymd).toLocaleDateString('en-GB', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  })
}

function ChevronLeft() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" aria-hidden>
      <polyline points="15 18 9 12 15 6" />
    </svg>
  )
}

function ChevronRight() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" aria-hidden>
      <polyline points="9 18 15 12 9 6" />
    </svg>
  )
}

function CalendarIcon() {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" className="shrink-0 opacity-70" aria-hidden>
      <rect x="3" y="4" width="18" height="18" rx="2" />
      <line x1="16" y1="2" x2="16" y2="6" />
      <line x1="8" y1="2" x2="8" y2="6" />
      <line x1="3" y1="10" x2="21" y2="10" />
    </svg>
  )
}

export default function BookingDateCalendar({ value, minDate, maxDate, onChange }) {
  const today = useMemo(() => ymdFromDate(new Date()), [])
  const rootRef = useRef(null)
  const [open, setOpen] = useState(false)
  const [viewMonth, setViewMonth] = useState(() => startOfMonth(parseYmd(value || minDate || today)))

  const min = minDate || today
  const max = maxDate || min

  useEffect(() => {
    if (value) {
      setViewMonth(startOfMonth(parseYmd(value)))
    }
  }, [value])

  useEffect(() => {
    if (!open) return
    const onPointerDown = (e) => {
      if (rootRef.current && !rootRef.current.contains(e.target)) {
        setOpen(false)
      }
    }
    const onKeyDown = (e) => {
      if (e.key === 'Escape') setOpen(false)
    }
    document.addEventListener('mousedown', onPointerDown)
    document.addEventListener('keydown', onKeyDown)
    return () => {
      document.removeEventListener('mousedown', onPointerDown)
      document.removeEventListener('keydown', onKeyDown)
    }
  }, [open])

  const canGoPrev = ymdFromDate(startOfMonth(viewMonth)) > ymdFromDate(startOfMonth(parseYmd(min)))
  const canGoNext = ymdFromDate(startOfMonth(addMonths(viewMonth, 1))) <= ymdFromDate(startOfMonth(parseYmd(max)))

  const cells = useMemo(() => {
    const first = startOfMonth(viewMonth)
    const last = new Date(viewMonth.getFullYear(), viewMonth.getMonth() + 1, 0)
    const offset = (first.getDay() + 6) % 7
    const grid = []

    for (let i = 0; i < offset; i++) {
      grid.push({ type: 'pad', key: `pad-start-${i}` })
    }

    for (let day = 1; day <= last.getDate(); day++) {
      const date = new Date(viewMonth.getFullYear(), viewMonth.getMonth(), day)
      const ymd = ymdFromDate(date)
      grid.push({
        type: 'day',
        key: ymd,
        ymd,
        day,
        disabled: ymd < min || ymd > max,
        isToday: ymd === today,
        isSelected: ymd === value,
      })
    }

    while (grid.length % 7 !== 0) {
      grid.push({ type: 'pad', key: `pad-end-${grid.length}` })
    }

    return grid
  }, [viewMonth, min, max, today, value])

  const pickDay = (ymd) => {
    if (ymd < min || ymd > max) return
    onChange(ymd)
    setOpen(false)
  }

  const goToday = () => {
    if (today < min || today > max) return
    setViewMonth(startOfMonth(parseYmd(today)))
    onChange(today)
    setOpen(false)
  }

  return (
    <div ref={rootRef} className="relative w-full">
      <button
        type="button"
        onClick={() => setOpen((o) => !o)}
        aria-expanded={open}
        aria-haspopup="dialog"
        className={`w-full flex items-center gap-3 rounded-xl border px-4 py-3.5 text-left transition-colors ${
          open
            ? 'border-primary/50 bg-white/10'
            : 'border-white/20 bg-white/5 hover:border-white/30 hover:bg-white/[0.07]'
        }`}
      >
        <CalendarIcon />
        <span className={`flex-1 min-w-0 text-sm font-medium truncate ${value ? 'text-white' : 'text-white/45'}`}>
          {value ? formatDisplayDate(value) : 'Select a date'}
        </span>
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="16"
          height="16"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          strokeWidth="2"
          className={`shrink-0 text-white/50 transition-transform duration-200 ${open ? 'rotate-180' : ''}`}
          aria-hidden
        >
          <polyline points="6 9 12 15 18 9" />
        </svg>
      </button>

      {open ? (
        <div
          role="dialog"
          aria-label="Choose date"
          className="absolute z-30 left-0 right-0 mt-2 rounded-xl border border-white/10 bg-gradient-to-br from-[#1a1f2e] to-[#12151c] p-3 sm:p-3.5 shadow-xl shadow-black/40"
        >
          <div className="flex items-center justify-between gap-2 mb-2.5">
            <button
              type="button"
              disabled={!canGoPrev}
              onClick={() => setViewMonth((m) => addMonths(m, -1))}
              className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white/70 hover:bg-white/10 hover:text-white disabled:opacity-30 disabled:pointer-events-none transition-colors"
              aria-label="Previous month"
            >
              <ChevronLeft />
            </button>

            <p className="font-manrope font-bold text-sm text-white tracking-tight truncate">
              {MONTHS[viewMonth.getMonth()]} {viewMonth.getFullYear()}
            </p>

            <button
              type="button"
              disabled={!canGoNext}
              onClick={() => setViewMonth((m) => addMonths(m, 1))}
              className="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-white/10 bg-white/5 text-white/70 hover:bg-white/10 hover:text-white disabled:opacity-30 disabled:pointer-events-none transition-colors"
              aria-label="Next month"
            >
              <ChevronRight />
            </button>
          </div>

          <div className="grid grid-cols-7 gap-0.5 mb-1">
            {WEEKDAYS.map((label) => (
              <div key={label} className="text-center text-[9px] sm:text-[10px] font-semibold uppercase text-white/35 py-0.5">
                {label}
              </div>
            ))}
          </div>

          <div className="grid grid-cols-7 gap-0.5 sm:gap-1" role="grid">
            {cells.map((cell) => {
              if (cell.type === 'pad') {
                return <div key={cell.key} className="h-8 sm:h-9" aria-hidden />
              }

              const { ymd, day, disabled, isToday, isSelected } = cell

              return (
                <button
                  key={cell.key}
                  type="button"
                  disabled={disabled}
                  onClick={() => pickDay(ymd)}
                  className={`
                    h-8 sm:h-9 rounded-lg text-xs sm:text-sm font-semibold transition-colors duration-150
                    ${disabled
                      ? 'text-white/15 cursor-not-allowed'
                      : isSelected
                        ? 'bg-primary text-white shadow-sm shadow-primary/30'
                        : 'text-white/80 hover:bg-white/10 hover:text-white'
                    }
                    ${isToday && !isSelected ? 'border border-primary/50' : 'border border-transparent'}
                  `}
                  aria-label={ymd}
                  aria-pressed={isSelected}
                >
                  {day}
                </button>
              )
            })}
          </div>

          <div className="mt-2.5 pt-2 border-t border-white/10 flex justify-end">
            <button
              type="button"
              onClick={goToday}
              disabled={today < min || today > max}
              className="px-2.5 py-1 rounded-full border border-primary/35 text-primary text-[10px] font-semibold uppercase tracking-wide hover:bg-primary/10 disabled:opacity-30 disabled:pointer-events-none transition-colors"
            >
              Today
            </button>
          </div>
        </div>
      ) : null}
    </div>
  )
}
