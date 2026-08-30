import { assetUrl } from '@salon/core/lib/assetUrl'

export default function PremiumBanner() {
  return (
    // Changed bg-[#F8F8F8] to bg-black to stay uniform with the dark layout
    <section className="w-full bg-black py-16 lg:py-20">
      <div className="max-w-[1360px] mx-auto px-4">
        {/* Changed bg to zinc-900, added an understated white/5 border and premium dark shadow */}
        <div className="relative rounded-[32px] overflow-hidden bg-zinc-900 border border-white/5 min-h-[340px] md:min-h-[380px] flex items-center shadow-2xl hover:border-[#9a031e]/30 transition-all duration-500">
          {/* Background gradient mask - updated hex variables to clean zinc utilities */}
          <div className="absolute inset-0 z-[1] bg-gradient-to-r from-zinc-900 via-zinc-900/80 to-zinc-900/40 lg:to-transparent pointer-events-none"></div>

          {/* Background image container */}
          <div className="absolute right-0 top-0 w-full md:w-[65%] h-full z-0">
            <img
              src={assetUrl('assets/image 2.png')}
              alt="Premium grooming"
              className="w-full h-full object-cover opacity-20 lg:opacity-35 mix-blend-luminosity"
            />
          </div>

          {/* Content */}
          <div className="relative z-10 flex flex-col lg:flex-row items-center justify-between w-full h-full px-6 sm:px-10 md:px-16 lg:px-20 py-12 md:py-16 gap-10">
            {/* Left text block */}
            <div className="max-w-[550px] text-center lg:text-left flex flex-col items-center lg:items-start">
              {/* Swapped out broken 'text-salmon' class for your custom accent red */}
              <span className="text-[#9a031e] font-manrope font-semibold text-sm uppercase tracking-widest mb-2 block">
                Book Now
              </span>
              <h3 className="font-manrope font-extrabold text-3xl sm:text-4xl lg:text-[50px] lg:leading-[55px] text-white mb-6 tracking-tight">
                You Deserve To Feel
                <br className="hidden lg:block" />
                Your Best
              </h3>
              {/* Changed text-white/80 to text-gray-400 for uniform reading visibility across sections */}
              <p className="text-gray-400 font-inter font-light text-base md:text-lg leading-relaxed max-w-[460px]">
                Escape the stress of everyday life and immerse yourself in a
                world of calm, comfort, and luxury wellness.
              </p>
            </div>

            {/* Right booking card */}
            <a
              href="#services"
              // Replaced broken hover:bg-primary-dark and hover:shadow classes with your signature brand palette
              className="bg-[#9a031e] hover:bg-[#7a0218] rounded-2xl p-8 md:p-10 flex flex-col items-center justify-center text-center w-full max-w-[320px] shadow-lg shadow-[#9a031e]/20 hover:shadow-[#9a031e]/40 transition-all duration-300 hover:-translate-y-1.5 group outline-none focus-visible:ring-2 focus-visible:ring-white"
            >
              {/* Calendar icon */}
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="64"
                height="64"
                viewBox="0 0 24 24"
                fill="none"
                stroke="white"
                strokeWidth="1.5"
                className="mb-5 transition-transform duration-500 group-hover:scale-105"
              >
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                <line x1="16" y1="2" x2="16" y2="6" />
                <line x1="8" y1="2" x2="8" y2="6" />
                <line x1="3" y1="10" x2="21" y2="10" />
                <rect
                  x="7"
                  y="13"
                  width="3"
                  height="3"
                  rx="0.5"
                  fill="white"
                  className="opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                />
                <rect
                  x="14"
                  y="13"
                  width="3"
                  height="3"
                  rx="0.5"
                  fill="white"
                  className="opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                />
                <rect
                  x="7"
                  y="17"
                  width="3"
                  height="3"
                  rx="0.5"
                  fill="white"
                  className="opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                />
                <rect
                  x="14"
                  y="17"
                  width="3"
                  height="3"
                  rx="0.5"
                  fill="white"
                  className="opacity-0 group-hover:opacity-100 transition-opacity duration-300"
                />
              </svg>
              <h4 className="font-manrope font-bold text-xl md:text-2xl text-white uppercase tracking-wider leading-tight">
                Book Your
                <br />
                Appointment
              </h4>
            </a>
          </div>
        </div>
      </div>
    </section>
  );
}
