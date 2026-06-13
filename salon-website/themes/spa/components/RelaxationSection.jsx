import { assetUrl } from '@salon/core/lib/assetUrl'

const amenitiesRow1 = [
  {
    iconFile: 'noun-spa-8374759 1.png',
    title: "Relaxing Atmosphere",
    desc: "A calming environment created to make every visit peaceful.",
  },
  {
    iconFile: 'noun-shampoo-708132 1.png',
    title: "Skin-Friendly Products",
    desc: "Premium natural products gentle on every skin type.",
  },
  {
    iconFile: 'noun-spa-8374759 2.png',
    title: "Acupressure",
    desc: "A soothing massage to improve blood flow and relieve stress.",
  },
  {
    iconFile: 'noun-spa-8374759 1 (1).png',
    title: "Organic Brew Bar",
    desc: "Freshly brewed artisan coffee or herbal teas while you wait.",
  },
];

const amenitiesRow2 = [
  {
    iconFile: 'noun-spa-8374759 1 (2).png',
    title: "Ultra-Sanitized Tools",
    desc: "Hospital-grade sterilization for every tool.",
  },
  {
    iconFile: 'noun-spa-8374759 1 (3).png',
    title: "High-Speed Wi-Fi",
    desc: "Stay connected or work comfortably during treatment.",
  },
  {
    iconFile: 'noun-spa-8374759 1 (4).png',
    title: "Charging Stations",
    desc: "Individual charging ports at every station for your devices.",
  },
  {
    iconFile: 'noun-spa-8374759 1 (5).png',
    title: "Private Rooms",
    desc: "Peaceful personal spaces designed for complete comfort.",
  },
];

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
      <h3 className="font-manrope font-bold text-base md:text-lg text-black mb-2">
        {title}
      </h3>
      <p className="font-inter font-light text-xs md:text-sm text-text-muted leading-[20px] max-w-[210px]">
        {desc}
      </p>
    </div>
  );
}

export default function RelaxationSection() {
  return (
    <section
      id="amenities"
      className="w-full bg-section-lighter py-20 lg:py-24"
    >
      <div className="max-w-[1360px] mx-auto px-4">
        {/* Header */}
        <div className="text-center mb-16">
          <span className="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">
            Amenities
          </span>
          <h2 className="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black mb-4 tracking-tight">
            Experience Pure Relaxation
          </h2>
          <p className="text-text-muted font-inter font-light text-sm md:text-lg max-w-[700px] mx-auto leading-relaxed">
            Beyond just a nail art ,we provide a sanctuary of comfort. Indulge
            in our curated world-class amenities designed to make every visit a
            refreshing escape.
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
  );
}
