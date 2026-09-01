import { useEffect, useState } from 'react'

const PLACEHOLDER = 'Your Logo'

export default function SalonLogo({
  logoUrl,
  salonName = '',
  imageClassName = 'h-8 md:h-9 w-auto max-w-[220px] object-contain',
  placeholderClassName = 'font-pacifico text-white text-2xl md:text-[30px] leading-[30px]',
}) {
  const [failed, setFailed] = useState(false)

  useEffect(() => {
    setFailed(false)
  }, [logoUrl])

  const showImage = Boolean(logoUrl) && !failed

  if (showImage) {
    return (
      <img
        src={logoUrl}
        alt={salonName ? `${salonName} logo` : 'Salon logo'}
        className={imageClassName}
        onError={() => setFailed(true)}
      />
    )
  }

  return <span className={placeholderClassName}>{PLACEHOLDER}</span>
}
