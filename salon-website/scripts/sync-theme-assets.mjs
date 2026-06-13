import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const themesDir = path.resolve(__dirname, '../themes')
const publicRoot = path.resolve(__dirname, '../public/themes')

const themes = fs.readdirSync(themesDir).filter((name) =>
  fs.statSync(path.join(themesDir, name)).isDirectory()
)

for (const theme of themes) {
  const srcPublic = path.join(themesDir, theme, 'public')
  const dest = path.join(publicRoot, theme)
  if (!fs.existsSync(srcPublic)) continue
  fs.mkdirSync(dest, { recursive: true })
  fs.cpSync(srcPublic, dest, { recursive: true, force: true })
  console.log(`Synced assets: ${theme}`)
}

console.log('Theme public assets ready under public/themes/')
