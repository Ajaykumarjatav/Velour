export const ROUTES = {
  home: "/",
  pricing: "/pricing",
  features: "/features",
  appointments: "/features/appointments",
  staffMgmt: "/features/staff",
  pos: "/features/pos",
  website: "/features/website",
  analytics: "/features/analytics",
  marketing: "/features/marketing",
  clients: "/features/clients",
  retail: "/features/retail",
  reviews: "/features/reviews",
  multiLocation: "/features/multi-location",
  howItWorks: "/how-it-works",
  helpCenter: "/help-centre",
  gettingStarted: "/getting-started",
  blog: "/blog",
  about: "/about",
  contact: "/contact",
  signup: "/signup",
  login: "/login",
};

export const BIZ_SLUGS = {
  "Hair Salon": "hair-salon",
  "Barber Shop": "barber-shop",
  "Nail Studio": "nail-studio",
  "Spa & Massage": "spa-massage",
  "Tattoo Studio": "tattoo-studio",
  "Makeup Artist": "makeup-artist",
  "Pet Grooming": "pet-grooming",
};

export const BIZ_BY_SLUG = Object.fromEntries(
  Object.entries(BIZ_SLUGS).map(([name, slug]) => [slug, name])
);

export function routeFor(key, opts = {}) {
  if (key === "bizType" && opts.type) {
    const slug = BIZ_SLUGS[opts.type] || "hair-salon";
    return `/business/${slug}`;
  }

  return ROUTES[key] || ROUTES.home;
}
