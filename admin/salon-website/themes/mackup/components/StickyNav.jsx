import { useState, useEffect } from "react";

const navLinks = [
  { label: "Who We are", href: "#about" },
  { label: "Services", href: "#services" },
  { label: "Packages", href: "#packages" },
  { label: "Amenities", href: "#amenities" },
  { label: "Our Staff", href: "#staff" },
  { label: "Reach us", href: "#locations" },
  { label: "Testimonials", href: "#testimonials" },
];

export default function StickyNav() {
  const [active, setActive] = useState("Who We are");
  const [isSticky, setIsSticky] = useState(false);
  const [menuOpen, setMenuOpen] = useState(false);

  useEffect(() => {
    const handleScroll = () => {
      setIsSticky(window.scrollY > 120);
    };
    window.addEventListener("scroll", handleScroll);
    return () => window.removeEventListener("scroll", handleScroll);
  }, []);

  return (
    <nav
      className={`w-full z-50 transition-all duration-300 border-b border-[#F2EBE8]
        ${
          isSticky
            ? "fixed top-0 left-0 right-0 shadow-lg bg-[#F2EBE8]/95 backdrop-blur-md py-1"
            : "relative bg-section-lightest py-3"
        }`}
    >
      <div className="max-w-[1360px] mx-auto px-4">
        {/* Mobile Navbar Header */}
        <div className="flex items-center justify-between py-2 lg:hidden">
          <span className="font-manrope font-bold text-lg text-black tracking-wide uppercase">
            Menu
          </span>
          <button
            onClick={() => setMenuOpen(!menuOpen)}
            className="p-2.5 rounded-full hover:bg-gray-100 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#F2EBE8]-dark"
            aria-label="Toggle menu"
            aria-expanded={menuOpen}
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              width="24"
              height="24"
              viewBox="0 0 24 24"
              fill="none"
              stroke="currentColor"
              strokeWidth="2.5"
              className="text-black"
            >
              {menuOpen ? (
                <path
                  d="M18 6L6 18M6 6l12 12"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                />
              ) : (
                <>
                  <line x1="3" y1="6" x2="21" y2="6" strokeLinecap="round" />
                  <line x1="3" y1="12" x2="21" y2="12" strokeLinecap="round" />
                  <line x1="3" y1="18" x2="21" y2="18" strokeLinecap="round" />
                </>
              )}
            </svg>
          </button>
        </div>

        {/* Navigation Links - Desktop & Mobile Drawer */}
        <div
          className={`
            ${menuOpen ? "flex max-h-[500px] opacity-100 py-4" : "hidden max-h-0 opacity-0 lg:max-h-none lg:opacity-100"} 
            lg:flex flex-col lg:flex-row items-center justify-between gap-2 lg:gap-1 lg:py-1 transition-all duration-300 ease-in-out overflow-hidden lg:overflow-visible
          `}
        >
          <div className="flex flex-col lg:flex-row items-center w-full lg:justify-center gap-1.5 lg:gap-8">
            {navLinks.map((link) => {
              const isActive = active === link.label;
              return (
                <a
                  key={link.label}
                  href={link.href}
                  onClick={() => {
                    setActive(link.label);
                    setMenuOpen(false);
                  }}
                  className={`
                    relative py-2.5 lg:py-4 px-4 font-manrope font-semibold text-xs tracking-wider uppercase transition-all duration-300 w-full lg:w-auto text-center rounded-lg lg:rounded-none
                    ${
                      isActive
                        ? "text-primary bg-primary/5 lg:bg-transparent"
                        : "text-black/70 hover:text-primary hover:bg-gray-55/30 lg:hover:bg-transparent"
                    }
                  `}
                >
                  <span className="relative z-10">{link.label}</span>
                  {/* Underline for Desktop */}
                  <span
                    className={`absolute bottom-0 left-0 right-0 h-0.5 bg-primary rounded-full transition-all duration-300 hidden lg:block
                      ${isActive ? "w-full opacity-100" : "w-0 opacity-0 group-hover:w-full"}
                    `}
                  />
                </a>
              );
            })}
          </div>
        </div>
      </div>
    </nav>
  );
}
