import { useEffect, useRef, useState } from 'react'

export default function HorizontalDragScroll({ children, className = '', gapClass = 'gap-6', ariaLabel = 'Scrollable row' }) {
  const trackRef = useRef(null)
  const isDragging = useRef(false)
  const startX = useRef(0)
  const scrollStart = useRef(0)
  const [grabbing, setGrabbing] = useState(false)

  const endDrag = () => {
    isDragging.current = false
    setGrabbing(false)
  }

  const onMouseDown = (e) => {
    if (e.button !== 0 || !trackRef.current) return
    isDragging.current = true
    startX.current = e.pageX
    scrollStart.current = trackRef.current.scrollLeft
    setGrabbing(true)
  }

  const onMouseMove = (e) => {
    if (!isDragging.current || !trackRef.current) return
    const delta = e.pageX - startX.current
    trackRef.current.scrollLeft = scrollStart.current - delta
  }

  const onWheel = (e) => {
    const el = trackRef.current
    if (!el || el.scrollWidth <= el.clientWidth) return
    if (Math.abs(e.deltaY) <= Math.abs(e.deltaX)) return
    el.scrollLeft += e.deltaY
    e.preventDefault()
  }

  useEffect(() => {
    if (!grabbing) return
    const stop = () => endDrag()
    document.addEventListener('mouseup', stop)
    return () => document.removeEventListener('mouseup', stop)
  }, [grabbing])

  return (
    <div className={`w-full min-w-0 ${className}`}>
      <div
        ref={trackRef}
        role="region"
        aria-label={ariaLabel}
        onMouseDown={onMouseDown}
        onMouseLeave={endDrag}
        onMouseUp={endDrag}
        onMouseMove={onMouseMove}
        onWheel={onWheel}
        className={`flex items-stretch ${gapClass} overflow-x-auto scrollbar-none scroll-smooth snap-x snap-mandatory py-2 w-full min-w-0 touch-pan-x select-none
          ${grabbing ? 'cursor-grabbing snap-none' : 'cursor-grab'}`}
      >
        {children}
      </div>
    </div>
  )
}
