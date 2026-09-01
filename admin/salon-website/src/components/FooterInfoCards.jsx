import { useSalon } from '../context/SalonContext'

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
    <section className="w-full bg-section-lighter py-16 lg:py-20">
      <div className="max-w-[1360px] mx-auto px-4">
        <div className="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
          {cards.map((card) => (
            <div
              key={card.title}
              className="bg-white rounded-3xl p-8 md:p-10 flex flex-col items-center text-center shadow-sm border border-gray-100 hover:shadow-lg transition-all duration-300"
            >
              <div className="text-primary mb-6">{card.icon}</div>
              <h3 className="font-manrope font-bold text-xl text-black mb-4">{card.title}</h3>
              <div className="flex flex-col gap-2">
                {card.details.map((line, i) => (
                  <p key={i} className="font-inter font-light text-sm md:text-base text-text-muted leading-relaxed">
                    {line}
                  </p>
                ))}
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
