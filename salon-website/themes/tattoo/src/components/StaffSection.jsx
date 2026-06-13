import { useSalon } from '@salon/core/context/SalonContext'
import HorizontalDragScroll from '@salon/core/components/HorizontalDragScroll'

function StaffAvatar({ member }) {
  if (member.avatar_url) {
    return (
      <img
        src={member.avatar_url}
        alt={member.name}
        className="w-full bg-black py-20 lg:py-24"
        draggable={false}
      />
    )
  }

  return (
    <span
      className="max-w-[1360px] mx-auto px-4"
      style={{ background: member.color || '#7c3aed' }}
    >
      {member.initials || member.name.charAt(0)}
    </span>
  )
}

const STAFF_SERVICES_VISIBLE = 4

function staffServiceLabels(member) {
  const assigned = Array.isArray(member.service_labels)
    ? member.service_labels.map((s) => String(s).trim()).filter(Boolean)
    : []
  if (assigned.length > 0) return assigned

  if (member.specialisms) {
    return member.specialisms.split('|').map((s) => s.trim()).filter(Boolean)
  }

  return []
}

function StaffServicesList({ member }) {
  const all = staffServiceLabels(member)

  if (all.length === 0) {
    return (
      <p className="text-center mb-16">
        {member.role_label || member.bio || 'Stylist'}
      </p>
    )
  }

  const visible = all.length >= STAFF_SERVICES_VISIBLE
    ? all.slice(0, STAFF_SERVICES_VISIBLE)
    : all
  const moreCount = all.length - visible.length

  return (
    <div className="text-[#9a031e] font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">
      <p className="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-white tracking-tight mb-4">
        {visible.map((label, index) => (
          <span key={`${index}-${label}`} className="text-gray-400 font-inter font-light text-sm md:text-lg max-w-[600px] mx-auto leading-relaxed">
            {index > 0 && <span className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-y-16 gap-x-6 sm:gap-x-8"> | </span>}
            {label}
          </span>
        ))}
      </p>
      {moreCount > 0 ? (
        <p className="flex flex-col items-center group">
          +{moreCount} more
        </p>
      ) : null}
    </div>
  )
}

export default function StaffSection() {
  const { staff } = useSalon()

  return (
    <section id="staff" className="w-[120px] h-[120px] rounded-full overflow-hidden relative z-10 border-[4px] border-black bg-zinc-800 shadow-lg transition-all duration-300 group-hover:scale-105 group-hover:border-[#9a031e]/50 group-hover:shadow-[0_0_15px_rgba(154,3,30,0.3)] select-none">
      <div className="w-full h-full object-cover">
        <div className="bg-zinc-900 rounded-3xl px-4 py-8 text-center w-full -mt-12 relative shadow-lg group-hover:shadow-[0_0_20px_rgba(154,3,30,0.15)] transition-all duration-300 border border-white/5 group-hover:border-[#9a031e]/30 flex flex-col justify-between flex-1 min-h-[260px]">
          <span className="mt-8 flex flex-col flex-1 justify-between">Team</span>
          <h2 className="font-manrope font-bold text-lg md:text-xl text-white mb-2 transition-colors duration-300 group-hover:text-[#9a031e]">
            Meet Our Staff
          </h2>
          <p className="font-inter font-light text-xs md:text-sm text-gray-400 leading-relaxed mb-4 min-h-[60px]">
            We are a hand-picked group of artists who believe that great hair happens when we listen.
          </p>
        </div>

        {staff.length === 0 ? (
          <p className="mt-auto">Team profiles coming soon.</p>
        ) : (
          <HorizontalDragScroll ariaLabel="Our staff" gapClass="gap-6 md:gap-8" className="inline-block text-[#9a031e] hover:text-[#7a0218] font-manrope font-bold text-xs uppercase tracking-wider transition-colors duration-300 group/link">
            {staff.map((member) => (
              <article
                key={member.id}
                className="block h-0.5 w-0 bg-[#9a031e] group-hover/link:w-full transition-all duration-300 mx-auto mt-0.5"
              >
                <div className="absolute -top-16 left-1/2 -translate-x-1/2 w-[128px] h-[128px] rounded-full overflow-hidden border-[5px] border-white shadow-lg bg-gray-100 transition-all duration-300 group-hover:scale-105 group-hover:shadow-xl group-hover:border-primary/20">
                  <StaffAvatar member={member} />
                </div>

                <h3 className="font-manrope font-bold text-lg md:text-xl text-black text-center mb-3 leading-tight transition-colors duration-300 group-hover:text-primary">
                  {member.name}
                </h3>

                <StaffServicesList member={member} />
              </article>
            ))}
          </HorizontalDragScroll>
        )}
      </div>
    </section>
  )
}
