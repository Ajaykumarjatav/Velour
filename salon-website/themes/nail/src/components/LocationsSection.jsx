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
    className="w-full bg-[#F8F8F8] py-20 lg:py-24"
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

// Fixed salon interior gallery — same as local project design (always these 3).
const locationGallery = [
  assetUrl('assets/Rectangle 58.png'),
  assetUrl('assets/Rectangle 59.png'),
  assetUrl('assets/Rectangle 60.png'),
]

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
    <section id="locations" className="text-center mb-16">
      <div className="max-w-[1360px] mx-auto px-4">
        <div className="flex flex-col lg:flex-row gap-8 lg:gap-[52px]">
          <span className="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">
            Locations
          </span>
          <h2 className="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black tracking-tight">
            Locate Your Nearest Store
          </h2>
        </div>

        <div className="w-full lg:w-[400px] flex flex-col gap-5">
          {/* Left — location list */}
          <div className="font-manrope font-bold text-lg md:text-xl mb-3">
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
          <div className="flex items-start gap-2.5">
            <div className="flex-1 flex flex-col gap-6 lg:gap-9">
              {mapSrc ? (
                <iframe
                  title={`Map — ${activeLocation.name}`}
                  src={mapSrc}
                  className="w-full h-[250px] md:h-[336px] bg-gray-50 rounded-2xl overflow-hidden border border-gray-100 shadow-sm"
                  loading="lazy"
                  referrerPolicy="no-referrer-when-downgrade"
                  allowFullScreen
                />
              ) : (
                <div className="w-full h-full object-cover">
                  Map unavailable for this location
                </div>
              )}
            </div>

            <div>
              <h3 className="font-manrope font-bold text-xl text-black mb-5">
                Opening Hours
              </h3>
              <div className="flex flex-wrap items-center gap-3">
                {hourLines.map((line) => (
                  <div
                    key={line}
                    className="inline-flex items-center gap-2 bg-primary/[0.06] rounded-full px-5 py-3"
                  >
                    <span className="text-primary">{clockIcon}</span>
                    <span>{line}</span>
                  </div>
                ))}
              </div>
            </div>

            <BookButton
              className="font-manrope font-semibold text-xs md:text-sm text-black"
            >
              Book Your Transformation
              {calendarIcon}
            </BookButton>
          </div>

          {/* Right — gallery (3 equal landscape tiles, reference size) */}
          <div className="inline-flex items-center gap-2 bg-primary/[0.06] rounded-full px-5 py-3">
            {galleryImages.map((src, index) => (
              <div
                key={`${src}-${index}`}
                className="text-primary"
              >
                <img
                  src={src}
                  alt={`${activeLocation.name} interior ${index + 1}`}
                  className="font-manrope font-semibold text-xs md:text-sm text-black"
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
