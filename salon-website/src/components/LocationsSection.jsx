import { useSalon } from '../context/SalonContext'

export default function LocationsSection() {
  const { salon } = useSalon()
  if (!salon?.full_address) return null

  const locations = [
    {
      name: salon.name,
      address: salon.full_address,
      featured: true,
    },
  ]

  return (
    <section id="locations" className="w-full bg-white py-20 lg:py-24">
      <div className="max-w-[1360px] mx-auto px-4">
        <div className="text-center mb-16">
          <span className="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">
            Location
          </span>
          <h2 className="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black tracking-tight">
            Visit Us
          </h2>
        </div>

        <div className="flex flex-col lg:flex-row gap-8 lg:gap-[52px]">
          <div className="w-full lg:w-[400px] flex flex-col gap-5">
            {locations.map((loc) => (
              <div
                key={loc.name}
                className="rounded-2xl p-6 md:p-8 bg-deep-maroon text-white border-transparent shadow-lg shadow-deep-maroon/20"
              >
                <h3 className="font-manrope font-bold text-xl mb-3">{loc.name}</h3>
                <p className="font-inter font-light text-sm md:text-base text-white/80 leading-relaxed">{loc.address}</p>
              </div>
            ))}
          </div>

          <div className="flex-1 min-h-[320px] rounded-3xl overflow-hidden bg-section-light border border-gray-100 flex items-center justify-center p-8">
            <p className="text-text-muted text-center text-sm">
              {salon.city ? `Find us in ${salon.city}` : 'We look forward to welcoming you.'}
            </p>
          </div>
        </div>
      </div>
    </section>
  )
}
