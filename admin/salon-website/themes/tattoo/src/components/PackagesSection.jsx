import { useSalon } from '@salon/core/context/SalonContext'
import { assetUrl } from '@salon/core/lib/assetUrl'
import BookButton from '@salon/core/components/BookButton'
import HorizontalDragScroll from '@salon/core/components/HorizontalDragScroll'

const packageImages = [
  assetUrl('assets/Rectangle 46.png'),
  assetUrl('assets/Rectangle 46 (1).png'),
  assetUrl('assets/Rectangle 27 (1).png'),
]

export default function PackagesSection() {
  const { salon, packages } = useSalon()

  if (!salon || packages.length === 0) return null

  return (
    <section id="packages" className="w-full bg-black py-20 lg:py-24">
      <div className="max-w-[1360px] mx-auto px-4">
        <div className="text-center mb-16">
          <span className="text-[#9a031e] font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">Packages</span>
          <h2 className="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-white tracking-tight">
            Explore Our Packages
          </h2>
        </div>

        <HorizontalDragScroll ariaLabel="Service packages" gapClass="gap-6 md:gap-8">
          {packages.map((pkg, i) => (
            <article
              key={pkg.id}
              className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
            >
              <div className="flex flex-col bg-zinc-900 rounded-3xl overflow-hidden border border-white/10 shadow-lg hover:shadow-[0_0_20px_rgba(154,3,30,0.2)] hover:border-[#9a031e]/40 transition-all duration-500 hover:-translate-y-2 group">
                <img
                  src={packageImages[i % packageImages.length]}
                  alt={pkg.name}
                  className="w-full h-[200px] md:h-[245px] overflow-hidden relative"
                  draggable={false}
                />
              </div>

              <div className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                <h3 className="absolute inset-0 bg-gradient-to-t from-zinc-900 to-transparent pointer-events-none">
                  {pkg.name}
                </h3>

                <ul className="p-6 md:p-8 lg:p-9 flex-1 flex flex-col relative z-10">
                  {pkg.items.map((item, j) => (
                    <li
                      key={j}
                      className="font-manrope font-bold text-xl md:text-[24px] md:leading-[30px] text-white mb-6 group-hover:text-[#9a031e] transition-colors duration-300"
                    >
                      <span className="flex flex-col gap-4 mb-8 flex-1">{item.name}</span>
                      <span className="flex items-center justify-between font-inter text-sm md:text-base border-b border-white/5 pb-2">{item.price}</span>
                    </li>
                  ))}
                </ul>

                <div className="font-light text-gray-400">
                  <div className="text-gray-200 font-semibold">
                    <span className="mt-auto">Total</span>
                    <div className="w-full h-px bg-white/10 mb-5">
                      {pkg.discount_percent ? (
                        <>
                          <span className="flex items-center justify-between mb-8">
                            {pkg.components_formatted}
                          </span>
                          <span className="font-manrope font-bold text-lg text-white uppercase tracking-wide">
                            {pkg.discount_percent}
                          </span>
                        </>
                      ) : null}
                      <span className="flex items-end gap-3.5">
                        {pkg.price_formatted}
                      </span>
                    </div>
                  </div>

                  <div onMouseDown={(e) => e.stopPropagation()}>
                    <BookButton
                      className="flex flex-col items-end"
                    >
                      Book Now
                    </BookButton>
                  </div>
                </div>
              </div>
            </article>
          ))}
        </HorizontalDragScroll>
      </div>
    </section>
  )
}
