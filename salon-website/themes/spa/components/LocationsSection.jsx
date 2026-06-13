import { useEffect, useMemo, useState } from 'react'
import { useSalon } from '@salon/core/context/SalonContext'
import BookButton from '@salon/core/components/BookButton'
import { assetUrl } from '@salon/core/lib/assetUrl'

const pinIcon = (active) => (
  <svg
    xmlns="http://www.w3.org/2000/svg"
    width="18"
    height="18"
    viewBox="0 0 24 24"
    fill="currentColor"
    className="shrink-0 mt-0.5"
    aria-hidden
  >
    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
  </svg>
)

const clockIcon = (
  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
    <circle cx="12" cy="12" r="10" />
    <polyline points="12 6 12 12 16 14" />
  </svg>
)

const calendarIcon = (
  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" aria-hidden>
    <rect x="3" y="4" width="18" height="18" rx="2" />
    <line x1="16" y1="2" x2="16" y2="6" />
    <line x1="8" y1="2" x2="8" y2="6" />
    <line x1="3" y1="10" x2="21" y2="10" />
  </svg>
)

function LocationCard({ location, isActive, onSelect }) {
  return (
    <button
      type="button"
      onClick={() => onSelect(location.id)}
      className={`w-full text-left rounded-2xl p-6 md:p-7 transition-all duration-300 outline-none focus-visible:ring-2 focus-visible:ring-primary
        ${isActive
          ? 'bg-deep-maroon text-white shadow-lg shadow-deep-maroon/25 scale-[1.01]'
          : 'bg-white text-black border border-gray-100 shadow-sm hover:shadow-md hover:border-primary/20'
        }`}
    >
      <h3 className={`font-manrope font-bold text-lg md:text-xl mb-3 ${isActive ? 'text-white' : 'text-black'}`}>
        {location.name}
      </h3>
      <div className={`flex items-start gap-2.5 font-inter text-sm leading-relaxed ${isActive ? 'text-white/85' : 'text-text-muted'}`}>
        <span className={isActive ? 'text-white' : 'text-primary'}>{pinIcon(isActive)}</span>
        <span>{location.address || 'Address coming soon'}</span>
      </div>
    </button>
  )
}

export default function LocationsSection() {
  const { salon, locations } = useSalon()

  const locationGallery = [
  assetUrl('assets/Rectangle 58.png'),
  assetUrl('assets/Rectangle 59.png'),
  assetUrl('assets/Rectangle 60.png'),
]

  const locationList = useMemo(() => {
    if (locations?.length) return locations
    if (!salon?.full_address && !salon?.name) return []
    return [{
      id: salon.id,
      name: salon.name,
      address: salon.full_address,
      is_current: true,
      map_embed_url: null,
      opening_hours_lines: salon.opening_hours_lines ?? [],
      photos: [],
    }]
  }, [locations, salon])

  const [activeId, setActiveId] = useState(null)

  useEffect(() => {
    if (locationList.length === 0) {
      setActiveId(null)
      return
    }
    const current = locationList.find((l) => l.is_current)?.id ?? locationList[0].id
    if (activeId === null || !locationList.some((l) => l.id === activeId)) {
      setActiveId(current)
    }
  }, [locationList, activeId])

  const activeLocation = locationList.find((l) => l.id === activeId) ?? locationList[0] ?? null

  if (!salon || locationList.length === 0 || !activeLocation) return null

  const hourLines = activeLocation.opening_hours_lines?.length
    ? activeLocation.opening_hours_lines
    : (salon.opening_hours_lines?.length ? salon.opening_hours_lines : ['Mon – Fri: 9:00 AM – 9:00 PM', 'Sat – Sun: 10:00 AM – 7:00 PM'])

  const galleryImages = locationGallery

  const mapSrc = activeLocation.map_embed_url
    || (activeLocation.address
      ? `https://www.google.com/maps?q=${encodeURIComponent(activeLocation.address)}&z=15&output=embed`
      : null)

  return (
    <section id="locations" className="w-full bg-white py-20 lg:py-24">
      <div className="max-w-[1360px] mx-auto px-4">
        <div className="text-center mb-12 md:mb-16">
          <span className="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">
            Locations
          </span>
          <h2 className="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black tracking-tight">
            Locate Your Nearest Store
          </h2>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-[30px] lg:items-stretch">
          {/* Left — location list */}
          <div className="lg:col-span-3 flex flex-col gap-4 md:gap-5">
            {locationList.map((loc) => (
              <LocationCard
                key={loc.id}
                location={loc}
                isActive={loc.id === activeId}
                onSelect={setActiveId}
              />
            ))}
          </div>

          {/* Center — map, hours, CTA */}
          <div className="lg:col-span-5 flex flex-col gap-5 lg:min-h-[548px]">
            <div className="rounded-2xl overflow-hidden border border-gray-100 shadow-sm bg-section-light shrink-0">
              {mapSrc ? (
                <iframe
                  title={`Map — ${activeLocation.name}`}
                  src={mapSrc}
                  className="w-full h-[200px] md:h-[240px] lg:h-[286px] border-0"
                  loading="lazy"
                  referrerPolicy="no-referrer-when-downgrade"
                  allowFullScreen
                />
              ) : (
                <div className="w-full h-[200px] md:h-[240px] lg:h-[286px] flex items-center justify-center text-text-muted text-sm px-6 text-center">
                  Map unavailable for this location
                </div>
              )}
            </div>

            <div>
              <h3 className="font-manrope font-bold text-xl md:text-2xl text-black mb-4">
                Opening Hours
              </h3>
              <div className="flex flex-col gap-3">
                {hourLines.map((line) => (
                  <div
                    key={line}
                    className="inline-flex items-center gap-3 w-fit max-w-full bg-[#FFEFEF] text-black font-inter text-sm md:text-base rounded-full px-5 py-3"
                  >
                    <span className="text-primary shrink-0">{clockIcon}</span>
                    <span>{line}</span>
                  </div>
                ))}
              </div>
            </div>

            <BookButton
              className="inline-flex items-center justify-center gap-3 w-full bg-primary hover:bg-primary-dark text-white font-manrope font-bold text-sm md:text-base uppercase tracking-wider rounded-full px-8 py-4 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] shadow-md hover:shadow-primary/25 outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >
              Book Your Transformation
              {calendarIcon}
            </BookButton>
          </div>

          {/* Right — gallery (3 equal landscape tiles, reference size) */}
          <div className="lg:col-span-4 flex flex-col gap-5 lg:min-h-[548px]">
            {galleryImages.map((src, index) => (
              <div
                key={`${src}-${index}`}
                className="rounded-2xl overflow-hidden shadow-md bg-section-light w-full aspect-[11/5] lg:aspect-auto lg:flex-1 lg:min-h-0"
              >
                <img
                  src={src}
                  alt={`${activeLocation.name} interior ${index + 1}`}
                  className="w-full h-full object-cover object-center transition-transform duration-700 hover:scale-105"
                  loading="lazy"
                  onError={(e) => {
                    const fallback = locationGallery[index % locationGallery.length]
                    if (e.currentTarget.src !== fallback) {
                      e.currentTarget.src = fallback
                    }
                  }}
                />
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  )
}
