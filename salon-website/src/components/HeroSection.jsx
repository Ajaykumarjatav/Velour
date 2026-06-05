import { useSalon } from '../context/SalonContext'
import { assetUrl } from '../lib/assetUrl'
import BookButton from './BookButton'

export default function HeroSection() {
  const { salon } = useSalon()
  if (!salon) return null

  const heroImage = salon.cover_image_url || assetUrl('assets/26254 1.png')
  const ratingLabel =
    salon.avg_rating && salon.review_count
      ? `Rated ${salon.avg_rating} Stars · ${salon.review_count} reviews`
      : 'Rated 5 Stars by Clients'

  return (
    <section
      id="hero"
      className="relative w-full bg-black min-h-[500px] lg:min-h-[800px] xl:min-h-[900px] overflow-hidden"
    >
      {/* Background Image with Dark Mask */}
      <div className="absolute right-0 top-0 w-full lg:w-[60%] h-full opacity-50 lg:opacity-90">
        <img
          src={heroImage}
          alt={salon.name}
          className="w-full h-full object-cover object-center"
        />
        {/* Dark gradient mask to ensure text contrast */}
        <div className="absolute inset-0 bg-gradient-to-r from-black via-black/70 lg:via-black/40 to-transparent pointer-events-none"></div>
      </div>

      {/* Decorative circles */}
      <div className="absolute left-[-160px] top-[120px] w-[520px] h-[520px] rounded-full border border-[#D9D9D9] opacity-10 hidden lg:block"></div>
      <div className="absolute left-[-15px] top-[640px] w-[187px] h-[349px] border border-[#D9D9D9] rounded-br-[180px] opacity-10 hidden lg:block"></div>

      {/* Hero Content */}
      <div className="relative z-10 max-w-[1360px] mx-auto px-4 pt-16 md:pt-24 lg:pt-[80px] pb-24">
        <div className="max-w-[800px] text-center lg:text-left">
          {/* Rating Badge */}
          <div
            className="inline-flex items-center gap-2.5 px-5 py-3.5 rounded-full mb-6 mx-auto lg:mx-0 hover:scale-105 transition-transform duration-300 cursor-default"
            style={{
              background:
                "linear-gradient(93deg, #161616 2.49%, #4A4A4A 96.02%, #000000 184.57%)",
              border: "0.72px solid #353535",
            }}
          >
            <div className="flex items-center gap-[2.9px]">
              {[...Array(5)].map((_, i) => (
                <svg
                  key={i}
                  width="16"
                  height="16"
                  viewBox="0 0 16 16"
                  fill="none"
                >
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

          {/* Main Title — fixed marketing headline (salon name only in header logo area) */}
          <h2 className="font-manrope font-extrabold text-4xl sm:text-6xl md:text-7xl lg:text-[75px] xl:text-[90px] leading-tight lg:leading-[90px] xl:leading-[100px] text-white mb-6 md:mb-8 tracking-tight">
            Redefining Style for Every You.
          </h2>

          <p className="text-[#A5A5A5] font-inter font-light text-sm md:text-lg max-w-[500px] mb-8 md:mb-10 mx-auto lg:mx-0 leading-relaxed">
            Premium hair, skin, and grooming services tailored for all genders. Experience a new standard of self-care.
          </p>

          {/* Consultation Items */}
          <div className="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-8 md:gap-12 mb-10 md:mb-12">
            {[1, 2, 3].map((_, i) => (
              <div
                key={i}
                className="flex flex-col items-center lg:items-start gap-3 group/item cursor-default"
              >
                <div className="w-[50px] h-[50px] rounded-full bg-white/5 border border-white/10 flex items-center justify-center transition-all duration-300 group-hover/item:bg-primary/20 group-hover/item:border-primary/50 group-hover/item:scale-105">
                  <svg
                    width="24"
                    height="24"
                    viewBox="0 0 35 35"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                  >
                    <path
                      fillRule="evenodd"
                      clipRule="evenodd"
                      d="M28.2056 9.09709C28.5194 8.87727 28.8734 8.72141 29.2474 8.63842C29.6214 8.55544 30.0081 8.54695 30.3854 8.61344C30.7627 8.67994 31.1232 8.82011 31.4463 9.02596C31.7695 9.2318 32.0488 9.49929 32.2686 9.81313L21.2902 17.5L32.2686 25.1869C32.0488 25.5007 31.7695 25.7682 31.4463 25.9741C31.1232 26.1799 30.7627 26.3201 30.3854 26.3866C30.0081 26.4531 29.6214 26.4446 29.2474 26.3616C28.8734 26.2786 28.5194 26.1228 28.2056 25.9029L18.7469 19.2806L14.124 22.5181C14.6772 23.8255 14.7347 25.2898 14.2856 26.6366C13.8366 27.9833 12.9118 29.1201 11.6847 29.8339C10.4575 30.5476 9.01215 30.7894 7.61951 30.5138C6.22686 30.2382 4.98251 29.4643 4.11964 28.3369C3.25678 27.2096 2.83464 25.8063 2.93232 24.39C3.03001 22.9738 3.64082 21.6417 4.65028 20.6435C5.65975 19.6453 6.99858 19.0495 8.41587 18.9678C9.83316 18.886 11.2316 19.3239 12.3492 20.1994L16.205 17.5015L12.3477 14.8006C11.2302 15.6761 9.8317 16.114 8.41441 16.0322C6.99712 15.9505 5.65829 15.3547 4.64883 14.3565C3.63936 13.3583 3.02855 12.0263 2.93086 10.61C2.83318 9.1937 3.25532 7.7904 4.11819 6.66308C4.98105 5.53575 6.2254 4.76178 7.61805 4.48621C9.01069 4.21064 10.456 4.45239 11.6832 5.16614C12.9104 5.8799 13.8351 7.01668 14.2842 8.36343C14.7332 9.71019 14.6758 11.1745 14.1225 12.4819L18.7469 15.7194L28.2056 9.09709ZM11.6667 10.2083C11.6667 9.43479 11.3594 8.69293 10.8124 8.14595C10.2654 7.59896 9.52356 7.29167 8.75001 7.29167C7.97647 7.29167 7.2346 7.59896 6.68762 8.14595C6.14064 8.69293 5.83335 9.43479 5.83335 10.2083C5.83335 10.9819 6.14064 11.7238 6.68762 12.2707C7.2346 12.8177 7.97647 13.125 8.75001 13.125C9.52356 13.125 10.2654 12.8177 10.8124 12.2707C11.3594 11.7238 11.6667 10.9819 11.6667 10.2083ZM11.6667 24.7917C11.6667 24.0181 11.3594 23.2763 10.8124 22.7293C10.2654 22.1823 9.52356 21.875 8.75001 21.875C7.97647 21.875 7.2346 22.1823 6.68762 22.7293C6.14064 23.2763 5.83335 24.0181 5.83335 24.7917C5.83335 25.5652 6.14064 26.3071 6.68762 26.8541C7.2346 27.4011 7.97647 27.7083 8.75001 27.7083C9.52356 27.7083 10.2654 27.4011 10.8124 26.8541C11.3594 26.3071 11.6667 25.5652 11.6667 24.7917Z"
                      fill="currentColor"
                      className="text-white"
                    />
                  </svg>
                </div>

                <span className="text-white/80 font-inter font-light text-xs md:text-sm leading-relaxed text-center lg:text-left">
                  Consultations are
                  <br />
                  always free.
                </span>
              </div>
            ))}
          </div>

          {/* CTA Buttons */}
          <div className="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 md:gap-6 w-full sm:w-auto">
            <BookButton
              className="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 bg-primary hover:bg-primary-dark text-white font-semibold text-sm md:text-base rounded-full px-8 py-4 transition-all duration-300 shadow-md hover:shadow-primary/20 hover:scale-[1.02] active:scale-[0.98] outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 focus-visible:ring-offset-black"
            >
              Book Your Transformation
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
              >
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
              className="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 bg-[#1C1C1C] hover:bg-[#25D366]/10 text-white hover:text-[#25D366] font-semibold text-sm md:text-base rounded-full px-8 py-4 border border-[#353535] hover:border-[#25D366]/30 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] outline-none focus-visible:ring-2 focus-visible:ring-green-500 focus-visible:ring-offset-2 focus-visible:ring-offset-black"
            >
              <svg
                width="24"
                height="24"
                viewBox="0 0 30 30"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                className="w-6 h-6 flex-shrink-0"
              >
                <g clipPath="url(#clip0_90_1491)">
                  <path
                    d="M0.640234 14.8205C0.639531 17.3411 1.30328 19.8022 2.56539 21.9715L0.519531 29.3834L8.16391 27.3946C10.2782 28.5366 12.6472 29.1351 15.0545 29.1353H15.0609C23.0079 29.1353 29.477 22.7186 29.4804 14.8318C29.482 11.01 27.9834 7.4163 25.2606 4.71258C22.5384 2.0091 18.9179 0.519444 15.0603 0.5177C7.11227 0.5177 0.643633 6.93398 0.640352 14.8205"
                    fill="url(#paint0_linear_90_1491)"
                  />
                  <path
                    d="M0.125391 14.8158C0.12457 17.4271 0.812109 19.9763 2.11922 22.2233L0 29.9008L7.91848 27.8407C10.1003 29.021 12.5568 29.6434 15.0564 29.6443H15.0628C23.295 29.6443 29.9965 22.9969 30 14.8277C30.0014 10.8686 28.4489 7.1457 25.6289 4.34512C22.8086 1.54488 19.0586 0.00162791 15.0628 0C6.82922 0 0.128672 6.64651 0.125391 14.8158ZM4.84113 21.8363L4.54547 21.3706C3.30258 19.4097 2.64656 17.1436 2.6475 14.8167C2.65008 8.02663 8.2193 2.50233 15.0675 2.50233C18.3839 2.50372 21.5006 3.78651 23.8448 6.11395C26.1889 8.44163 27.478 11.5358 27.478 14.8267C27.475 21.6169 21.9056 27.1419 15.0628 27.1419H15.0579C12.8298 27.1407 10.6446 26.547 8.73891 25.425L8.28539 25.1581L3.58641 26.3806L4.84113 21.8363Z"
                    fill="url(#paint1_linear_90_1491)"
                  />
                  <path
                    d="M11.3295 8.62189C11.0499 8.00526 10.7557 7.99282 10.4898 7.982C10.272 7.9727 10.0231 7.9734 9.77446 7.9734C9.52556 7.9734 9.12114 8.06631 8.77931 8.43666C8.43712 8.80735 7.4729 9.70317 7.4729 11.5251C7.4729 13.3471 8.81036 15.1081 8.99681 15.3554C9.18349 15.6022 11.5788 19.4608 15.3724 20.9453C18.5252 22.1789 19.1668 21.9335 19.8511 21.8717C20.5354 21.81 22.0593 20.9761 22.3702 20.1113C22.6814 19.2467 22.6814 18.5055 22.5881 18.3506C22.4948 18.1963 22.2459 18.1036 21.8727 17.9185C21.4994 17.7334 19.6644 16.8374 19.3223 16.7137C18.9801 16.5903 18.7313 16.5286 18.4824 16.8994C18.2335 17.2697 17.5188 18.1036 17.3009 18.3506C17.0833 18.5982 16.8655 18.629 16.4924 18.4437C16.1189 18.2579 14.9169 17.8674 13.4908 16.6058C12.3813 15.6242 11.6322 14.412 11.4145 14.0412C11.1968 13.671 11.3912 13.4703 11.5783 13.2857C11.746 13.1198 11.9517 12.8533 12.1385 12.6371C12.3246 12.4208 12.3867 12.2665 12.5111 12.0196C12.6357 11.7724 12.5734 11.5561 12.4802 11.3708C12.3867 11.1856 11.6614 9.3541 11.3295 8.62189Z"
                    fill="white"
                  />
                </g>
                <defs>
                  <linearGradient
                    id="paint0_linear_90_1491"
                    x1="15"
                    y1="29.3834"
                    x2="15"
                    y2="0.5177"
                    gradientUnits="userSpaceOnUse"
                  >
                    <stop stopColor="#1FAF38" />
                    <stop offset="1" stopColor="#60D669" />
                  </linearGradient>
                  <linearGradient
                    id="paint1_linear_90_1491"
                    x1="15"
                    y1="29.9008"
                    x2="15"
                    y2="0"
                    gradientUnits="userSpaceOnUse"
                  >
                    <stop stopColor="#F9F9F9" />
                    <stop offset="1" stopColor="white" />
                  </linearGradient>
                  <clipPath id="clip0_90_1491">
                    <rect width="30" height="30" fill="white" />
                  </clipPath>
                </defs>
              </svg>
              <span>WhatsApp</span>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="20"
                height="20"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
              >
                <line x1="5" y1="12" x2="19" y2="12" />
                <polyline points="12 5 19 12 12 19" />
              </svg>
            </a>
          </div>
        </div>
      </div>
    </section>
  );
}
