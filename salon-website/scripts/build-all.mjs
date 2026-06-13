import { spawnSync } from 'child_process'
import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const root = path.resolve(__dirname, '..')
const themesDir = path.join(root, 'themes')
const publicWebsite = path.resolve(__dirname, '../../public/website')

const themes = fs.readdirSync(themesDir).filter((name) =>
  fs.statSync(path.join(themesDir, name)).isDirectory()
)

function resolveAssetRoot() {
  if (process.env.STOREFRONT_ASSET_BASE) {
    return process.env.STOREFRONT_ASSET_BASE
  }

  const adminEnv = path.resolve(__dirname, '../../.env')
  if (fs.existsSync(adminEnv)) {
    const match = fs.readFileSync(adminEnv, 'utf8').match(/^STOREFRONT_ASSET_BASE=(.+)$/m)
    if (match?.[1]) {
      return match[1].trim().replace(/^["']|["']$/g, '')
    }
  }

  return '/website/'
}

const assetRoot = resolveAssetRoot().replace(/\/?$/, '/')

let failed = false

for (const theme of themes) {
  const storefrontBase = `${assetRoot}${theme}/`
  console.log(`\n=== Building theme: ${theme} (base: ${storefrontBase}) ===`)
  const result = spawnSync('npx', ['vite', 'build'], {
      cwd: root,
      stdio: 'inherit',
      shell: true,
      env: {
        ...process.env,
        VITE_BUILD_THEME: theme,
        VITE_STOREFRONT_BASE: storefrontBase,
      },
    },
  )
  if (result.status !== 0) {
    failed = true
    console.error(`Build failed for ${theme}`)
  }
}

const glowRoseDir = path.join(publicWebsite, 'glow-rose')
if (fs.existsSync(path.join(glowRoseDir, 'index.html'))) {
  for (const entry of fs.readdirSync(glowRoseDir)) {
    fs.cpSync(path.join(glowRoseDir, entry), path.join(publicWebsite, entry), {
      recursive: true,
      force: true,
    })
  }
  console.log('\nLegacy public/website/ synced from glow-rose build.')
}

if (failed) process.exit(1)
console.log('\nAll theme builds completed.')
