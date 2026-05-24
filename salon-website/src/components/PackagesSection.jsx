import { useSalon } from '../context/SalonContext'
import { assetUrl } from '../lib/assetUrl'
import BookButton from './BookButton'

const packageImages = [
  assetUrl('assets/Rectangle 46.png'),
  assetUrl('assets/Rectangle 46 (1).png'),
  assetUrl('assets/Rectangle 27 (1).png'),
]

export default function PackagesSection() {
  const { salon, packages } = useSalon()

  if (!salon || packages.length === 0) return null

  return (
    <section id="packages" className="w-full bg-white py-20 lg:py-24">
      <div className="max-w-[1360px] mx-auto px-4">
        <div className="text-center mb-16">
          <span className="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">Packages</span>
          <h2 className="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black tracking-tight">
            Explore Our Packages
          </h2>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          {packages.map((pkg, i) => (
            <div key={pkg.id} className="flex flex-col bg-white rounded-3xl overflow-hidden border border-gray-100 shadow-md hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 group">
              <div className="w-full h-[200px] md:h-[245px] overflow-hidden relative bg-section-light">
                <img src={packageImages[i % packageImages.length]} alt={pkg.name} className="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" />
              </div>

              <div className="p-6 md:p-8 lg:p-9 flex-1 flex flex-col">
                <h3 className="font-manrope font-bold text-xl md:text-[24px] md:leading-[30px] text-black mb-6 group-hover:text-primary transition-colors duration-300">
                  {pkg.name}
                </h3>

                <div className="flex flex-col gap-4 mb-8 flex-1">
                  {pkg.items.map((item, j) => (
                    <div key={j} className="flex items-center justify-between font-inter text-sm md:text-base text-text-secondary border-b border-gray-50 pb-2">
                      <span className="font-light">{item.name}</span>
                      <span className="text-black font-semibold">{item.price}</span>
                    </div>
                  ))}
                </div>

                <div className="mt-auto">
                  <div className="w-full h-px bg-gray-100 mb-5"></div>
                  <div className="flex items-center justify-between mb-8">
                    <span className="font-manrope font-bold text-lg text-black uppercase tracking-wide">Total</span>
                    <div className="flex items-end gap-3.5">
                      {pkg.discount_percent ? (
                        <div className="flex flex-col items-end">
                          <span className="text-xs md:text-sm text-text-faded line-through">{pkg.components_formatted}</span>
                          <span className="bg-primary/10 text-primary font-manrope font-bold text-[10px] px-2 py-0.5 rounded-full mt-0.5">{pkg.discount_percent}</span>
                        </div>
                      ) : null}
                      <span className="font-manrope font-extrabold text-2xl md:text-3xl text-black leading-none">{pkg.price_formatted}</span>
                    </div>
                  </div>
                  <BookButton
                    className="block w-full text-center bg-primary hover:bg-primary-dark text-white font-manrope font-bold text-sm md:text-base uppercase tracking-wider rounded-full py-4 transition-all duration-300 hover:scale-[1.02]"
                  >
                    Book Now
                  </BookButton>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  )
}
