import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'
import { themeAssets } from '../src/themeTokens.js'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const root = path.resolve(__dirname, '..')
const sourceDir = path.join(root, 'themes/glow-rose/components')

const dynamicFiles = [
  'ServicesSection.jsx',
  'StaffSection.jsx',
  'TestimonialsSection.jsx',
  'PackagesSection.jsx',
  'LocationsSection.jsx',
]

const themes = fs.readdirSync(path.join(root, 'themes')).filter((name) => {
  if (name === 'glow-rose') return false
  return fs.statSync(path.join(root, 'themes', name)).isDirectory()
})

function buildPackageImagesBlock(files) {
  const lines = files.map((f) => `  assetUrl('assets/${f}'),`)
  return `const packageImages = [\n${lines.join('\n')}\n]`
}

function buildLocationGalleryBlock(files) {
  const lines = files.map((f) => `  assetUrl('assets/${f}'),`)
  return `const locationGallery = [\n${lines.join('\n')}\n]`
}

function patchThemeContent(content, themeId) {
  const assets = themeAssets[themeId] || themeAssets['glow-rose']

  content = content.replace(
    /const packageImages = \[[\s\S]*?\]/,
    buildPackageImagesBlock(assets.packageImages),
  )

  content = content.replace(
    /const locationGallery = \[[\s\S]*?\]/,
    buildLocationGalleryBlock(assets.locationGallery),
  )

  content = content.replace(
    /assetUrl\('assets\/noun-hair-cut-6384205 1\.png'\)/g,
    `assetUrl('assets/${assets.serviceIcon}')`,
  )

  return content
}

for (const themeId of themes) {
  const destDir = path.join(root, 'themes', themeId, 'components')
  fs.mkdirSync(destDir, { recursive: true })

  for (const file of dynamicFiles) {
    const src = path.join(sourceDir, file)
    if (!fs.existsSync(src)) {
      console.warn(`Missing source: ${file}`)
      continue
    }
    let content = fs.readFileSync(src, 'utf8')
    content = patchThemeContent(content, themeId)
    fs.writeFileSync(path.join(destDir, file), content)
    console.log(`Synced ${themeId}/${file}`)
  }
}

console.log('Dynamic components synced from glow-rose.')
