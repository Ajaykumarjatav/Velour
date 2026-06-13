import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const themesDir = path.resolve(__dirname, '../themes')
const themes = fs.readdirSync(themesDir).filter((name) =>
  fs.statSync(path.join(themesDir, name)).isDirectory()
)

const removeRelPaths = [
  'package.json',
  'package-lock.json',
  'vite.config.js',
  'index.html',
  '.env',
  '.env.example',
  'src/main.jsx',
  'src/App.jsx',
]

for (const theme of themes) {
  const root = path.join(themesDir, theme)
  for (const rel of removeRelPaths) {
    const target = path.join(root, rel)
    if (fs.existsSync(target)) {
      fs.rmSync(target, { recursive: true, force: true })
      console.log(`Removed ${theme}/${rel}`)
    }
  }
  const nm = path.join(root, 'node_modules')
  if (fs.existsSync(nm)) {
    fs.rmSync(nm, { recursive: true, force: true })
    console.log(`Removed ${theme}/node_modules`)
  }
}

console.log('Per-theme duplicate setup removed. Use root salon-website only.')
