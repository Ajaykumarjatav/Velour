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
      // Updated borders to dark mode, and gave the sticky state a sleek black translucent blur
      className={`w-full z-50 transition-all duration-300 border-b border-white/5
        ${
          isSticky
            ? "fixed top-0 left-0 right-0 shadow-[0_4px_30px_rgba(0,0,0,0.5)] bg-black/90 backdrop-blur-md py-1"
            : "relative bg-black py-3"
        }`}
    >
      <div className="max-w-[1360px] mx-auto px-4">
        {/* Mobile Navbar Header */}
        <div className="flex items-center justify-between py-2 lg:hidden">
          {/* Changed text-black to text-white */}
          <span className="font-manrope font-bold text-lg text-white tracking-wide uppercase">
            Menu
          </span>
          <button
            onClick={() => setMenuOpen(!menuOpen)}
            // Changed hover background and focus ring to match the dark/red theme
            className="p-2.5 rounded-full hover:bg-white/10 transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-[#9a031e]"
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
              // Changed icon color to white
              className="text-white"
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
                  // Added 'group' class here so the group-hover in the underline span works
                  className={`group relative py-2.5 lg:py-4 px-4 font-manrope font-semibold text-xs tracking-wider uppercase transition-all duration-300 w-full lg:w-auto text-center rounded-lg lg:rounded-none
                    ${
                      isActive
                        ? // Changed mobile active background to red tint
                          "text-[#9a031e] bg-[#9a031e]/10 lg:bg-transparent"
                        : // Changed inactive text to gray-400 and hover states to dark mode
                          "text-gray-400 hover:text-[#9a031e] hover:bg-white/5 lg:hover:bg-transparent"
                    }
                  `}
                >
                  <span className="relative z-10">{link.label}</span>
                  {/* Underline for Desktop */}
                  <span
                    // Changed bg-primary to bg-[#9a031e]
                    className={`absolute bottom-0 left-0 right-0 h-0.5 bg-[#9a031e] rounded-full transition-all duration-300 hidden lg:block
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
