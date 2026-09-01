import { useEffect } from 'react'
import { useSalon } from '../../context/SalonContext'

export default function AuthModal({ open, onClose, title, children, wide = false }) {
  const { salon } = useSalon()

  useEffect(() => {
    if (!open) return
    const prev = document.body.style.overflow
    document.body.style.overflow = 'hidden'
    const onKey = (e) => { if (e.key === 'Escape') onClose() }
    window.addEventListener('keydown', onKey)
    return () => {
      document.body.style.overflow = prev
      window.removeEventListener('keydown', onKey)
    }
  }, [open, onClose])

  if (!open) return null

  return (
    <div className="fixed inset-0 z-[300] flex items-center justify-center p-4" role="dialog" aria-modal="true">
      <button
        type="button"
        aria-label="Close"
        className="absolute inset-0 bg-black/75 backdrop-blur-[2px]"
        onClick={onClose}
      />
      <div
        className={`relative w-full bg-[#141820] border border-white/10 rounded-2xl shadow-2xl text-white overflow-hidden max-h-[90vh] flex flex-col ${
          wide ? 'max-w-xl sm:max-w-2xl' : 'max-w-md'
        }`}
      >
        <div className="px-6 sm:px-8 pt-6 pb-4 border-b border-white/10 flex items-start justify-between gap-4 shrink-0">
          <div>
            <p className="text-[10px] uppercase tracking-widest text-white/45 font-inter">{salon?.name}</p>
            <h2 className="font-manrope font-bold text-xl mt-1">{title}</h2>
          </div>
          <button
            type="button"
            onClick={onClose}
            className="text-white/50 hover:text-white text-sm shrink-0 px-2 py-1 rounded-lg hover:bg-white/5"
          >
            ✕
          </button>
        </div>
        <div className="px-6 sm:px-8 py-6 overflow-y-auto">{children}</div>
      </div>
    </div>
  )
}
