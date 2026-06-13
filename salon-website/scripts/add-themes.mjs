import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const root = path.resolve(__dirname, '..')
const sourceRoot = 'D:/projects/ashu/saloon/Front-web'
const glowRoseSrc = path.join(root, 'themes/glow-rose/src')

const themes = [
  { id: 'pet-grooming', source: 'salon-petgrooming-main/salon-petgrooming-main' },
  { id: 'spa', source: 'salon-spa-main/salon-spa-main' },
]

function cp(src, dest) {
  fs.mkdirSync(path.dirname(dest), { recursive: true })
  fs.cpSync(src, dest, { recursive: true, force: true })
}

function wireComponentImports(filePath) {
  let content = fs.readFileSync(filePath, 'utf8')
  const replacements = [
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

function mergeComponent(staticContent, wiredContent) {
  let result = wiredContent
  const staticClasses = [...staticContent.matchAll(/className="([^"]+)"/g)].map((m) => m[1])
  const wiredClasses = [...wiredContent.matchAll(/className="([^"]+)"/g)].map((m) => m[1])
  for (let i = 0; i < Math.min(staticClasses.length, wiredClasses.length); i++) {
    if (staticClasses[i] !== wiredClasses[i]) {
      result = result.replace(`className="${wiredClasses[i]}"`, `className="${staticClasses[i]}"`)
    }
  }
  const staticStyles = [...staticContent.matchAll(/style=\{\{([\s\S]*?)\}\}/g)].map((m) => m[0])
  const wiredStyles = [...wiredContent.matchAll(/style=\{\{([\s\S]*?)\}\}/g)].map((m) => m[0])
  for (let i = 0; i < Math.min(staticStyles.length, wiredStyles.length); i++) {
    if (staticStyles[i] !== wiredStyles[i]) {
      result = result.replace(wiredStyles[i], staticStyles[i])
    }
  }
  return result
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
    fs.writeFileSync(themePath, mergeComponent(theme, wired))
  }
}

for (const theme of themes) {
  const dest = path.join(root, 'themes', theme.id)
  if (fs.existsSync(path.join(dest, 'src', 'components'))) {
    console.log(`Skip existing theme: ${theme.id}`)
    continue
  }

  const srcPath = path.join(sourceRoot, theme.source)
  if (!fs.existsSync(srcPath)) {
    console.error(`Source not found: ${srcPath}`)
    process.exit(1)
  }

  fs.mkdirSync(dest, { recursive: true })
  cp(path.join(srcPath, 'public'), path.join(dest, 'public'))
  fs.mkdirSync(path.join(dest, 'src/components'), { recursive: true })
  cp(path.join(srcPath, 'src/index.css'), path.join(dest, 'src/index.css'))

  const themeComponents = path.join(srcPath, 'src/components')
  const destComponents = path.join(dest, 'src/components')
  cp(themeComponents, destComponents)

  for (const file of fs.readdirSync(destComponents)) {
    if (file.endsWith('.jsx')) wireComponentImports(path.join(destComponents, file))
  }

  console.log(`Added theme: ${theme.id}`)
}

console.log('New themes added.')
