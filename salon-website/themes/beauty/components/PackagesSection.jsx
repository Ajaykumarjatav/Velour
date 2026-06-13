import { useSalon } from '@salon/core/context/SalonContext'
import { assetUrl } from '@salon/core/lib/assetUrl'
import BookButton from '@salon/core/components/BookButton'
import HorizontalDragScroll from '@salon/core/components/HorizontalDragScroll'

const packageImages = [
  assetUrl('assets/Rectangle 48.png'),
  assetUrl('assets/Group 84.png'),
  assetUrl('assets/Rectangle 46.png'),
]

export default function PackagesSection() {
  const { salon, packages } = useSalon()

  if (!salon || packages.length === 0) return null

  return (
    <section id="packages" className="w-full bg-white py-20 lg:py-24 overflow-hidden">
      <div className="max-w-[1360px] mx-auto px-4 min-w-0">
        <div className="text-center mb-12 md:mb-16">
          <span className="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">Packages</span>
          <h2 className="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black tracking-tight">
            Explore Our Packages
          </h2>
        </div>

        <HorizontalDragScroll ariaLabel="Service packages" gapClass="gap-6 md:gap-8">
          {packages.map((pkg, i) => (
            <article
              key={pkg.id}
              className="shrink-0 snap-start w-[min(88vw,340px)] sm:w-[360px] lg:w-[400px] flex flex-col bg-white rounded-2xl overflow-hidden border border-gray-100 shadow-[0_4px_24px_rgba(0,0,0,0.06)]"
            >
              <div className="w-full h-[200px] md:h-[230px] overflow-hidden bg-section-light shrink-0">
                <img
                  src={packageImages[i % packageImages.length]}
                  alt={pkg.name}
                  className="w-full h-full object-cover"
                  draggable={false}
                />
              </div>

              <div className="p-6 md:p-7 flex flex-col flex-1">
                <h3 className="font-manrope font-bold text-xl md:text-2xl text-primary mb-5 leading-tight">
                  {pkg.name}
                </h3>

                <ul className="flex flex-col flex-1 mb-6">
                  {pkg.items.map((item, j) => (
                    <li
                      key={j}
                      className="flex items-center justify-between gap-4 py-3 border-b border-gray-100 last:border-b-0 font-inter text-sm md:text-[15px]"
                    >
                      <span className="text-text-secondary font-normal">{item.name}</span>
                      <span className="text-black font-semibold whitespace-nowrap shrink-0">{item.price}</span>
                    </li>
                  ))}
                </ul>

                <div className="mt-auto pt-2">
                  <div className="flex items-end justify-between gap-3 mb-6">
                    <span className="font-manrope font-bold text-sm md:text-base text-black uppercase tracking-wide">Total</span>
                    <div className="flex items-center gap-2 md:gap-3 flex-wrap justify-end">
                      {pkg.discount_percent ? (
                        <>
                          <span className="text-xs md:text-sm text-text-faded line-through font-inter">
                            {pkg.components_formatted}
                          </span>
                          <span className="text-[10px] md:text-[11px] font-bold text-primary bg-[#FFEFEF] px-2 py-1 rounded">
                            {pkg.discount_percent}
                          </span>
                        </>
                      ) : null}
                      <span className="font-manrope font-extrabold text-2xl md:text-[28px] text-black leading-none">
                        {pkg.price_formatted}
                      </span>
                    </div>
                  </div>

                  <div onMouseDown={(e) => e.stopPropagation()}>
                    <BookButton
                      className="block w-full text-center bg-primary hover:bg-primary-dark text-white font-manrope font-bold text-sm uppercase tracking-wider rounded-full py-3.5 md:py-4 transition-colors duration-300"
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
