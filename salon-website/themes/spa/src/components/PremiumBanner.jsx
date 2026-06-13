import { assetUrl } from '@salon/core/lib/assetUrl'
import BookButton from '@salon/core/components/BookButton'

export default function PremiumBanner() {
  return (
    <section className="w-full bg-[#F8F8F8] py-16 lg:py-20">
      <div className="max-w-[1360px] mx-auto px-4">
        <div className="relative rounded-[32px] overflow-hidden bg-[#2a2a2a] min-h-[340px] md:min-h-[380px] flex items-center shadow-xl">
          {/* Background gradient + image */}
          <div className="absolute inset-0 z-[1] bg-gradient-to-r from-[#2a2a2a] via-[#2a2a2a]/55 to-[#2a2a2a]/40 lg:to-transparent"></div>
          <div className="absolute right-0 top-0 w-full md:w-[65%] h-full">
            <img src={assetUrl('assets/image 2.png')} alt="Premium grooming" className="w-full h-full object-cover opacity-30 lg:opacity-40" />
          </div>

          {/* Content */}
          <div className="relative z-10 flex flex-col lg:flex-row items-center justify-between w-full h-full px-6 sm:px-10 md:px-16 lg:px-20 py-12 md:py-16 gap-10">
            {/* Left text */}
            <div className="max-w-[550px] text-center lg:text-left flex flex-col items-center lg:items-start">
              <span className="text-salmon font-manrope font-semibold text-sm uppercase tracking-widest mb-2 block">Book Now</span>
              <h3 className="font-manrope font-extrabold text-3xl sm:text-4xl lg:text-[50px] lg:leading-[55px] text-white mb-6 tracking-tight">
                Experience<br className="hidden lg:block" /> Premium Grooming.
              </h3>
              <p className="text-white/80 font-inter font-light text-base md:text-lg leading-relaxed max-w-[460px]">
                Luxury services tailored for every individual. Your seat in the stylist's chair is waiting for you.
              </p>
            </div>

            {/* Right booking card */}
            <BookButton
              className="bg-[#7f390B] hover:bg-primary-dark rounded-2xl p-8 md:p-10 flex flex-col items-center justify-center text-center w-full max-w-[320px] shadow-lg hover:shadow-primary/30 transition-all duration-300 hover:-translate-y-1.5 group outline-none focus-visible:ring-2 focus-visible:ring-white"
            >
              {/* Calendar icon */}
              <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="1.5" className="mb-5 transition-transform duration-500 group-hover:scale-110">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
                <rect x="7" y="13" width="3" height="3" rx="0.5" fill="white" className="opacity-0 group-hover:opacity-100 transition-opacity duration-300"/>
                <rect x="14" y="13" width="3" height="3" rx="0.5" fill="white" className="opacity-0 group-hover:opacity-100 transition-opacity duration-300"/>
                <rect x="7" y="17" width="3" height="3" rx="0.5" fill="white" className="opacity-0 group-hover:opacity-100 transition-opacity duration-300"/>
                <rect x="14" y="17" width="3" height="3" rx="0.5" fill="white" className="opacity-0 group-hover:opacity-100 transition-opacity duration-300"/>
              </svg>
              <h4 className="font-manrope font-bold text-xl md:text-2xl text-white uppercase tracking-wider leading-tight">
                Book Your<br />Appointment
              </h4>
            </BookButton>
          </div>
        </div>
      </div>
    </section>
  )
}
