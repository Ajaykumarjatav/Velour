import { useEffect, useMemo, useRef, useState } from 'react'
import { useSalon } from '@salon/core/context/SalonContext'
import { assetUrl } from '@salon/core/lib/assetUrl'

function CategorySlider({ categories, activeCategoryId, onSelect }) {
  const trackRef = useRef(null)
  const isDragging = useRef(false)
  const didDrag = useRef(false)
  const startX = useRef(0)
  const scrollStart = useRef(0)
  const [grabbing, setGrabbing] = useState(false)

  const endDrag = () => {
    isDragging.current = false
    setGrabbing(false)
  }

  const onMouseDown = (e) => {
    if (e.button !== 0 || !trackRef.current) return
    isDragging.current = true
    didDrag.current = false
    startX.current = e.pageX
    scrollStart.current = trackRef.current.scrollLeft
    setGrabbing(true)
  }

  const onMouseMove = (e) => {
    if (!isDragging.current || !trackRef.current) return
    const delta = e.pageX - startX.current
    if (Math.abs(delta) > 4) didDrag.current = true
    trackRef.current.scrollLeft = scrollStart.current - delta
  }

  const onWheel = (e) => {
    const el = trackRef.current
    if (!el || el.scrollWidth <= el.clientWidth) return
    if (Math.abs(e.deltaY) <= Math.abs(e.deltaX)) return
    el.scrollLeft += e.deltaY
    e.preventDefault()
  }

  const handleSelect = (id) => {
    if (didDrag.current) return
    onSelect(id)
  }

  useEffect(() => {
    if (!grabbing) return
    const stop = () => endDrag()
    document.addEventListener('mouseup', stop)
    return () => document.removeEventListener('mouseup', stop)
  }, [grabbing])

  return (
    <div className="w-full bg-black py-20 lg:py-24">
      <div
        ref={trackRef}
        role="region"
        aria-label="Service categories"
        onMouseDown={onMouseDown}
        onMouseLeave={endDrag}
        onMouseUp={endDrag}
        onMouseMove={onMouseMove}
        onWheel={onWheel}
        className={`flex items-center gap-3 md:gap-4 overflow-x-auto scrollbar-none scroll-smooth snap-x snap-mandatory py-1 w-full min-w-0 touch-pan-x select-none
          ${grabbing ? 'cursor-grabbing snap-none' : 'cursor-grab'}`}
      >
        {categories.map((cat) => (
          <button
            key={cat.id}
            type="button"
            onClick={() => handleSelect(cat.id)}
            className={`shrink-0 snap-start px-5 md:px-7 py-2.5 rounded-full font-manrope font-semibold text-xs md:text-sm uppercase tracking-wider transition-all duration-300 outline-none focus-visible:ring-2 focus-visible:ring-primary whitespace-nowrap max-w-[min(100%,20rem)] truncate
              ${activeCategoryId === cat.id
                ? 'bg-primary text-white shadow-md shadow-primary/25'
                : 'bg-pill-inactive text-black/70 hover:bg-gray-200'
              }`}
            title={cat.name}
          >
            {cat.name}
          </button>
        ))}
      </div>
    </div>
  )
}


export default function ServicesSection() {
  const { salon, serviceCategories } = useSalon()
  const [activeCategoryId, setActiveCategoryId] = useState(null)

  const categories = useMemo(
    () => serviceCategories.filter((c) => (c.services?.length ?? 0) > 0),
    [serviceCategories],
  )

  useEffect(() => {
    if (categories.length === 0) {
      setActiveCategoryId(null)
      return
    }
    if (activeCategoryId === null || !categories.some((c) => c.id === activeCategoryId)) {
      setActiveCategoryId(categories[0].id)
    }
  }, [categories, activeCategoryId])

  const activeCategory = categories.find((c) => c.id === activeCategoryId) ?? categories[0] ?? null
  const services = activeCategory?.services ?? []

  if (!salon) return null

  return (
    <section id="services" className="max-w-[1360px] mx-auto px-4">
      <div className="flex flex-col md:flex-row md:items-end md:justify-between gap-6 mb-12">
        {/* Header */}
        <div className="text-[#9a031e] font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">
          <span className="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-white tracking-tight">Services</span>
          <h2 className="flex items-center gap-3 md:gap-4 overflow-x-auto scrollbar-none py-1">
            What we offer
          </h2>
        </div>

        {/* Category slider — full width row */}
        {categories.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-x-8 gap-y-2">
            <CategorySlider
              categories={categories}
              activeCategoryId={activeCategoryId}
              onSelect={setActiveCategoryId}
            />
          </div>
        ) : null}

        {/* Services for selected category */}
        {services.length > 0 ? (
          <div className="flex items-center justify-between py-5 px-3 rounded-xl border-b border-white/10 hover:bg-zinc-900 hover:shadow-md hover:border-transparent transition-all duration-300 gap-4 group/item cursor-default">
            {services.map((service) => (
              <div
                key={service.id}
                className="flex items-center gap-4"
              >
                <div className="w-[50px] h-[50px] md:w-[60px] md:h-[60px] flex-shrink-0 flex items-center justify-center bg-black border border-white/5 rounded-lg p-1.5 transition-all duration-300 group-hover/item:scale-105 group-hover/item:border-[#9a031e]/50 group-hover/item:shadow-[0_0_10px_rgba(154,3,30,0.2)]">
                  <div className="flex flex-col gap-1">
                    <img src={assetUrl('assets/noun-hair-cut-6384205 1.png')} alt="" className="w-full h-full object-contain" />
                  </div>
                  <div className="font-manrope font-bold text-base md:text-lg text-white transition-colors duration-300 group-hover/item:text-[#9a031e]">
                    <span className="font-inter font-normal text-xs md:text-sm text-gray-400">{service.name}</span>
                    <span className="font-manrope font-bold text-xl md:text-2xl text-white whitespace-nowrap">
                      {service.description || `${service.duration_minutes} min`}
                    </span>
                  </div>
                </div>
                <span className="transition-transform duration-300 group-hover:translate-x-1">
                  {service.price_formatted}
                </span>
              </div>
            ))}
          </div>
        ) : (
          <p className="group inline-flex items-center justify-center gap-2.5 bg-[#9a031e] hover:bg-[#7a0218] text-white font-manrope font-bold text-sm md:text-base uppercase tracking-wider rounded-full px-8 md:px-12 py-4 md:py-4.5 transition-all duration-300 shadow-md hover:shadow-[#9a031e]/40 hover:scale-[1.02] active:scale-[0.98] outline-none focus-visible:ring-2 focus-visible:ring-[#9a031e]">
            No services are listed for this salon yet.
          </p>
        )}

        {categories.length > 0 ? (
          <div className="flex justify-center mt-12">
            <a
              href="#services"
              className="inline-flex items-center justify-center gap-2 border-2 border-primary text-primary hover:bg-primary hover:text-white font-manrope font-semibold text-sm md:text-base rounded-full px-10 py-4 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] outline-none focus-visible:ring-2 focus-visible:ring-primary"
            >
              View All Services
            </a>
          </div>
        ) : null}
      </div>
    </section>
  )
}
