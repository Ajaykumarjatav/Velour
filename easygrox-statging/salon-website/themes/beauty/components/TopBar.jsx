import { useSalon } from '@salon/core/context/SalonContext'
import SalonLogo from '@salon/core/components/SalonLogo'

export default function TopBar() {
  const { salon } = useSalon()
  if (!salon) return null

  const lines = salon.opening_hours_lines || []
  const weekdayLine = lines[0] || 'Monday to Friday - 10 AM to 7 PM'
  const weekendLine = lines[1] || 'Saturday & Sunday - 10 AM to 10 PM'

  return (
    <div className="w-full bg-[#795152] py-4 md:py-5 px-4 border-b border-white/10">
      <div className="max-w-[1360px] mx-auto flex flex-col sm:flex-row items-center justify-between gap-4">
        <a
          href="#hero"
          className="inline-flex items-center flex-shrink-0 text-center sm:text-left transition-opacity duration-300 hover:opacity-90"
        >
          <SalonLogo logoUrl={salon.logo_url} salonName={salon.name} variant="header" />
        </a>

        <div className="flex flex-col sm:flex-row items-center gap-4 lg:gap-10">
          <div className="flex flex-col md:flex-row items-center gap-2 md:gap-3 text-center md:text-right">
            <span className="text-salmon font-inter font-semibold text-xs md:text-sm uppercase tracking-widest">Timings</span>
            <div className="flex items-center gap-2 md:gap-2.5 flex-wrap justify-center font-inter">
              <span className="text-white/90 font-light text-[11px] md:text-sm whitespace-nowrap">{weekdayLine}</span>
              <span className="hidden md:block w-px h-4 bg-[#444]"></span>
              <span className="text-white/90 font-light text-[11px] md:text-sm whitespace-nowrap">{weekendLine}</span>
            </div>
          </div>

          {salon.phone ? (
            <a
              href={`tel:${salon.phone}`}
              className="flex items-center gap-2 text-white hover:text-salmon transition-colors duration-300 rounded-md px-2.5 py-1.5 bg-white/5 hover:bg-white/10 border border-white/10"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z" />
              </svg>
              <span className="font-semibold text-xs md:text-sm whitespace-nowrap">{salon.phone}</span>
            </a>
          ) : null}
        </div>
      </div>
    </div>
  )
}
