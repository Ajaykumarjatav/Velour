import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const root = path.resolve(__dirname, '..')
const sourceRoot = 'D:/projects/ashu/saloon/Front-web'
const glowRoseSrc = path.join(root, 'themes/glow-rose/src')

const themes = [
  { id: 'beauty', source: 'salon-beauty/salon-beauty', port: 5174 },
  { id: 'nail', source: 'salon-nail/salon-nail', port: 5175 },
  { id: 'tattoo', source: 'salon-tattoo/salon-tattoo', port: 5176 },
  { id: 'mackup', source: 'salon-mackup/salon-mackup', port: 5177 },
  { id: 'pet-grooming', source: 'salon-petgrooming-main/salon-petgrooming-main', port: 5178 },
  { id: 'spa', source: 'salon-spa-main/salon-spa-main', port: 5179 },
]

const appJsx = `import { SalonProvider, useSalon } from '@salon/core/context/SalonContext'
import SalonSiteShell from '@salon/core/components/SalonSiteShell'
import BookingFlow from '@salon/core/components/BookingFlow'
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

function MarketingSite() {
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

function SalonApp() {
  const { view } = useSalon()

  if (view === 'booking') {
    return (
      <SalonSiteShell>
        <BookingFlow />
      </SalonSiteShell>
    )
  }

  return (
    <SalonSiteShell>
      <MarketingSite />
    </SalonSiteShell>
  )
}

export default function App() {
  return (
    <SalonProvider>
      <SalonApp />
    </SalonProvider>
  )
}
`

function cp(src, dest) {
  fs.mkdirSync(path.dirname(dest), { recursive: true })
  fs.cpSync(src, dest, { recursive: true, force: true })
}

function wireComponentImports(filePath) {
  let content = fs.readFileSync(filePath, 'utf8')
  const replacements = [
    [/"from '\.\.\/context\/SalonContext'"/g, "from '@salon/core/context/SalonContext'"],
    [/from '\.\.\/context\/SalonContext'/g, "from '@salon/core/context/SalonContext'"],
    [/from '\.\.\/lib\/assetUrl'/g, "from '@salon/core/lib/assetUrl'"],
    [/from '\.\/SalonLogo'/g, "from '@salon/core/components/SalonLogo'"],
    [/from '\.\/BookButton'/g, "from '@salon/core/components/BookButton'"],
    [/from '\.\/HorizontalDragScroll'/g, "from '@salon/core/components/HorizontalDragScroll'"],
  ]
  for (const [pattern, replacement] of replacements) {
    content = content.replace(pattern, replacement)
  }
  fs.writeFileSync(filePath, content)
}

function wireThemeComponents(themeComponentsDir, wiredComponentsDir) {
  const wiredFiles = fs.readdirSync(wiredComponentsDir).filter((f) => f.endsWith('.jsx'))
  for (const file of wiredFiles) {
    const wired = fs.readFileSync(path.join(wiredComponentsDir, file), 'utf8')
    const themePath = path.join(themeComponentsDir, file)
    if (!fs.existsSync(themePath)) {
      fs.writeFileSync(themePath, wired)
      continue
    }
    const theme = fs.readFileSync(themePath, 'utf8')
    // Preserve theme-specific class strings; apply dynamic logic from wired component.
    const merged = mergeComponent(theme, wired)
    fs.writeFileSync(themePath, merged)
  }
}

function mergeComponent(staticContent, wiredContent) {
  // Use wired (dynamic) component as base; overlay theme-only visual class differences from static.
  let result = wiredContent

  const staticClasses = [...staticContent.matchAll(/className="([^"]+)"/g)].map((m) => m[1])
  const wiredClasses = [...wiredContent.matchAll(/className="([^"]+)"/g)].map((m) => m[1])

  for (let i = 0; i < Math.min(staticClasses.length, wiredClasses.length); i++) {
    if (staticClasses[i] !== wiredClasses[i]) {
      result = result.replace(`className="${wiredClasses[i]}"`, `className="${staticClasses[i]}"`)
    }
  }

  // Theme inline style accents (e.g. tattoo borders)
  const staticStyles = [...staticContent.matchAll(/style=\{\{([\s\S]*?)\}\}/g)].map((m) => m[0])
  const wiredStyles = [...wiredContent.matchAll(/style=\{\{([\s\S]*?)\}\}/g)].map((m) => m[0])
  for (let i = 0; i < Math.min(staticStyles.length, wiredStyles.length); i++) {
    if (staticStyles[i] !== wiredStyles[i]) {
      result = result.replace(wiredStyles[i], staticStyles[i])
    }
  }

  return result
}

for (const theme of themes) {
  const srcPath = path.join(sourceRoot, theme.source)
  const dest = path.join(root, 'themes', theme.id)
  fs.mkdirSync(dest, { recursive: true })

  cp(path.join(srcPath, 'index.html'), path.join(dest, 'index.html'))
  cp(path.join(srcPath, 'public'), path.join(dest, 'public'))
  fs.mkdirSync(path.join(dest, 'src/components'), { recursive: true })

  cp(path.join(srcPath, 'src/index.css'), path.join(dest, 'src/index.css'))
  cp(path.join(glowRoseSrc, 'main.jsx'), path.join(dest, 'src/main.jsx'))
  fs.writeFileSync(path.join(dest, 'src/App.jsx'), appJsx)

  const themeComponents = path.join(srcPath, 'src/components')
  const destComponents = path.join(dest, 'src/components')
  cp(themeComponents, destComponents)
  wireThemeComponents(destComponents, path.join(glowRoseSrc, 'components'))

  for (const file of fs.readdirSync(destComponents)) {
    if (file.endsWith('.jsx')) wireComponentImports(path.join(destComponents, file))
  }

  const pkg = {
    name: `salon-theme-${theme.id}`,
    private: true,
    version: '1.0.0',
    type: 'module',
    scripts: { dev: 'vite', build: 'vite build', preview: 'vite preview' },
    dependencies: {
      '@salon/core': 'file:../../packages/core',
      react: '^19.1.0',
      'react-dom': '^19.1.0',
    },
    devDependencies: {
      '@tailwindcss/vite': '^4.1.7',
      '@types/react': '^19.1.2',
      '@types/react-dom': '^19.1.2',
      '@vitejs/plugin-react': '^4.4.1',
      tailwindcss: '^4.1.7',
      vite: '^6.3.5',
    },
  }
  fs.writeFileSync(path.join(dest, 'package.json'), JSON.stringify(pkg, null, 2) + '\n')
  fs.writeFileSync(
    path.join(dest, 'vite.config.js'),
    `import { createThemeConfig } from '../../scripts/vite.shared.js'\n\nexport default createThemeConfig('${theme.id}', ${theme.port})\n`,
  )
  const env = `VITE_STOREFRONT_BASE=/vellor/admin/website/${theme.id}/\nVITE_API_BASE=\nVITE_API_PROXY_TARGET=http://localhost/vellor/admin\nVITE_DEFAULT_SALON_SLUG=ak-salon\n`
  fs.writeFileSync(path.join(dest, '.env'), env)
  fs.writeFileSync(path.join(dest, '.env.example'), env)
  console.log(`Setup theme: ${theme.id}`)
}

console.log('All themes setup complete.')
