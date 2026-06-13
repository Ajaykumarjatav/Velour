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
      className="w-full h-full object-cover group-hover:scale-110 transition duration-700 ease-out"
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
    <section id="about" className="w-full bg-white relative overflow-hidden">
      {/* Main Content Area */}
      <div className="relative w-full bg-section-light py-20 overflow-hidden">
        <div className="max-w-[1360px] mx-auto px-4 relative z-25">
          {/* Content Wrapper */}
          <div className="w-full lg:w-[55%] relative z-20 mx-auto text-center lg:text-left">
            {/* Subtitle */}
            <span className="text-primary font-manrope font-semibold text-sm uppercase tracking-widest mb-3 block">
              Who we are
            </span>

            {/* Heading */}
            <h2 className="font-manrope font-extrabold text-4xl md:text-5xl lg:text-[60px] lg:leading-[69px] text-black mb-6 tracking-tight">
              Where <span className="text-deep-maroon font-pacifico font-normal lowercase tracking-normal">artistry</span> meets
              <br />
              the art of living.
            </h2>

            {/* Description — fixed marketing copy (not salon name from backend) */}
            <p className="text-text-secondary font-inter font-light text-base md:text-lg leading-relaxed mb-12 max-w-[777px] mx-auto lg:mx-0">
              Your Salon is more than a destination for hair; it is a sanctuary for self-care. We believe that true
              luxury lies in the details—from the initial consultation to the final sweep of the brush. Our master
              stylists specialize in bespoke color and restorative treatments, ensuring that every guest leaves feeling
              as vibrant as they look. Step in, exhale, and let us curate a look that is timelessly yours.
            </p>

            {/* Stats */}
            <div className="flex items-center justify-center lg:justify-start gap-8 md:gap-14">
              {/* Founded */}
              <div className="flex flex-col items-center text-center gap-1.5">
                <span className="font-manrope font-bold text-5xl md:text-7xl lg:text-[85px] lg:leading-[90px] text-deep-maroon tracking-tight">
                  2015
                </span>
                <span className="font-manrope font-semibold text-xs md:text-sm text-black uppercase tracking-wider">
                  Founded
                </span>
              </div>

              {/* Divider */}
              <div className="w-px h-[90px] md:h-[110px] bg-gray-200"></div>

              {/* Customers */}
              <div className="flex flex-col items-center text-center gap-1.5">
                <span className="font-manrope font-bold text-5xl md:text-7xl lg:text-[85px] lg:leading-[90px] text-deep-maroon tracking-tight">
                  300+
                </span>
                <span className="font-manrope font-semibold text-xs md:text-sm text-black uppercase tracking-wider">
                  Trusted Clients
                </span>
              </div>
            </div>
          </div>
        </div>

        {/* Left Decorative Image */}
        <div className="absolute -left-24 top-10 w-[380px] h-[680px] pointer-events-none opacity-30 lg:opacity-100 transition-opacity">
          <img
            src={assetUrl('assets/Group 23.png')}
            alt="Decoration Left"
            className="w-full h-full object-contain"
          />
        </div>

        {/* Right Decorative Image */}
        <div className="absolute -right-24 top-10 w-[380px] h-[680px] pointer-events-none opacity-30 lg:opacity-100 transition-opacity">
          <img
            src={assetUrl('assets/Group 25.png')}
            alt="Decoration Right"
            className="w-full h-full object-contain"
          />
        </div>
      </div>

      {/* Gallery Strip with Snapping on Mobile & Hover Scale */}
      <div className="w-full overflow-hidden relative z-10 py-10 bg-white">
        <div className="max-w-[1360px] mx-auto px-4">
          <div className="flex justify-start md:justify-center items-center gap-5 overflow-x-auto scrollbar-none snap-x snap-mandatory pb-4">
            {galleryImages.map((img, i) => (
              <div
                key={i}
                className="flex-shrink-0 w-[280px] sm:w-[300px] lg:w-[327px] h-[220px] md:h-[280px] lg:h-[345px] overflow-hidden rounded-2xl shadow-md hover:shadow-xl transition-all duration-500 snap-center group cursor-pointer"
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
