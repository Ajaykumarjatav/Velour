import { useSalon } from '@salon/core/context/SalonContext'

const phoneIcon = (
  <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07 19.5 19.5 0 01-6-6 19.79 19.79 0 01-3.07-8.67A2 2 0 014.11 2h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 16.92z" />
  </svg>
)

const clockIcon = (
  <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5">
    <circle cx="12" cy="12" r="10" />
    <polyline points="12 6 12 12 16 14" />
  </svg>
)

const locationIcon = (
  <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="currentColor">
    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
  </svg>
)

export default function FooterInfoCards() {
  const { salon } = useSalon()
  if (!salon) return null

  const contactDetails = [salon.phone, salon.email].filter(Boolean)
  const hourLines = salon.opening_hours_lines?.length
    ? salon.opening_hours_lines
    : ['Contact us for opening hours']
  const locationDetails = salon.full_address ? [salon.full_address] : []

  const cards = [
    { title: 'Contact', icon: phoneIcon, details: contactDetails.length ? contactDetails : ['Contact details coming soon'] },
    { title: 'Opening Hours', icon: clockIcon, details: hourLines },
    { title: 'Location', icon: locationIcon, details: locationDetails.length ? locationDetails : ['Address coming soon'] },
  ]

  return (
    <section className="w-full relative select-none mt-30 pb-20 md:pb-28 bg-black">
      <div className="absolute inset-x-0 top-[160px] md:top-[180px] bottom-0 bg-black">
        <div className="absolute inset-0 bg-black/80 backdrop-blur-[2px]"></div>
      </div>

      <div className="relative z-10 max-w-[1100px] mx-auto px-4">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
          {cards.map((card) => (
            <div
              key={card.title}
              className="bg-zinc-900 rounded-3xl shadow-lg hover:shadow-[0_0_20px_rgba(154,3,30,0.2)] hover:border-[#9a031e]/40 transition-all duration-300 hover:-translate-y-1.5 px-6 py-10 flex flex-col items-center text-center border border-white/10 group"
            >
              <div className="w-[74px] h-[74px] bg-black border border-white/5 rounded-full flex items-center justify-center mb-6 text-[#9a031e] transition-all duration-300 group-hover:scale-110 group-hover:bg-[#9a031e]/20 group-hover:border-[#9a031e]/50 group-hover:shadow-[0_0_15px_rgba(154,3,30,0.2)]">
                {card.icon}
              </div>
              <h3 className="font-manrope font-bold text-xl md:text-2xl text-white mb-5 transition-colors duration-300 group-hover:text-[#9a031e]">
                {card.title}
              </h3>
              <div className="flex flex-col gap-2.5">
                {card.details.map((detail, j) => {
                  if (detail.startsWith('+')) {
                    return (
                      <a
                        key={j}
                        href={`tel:${detail.replace(/\s+/g, '')}`}
                        className="font-inter font-light text-sm md:text-base text-gray-400 hover:text-[#9a031e] transition-colors duration-200 select-text"
                      >
                        {detail}
                      </a>
                    )
                  }
                  if (detail.includes('@')) {
                    return (
                      <a
                        key={j}
                        href={`mailto:${detail}`}
                        className="font-inter font-light text-sm md:text-base text-gray-400 hover:text-[#9a031e] transition-colors duration-200 select-text"
                      >
                        {detail}
                      </a>
                    )
                  }
                  return (
                    <span key={j} className="font-inter font-light text-sm md:text-base text-gray-400 leading-relaxed group-hover:text-white transition-colors duration-300">
                      {detail}
                    </span>
                  )
                })}
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
