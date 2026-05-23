import { useSalon } from '../context/SalonContext'

/** Opens in-site booking (current black theme) — never redirects to /book/ */
export default function BookButton({ children, className = '', as: Tag = 'button' }) {
  const { openBooking } = useSalon()

  if (Tag === 'a') {
    return (
      <a
        href="#book"
        onClick={(e) => {
          e.preventDefault()
          openBooking()
        }}
        className={className}
      >
        {children}
      </a>
    )
  }

  return (
    <button type="button" onClick={openBooking} className={className}>
      {children}
    </button>
  )
}
