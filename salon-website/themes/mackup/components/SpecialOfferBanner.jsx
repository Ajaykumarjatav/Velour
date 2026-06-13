import { assetUrl } from '@salon/core/lib/assetUrl'

export default function SpecialOfferBanner() {
  return (
    <section className="w-full bg-[#F2EBE8] py-16 lg:py-20 overflow-hidden">
      <div className="max-w-[1360px] mx-auto px-4">
        <div className="relative rounded-[32px] overflow-hidden bg-deep-[#b7846a] min-h-[360px] md:min-h-[400px] flex items-center shadow-xl">
          {/* Background gradient overlay */}
          <div className="absolute inset-0 z-[1] bg-gradient-to-r from-deep-maroon via-deep-maroon/90 to-deep-maroon/40"></div>

          {/* Background image */}
          <div className="absolute right-0 top-0 w-full md:w-[70%] h-full">
            <img
              src={assetUrl('assets/Rectangle 111.png')}
              alt="Salon special offer"
              className="w-full h-full object-contain opacity-30"
            />
          </div>

          {/* Content */}
          <div className="relative z-10 flex flex-col lg:flex-row items-center justify-between w-full px-6 sm:px-10 md:px-16 py-12 lg:py-10 gap-10 lg:gap-8">
            <div className="max-w-[550px] text-center lg:text-left flex flex-col items-center lg:items-start">
              <span className="text-white/80 font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">
                Special Discount for
              </span>
              <h3 className="font-manrope font-extrabold text-3xl sm:text-4xl md:text-5xl text-white mb-4 tracking-tight">
                Special Person
              </h3>
              <p className="text-white/70 font-inter font-light text-sm sm:text-base mb-8 max-w-[460px] leading-relaxed">
                Luxury services tailored for every individual. Your seat in the
                stylist's chair is waiting for you.
              </p>
              <a
                href="#services"
                className="group inline-flex items-center gap-2.5 bg-white/10 hover:bg-white border border-white/20 hover:border-transparent text-white hover:text-deep-maroon font-manrope font-bold text-sm uppercase tracking-wider rounded-full px-8 py-4 transition-all duration-300 shadow-lg hover:shadow-white/10 active:scale-[0.98] outline-none focus-visible:ring-2 focus-visible:ring-white"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="18"
                  height="18"
                  viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  strokeWidth="2.5"
                  className="transition-transform duration-300 group-hover:scale-105"
                >
                  <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                  <line x1="16" y1="2" x2="16" y2="6" />
                  <line x1="8" y1="2" x2="8" y2="6" />
                  <line x1="3" y1="10" x2="21" y2="10" />
                </svg>
                Book Your Appointment
              </a>
            </div>

            {/* Circular Text Element for Offer */}
            <div className="relative flex items-center justify-center w-[200px] h-[200px] sm:w-[240px] sm:h-[240px] flex-shrink-0">
              <div className="absolute inset-0 rotate-infinite opacity-30 select-none pointer-events-none">
                <svg width="100%" height="100%" viewBox="0 0 200 200">
                  <path
                    id="offerCircle"
                    d="M 100, 100 m -80, 0 a 80,80 0 1,1 160,0 a 80,80 0 1,1 -160,0"
                    fill="none"
                  />
                  <text className="font-manrope font-bold uppercase text-[11px] tracking-[6px] fill-white">
                    <textPath href="#offerCircle">
                      SPECIAL SALE • SPECIAL SALE •{" "}
                    </textPath>
                  </text>
                </svg>
              </div>
              <div className="relative text-center z-10 select-none">
                <span className="font-manrope font-black text-6xl sm:text-7xl md:text-[85px] text-white leading-none tracking-tight">
                  30%
                </span>
                <span className="block font-manrope font-bold text-2xl sm:text-3xl text-white -mt-1 tracking-widest">
                  OFF
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
}
