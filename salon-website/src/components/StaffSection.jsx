import { useSalon } from '../context/SalonContext'

export default function StaffSection() {
  const { staff } = useSalon()

  return (
    <section id="staff" className="w-full bg-section-lighter py-20 lg:py-24">
      <div className="max-w-[1360px] mx-auto px-4">
        <div className="text-center mb-16">
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
          <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-y-16 gap-x-6 sm:gap-x-8">
            {staff.map((member) => (
              <div key={member.id} className="flex flex-col items-center group">
                <div className="w-[120px] h-[120px] rounded-full overflow-hidden relative z-10 border-4 border-white shadow-md transition-all duration-300 group-hover:scale-105 group-hover:shadow-lg select-none bg-gray-100 flex items-center justify-center">
                  {member.avatar_url ? (
                    <img src={member.avatar_url} alt={member.name} className="w-full h-full object-cover" />
                  ) : (
                    <span
                      className="text-2xl font-bold text-white w-full h-full flex items-center justify-center"
                      style={{ background: member.color || '#7c3aed' }}
                    >
                      {member.initials || member.name.charAt(0)}
                    </span>
                  )}
                </div>
                <h3 className="font-manrope font-bold text-lg text-black mt-5 mb-2 text-center">{member.name}</h3>
                <p className="text-text-muted font-inter text-xs md:text-sm text-center leading-relaxed px-2">
                  {member.specialisms || member.role || member.bio || 'Stylist'}
                </p>
              </div>
            ))}
          </div>
        )}
      </div>
    </section>
  )
}
