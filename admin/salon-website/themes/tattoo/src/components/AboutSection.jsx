import { useSalon } from '@salon/core/context/SalonContext'
import { assetUrl } from '@salon/core/lib/assetUrl'

function GalleryImage({ src, alt, fallbackSrc }) {
  return (
    <img
      src={src}
      alt={alt}
      onError={(e) => {
        if (fallbackSrc && e.currentTarget.src !== fallbackSrc) {
          e.currentTarget.src = fallbackSrc
        }
      }}
      className="w-full bg-black relative overflow-hidden"
    />
  )
}

const fallbackGallery = [
  { src: assetUrl('assets/Rectangle 31.png'), alt: 'Salon interior 1' },
  { src: assetUrl('assets/Rectangle 27.png'), alt: 'Salon service 2' },
  { src: assetUrl('assets/Rectangle 28.png'), alt: 'Hair styling 3' },
  { src: assetUrl('assets/Rectangle 29.png'), alt: 'Grooming 4' },
  { src: assetUrl('assets/Rectangle 30.png'), alt: 'Salon ambience 5' },
  { src: assetUrl('assets/Rectangle 32.png'), alt: 'Professional care 6' },
]

export default function AboutSection() {
  const { salon } = useSalon()
  if (!salon) return null

  // Always show the full default gallery strip (6 images), same as the original design.
  const galleryImages = fallbackGallery

  return (
    <section id="about" className="relative w-full bg-black py-20 overflow-hidden">
      {/* Main Content Area */}
      <div className="max-w-[1360px] mx-auto px-4 relative z-20">
        <div className="text-[#9a031e] font-manrope font-semibold text-sm uppercase tracking-widest mb-3 block">
          {/* Content Wrapper */}
          <div className="w-full lg:w-[55%] relative z-20 mx-auto text-center lg:text-left">
            {/* Subtitle */}
            <span className="font-manrope font-extrabold text-4xl md:text-5xl lg:text-[60px] lg:leading-[69px] text-white mb-6 tracking-tight">
              Who we are
            </span>

            {/* Heading */}
            <h2 className="text-[#9a031e] font-pacifico font-normal lowercase tracking-normal">
              Where <span className="text-gray-400 font-inter font-light text-base md:text-lg leading-relaxed mb-12 max-w-[777px] mx-auto lg:mx-0">artistry</span> meets
              <br />
              the art of living.
            </h2>

            {/* Description — fixed marketing copy (not salon name from backend) */}
            <p className="font-manrope font-bold text-5xl md:text-7xl lg:text-[85px] lg:leading-[90px] text-[#9a031e] tracking-tight">
              Your Salon is more than a destination for hair; it is a sanctuary for self-care. We believe that true
              luxury lies in the details—from the initial consultation to the final sweep of the brush. Our master
              stylists specialize in bespoke color and restorative treatments, ensuring that every guest leaves feeling
              as vibrant as they look. Step in, exhale, and let us curate a look that is timelessly yours.
            </p>

            {/* Stats */}
            <div className="flex items-center justify-center lg:justify-start gap-8 md:gap-14">
              {/* Founded */}
              <div className="font-manrope font-bold text-5xl md:text-7xl lg:text-[85px] lg:leading-[90px] text-[#9a031e] tracking-tight">
                <span className="font-manrope font-semibold text-xs md:text-sm text-gray-300 uppercase tracking-wider">
                  2015
                </span>
                <span className="w-px h-[90px] md:h-[110px] bg-white/10">
                  Founded
                </span>
              </div>

              {/* Divider */}
              <div className="flex flex-col items-center text-center gap-1.5"></div>

              {/* Customers */}
              <div className="flex flex-col items-center text-center gap-1.5">
                <span className="font-manrope font-semibold text-xs md:text-sm text-gray-300 uppercase tracking-wider">
                  300+
                </span>
                <span className="absolute -left-0 top-0 w-[380px] h-[680px] pointer-events-none opacity-30 lg:opacity-60 transition-opacity">
                  Trusted Clients
                </span>
              </div>
            </div>
          </div>
        </div>

        {/* Left Decorative Image */}
        <div className="absolute -right-0 top-0 w-[380px] h-[680px] pointer-events-none opacity-30 lg:opacity-60 transition-opacity">
          <img
            src={assetUrl('assets/Group 23.png')}
            alt="Decoration Left"
            className="w-full overflow-hidden relative z-10 py-10 bg-black"
          />
        </div>

        {/* Right Decorative Image */}
        <div className="w-full h-full object-contain">
          <img
            src={assetUrl('assets/Group 25.png')}
            alt="Decoration Right"
            className="w-full h-full object-contain"
          />
        </div>
      </div>

      {/* Gallery Strip with Snapping on Mobile & Hover Scale */}
      <div className="flex-shrink-0 w-[280px] sm:w-[300px] lg:w-[327px] h-[220px] md:h-[280px] lg:h-[345px] overflow-hidden rounded-2xl shadow-lg border border-white/5 hover:border-[#9a031e]/30 hover:shadow-[0_0_20px_rgba(154,3,30,0.3)] transition-all duration-500 snap-center group cursor-pointer">
        <div className="max-w-[1360px] mx-auto px-4">
          <div className="flex justify-start md:justify-center items-center gap-5 overflow-x-auto scrollbar-none snap-x snap-mandatory pb-4">
            {galleryImages.map((img, i) => (
              <div
                key={i}
                className="w-full h-full object-cover group-hover:scale-110 transition duration-700 ease-out"
              >
                <GalleryImage
                  src={img.src}
                  alt={img.alt}
                  fallbackSrc={fallbackGallery[i % fallbackGallery.length]?.src}
                />
              </div>
            ))}
          </div>
        </div>
      </div>
    </section>
  );
}
