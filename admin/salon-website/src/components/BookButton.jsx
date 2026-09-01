import { useSalon } from '../context/SalonContext'

/** Opens in-site booking — no login required */
export default function BookButton({ children, className = '', as: Tag = 'button' }) {
  const { openBooking } = useSalon()

  const handleClick = (e) => {
    e.preventDefault()
    openBooking()
  }

  if (Tag === 'a') {
    return (
      <a href="#book" onClick={handleClick} className={className}>
        {children}
      </a>
    )
  }

  return (
    <button type="button" onClick={handleClick} className={className}>
      {children}
    </button>
  )
}
