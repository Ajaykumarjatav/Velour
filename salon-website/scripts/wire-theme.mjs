import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const themeId = process.argv[2]
if (!themeId) {
  console.error('Usage: node wire-theme.mjs <theme-id>')
  process.exit(1)
}

const componentsDir = path.resolve(__dirname, `../themes/${themeId}/components`)
const files = fs.readdirSync(componentsDir).filter((f) => f.endsWith('.jsx'))

const salonComponents = new Set([
  'TopBar', 'HeroSection', 'AboutSection', 'ServicesSection', 'PackagesSection',
  'StaffSection', 'TestimonialsSection', 'LocationsSection', 'FooterInfoCards', 'Footer',
])

const skipFiles = new Set(['HeroSection.jsx', 'TopBar.jsx'])

function fixBrokenClasses(content) {
  return content
    .replace(/bg-bg-\[(#[^\]]+)\]-light/g, 'bg-[$1]')
    .replace(/bg-bg-\[(#[^\]]+)\]/g, 'bg-[$1]/50')
    .replace(/hover:bg-\[#c98f8f\]-dark/g, 'hover:bg-[#b87f7f]')
    .replace(/hover:bg-\[#b5556e\]-dark/g, 'hover:bg-[#9a4860]')
    .replace(/hover:shadow-\[#c98f8f\]y\/20/g, 'hover:shadow-[#c98f8f]/20')
    .replace(/hover:bg-\[#b7846a\]-dark/g, 'hover:bg-[#a6755d]')
    .replace(/hover:shadow-\[#b7846a\]y\/20/g, 'hover:shadow-[#b7846a]/20')
    .replace(/hover:bg-\[#7a8b72\]-dark/g, 'hover:bg-[#6a7b62]')
    .replace(/hover:shadow-\[#7a8b72\]y\/20/g, 'hover:shadow-[#7a8b72]/20')
    .replace(/hover:text-\[#7a8b72\]-dark/g, 'hover:text-[#6a7b62]')
    .replace(/bg-deep-\[#7a8b72\]/g, 'bg-[#7a8b72]')
    .replace(/focus:ring-\[#F5ECE7\]-dark/g, 'focus:ring-[#c98f8f]')
    .replace(/focus:ring-\[#F8F8F8\]-dark/g, 'focus:ring-[#b5556e]')
}

function ensureImport(content, importLine) {
  if (content.includes(importLine)) return content
  const match = content.match(/^import .+$/m)
  if (match) {
    return content.replace(match[0], `${match[0]}\n${importLine}`)
  }
  return `${importLine}\n\n${content}`
}

function wireFile(file) {
  const filePath = path.join(componentsDir, file)
  let content = fs.readFileSync(filePath, 'utf8')
  const base = file.replace('.jsx', '')

  content = fixBrokenClasses(content)
  content = content.replace(/src="\/assets\/([^"]+)"/g, "src={assetUrl('assets/$1')}")
  content = content.replace(/url\(\/assets\/([^)]+)\)/g, "url(${assetUrl('assets/$1')})")

  if (content.includes('assetUrl(')) {
    content = ensureImport(content, "import { assetUrl } from '@salon/core/lib/assetUrl'")
  }
  if (salonComponents.has(base)) {
    content = ensureImport(content, "import { useSalon } from '@salon/core/context/SalonContext'")
  }
  if (content.includes('Book Your Transformation') || content.includes('BOOK NOW') || content.includes('BOOK APPOINTMENT')) {
    if (!content.includes('BookButton')) {
      content = ensureImport(content, "import BookButton from '@salon/core/components/BookButton'")
    }
  }
  if (base === 'TopBar' || base === 'Footer') {
    content = ensureImport(content, "import SalonLogo from '@salon/core/components/SalonLogo'")
  }
  if (base === 'StaffSection' || base === 'PackagesSection') {
    content = ensureImport(content, "import HorizontalDragScroll from '@salon/core/components/HorizontalDragScroll'")
  }

  if (salonComponents.has(base) && !content.includes('const { salon }')) {
    content = content.replace(
      /export default function (\w+)\(\) \{\s*/,
      'export default function $1() {\n  const { salon } = useSalon()\n  if (!salon) return null\n\n  ',
    )
  }

  // Replace static book links with BookButton (including closing tag)
  content = content.replace(
    /<a\s+href="#services"([^>]*)>([\s\S]*?)<\/a>/g,
    (match, attrs, inner) => {
      if (!inner.includes('Book Your Transformation') && !inner.includes('BOOK NOW')) return match
      const classMatch = attrs.match(/className="([^"]+)"/)
      const cls = classMatch?.[1] || 'inline-flex items-center justify-center gap-2.5 bg-[#c98f8f] hover:bg-[#b87f7f] text-white font-semibold text-sm md:text-base rounded-full px-8 py-4 transition-all duration-300'
      return `<BookButton className="${cls}">${inner}</BookButton>`
    },
  )

  fs.writeFileSync(filePath, content)
  console.log(`Wired ${themeId}/${file}`)
}

for (const file of files) {
  if (skipFiles.has(file)) continue
  wireFile(file)
}

console.log(`Done (${[...skipFiles].join(', ')} skipped — wire manually).`)
