import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const themesDir = path.resolve(__dirname, '../themes')
const themes = fs.readdirSync(themesDir).filter((name) =>
  fs.statSync(path.join(themesDir, name)).isDirectory()
)

const marketingSiteTemplate = (imports, componentList) => `import TopBar from './components/TopBar'
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
`

for (const theme of themes) {
  const themeRoot = path.join(themesDir, theme)
  const componentsSrc = path.join(themeRoot, 'src', 'components')
  const componentsDest = path.join(themeRoot, 'components')

  if (fs.existsSync(componentsSrc) && !fs.existsSync(componentsDest)) {
    fs.cpSync(componentsSrc, componentsDest, { recursive: true })
  }

  const marketingPath = path.join(themeRoot, 'MarketingSite.jsx')
  if (!fs.existsSync(marketingPath)) {
    fs.writeFileSync(marketingPath, marketingSiteTemplate())
  }
}

console.log('Theme MarketingSite.jsx files ready.')
