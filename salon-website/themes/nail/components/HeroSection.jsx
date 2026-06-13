import { useSalon } from '@salon/core/context/SalonContext'
import { assetUrl } from '@salon/core/lib/assetUrl'
import BookButton from '@salon/core/components/BookButton'

export default function HeroSection() {
  const { salon } = useSalon()
  if (!salon) return null

  const heroImage = salon.cover_image_url || assetUrl('assets/Rectangle 65.png')
  const ratingLabel =
    salon.avg_rating && salon.review_count
      ? `Rated ${salon.avg_rating} Stars · ${salon.review_count} reviews`
      : 'Rated 5 Stars by Clients'

  return (
    <section
      id="hero"
      className="relative w-full bg-black min-h-[500px] lg:min-h-[800px] xl:min-h-[900px] overflow-hidden"
    >
      <div className="absolute right-0 top-0 w-full lg:w-[60%] h-full opacity-50 lg:opacity-90">
        <img
          src={heroImage}
          alt={salon.name}
          className="w-full h-full object-cover object-center"
        />
        <div className="absolute inset-0 bg-gradient-to-r from-black via-black/70 lg:via-black/40 to-transparent pointer-events-none"></div>
      </div>

      <div className="absolute left-[-160px] top-[120px] w-[520px] h-[520px] rounded-full border border-[#D9D9D9] opacity-10 hidden lg:block"></div>
      <div className="absolute left-[-15px] top-[640px] w-[187px] h-[349px] border border-[#D9D9D9] rounded-br-[180px] opacity-10 hidden lg:block"></div>

      <div className="relative z-10 max-w-[1360px] mx-auto px-4 pt-16 md:pt-24 lg:pt-[80px] pb-24">
        <div className="max-w-[800px] text-center lg:text-left">
          <div
            className="inline-flex items-center gap-2.5 px-5 py-3.5 rounded-full mb-6 mx-auto lg:mx-0 hover:scale-105 transition-transform duration-300 cursor-default"
            style={{
              background:
                'linear-gradient(93deg, #161616 2.49%, #4A4A4A 96.02%, #000000 184.57%)',
              border: '0.72px solid #353535',
            }}
          >
            <div className="flex items-center gap-[2.9px]">
              {[...Array(5)].map((_, i) => (
                <svg key={i} width="16" height="16" viewBox="0 0 16 16" fill="none">
                  <path
                    d="M8 1L10.1633 5.38197L15 6.08442L11.5 9.49756L12.3267 14.3156L8 12.04L3.67335 14.3156L4.5 9.49756L1 6.08442L5.83668 5.38197L8 1Z"
                    fill="#FFC700"
                    stroke="#FFC700"
                    strokeWidth="1.1"
                    strokeLinejoin="round"
                  />
                </svg>
              ))}
            </div>
            <span className="text-white/90 font-manrope font-semibold text-xs md:text-sm tracking-wider">
              {ratingLabel}
            </span>
          </div>

          <h2 className="font-manrope font-extrabold text-4xl sm:text-6xl md:text-7xl lg:text-[75px] xl:text-[90px] leading-tight lg:leading-[90px] xl:leading-[100px] text-white mb-6 md:mb-8 tracking-tight">
            When Nails
            <br className="hidden sm:block" /> Becomes Art
          </h2>

          <p className="text-[#A5A5A5] font-inter font-light text-sm md:text-lg max-w-[500px] mb-8 md:mb-10 mx-auto lg:mx-0 leading-relaxed">
            Luxury nails create timeless design crafted to elevate your everyday style.
          </p>

          <div className="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-8 md:gap-12 mb-10 md:mb-12">
            {[1, 2, 3].map((_, i) => (
              <div
                key={i}
                className="flex flex-col items-center lg:items-start gap-3 group/item cursor-default"
              >
                <div className="w-[50px] h-[50px] rounded-full bg-white/5 border border-white/10 flex items-center justify-center transition-all duration-300 group-hover/item:bg-primary/20 group-hover/item:border-primary/50 group-hover/item:scale-105">
                  <img src={assetUrl('assets/nail-art 1.png')} alt="" />
                </div>
                <span className="text-white/80 font-inter font-light text-xs md:text-sm leading-relaxed text-center lg:text-left">
                  Consultations are
                  <br />
                  always free.
                </span>
              </div>
            ))}
          </div>

          <div className="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 md:gap-6 w-full sm:w-auto">
            <BookButton className="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 bg-[#b5556e] hover:bg-[#9a4860] text-white font-semibold text-sm md:text-base rounded-full px-8 py-4 transition-all duration-300 shadow-md hover:shadow-[#b5556e]/20 hover:scale-[1.02] active:scale-[0.98]">
              Book Your Transformation
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
              </svg>
            </BookButton>
            <a
              href={salon.whatsapp_url || '#'}
              target="_blank"
              rel="noopener noreferrer"
              style={salon.whatsapp_url ? undefined : { pointerEvents: 'none', opacity: 0.5 }}
              className="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 bg-[#1C1C1C] hover:bg-[#25D366]/10 text-white hover:text-[#25D366] font-semibold text-sm md:text-base rounded-full px-8 py-4 border border-[#353535] hover:border-[#25D366]/30 transition-all duration-300"
            >
              <span>WhatsApp</span>
            </a>
          </div>
        </div>
      </div>
    </section>
  )
}
