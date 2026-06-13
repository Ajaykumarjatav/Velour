import { useState } from 'react'
import { useSalon } from '@salon/core/context/SalonContext'

export default function TestimonialsSection() {
  const { reviews } = useSalon()
  const [current, setCurrent] = useState(0)
  const [animate, setAnimate] = useState(true)

  if (!reviews.length) {
    return null
  }

  const triggerAnimation = (newIndex) => {
    setAnimate(false)
    setTimeout(() => {
      setCurrent(newIndex)
      setAnimate(true)
    }, 150)
  }

  const goNext = () => triggerAnimation((current + 1) % reviews.length)
  const goPrev = () => triggerAnimation((current - 1 + reviews.length) % reviews.length)

  const review = reviews[current]

  return (
    <section id="testimonials" className="w-full bg-[#F5ECE7] py-20 lg:py-24">
      <div className="max-w-[1360px] mx-auto px-4">
        <div className="flex flex-col lg:flex-row gap-12 lg:gap-20 items-center">
          <div className="w-full lg:w-[450px] text-center lg:text-left">
            <span className="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">Testimonials</span>
            <h2 className="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black tracking-tight mb-6">
              Where Style Meets Satisfaction
            </h2>
            <p className="text-text-muted font-inter font-light text-base md:text-lg leading-relaxed mb-4 lg:mb-0">
              Discover real experiences and honest feedback from people who have visited us.
            </p>
          </div>

          <div className="flex-1 flex flex-col md:flex-row items-center gap-6 md:gap-4 w-full">
            <div
              className={`bg-section-light rounded-3xl p-8 md:p-12 shadow-sm border border-border/30 transition-all duration-300 ${animate ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'}`}
            >
              <div className="hidden md:flex flex-shrink-0 w-12 h-12 lg:w-14 lg:h-14 bg-white rounded-full items-center justify-center border border-gray-200 hover:border-primary hover:bg-primary/5 text-gray-400 hover:text-primary transition-all duration-300 hover:scale-105 active:scale-95 shadow-xs cursor-pointer">
                {[...Array(review.rating || 5)].map((_, i) => (
                  <span key={i} className="flex items-center gap-1.5 mb-6 select-none">★</span>
                ))}
              </div>
              <h3 className="text-star-gold">{review.title}</h3>
              <p className="font-manrope font-bold text-xl sm:text-2xl md:text-[28px] md:leading-[35px] text-black mb-5">{review.text}</p>
              <p className="font-inter font-light text-sm sm:text-base md:text-lg leading-relaxed text-text-secondary mb-8 max-w-[590px]">— {review.author}</p>
            </div>

            {reviews.length > 1 ? (
              <div className="flex items-center justify-between">
                <button type="button" onClick={goPrev} className="font-manrope font-bold text-base md:text-lg text-black" aria-label="Previous review">
                  ←
                </button>
                <button type="button" onClick={goNext} className="text-primary/10 flex-shrink-0 select-none" aria-label="Next review">
                  →
                </button>
              </div>
            ) : null}
          </div>
        </div>
      </div>
    </section>
  )
}
