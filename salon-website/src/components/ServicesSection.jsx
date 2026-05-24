import { useMemo, useState } from 'react'
import { useSalon } from '../context/SalonContext'
import { assetUrl } from '../lib/assetUrl'

export default function ServicesSection() {
  const { salon, serviceCategories } = useSalon()
  const [activeCategory, setActiveCategory] = useState('')

  const categories = useMemo(() => {
    const names = serviceCategories.map((c) => c.name)
    if (names.length > 0) {
      return names.slice(0, 3)
    }
    return ['Haircut', 'Nail Art', 'Others']
  }, [serviceCategories])

  const services = useMemo(
    () => serviceCategories.flatMap((c) => c.services),
    [serviceCategories],
  )

  const displayCategory = activeCategory || categories[0] || 'Haircut'

  if (!salon) return null

  return (
    <section id="services" className="w-full bg-section-light py-20 lg:py-24">
      <div className="max-w-[1360px] mx-auto px-4">
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-12">
          <div>
            <span className="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">Services</span>
            <h2 className="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black tracking-tight">
              What we offer
            </h2>
          </div>

          {/* Category Pills */}
          <div className="flex items-center gap-3 md:gap-4 overflow-x-auto scrollbar-none py-1">
            {categories.map((cat) => (
              <button
                key={cat}
                type="button"
                onClick={() => setActiveCategory(cat)}
                className={`px-6 md:px-8 py-2.5 rounded-full font-manrope font-semibold text-xs md:text-sm uppercase tracking-wider transition-all duration-300 outline-none focus-visible:ring-2 focus-visible:ring-primary
                  ${displayCategory === cat
                    ? 'bg-primary text-white shadow-md shadow-primary/25'
                    : 'bg-pill-inactive text-black/70 hover:bg-gray-200'
                  }`}
              >
                {cat}
              </button>
            ))}
          </div>
        </div>

        {/* Services Grid — original layout: all services visible */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-2">
          {services.map((service) => (
            <div
              key={service.id}
              className="flex items-center justify-between py-5 px-3 rounded-xl border-b border-border/40 hover:bg-white hover:shadow-md hover:border-transparent transition-all duration-300 gap-4 group/item cursor-default"
            >
              <div className="flex items-center gap-4">
                <div className="w-[50px] h-[50px] md:w-[60px] md:h-[60px] flex-shrink-0 flex items-center justify-center bg-gray-50 rounded-lg p-1.5 transition-transform duration-300 group-hover/item:scale-105">
                  <img src={assetUrl('assets/noun-hair-cut-6384205 1.png')} alt="Haircut icon" className="w-full h-full object-contain" />
                </div>
                <div className="flex flex-col gap-1">
                  <span className="font-manrope font-bold text-base md:text-lg text-black transition-colors duration-300 group-hover/item:text-primary">{service.name}</span>
                  <span className="font-inter font-normal text-xs md:text-sm text-text-muted">
                    {service.description || `${service.duration_minutes} min`}
                  </span>
                </div>
              </div>
              <span className="font-manrope font-bold text-xl md:text-2xl text-black whitespace-nowrap">
                {service.price_formatted}
              </span>
            </div>
          ))}
        </div>

        {/* CTA */}
        <div className="flex justify-center mt-12">
          <a
            href="#services"
            className="inline-flex items-center justify-center gap-2 border-2 border-primary text-primary hover:bg-primary hover:text-white font-manrope font-semibold text-sm md:text-base rounded-full px-10 py-4 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] outline-none focus-visible:ring-2 focus-visible:ring-primary"
          >
            View All Services
          </a>
        </div>
      </div>
    </section>
  )
}
