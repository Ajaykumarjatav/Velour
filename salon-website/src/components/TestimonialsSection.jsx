import { useState } from 'react'
import { useSalon } from '../context/SalonContext'

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
    <section id="testimonials" className="w-full bg-white py-20 lg:py-24">
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

          <div className="w-full lg:flex-1 relative">
            <div
              className={`bg-section-light rounded-3xl p-8 md:p-12 shadow-sm border border-border/30 transition-all duration-300 ${animate ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-2'}`}
            >
              <div className="flex gap-1 mb-4">
                {[...Array(review.rating || 5)].map((_, i) => (
                  <span key={i} className="text-[#FFC700] text-lg">★</span>
                ))}
              </div>
              <h3 className="font-manrope font-bold text-xl md:text-2xl text-black mb-4">{review.title}</h3>
              <p className="text-text-secondary font-inter text-sm md:text-base leading-relaxed mb-6">{review.text}</p>
              <p className="font-manrope font-semibold text-black">— {review.author}</p>
            </div>

            {reviews.length > 1 ? (
              <div className="flex justify-center gap-4 mt-6">
                <button type="button" onClick={goPrev} className="w-10 h-10 rounded-full border border-border flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition-colors" aria-label="Previous review">
                  ←
                </button>
                <button type="button" onClick={goNext} className="w-10 h-10 rounded-full border border-border flex items-center justify-center hover:bg-primary hover:text-white hover:border-primary transition-colors" aria-label="Next review">
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
