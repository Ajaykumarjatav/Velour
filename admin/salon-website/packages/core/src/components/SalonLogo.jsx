import { useEffect, useState } from 'react'
import { assetUrl } from '../lib/assetUrl'

const PLACEHOLDER = 'Your Logo'
const BRAND_NAME = 'EasyGrox'
const ICON_ASSET = 'assets/easygrox-icon.png'

const VARIANT_LAYOUT = {
  header: {
    wrap: 'inline-flex items-center gap-2.5 md:gap-3',
    icon: 'h-11 w-11 md:h-14 md:w-14 shrink-0 object-contain mix-blend-screen',
    text: 'font-bold text-[22px] md:text-[32px] leading-none tracking-tight text-white',
  },
  footer: {
    wrap: 'inline-flex items-center gap-2.5 md:gap-3',
    icon: 'h-10 w-10 md:h-12 md:w-12 shrink-0 object-contain mix-blend-screen',
    text: 'font-bold text-xl md:text-[28px] leading-none tracking-tight text-white',
  },
}

const VARIANT_STYLES = {
  header: 'h-12 md:h-14 w-auto max-w-[400px] min-w-[160px] object-contain object-left',
  footer: 'h-12 md:h-14 w-auto max-w-[320px] object-contain',
}

function EasyGroxBrandMark({ variant = 'header' }) {
  const layout = VARIANT_LAYOUT[variant] || VARIANT_LAYOUT.header
  const iconSrc = assetUrl(ICON_ASSET)

  return (
    <span className={layout.wrap} aria-label={`${BRAND_NAME} logo`}>
      <img src={iconSrc} alt="" className={layout.icon} />
      <span className={layout.text}>{BRAND_NAME}</span>
    </span>
  )
}

export default function SalonLogo({
  logoUrl,
  salonName = '',
  fallbackLogoUrl,
  variant = 'header',
  imageClassName,
  placeholderClassName = 'font-pacifico text-white text-2xl md:text-[30px] leading-[30px]',
}) {
  const [salonFailed, setSalonFailed] = useState(false)
  const [fallbackFailed, setFallbackFailed] = useState(false)
  const resolvedClassName = imageClassName || VARIANT_STYLES[variant] || VARIANT_STYLES.header
  const fallbackSrc = fallbackLogoUrl || null

  useEffect(() => {
    setSalonFailed(false)
    setFallbackFailed(false)
  }, [logoUrl, fallbackSrc])

  if (logoUrl && !salonFailed) {
    return (
      <img
        src={logoUrl}
        alt={salonName ? `${salonName} logo` : 'Salon logo'}
        className={resolvedClassName}
        onError={() => setSalonFailed(true)}
      />
    )
  }

  if (fallbackSrc && !fallbackFailed) {
    return (
      <img
        src={fallbackSrc}
        alt={salonName ? `${salonName} logo` : 'EasyGrox logo'}
        className={resolvedClassName}
        onError={() => setFallbackFailed(true)}
      />
    )
  }

  return <EasyGroxBrandMark variant={variant} />
}
