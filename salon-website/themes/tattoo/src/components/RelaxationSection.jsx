import { assetUrl } from '@salon/core/lib/assetUrl'

const amenitiesRow1 = [
  {
    icon: assetUrl('assets/noun-spa-8374759 1.png'),
    title: 'Aromatic Steam',
    desc: 'Open your pores and relax your senses with herbal steam.',
  },
  {
    icon: assetUrl('assets/Vector.png'),
    title: 'Ergonomic Wash',
    desc: 'Lie back in fully reclining chairs designed for neck comfort.',
  },
  {
    icon: assetUrl('assets/Vector (1).png'),
    title: 'Scalp Acupressure',
    desc: 'A soothing massage to improve blood flow and relieve stress.',
  },
  {
    icon: assetUrl('assets/Vector (2).png'),
    title: 'Organic Brew Bar',
    desc: 'Freshly brewed artisan coffee or herbal teas while you wait.',
  },
]

const amenitiesRow2 = [
  {
    icon: assetUrl('assets/Group 65.png'),
    title: 'Ultra-Sanitized Tools',
    desc: 'Hospital-grade sterilization for every comb, clip, and brush.',
  },
  {
    icon: assetUrl('assets/Vector (3).png'),
    title: 'High-Speed Wi-Fi',
    desc: 'Stay connected or work comfortably during treatment.',
  },
  {
    icon: assetUrl('assets/Vector (4).png'),
    title: 'Charging Stations',
    desc: 'Individual charging ports at every station for your devices.',
  },
  {
    icon: assetUrl('assets/noun-face-mask-3511333 1.png'),
    title: 'Premium Silk Wraps',
    desc: 'We use only 100% pure silk and cotton for hair drying.',
  },
]

function AmenityCard({ icon, title, desc }) {
  return (
    <div className="flex flex-col items-center text-center p-6 md:p-8 w-full bg-gradient-to-b from-zinc-800/40 to-zinc-900 rounded-2xl border border-white/5 shadow-lg hover:shadow-[0_0_20px_rgba(154,3,30,0.15)] hover:border-[#9a031e]/30 transition-all duration-300 hover:-translate-y-1 group cursor-default">
      <div className="mb-6 flex items-center justify-center transition-transform duration-300 group-hover:scale-110">
        <img src={icon} alt={title} className="w-10 h-10 md:w-12 md:h-12 object-contain opacity-90 group-hover:opacity-100 transition-opacity" />
      </div>
      <h3 className="font-manrope font-semibold text-base md:text-lg text-white mb-2.5 transition-colors duration-300 group-hover:text-[#9a031e]">{title}</h3>
      <p className="font-inter font-light text-xs md:text-sm text-gray-400 leading-[22px]">{desc}</p>
    </div>
  )
}

export default function RelaxationSection() {
  return (
    <section id="amenities" className="w-full bg-black py-20 lg:py-24">
      <div className="max-w-[1360px] mx-auto px-4">
        {/* Header */}
        <div className="text-center mb-16">
          <span className="text-gray-300 font-manrope font-medium text-sm block mb-3">Amenities</span>
          <h2 className="font-serif font-bold text-3xl md:text-[45px] md:leading-[55px] text-white mb-6 tracking-wide uppercase">
            Experience Pure Relaxation
          </h2>
          <p className="text-gray-400 font-inter font-light text-sm md:text-base max-w-[750px] mx-auto leading-relaxed">
            Beyond the cut & colour, we provide a sanctuary of comfort. Indulge in our curated amenity menu
            designed to elevate every visit.
          </p>
        </div>

        {/* Grid for Amenities */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          {amenitiesRow1.map((item, i) => (
            <AmenityCard key={i} {...item} />
          ))}
          {amenitiesRow2.map((item, i) => (
            <AmenityCard key={i} {...item} />
          ))}
        </div>
      </div>
    </section>
  )
}
