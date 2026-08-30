import { assetUrl } from '@salon/core/lib/assetUrl'

const amenitiesRow1 = [
  { iconFile: 'noun-spa-8374759 1.png', title: 'Relaxing Atmosphere', desc: 'A calming environment created to make every visit peaceful.' },
  { iconFile: 'noun-spa-8374759 2.png', title: 'Premium Products', desc: 'Luxury beauty products for flawless results.' },
  { iconFile: 'noun-spa-8374759 3.png', title: ' Professional Lighting', desc: 'Perfect lighting for flawless makeup application.' },
  { iconFile: 'noun-spa-8374759 1 (1).png', title: 'Organic Brew Bar', desc: 'Freshly brewed artisan coffee or herbal teas while you wait.' },
]

const amenitiesRow2 = [
  { iconFile: 'noun-spa-8374759 1 (2).png', title: 'Ultra-Sanitized Tools', desc: 'Hospital-grade sterilization for every tool.' },
  { iconFile: 'noun-spa-8374759 1 (3).png', title: 'High-Speed Wi-Fi', desc: 'Stay connected or work comfortably during treatment.' },
  { iconFile: 'noun-spa-8374759 1 (4).png', title: 'Charging Stations', desc: 'Individual charging ports at every station for your devices.' },
  { iconFile: 'noun-spa-8374759 1 (5).png', title: 'Premium Silk Wraps', desc: 'Tailored experiences crafted around your style and comfort.' },
]

function AmenityCard({ iconFile, title, desc }) {
  return (
    <div className="flex flex-col items-center text-center p-6 md:p-8 w-full bg-white rounded-2xl border border-gray-100/80 shadow-xs hover:shadow-md transition-all duration-300 hover:-translate-y-1 group cursor-default">
      <div className="w-[60px] h-[60px] md:w-[70px] md:h-[70px] mb-5 flex items-center justify-center bg-icon-circle rounded-full transition-all duration-300 group-hover:scale-110 group-hover:bg-primary/10">
        <img
          src={assetUrl(`assets/${iconFile}`)}
          alt={title}
          className="w-[30px] h-[30px] md:w-[35px] md:h-[35px] object-contain"
        />
      </div>
      <h3 className="font-manrope font-bold text-base md:text-lg text-black mb-2">{title}</h3>
      <p className="font-inter font-light text-xs md:text-sm text-text-muted leading-[20px] max-w-[210px]">
        {desc}
      </p>
    </div>
  )
}

export default function RelaxationSection() {
  return (
    <section id="amenities" className="w-full bg-[#F5ECE7] py-20 lg:py-24">
      <div className="max-w-[1360px] mx-auto px-4">
        <div className="text-center mb-14">
          <span className="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">
            Amenities
          </span>
          <h2 className="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black tracking-tight mb-4">
            Luxury Comforts For Your Beauty Experience
          </h2>
          <p className="text-text-muted font-inter font-light text-sm md:text-lg max-w-[700px] mx-auto leading-relaxed">
            Premium amenities designed to make every appointment relaxing, elegant, and comfortable.
          </p>
        </div>

        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6 mb-5 md:mb-6">
          {amenitiesRow1.map((item) => (
            <AmenityCard key={item.title} {...item} />
          ))}
        </div>
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 md:gap-6">
          {amenitiesRow2.map((item) => (
            <AmenityCard key={item.title} {...item} />
          ))}
        </div>
      </div>
    </section>
  )
}
