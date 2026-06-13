import TopBar from './components/TopBar'
import HeroSection from './components/HeroSection'
import StickyNav from './components/StickyNav'
import AboutSection from './components/AboutSection'
import ServicesSection from './components/ServicesSection'
import PackagesSection from './components/PackagesSection'
import RelaxationSection from './components/RelaxationSection'
import SpecialOfferBanner from './components/SpecialOfferBanner'
import StaffSection from './components/StaffSection'
import PremiumBanner from './components/PremiumBanner'
import LocationsSection from './components/LocationsSection'
import TestimonialsSection from './components/TestimonialsSection'
import FooterInfoCards from './components/FooterInfoCards'
import Footer from './components/Footer'

export default function MarketingSite() {
  return (
    <div className="w-full min-h-screen bg-white overflow-x-hidden">
      <TopBar />
      <HeroSection />
      <StickyNav />
      <AboutSection />
      <ServicesSection />
      <PackagesSection />
      <RelaxationSection />
      <SpecialOfferBanner />
      <StaffSection />
      <PremiumBanner />
      <LocationsSection />
      <TestimonialsSection />
      <FooterInfoCards />
      <Footer />
    </div>
  )
}
