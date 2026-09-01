import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const root = path.resolve(__dirname, '..')
const themesDir = path.join(root, 'themes')
const sourceDir = 'D:/projects/ashu/saloon/backup-files/images'

const brandFiles = [
  { src: 'dark_theme-removebg.png', dest: 'easygrox-logo.png' },
  { src: 'easygrox_logo-removebg-preview.png', dest: 'easygrox-icon.png' },
]

const themes = fs.readdirSync(themesDir).filter((name) =>
  fs.statSync(path.join(themesDir, name)).isDirectory(),
)

for (const theme of themes) {
  const assetsDir = path.join(themesDir, theme, 'public', 'assets')
  fs.mkdirSync(assetsDir, { recursive: true })

  for (const { src, dest } of brandFiles) {
    const from = path.join(sourceDir, src)
    if (!fs.existsSync(from)) {
      console.warn(`Missing brand file: ${from}`)
      continue
    }
    fs.copyFileSync(from, path.join(assetsDir, dest))
    console.log(`Copied ${dest} -> ${theme}/public/assets/`)
  }
}

console.log('Brand logos synced to all themes.')
