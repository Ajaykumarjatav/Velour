import { assetUrl } from '@salon/core/lib/assetUrl'
import { useSalon } from '@salon/core/context/SalonContext'

const galleryImageFiles = [
  { file: 'Rectangle 31.png', alt: 'Salon interior 1' },
  { file: 'Rectangle 27.png', alt: 'Salon service 2' },
  { file: 'Rectangle 28.png', alt: 'Hair styling 3' },
  { file: 'Rectangle 30.png', alt: 'Grooming 4' },
  { file: 'Rectangle 32.png', alt: 'Salon ambience 5' },
  { file: 'Rectangle 29.png', alt: 'Professional care 6' },
]

export default function AboutSection() {
  const { salon } = useSalon()
  if (!salon) return null

  const galleryImages = galleryImageFiles.map((img) => ({
    src: assetUrl(`assets/${img.file}`),
    alt: img.alt,
  }))

  return (
    <section
      id="about"
      // Removed the #F8F8F8 background to maintain the dark theme
      className="w-full bg-black relative overflow-hidden"
    >
      {/* Main Content Area */}
      <div className="relative w-full bg-black py-20 overflow-hidden">
        <div className="max-w-[1360px] mx-auto px-4 relative z-20">
          {/* Content Wrapper */}
          <div className="w-full lg:w-[55%] relative z-20 mx-auto text-center lg:text-left">
            {/* Subtitle */}
            <span className="text-[#9a031e] font-manrope font-semibold text-sm uppercase tracking-widest mb-3 block">
              Who we are
            </span>

            {/* Heading */}
            <h2 className="font-manrope font-extrabold text-4xl md:text-5xl lg:text-[60px] lg:leading-[69px] text-white mb-6 tracking-tight">
              Where Art{" "}
              {/* Changed text-deep-maroon to your specific hex code */}
              <span className="text-[#9a031e] font-pacifico font-normal lowercase tracking-normal">
                Meets
              </span>{" "}
              Identity.
            </h2>

            {/* Description */}
            <p className="text-gray-400 font-inter font-light text-base md:text-lg leading-relaxed mb-12 max-w-[777px] mx-auto lg:mx-0">
              At our tattoo studio, every design tells a story. We specialize in
              creating meaningful, custom tattoos in a safe, creative, and
              welcoming environment. From minimal fine-line work to bold
              statement pieces, our artists blend precision, artistry, and
              passion to craft tattoos that become a lifelong part of your
              journey.
            </p>

            {/* Stats */}
            <div className="flex items-center justify-center lg:justify-start gap-8 md:gap-14">
              {/* Founded */}
              <div className="flex flex-col items-center text-center gap-1.5">
                <span className="font-manrope font-bold text-5xl md:text-7xl lg:text-[85px] lg:leading-[90px] text-[#9a031e] tracking-tight">
                  2015
                </span>
                {/* Changed text-black to text-gray-300 so it shows up on dark background */}
                <span className="font-manrope font-semibold text-xs md:text-sm text-gray-300 uppercase tracking-wider">
                  Founded
                </span>
              </div>

              {/* Divider - Fixed typo and changed to a subtle dark-mode border color */}
              <div className="w-px h-[90px] md:h-[110px] bg-white/10"></div>

              {/* Customers */}
              <div className="flex flex-col items-center text-center gap-1.5">
                <span className="font-manrope font-bold text-5xl md:text-7xl lg:text-[85px] lg:leading-[90px] text-[#9a031e] tracking-tight">
                  300+
                </span>
                {/* Changed text-black to text-gray-300 */}
                <span className="font-manrope font-semibold text-xs md:text-sm text-gray-300 uppercase tracking-wider">
                  Trusted Clients
                </span>
              </div>
            </div>
          </div>
        </div>

        {/* Left Decorative Image */}
        <div className="absolute -left-0 top-0 w-[380px] h-[680px] pointer-events-none opacity-30 lg:opacity-60 transition-opacity">
          <img
            src={assetUrl('assets/tatto-removebg-preview 1.png')}
            alt=""
            className="w-full h-full object-contain"
            aria-hidden="true"
          />
        </div>

        {/* Right Decorative Image */}
        <div className="absolute -right-0 top-0 w-[380px] h-[680px] pointer-events-none opacity-30 lg:opacity-60 transition-opacity">
          <img
            src={assetUrl('assets/tatto-removebg-preview 2.png')}
            alt=""
            className="w-full h-full object-contain"
            aria-hidden="true"
          />
        </div>
      </div>

      {/* Gallery Strip with Snapping on Mobile & Hover Scale */}
      <div className="w-full overflow-hidden relative z-10 py-10 bg-black">
        <div className="max-w-[1360px] mx-auto px-4">
          <div className="flex justify-start md:justify-center items-center gap-5 overflow-x-auto scrollbar-none snap-x snap-mandatory pb-4">
            {galleryImages.map((img, i) => (
              <div
                key={i}
                // Replaced standard shadow with a subtle red accent shadow on hover
                className="flex-shrink-0 w-[280px] sm:w-[300px] lg:w-[327px] h-[220px] md:h-[280px] lg:h-[345px] overflow-hidden rounded-2xl shadow-lg border border-white/5 hover:border-[#9a031e]/30 hover:shadow-[0_0_20px_rgba(154,3,30,0.3)] transition-all duration-500 snap-center group cursor-pointer"
              >
                <img
                  src={img.src}
                  alt={img.alt}
                  className="w-full h-full object-cover group-hover:scale-110 transition duration-700 ease-out"
                />
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
