import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const themeIds = process.argv.slice(2)
if (!themeIds.length) {
  console.error('Usage: node fix-const-assets.mjs <theme-id> [...]')
  process.exit(1)
}

function ensureImport(content, importLine) {
  if (content.includes(importLine)) return content
  const match = content.match(/^import .+$/m)
  if (match) return content.replace(match[0], `${match[0]}\n${importLine}`)
  return `${importLine}\n\n${content}`
}

function fixFile(filePath) {
  let content = fs.readFileSync(filePath, 'utf8')
  let changed = false

  const before = content
  content = content
    .replace(/\bicon: "\/assets\/([^"]+)"/g, "iconFile: '$1'")
    .replace(/\bavatar: "\/assets\/([^"]+)"/g, "avatarFile: '$1'")
    .replace(/\bimage: "\/assets\/([^"]+)"/g, "imageFile: '$1'")
    .replace(/\{ src: "\/assets\/([^"]+)", alt: /g, "{ srcFile: '$1', alt: ")
    .replace(/src=\{icon\}/g, 'src={assetUrl(`assets/${iconFile}`)}')
    .replace(/function AmenityCard\(\{ icon,/g, 'function AmenityCard({ iconFile,')
    .replace(/src=\{avatar\}/g, 'src={assetUrl(`assets/${avatarFile}`)}')
    .replace(/src=\{pkg\.image\}/g, 'src={assetUrl(`assets/${pkg.imageFile}`)}')
    .replace(/src=\{item\.image\}/g, 'src={assetUrl(`assets/${item.imageFile}`)}')
    .replace(/backgroundImage: "url\('\/assets\/([^']+)'\)"/g, "backgroundImage: `url(${assetUrl('assets/$1')})`")

  if (content !== before) {
    if (content.includes('assetUrl(')) {
      content = ensureImport(content, "import { assetUrl } from '@salon/core/lib/assetUrl'")
    }
    fs.writeFileSync(filePath, content)
    changed = true
  }
  return changed
}

for (const themeId of themeIds) {
  const dir = path.resolve(__dirname, `../themes/${themeId}/components`)
  for (const file of fs.readdirSync(dir)) {
    if (!file.endsWith('.jsx')) continue
    if (fixFile(path.join(dir, file))) {
      console.log(`Fixed assets in ${themeId}/${file}`)
    }
  }
}
