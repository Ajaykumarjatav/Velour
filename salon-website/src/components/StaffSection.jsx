import { useSalon } from '../context/SalonContext'
import BookButton from './BookButton'
import HorizontalDragScroll from './HorizontalDragScroll'

function StaffAvatar({ member }) {
  if (member.avatar_url) {
    return (
      <img
        src={member.avatar_url}
        alt={member.name}
        className="w-full h-full object-cover"
        draggable={false}
      />
    )
  }

  return (
    <span
      className="text-3xl font-bold text-white w-full h-full flex items-center justify-center"
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
      <p className="flex-1 text-text-muted font-inter text-xs md:text-sm text-center leading-relaxed px-1 min-h-[5rem]">
        {member.role_label || member.bio || 'Stylist'}
      </p>
    )
  }

  const visible = all.length >= STAFF_SERVICES_VISIBLE
    ? all.slice(0, STAFF_SERVICES_VISIBLE)
    : all
  const moreCount = all.length - visible.length

  return (
    <div className="flex-1 flex flex-col items-center justify-start min-h-[5rem] px-1 w-full">
      <p className="text-text-muted font-inter text-xs md:text-sm text-center leading-relaxed">
        {visible.map((label, index) => (
          <span key={`${index}-${label}`} className="inline whitespace-normal">
            {index > 0 && <span className="text-text-muted/45"> | </span>}
            {label}
          </span>
        ))}
      </p>
      {moreCount > 0 ? (
        <p className="text-[10px] md:text-xs text-text-muted/55 mt-2 text-center group-hover:text-primary/70 transition-colors duration-300">
          +{moreCount} more
        </p>
      ) : null}
    </div>
  )
}

export default function StaffSection() {
  const { staff } = useSalon()

  return (
    <section id="staff" className="w-full bg-section-lighter py-20 lg:py-24 overflow-hidden">
      <div className="max-w-[1360px] mx-auto px-4 min-w-0">
        <div className="text-center mb-12 md:mb-16">
          <span className="text-primary font-manrope font-semibold text-sm uppercase tracking-widest block mb-2">Team</span>
          <h2 className="font-manrope font-extrabold text-3xl md:text-[45px] md:leading-[55px] text-black tracking-tight mb-4">
            Meet Our Staff
          </h2>
          <p className="text-text-muted font-inter font-light text-sm md:text-lg max-w-[600px] mx-auto leading-relaxed">
            We are a hand-picked group of artists who believe that great hair happens when we listen.
          </p>
        </div>

        {staff.length === 0 ? (
          <p className="text-center text-text-muted">Team profiles coming soon.</p>
        ) : (
          <HorizontalDragScroll ariaLabel="Our staff" gapClass="gap-6 md:gap-8" className="pt-16 pb-4">
            {staff.map((member) => (
              <article
                key={member.id}
                className="group shrink-0 snap-start w-[240px] sm:w-[252px] min-h-[320px] relative flex flex-col items-center bg-white rounded-2xl shadow-[0_8px_30px_rgba(0,0,0,0.08)] pt-20 pb-8 px-6 mt-16 cursor-pointer transition-all duration-300 ease-out hover:-translate-y-2 hover:shadow-[0_18px_50px_rgba(0,0,0,0.14)] hover:ring-1 hover:ring-primary/15"
              >
                <div className="absolute -top-16 left-1/2 -translate-x-1/2 w-[128px] h-[128px] rounded-full overflow-hidden border-[5px] border-white shadow-lg bg-gray-100 transition-all duration-300 group-hover:scale-105 group-hover:shadow-xl group-hover:border-primary/20">
                  <StaffAvatar member={member} />
                </div>

                <h3 className="font-manrope font-bold text-lg md:text-xl text-black text-center mb-3 leading-tight transition-colors duration-300 group-hover:text-primary">
                  {member.name}
                </h3>

                <StaffServicesList member={member} />

                <div className="mt-auto pt-6 shrink-0" onMouseDown={(e) => e.stopPropagation()}>
                  <BookButton
                    as="a"
                    className="inline-block text-primary group-hover:text-primary-dark font-manrope font-bold text-xs md:text-sm uppercase tracking-[0.12em] transition-all duration-300 group-hover:tracking-[0.14em]"
                  >
                    Book Appointment
                  </BookButton>
                </div>
              </article>
            ))}
          </HorizontalDragScroll>
        )}
      </div>
    </section>
  )
}
