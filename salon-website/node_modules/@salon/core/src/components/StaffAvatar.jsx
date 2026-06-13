import { useEffect, useMemo, useState } from 'react'
import { resolveMediaUrl } from '../lib/resolveMediaUrl.js'

const DEFAULT_COLOR = '#7c3aed'

function normalizeColor(color) {
  if (!color || typeof color !== 'string') return DEFAULT_COLOR
  const value = color.trim().toLowerCase()
  if (!value || value === '#fff' || value === '#ffffff' || value === 'white' || value === 'transparent') {
    return DEFAULT_COLOR
  }
  return color.trim()
}

function isLightColor(hex) {
  const raw = hex.replace('#', '')
  if (!/^[0-9a-f]{6}$/i.test(raw)) return false
  const r = parseInt(raw.slice(0, 2), 16)
  const g = parseInt(raw.slice(2, 4), 16)
  const b = parseInt(raw.slice(4, 6), 16)
  const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255
  return luminance > 0.82
}

function avatarColor(member) {
  const color = normalizeColor(member?.color)
  return isLightColor(color) ? DEFAULT_COLOR : color
}

function avatarInitials(member) {
  if (member?.initials) return String(member.initials).slice(0, 2).toUpperCase()
  const name = String(member?.name || '').trim()
  if (!name) return '?'
  const parts = name.split(/\s+/).filter(Boolean)
  if (parts.length >= 2) {
    return `${parts[0].charAt(0)}${parts[1].charAt(0)}`.toUpperCase()
  }
  return name.charAt(0).toUpperCase()
}

export default function StaffAvatar({
  member,
  imageClassName = 'absolute inset-0 w-full h-full object-cover',
  initialsClassName = 'absolute inset-0 flex items-center justify-center text-3xl font-bold text-white',
}) {
  const [imgFailed, setImgFailed] = useState(false)
  const src = useMemo(() => resolveMediaUrl(member?.avatar_url), [member?.avatar_url])
  const showImage = Boolean(src) && !imgFailed
  const initials = avatarInitials(member)
  const background = avatarColor(member)

  useEffect(() => {
    setImgFailed(false)
  }, [src])

  return (
    <div className="relative w-full h-full">
      <span
        className={initialsClassName}
        style={{ backgroundColor: background }}
        aria-hidden={showImage ? true : undefined}
      >
        {initials}
      </span>
      {showImage ? (
        <img
          src={src}
          alt={member?.name ? `${member.name}` : 'Staff member'}
          className={imageClassName}
          draggable={false}
          loading="lazy"
          decoding="async"
          onError={() => setImgFailed(true)}
        />
      ) : null}
    </div>
  )
}
